@php
    $isAr = app()->getLocale() === 'ar';
@endphp
<x-error-shell
    code="404"
    :title="($isAr ? 'الصفحة غير موجودة' : 'Page not found') . ' · My-lawyer'"
    :heading="$isAr ? 'لم نجد هذه الصفحة' : 'We couldn\'t find that page'"
    :message="$isAr
        ? 'الرابط الذي اتبعته قد يكون قديماً أو مكسوراً، أو ربما لم تُنشأ هذه الصفحة بعد. لا توجد بيانات قد فُقدت — فقط هذا العنوان لا يوجد.'
        : 'The link you followed may be broken, or the page may have moved. Nothing of yours is lost — only this URL doesn\'t exist.'"
    primary-href="/"
    :primary-label="$isAr ? 'العودة إلى الصفحة الرئيسية' : 'Return home'"
    secondary-href="/faq"
    :secondary-label="$isAr ? 'الأسئلة الشائعة' : 'Browse FAQ'"
/>
