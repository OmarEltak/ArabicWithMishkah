@php
    $isAr = app()->getLocale() === 'ar';
    // Request ID lets users quote a correlation ID to support. Try common
    // sources; never leak stack-trace text from $exception to the user.
    $requestId = request()?->header('X-Request-Id')
        ?? request()?->header('X-Correlation-Id')
        ?? substr(bin2hex(random_bytes(6)), 0, 12);
@endphp
<x-error-shell
    code="500"
    :title="($isAr ? 'خطأ في الخادم' : 'Something went wrong') . ' · My-lawyer'"
    :heading="$isAr ? 'حدث خطأ غير متوقع' : 'Something went wrong on our end'"
    :message="$isAr
        ? 'واجهنا مشكلة في معالجة طلبك. تم تنبيه فريقنا تلقائياً ونحن نعمل على إصلاحها. لم يحدث أي ضرر لبياناتك.'
        : 'We hit an unexpected issue while handling your request. Our team has been notified automatically and is looking into it. None of your data is at risk.'"
    primary-href="/"
    :primary-label="$isAr ? 'العودة إلى الصفحة الرئيسية' : 'Return home'"
    secondary-href="mailto:support@my-lawyer.app?subject=Error+500+(ref+{{ $requestId }})"
    :secondary-label="$isAr ? 'إبلاغ الدعم' : 'Report to support'"
>
    <div style="margin-top:1.75rem;padding:0.85rem 1rem;background:var(--surface);border:1px solid var(--hairline);border-radius:8px;font-family:var(--f-mono);font-size:12px;color:var(--ink-mute);">
        <span style="color:var(--ink-soft);">{{ $isAr ? 'المرجع' : 'Reference' }}:</span>
        <span style="color:var(--ink);user-select:all;">{{ $requestId }}</span>
    </div>
</x-error-shell>
