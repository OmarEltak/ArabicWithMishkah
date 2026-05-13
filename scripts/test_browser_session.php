<?php

declare(strict_types=1);

use App\Models\ChatMessage;
use App\Models\ChatSession;
use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\User;
use App\Services\Contracts\ContractDraftingService;

// Wipe prior data so the browser test sees a clean session #1.
Contract::truncate();
ChatMessage::truncate();
ChatSession::truncate();

$user = User::where('email', 'test@example.com')->firstOrFail();
$user->forceFill(['locale' => 'ar', 'email_verified_at' => now()])->save();

$template = ContractTemplate::where('slug', 'eg-land-sale')->firstOrFail();

$svc = ContractDraftingService::fromConfig();

$intent = <<<'AR'
أحتاج إلى صياغة عقد بيع قطعة أرض في القاهرة، جمهورية مصر العربية:
- البائع: السيد أحمد محمد عبد الله، رقم قومي 12345678901234، مقيم في القاهرة.
- المشتري: السيدة فاطمة علي حسن، رقم قومي 98765432109876، مقيمة في الجيزة.
- موضوع البيع: قطعة أرض فضاء مساحتها 500 متر مربع، رقم القطعة 42 بالتجمع الخامس.
- الثمن: 2,000,000 جنيه مصري، يُدفع نصفه عند توقيع العقد والنصف الآخر عند التسجيل.
- تاريخ التحرير: اليوم.
- القانون الحاكم: القانون المدني المصري وقانون الشهر العقاري.
- الاختصاص القضائي: محاكم القاهرة.
AR;

echo "Starting session...\n";
$session = $svc->startSession($user, $template, $intent);
echo "Session #{$session->id}\n";

echo "Producing draft (skipping clarification thanks to max_clarifying_questions=0)...\n";
$contract = $svc->finalize($session->fresh());
echo "Contract #{$contract->id} status={$contract->status} length=".mb_strlen($contract->body)."\n";
echo "Session URL: http://my-lawyer.test/lawyer/chat?session={$session->id}\n";
echo "Contract URL: http://my-lawyer.test/lawyer/contracts\n";
