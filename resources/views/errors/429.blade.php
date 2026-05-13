@php
    $isAr = app()->getLocale() === 'ar';
    // The framework adds Retry-After on the response; surface it if present
    // to give the user a concrete number of seconds to wait.
    $retryAfter = null;
    if (isset($exception) && method_exists($exception, 'getHeaders')) {
        $headers = $exception->getHeaders();
        $retryAfter = (int) ($headers['Retry-After'] ?? 0) ?: null;
    }
    $detail = $retryAfter
        ? ($isAr
            ? 'يمكنك المحاولة مرة أخرى بعد '.$retryAfter.' ثانية.'
            : 'You can try again in '.$retryAfter.' seconds.')
        : ($isAr
            ? 'يرجى الانتظار قليلاً قبل المحاولة مرة أخرى.'
            : 'Please wait a moment before trying again.');
@endphp
<x-error-shell
    code="429"
    :title="($isAr ? 'الكثير من الطلبات' : 'Too many requests') . ' · My-lawyer'"
    :heading="$isAr ? 'بطّئ قليلاً' : 'Slow down a moment'"
    :message="($isAr
        ? 'لقد أرسلت طلبات كثيرة في وقت قصير. هذا الإجراء يحمي خدمتك وخدمات الآخرين على المنصة. '
        : 'You\'ve made too many requests in a short period. This safeguard protects your service and other users on the platform. ') . $detail"
    primary-href="/"
    :primary-label="$isAr ? 'العودة إلى الصفحة الرئيسية' : 'Return home'"
/>
