@php
    $isAr = app()->getLocale() === 'ar';
    $code = isset($exception) && method_exists($exception, 'getStatusCode')
        ? (string) $exception->getStatusCode()
        : '400';
@endphp
<x-error-shell
    :code="$code"
    :title="($isAr ? 'طلب غير مقبول' : 'Request error') . ' · My-lawyer'"
    :heading="$isAr ? 'تعذّر إكمال الطلب' : 'We couldn\'t complete that request'"
    :message="$isAr
        ? 'يبدو أن هناك مشكلة في الطلب الذي أرسلته. حاول تحديث الصفحة، أو ارجع إلى الصفحة السابقة وأعد المحاولة.'
        : 'There seems to be a problem with the request. Try refreshing the page, or go back and try again.'"
    primary-href="/"
    :primary-label="$isAr ? 'العودة إلى الصفحة الرئيسية' : 'Return home'"
/>
