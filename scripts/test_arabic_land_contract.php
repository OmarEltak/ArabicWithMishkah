<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\User;
use App\Services\Contracts\ContractDraftingService;

$user = User::where('email', 'test@example.com')->firstOrFail();
echo "Using user: {$user->email} (#{$user->id})\n";

/** @var ContractDraftingService $svc */
$svc = app(ContractDraftingService::class);

$intent = <<<AR
أحتاج إلى صياغة عقد بيع قطعة أرض في القاهرة، جمهورية مصر العربية، مع توضيح:
- البائع: السيد أحمد محمد عبد الله، رقم قومي 12345678901234، مقيم في القاهرة.
- المشتري: السيدة فاطمة علي حسن، رقم قومي 98765432109876، مقيمة في الجيزة.
- موضوع البيع: قطعة أرض فضاء مساحتها 500 متر مربع، رقم القطعة 42 بمنطقة التجمع الخامس بالقاهرة الجديدة.
- الثمن: 2,000,000 جنيه مصري (مليونان جنيه) يُدفع: نصفه عند توقيع العقد والنصف الآخر عند التسجيل.
- تاريخ التحرير: اليوم.
- القانون الحاكم: القانون المدني المصري وقانون الشهر العقاري.
- الاختصاص القضائي: محاكم القاهرة.
صِغ العقد كاملاً بالعربية، مع كل البنود اللازمة (الأطراف، المحل، الثمن، التسليم، الضمانات، التسجيل، فسخ العقد، الاختصاص).
AR;

echo "\nStarting session...\n";
$session = $svc->startSession($user, null, $intent);
echo "Session #{$session->id} created.\n";

// Skip the clarifying step on tight TPM budgets (Groq free tier = 6K TPM).
// The intent already includes every fact the drafter needs, so we go
// straight to finalize and let the tool-use loop pull legal authority.
echo "\nForcing finalize directly (skipping clarification to stay under TPM)...\n";
$contract = $svc->finalize($session->fresh());
echo "Contract #{$contract->id} status={$contract->status} length=".mb_strlen($contract->body)."\n";

$audit = $contract->citation_audit ?? [];
$summary = $audit['summary'] ?? null;
if ($summary) {
    echo "\nCitation audit: verified={$summary['verified']} uncertain={$summary['uncertain']} unverified={$summary['unverified']} (total {$summary['total']})\n";
}

echo "\n--- Draft preview (first 2,000 chars) ---\n";
echo mb_substr($contract->body, 0, 2000);
echo "\n--- /preview ---\n";

echo "\nView in UI: http://my-lawyer.test/lawyer/contracts (open contract #{$contract->id})\n";
