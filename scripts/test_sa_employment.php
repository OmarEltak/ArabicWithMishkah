<?php

declare(strict_types=1);

use App\Models\ContractTemplate;
use App\Models\User;
use App\Services\Contracts\ContractDraftingService;

$user = User::where('email', 'test@example.com')->firstOrFail();
$user->forceFill(['locale' => 'ar', 'email_verified_at' => now()])->save();

$template = ContractTemplate::where('slug', 'sa-employment')->firstOrFail();

$svc = ContractDraftingService::fromConfig();

$intent = <<<AR
أحتاج عقد عمل سعودي لشركتنا (شركة المشاريع المتقدمة المحدودة) لتعيين السيد عبدالله بن سعود الشمري:
- صاحب العمل: شركة المشاريع المتقدمة المحدودة
- العامل: عبدالله بن سعود الشمري
- المسمى الوظيفي: مدير تطوير الأعمال
- تاريخ بدء العمل: 1 ربيع الأول 1448 هـ
- الراتب الشهري الإجمالي: 25,000 ريال سعودي
- تخضع العلاقة لنظام العمل السعودي ولوائحه التنفيذية.
AR;

echo "Starting SA employment session...\n";
$session = $svc->startSession($user, $template, $intent);
echo "Session #{$session->id}\n";

echo "Drafting (SA profile + lookup_law forced)...\n";
$contract = $svc->finalize($session->fresh());
echo "Contract #{$contract->id} status={$contract->status} length=".mb_strlen($contract->body)."\n";

echo "\n=== body ===\n".$contract->body."\n";
echo "\nSession URL: http://my-lawyer.test/lawyer/chat?session={$session->id}\n";
