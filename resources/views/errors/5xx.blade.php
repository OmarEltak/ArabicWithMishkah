@php
    $isAr = app()->getLocale() === 'ar';
    $code = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (string) $exception->getStatusCode()
        : '500';
    $requestId = request()?->header('X-Request-Id')
        ?? substr(bin2hex(random_bytes(6)), 0, 12);
@endphp
<x-error-shell
    :code="$code"
    :title="($isAr ? 'خطأ في الخادم' : 'Server error') . ' · My-lawyer'"
    :heading="$isAr ? 'الخدمة غير متاحة مؤقتاً' : 'The service is temporarily unavailable'"
    :message="$isAr
        ? 'نواجه حالياً مشكلة من جانبنا. تم إخطار فريقنا، يرجى المحاولة مرة أخرى بعد قليل.'
        : 'We\'re experiencing an issue on our side. Our team has been notified — please try again in a moment.'"
    primary-href="/"
    :primary-label="$isAr ? 'العودة إلى الصفحة الرئيسية' : 'Return home'"
>
    <div style="margin-top:1.75rem;padding:0.85rem 1rem;background:var(--surface);border:1px solid var(--hairline);border-radius:8px;font-family:var(--f-mono);font-size:12px;color:var(--ink-mute);">
        <span style="color:var(--ink-soft);">{{ $isAr ? 'المرجع' : 'Reference' }}:</span>
        <span style="color:var(--ink);user-select:all;">{{ $requestId }}</span>
    </div>
</x-error-shell>
