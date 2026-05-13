@php
    $isAr = app()->getLocale() === 'ar';
@endphp
<x-error-shell
    code="401"
    :title="($isAr ? 'مطلوب تسجيل الدخول' : 'Sign in required') . ' · My-lawyer'"
    :heading="$isAr ? 'يلزم تسجيل الدخول' : 'Please sign in to continue'"
    :message="$isAr
        ? 'هذه الصفحة متاحة فقط للمستخدمين المسجلين. سجّل الدخول للوصول إلى لوحة التحكم، أو أنشئ حساباً جديداً.'
        : 'This page is available only to signed-in users. Sign in to access your dashboard, or create a new account if you don\'t have one yet.'"
    primary-href="/login"
    :primary-label="$isAr ? 'تسجيل الدخول' : 'Sign in'"
    secondary-href="/register"
    :secondary-label="$isAr ? 'إنشاء حساب' : 'Create account'"
/>
