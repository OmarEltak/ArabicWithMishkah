@php
    $isAr = app()->getLocale() === 'ar';
    $back = url()->previous() !== url()->current() ? url()->previous() : '/';
@endphp
<x-error-shell
    code="419"
    :title="($isAr ? 'انتهت صلاحية الجلسة' : 'Session expired') . ' · My-lawyer'"
    :heading="$isAr ? 'انتهت صلاحية الصفحة' : 'Your session has expired'"
    :message="$isAr
        ? 'لقد انقضى وقت طويل منذ آخر نشاط لك، لذلك أنهينا جلستك حفاظاً على أمان حسابك. لم يتم حفظ أي تغييرات لم تُرسل بعد — يمكنك تحديث الصفحة والمحاولة مرة أخرى.'
        : 'For your security, we ended your session after a period of inactivity. Any unsaved changes were not submitted — refresh the page and try again.'"
    :primary-href="$back"
    :primary-label="$isAr ? 'تحديث الصفحة' : 'Refresh and try again'"
    secondary-href="/login"
    :secondary-label="$isAr ? 'إعادة تسجيل الدخول' : 'Sign in again'"
/>
