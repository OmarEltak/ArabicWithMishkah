@php
    $isAr = app()->getLocale() === 'ar';
@endphp

{{-- Cookie consent banner. Egypt Personal Data Protection Law 151/2020
     requires informed consent before non-essential cookies. The banner
     stays hidden until JS confirms there's no stored choice — that
     prevents a flash for returning visitors. Choice is kept in
     localStorage under `cookie-consent` (`accepted` | `declined`).

     Read the choice from JS before firing any analytics:
       const c = localStorage.getItem('cookie-consent');
       if (c === 'accepted') { /* boot analytics */ }                       --}}
<div id="cookie-consent" hidden role="region" aria-live="polite"
     aria-label="{{ $isAr ? 'إشعار ملفات تعريف الارتباط' : 'Cookie notice' }}">
    <div class="cc-inner">
        <p class="cc-text">
            @if ($isAr)
                نستخدم ملفات تعريف الارتباط الأساسية لتشغيل الموقع، وملفات اختيارية لقياس الاستخدام وتحسين الخدمة.
                للمزيد، راجع <a href="{{ route('legal.privacy') }}">سياسة الخصوصية</a>.
            @else
                We use essential cookies to run the site, and optional ones to measure usage and improve the service.
                See our <a href="{{ route('legal.privacy') }}">Privacy Policy</a> for details.
            @endif
        </p>
        <div class="cc-actions">
            <button type="button" class="cc-btn cc-decline" data-cc-action="decline">
                {{ $isAr ? 'الأساسية فقط' : 'Essential only' }}
            </button>
            <button type="button" class="cc-btn cc-accept" data-cc-action="accept">
                {{ $isAr ? 'قبول الكل' : 'Accept all' }}
            </button>
        </div>
    </div>
</div>

<style>
    #cookie-consent {
        position: fixed; bottom: 1rem; left: 1rem; right: 1rem;
        z-index: 60;
        background: var(--canvas, #fff);
        color: var(--ink, #0B1626);
        border: 1px solid var(--hairline, #DCE2EA);
        border-radius: 12px;
        box-shadow: 0 12px 32px -12px rgba(15, 27, 45, 0.18);
        padding: 1rem 1.25rem;
        max-width: 760px;
        margin: 0 auto;
        font-family: var(--f-sans, 'Inter', system-ui, sans-serif);
    }
    :where(html[dir="rtl"]) #cookie-consent { font-family: var(--f-arabic, 'Tajawal', sans-serif); }
    #cookie-consent .cc-inner {
        display: flex; flex-wrap: wrap; align-items: center;
        gap: 1rem 1.5rem; justify-content: space-between;
    }
    #cookie-consent .cc-text {
        margin: 0; flex: 1 1 320px; min-width: 0;
        font-size: 14px; line-height: 1.55;
        color: var(--ink-soft, #3F4A5A);
    }
    #cookie-consent .cc-text a {
        color: var(--ink, #0B1626);
        border-bottom: 1px solid var(--ink-mute, #5C6677);
    }
    #cookie-consent .cc-text a:hover { border-bottom-color: var(--ink, #0B1626); }
    #cookie-consent .cc-actions { display: flex; gap: 0.5rem; flex-shrink: 0; }
    #cookie-consent .cc-btn {
        font-family: inherit;
        padding: 0.55rem 1rem;
        font-size: 13px; font-weight: 600;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s, color 0.15s, transform 0.1s;
    }
    #cookie-consent .cc-btn:active { transform: translateY(1px); }
    #cookie-consent .cc-decline {
        background: transparent;
        color: var(--ink-soft, #3F4A5A);
        border-color: var(--hairline, #DCE2EA);
    }
    #cookie-consent .cc-decline:hover {
        color: var(--ink, #0B1626);
        border-color: var(--ink-mute, #5C6677);
    }
    #cookie-consent .cc-accept {
        background: var(--navy, #0F2942); color: #fff;
        border-color: var(--navy, #0F2942);
    }
    #cookie-consent .cc-accept:hover { background: var(--navy-deep, #0A1E33); }
    @media (max-width: 520px) {
        #cookie-consent { padding: 0.85rem 1rem; }
        #cookie-consent .cc-actions { width: 100%; }
        #cookie-consent .cc-btn { flex: 1; }
    }
</style>

<script>
(function () {
    var KEY = 'cookie-consent';
    var banner = document.getElementById('cookie-consent');
    if (!banner) return;

    try {
        var existing = localStorage.getItem(KEY);
        if (existing === 'accepted' || existing === 'declined') return;
    } catch (e) {
        // Storage blocked (private mode / cookies disabled). Still show
        // the banner so the user has a clear notice; choice won't persist.
    }

    banner.hidden = false;

    banner.querySelectorAll('[data-cc-action]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var choice = btn.getAttribute('data-cc-action') === 'accept' ? 'accepted' : 'declined';
            try { localStorage.setItem(KEY, choice); } catch (e) { /* ignored */ }
            banner.hidden = true;
            // Fire a custom event so analytics (when added) can hook in.
            document.dispatchEvent(new CustomEvent('cookie-consent-change', { detail: { choice: choice } }));
        });
    });
})();
</script>
