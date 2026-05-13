<?php

declare(strict_types=1);

// In-PHP cosine over 33K chunks needs more than the 128MB default.
// Real fix: switch to pgvector (Postgres). Tactical fix: bump for now.
ini_set('memory_limit', '512M');

use App\Models\ContractTemplate;
use App\Models\User;
use App\Services\Contracts\ContractDraftingService;

$user = User::where('email', 'test@example.com')->firstOrFail();
$user->forceFill(['locale' => 'ar', 'email_verified_at' => now()])->save();

$template = ContractTemplate::where('slug', 'eg-shareholders-agreement')->firstOrFail();
echo "Template: {$template->name} [{$template->jurisdiction}/{$template->category}]\n";

$svc = ContractDraftingService::fromConfig();

$intent = <<<'AR'
أحتاج عقد شركاء بين السيد محمد سيف الدين والسيد طارق فهمي لتأسيس شركة "تك جلوبال للحلول الرقمية" — شركة ذات مسؤولية محدودة برأس مال قدره 1,000,000 جنيه مصري.
- محمد سيف الدين: حصة 60%
- طارق فهمي: حصة 40%
- النشاط: تطوير برمجيات وحلول تقنية
- تاريخ التأسيس: اليوم
- مكان النشاط الرئيسي: القاهرة
نريد بنود قياسية للحوكمة، حق الشفعة، عدم المنافسة، والتحكيم.
AR;

echo "Starting EG shareholders session...\n";
$session = $svc->startSession($user, $template, $intent);
echo "Session #{$session->id}\n";

echo "Drafting (companies-category retrieval + EG profile)...\n";
$contract = $svc->finalize($session->fresh());
echo "Contract #{$contract->id} status={$contract->status} length=".mb_strlen($contract->body)."\n";

echo "\n=== body ===\n".$contract->body."\n";
echo "\nSession URL: http://my-lawyer.test/lawyer/chat?session={$session->id}\n";
