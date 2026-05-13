<?php
$locale = app()->getLocale();
$isAr = $locale === 'ar';
$effectiveDate = '2026-05-10';
$canonical = url('/legal/aup');

$pageTitle = $isAr
    ? 'سياسة الاستخدام المقبول · My-lawyer'
    : 'Acceptable Use Policy · My-lawyer';
$pageDescription = $isAr
    ? 'سياسة الاستخدام المقبول لمنصة My-lawyer — الاستخدامات المحظورة، حدود المعدل، وعواقب الإخلال.'
    : 'Acceptable Use Policy for the My-lawyer platform — prohibited uses, rate limits, and consequences of breach.';

$sections = $isAr ? [
    ['id' => 'principle',           'title' => 'المبدأ العام'],
    ['id' => 'prohibited',          'title' => 'الاستخدامات المحظورة'],
    ['id' => 'professional',        'title' => 'الاستخدام المهني'],
    ['id' => 'rate-limits',         'title' => 'حدود الاستخدام'],
    ['id' => 'security-research',   'title' => 'الأبحاث الأمنية'],
    ['id' => 'reporting',           'title' => 'الإبلاغ عن الانتهاكات'],
    ['id' => 'consequences',        'title' => 'عواقب الإخلال'],
] : [
    ['id' => 'principle',           'title' => 'General Principle'],
    ['id' => 'prohibited',          'title' => 'Prohibited Uses'],
    ['id' => 'professional',        'title' => 'Professional Use'],
    ['id' => 'rate-limits',         'title' => 'Rate Limits'],
    ['id' => 'security-research',   'title' => 'Security Research'],
    ['id' => 'reporting',           'title' => 'Reporting Violations'],
    ['id' => 'consequences',        'title' => 'Consequences of Breach'],
];

$heading = $isAr ? 'سياسة الاستخدام المقبول' : 'Acceptable Use Policy';
$intro = $isAr
    ? 'هذه السياسة تحدد ما يمكنك وما لا يمكنك القيام به على منصة My-lawyer. تكمل هذه السياسة شروط الاستخدام وتعد جزءاً من العقد بيننا.'
    : 'This Policy sets out what you may and may not do on the My-lawyer platform. It supplements the Terms of Service and forms part of the contract between us.';
?>

<x-legal-layout
    :page-title="$pageTitle"
    :page-description="$pageDescription"
    :canonical="$canonical"
    :heading="$heading"
    :intro="$intro"
    :effective-date="$effectiveDate"
    :sections="$sections"
>

@if ($isAr)

<section id="principle">
    <h2><span class="num">١.</span> المبدأ العام</h2>
    <p>المنصة موجهة لمحامي الشركات والمستشارين القانونيين المرخصين لاستخدامها في الأعمال القانونية المشروعة. يحق لنا تعليق أي حساب يستخدمها لأغراض تتعارض مع هذا الغرض.</p>
</section>

<section id="prohibited">
    <h2><span class="num">٢.</span> الاستخدامات المحظورة</h2>
    <h3>المحتوى غير القانوني</h3>
    <ul>
        <li>إنتاج وثائق تنتهك القوانين المصرية أو قوانين الولاية القضائية المختارة.</li>
        <li>صياغة عقود تستخدم لتحقيق أغراض غير قانونية (التهرب الضريبي، تبييض الأموال، الاحتيال).</li>
        <li>إنتاج محتوى تشهيري، مهين، أو ينتهك حقوق الملكية الفكرية للغير.</li>
        <li>توليد وثائق احتيالية أو مزورة (عقود وهمية، توقيعات منسوخة).</li>
    </ul>

    <h3>الممارسة غير المرخصة للمحاماة</h3>
    <ul>
        <li>تقديم استشارات قانونية للغير دون الحصول على ترخيص محاماة سارٍ في الولاية القضائية المعنية.</li>
        <li>التظاهر بأن مخرجات المنصة هي رأي قانوني صادر عن محامٍ.</li>
    </ul>

    <h3>إساءة استخدام النموذج</h3>
    <ul>
        <li>محاولة استخراج بيانات تدريب نماذج الذكاء الاصطناعي.</li>
        <li>محاولة كسر حدود الأمان أو تجاوز إجراءات الرفض.</li>
        <li>استخدام حقن الأوامر (Prompt Injection) لتغيير سلوك النموذج لأغراض ضارة.</li>
        <li>إنشاء حسابات متعددة لتجاوز حدود الاستخدام.</li>
    </ul>

    <h3>الأمن وسلامة البنية التحتية</h3>
    <ul>
        <li>محاولات الاختراق، حقن SQL، XSS، أو أي هجوم آخر على البنية التحتية.</li>
        <li>تحميل برمجيات خبيثة أو فيروسات.</li>
        <li>إساءة استخدام أو إعادة بيع بيانات اعتماد API.</li>
        <li>هجمات الحرمان من الخدمة، الإفراط في إرسال الطلبات، أو استنفاد الموارد.</li>
    </ul>

    <h3>محتوى ضار</h3>
    <ul>
        <li>إنتاج محتوى يحرّض على الكراهية، العنف، أو التمييز.</li>
        <li>صياغة وثائق تتضمن استغلالاً للقاصرين أو محتوى جنسي صريح.</li>
        <li>إنتاج deepfakes، تزوير هوية، أو انتحال شخصية.</li>
    </ul>
</section>

<section id="professional">
    <h2><span class="num">٣.</span> الاستخدام المهني</h2>
    <p>إذا كنت محامياً ممارساً، فأنت مسؤول عن:</p>
    <ul>
        <li>الالتزام بقواعد آداب المهنة لنقابة المحامين المختصة.</li>
        <li>مراجعة كل مخرج قبل تسليمه للعميل أو إيداعه لدى الجهات الرسمية.</li>
        <li>إفصاح استخدام أدوات الذكاء الاصطناعي في عمليات الصياغة عند الاقتضاء.</li>
        <li>الحفاظ على سرية بيانات عملائك — لا تشارك بيانات شخصية حساسة بشكل غير ضروري في الاستفسارات.</li>
    </ul>
</section>

<section id="rate-limits">
    <h2><span class="num">٤.</span> حدود الاستخدام</h2>
    <p>تخضع الخطط لحدود معدل لحماية البنية التحتية وعدالة الاستخدام:</p>
    <table>
        <thead><tr><th>الخطة</th><th>المسودات شهرياً</th><th>طلبات API/دقيقة</th></tr></thead>
        <tbody>
            <tr><td>مجاني</td><td>٣</td><td>٢٠</td></tr>
            <tr><td>فردي</td><td>٢٥</td><td>٦٠</td></tr>
            <tr><td>شركة</td><td>٢٠٠</td><td>١٢٠</td></tr>
            <tr><td>مؤسسات</td><td>غير محدود (مع SLA)</td><td>قابل للتفاوض</td></tr>
        </tbody>
    </table>
    <p>تجاوز الحدود قد يؤدي إلى تعليق مؤقت، رفع الخطة تلقائياً، أو إلغاء الحساب في حالات الإفراط المتكرر.</p>
</section>

<section id="security-research">
    <h2><span class="num">٥.</span> الأبحاث الأمنية</h2>
    <p>نرحب بإبلاغات الباحثين الأمنيين بشكل مسؤول. أرسل النتائج إلى <a href="mailto:security@my-lawyer.test">security@my-lawyer.test</a> مع تفاصيل قابلة للتكرار. لن نتخذ إجراءات قانونية ضد الباحثين الذين يلتزمون بأعراف الإفصاح المسؤول (٩٠ يوماً قبل النشر العام، عدم استخراج بيانات حقيقية، عدم تعطيل الخدمة).</p>
</section>

<section id="reporting">
    <h2><span class="num">٦.</span> الإبلاغ عن الانتهاكات</h2>
    <p>إذا رأيت أو اشتبهت في انتهاك لهذه السياسة من قبل مستخدم آخر، أبلغ على: <a href="mailto:abuse@my-lawyer.test">abuse@my-lawyer.test</a>. نحقق في كل بلاغ ونتخذ الإجراءات المناسبة.</p>
</section>

<section id="consequences">
    <h2><span class="num">٧.</span> عواقب الإخلال</h2>
    <p>قد تتراوح عواقب إخلال هذه السياسة من:</p>
    <ul>
        <li>تحذير كتابي + إصلاح إلزامي.</li>
        <li>تعليق مؤقت للحساب.</li>
        <li>إنهاء الحساب نهائياً دون استرداد رسوم.</li>
        <li>الإحالة إلى الجهات الرسمية (نيابة، شرطة الإنترنت) في حالات التعدي القانوني.</li>
        <li>المطالبة المدنية بالتعويض عن الأضرار.</li>
    </ul>
    <p>الانتهاكات الجسيمة (المحتوى غير القانوني، استغلال القاصرين، هجمات الأمن) تؤدي إلى إنهاء فوري وإحالة قانونية بدون إنذار سابق.</p>
</section>

@else

<section id="principle">
    <h2><span class="num">1.</span> General Principle</h2>
    <p>The Platform is intended for licensed corporate counsel and legal professionals using it for lawful legal work. We may suspend any account using it for purposes inconsistent with this purpose.</p>
</section>

<section id="prohibited">
    <h2><span class="num">2.</span> Prohibited Uses</h2>
    <h3>Unlawful content</h3>
    <ul>
        <li>Producing documents that violate Egyptian law or the law of the chosen jurisdiction.</li>
        <li>Drafting contracts used to achieve unlawful purposes (tax evasion, money laundering, fraud).</li>
        <li>Producing defamatory, abusive, or third-party-IP-infringing content.</li>
        <li>Generating fraudulent or forged documents (sham contracts, copied signatures).</li>
    </ul>

    <h3>Unauthorized practice of law</h3>
    <ul>
        <li>Providing legal advice to third parties without holding a current bar licence in the relevant jurisdiction.</li>
        <li>Representing that Platform Output is a legal opinion issued by a lawyer.</li>
    </ul>

    <h3>Model abuse</h3>
    <ul>
        <li>Attempting to extract AI model training data.</li>
        <li>Attempting to bypass safety guards or refusal mechanisms.</li>
        <li>Using prompt injection to alter model behavior for harmful purposes.</li>
        <li>Creating multiple accounts to exceed usage caps.</li>
    </ul>

    <h3>Security and infrastructure integrity</h3>
    <ul>
        <li>Attempts at intrusion, SQL injection, XSS, or any other attack on the infrastructure.</li>
        <li>Uploading malware or viruses.</li>
        <li>Misuse or resale of API credentials.</li>
        <li>Denial-of-service attacks, request flooding, or resource exhaustion.</li>
    </ul>

    <h3>Harmful content</h3>
    <ul>
        <li>Producing content that incites hatred, violence, or discrimination.</li>
        <li>Drafting documents involving the exploitation of minors or sexually explicit content.</li>
        <li>Producing deepfakes, identity forgery, or impersonation.</li>
    </ul>
</section>

<section id="professional">
    <h2><span class="num">3.</span> Professional Use</h2>
    <p>If you are a practicing lawyer, you are responsible for:</p>
    <ul>
        <li>Compliance with the professional-conduct rules of the relevant bar association.</li>
        <li>Reviewing every Output before delivery to a client or filing with authorities.</li>
        <li>Disclosing the use of AI tools in the drafting process where required.</li>
        <li>Maintaining your clients' confidentiality — do not share unnecessary sensitive personal data in queries.</li>
    </ul>
</section>

<section id="rate-limits">
    <h2><span class="num">4.</span> Rate Limits</h2>
    <p>Plans are subject to rate limits to protect the infrastructure and ensure fair use:</p>
    <table>
        <thead><tr><th>Plan</th><th>Drafts / month</th><th>API calls / minute</th></tr></thead>
        <tbody>
            <tr><td>Free</td><td>3</td><td>20</td></tr>
            <tr><td>Solo</td><td>25</td><td>60</td></tr>
            <tr><td>Firm</td><td>200</td><td>120</td></tr>
            <tr><td>Enterprise</td><td>Unlimited (with SLA)</td><td>Negotiable</td></tr>
        </tbody>
    </table>
    <p>Exceeding limits may result in temporary suspension, automatic plan upgrade, or account cancellation in cases of repeated excess.</p>
</section>

<section id="security-research">
    <h2><span class="num">5.</span> Security Research</h2>
    <p>We welcome responsible-disclosure reports from security researchers. Send findings to <a href="mailto:security@my-lawyer.test">security@my-lawyer.test</a> with reproducible details. We do not pursue legal action against researchers who follow responsible-disclosure norms (90 days before public disclosure, no exfiltration of real data, no service disruption).</p>
</section>

<section id="reporting">
    <h2><span class="num">6.</span> Reporting Violations</h2>
    <p>If you observe or suspect a violation of this Policy by another user, report it to <a href="mailto:abuse@my-lawyer.test">abuse@my-lawyer.test</a>. We investigate every report and take appropriate action.</p>
</section>

<section id="consequences">
    <h2><span class="num">7.</span> Consequences of Breach</h2>
    <p>Consequences of breach may range from:</p>
    <ul>
        <li>Written warning plus mandatory remediation.</li>
        <li>Temporary account suspension.</li>
        <li>Permanent account termination without refund.</li>
        <li>Referral to authorities (prosecution, cybercrime police) in cases of legal violation.</li>
        <li>Civil claim for damages.</li>
    </ul>
    <p>Serious violations (unlawful content, exploitation of minors, security attacks) result in immediate termination and legal referral without prior warning.</p>
</section>

@endif

</x-legal-layout>
