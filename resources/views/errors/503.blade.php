@php
    $isAr = app()->getLocale() === 'ar';
    // Maintenance mode passes the optional `message` through to this view
    // via the exception (Laravel sets $exception->getMessage() when
    // `artisan down --message="..."` is used). Surface it where present.
    $maintenanceMessage = trim($exception?->getMessage() ?? '');
@endphp
<x-error-shell
    code="503"
    :title="($isAr ? 'صيانة مجدولة' : 'Down for maintenance') . ' · My-lawyer'"
    :heading="$isAr ? 'الخدمة في وضع الصيانة' : 'We\'ll be back shortly'"
    :message="$maintenanceMessage !== ''
        ? $maintenanceMessage
        : ($isAr
            ? 'نقوم بإجراء صيانة مجدولة أو نشر تحديث للنظام. لن يستغرق ذلك وقتاً طويلاً — يرجى المحاولة مرة أخرى بعد بضع دقائق.'
            : 'We\'re performing scheduled maintenance or rolling out an update. This won\'t take long — please try again in a few minutes.')"
    primary-href="/"
    :primary-label="$isAr ? 'المحاولة مرة أخرى' : 'Try again'"
/>
