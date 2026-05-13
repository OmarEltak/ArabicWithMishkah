@php
    $isAr = app()->getLocale() === 'ar';
    $defaultMsg = $isAr
        ? 'لا تملك صلاحية الوصول إلى هذا المورد. إذا كنت تعتقد أن هذا خطأ، يرجى التواصل مع مسؤول حسابك أو فريق الدعم.'
        : 'You don\'t have permission to access this resource. If you believe this is a mistake, please contact your account administrator or our support team.';
    $custom = trim($exception?->getMessage() ?? '');
    $finalMsg = $custom !== '' && $custom !== 'This action is unauthorized.' ? $custom : $defaultMsg;
@endphp
<x-error-shell
    code="403"
    :title="($isAr ? 'الوصول مرفوض' : 'Access denied') . ' · My-lawyer'"
    :heading="$isAr ? 'الوصول مرفوض' : 'Access denied'"
    :message="$finalMsg"
    primary-href="/"
    :primary-label="$isAr ? 'العودة إلى الصفحة الرئيسية' : 'Return home'"
    secondary-href="/login"
    :secondary-label="$isAr ? 'تسجيل الدخول بحساب آخر' : 'Sign in with another account'"
/>
