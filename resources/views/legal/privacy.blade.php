<?php
$locale = app()->getLocale();
$isAr = $locale === 'ar';
$effectiveDate = '2026-05-10';
$canonical = url('/legal/privacy');

$pageTitle = $isAr
    ? 'سياسة الخصوصية · My-lawyer'
    : 'Privacy Policy · My-lawyer';
$pageDescription = $isAr
    ? 'كيف تجمع My-lawyer وتستخدم وتحمي بياناتك الشخصية، وفقاً لقانون حماية البيانات الشخصية المصري رقم ١٥١ لسنة ٢٠٢٠.'
    : 'How My-lawyer collects, uses, and protects your personal data, in compliance with Egyptian Personal Data Protection Law No. 151 of 2020.';

$sections = $isAr ? [
    ['id' => 'who',                 'title' => 'من نحن'],
    ['id' => 'what-we-collect',     'title' => 'البيانات التي نجمعها'],
    ['id' => 'lawful-bases',        'title' => 'الأسس القانونية للمعالجة'],
    ['id' => 'how-we-use',          'title' => 'كيف نستخدم بياناتك'],
    ['id' => 'sharing',             'title' => 'مشاركة البيانات'],
    ['id' => 'sub-processors',      'title' => 'المعالجون من الباطن'],
    ['id' => 'transfers',           'title' => 'نقل البيانات خارج مصر'],
    ['id' => 'retention',           'title' => 'مدد الاحتفاظ'],
    ['id' => 'rights',              'title' => 'حقوقك'],
    ['id' => 'security',            'title' => 'الإجراءات الأمنية'],
    ['id' => 'cookies',             'title' => 'ملفات الارتباط'],
    ['id' => 'children',            'title' => 'الأطفال'],
    ['id' => 'changes',             'title' => 'التعديلات'],
    ['id' => 'contact',             'title' => 'التواصل'],
] : [
    ['id' => 'who',                 'title' => 'Who We Are'],
    ['id' => 'what-we-collect',     'title' => 'What We Collect'],
    ['id' => 'lawful-bases',        'title' => 'Lawful Bases for Processing'],
    ['id' => 'how-we-use',          'title' => 'How We Use Your Data'],
    ['id' => 'sharing',             'title' => 'Data Sharing'],
    ['id' => 'sub-processors',      'title' => 'Sub-processors'],
    ['id' => 'transfers',           'title' => 'International Transfers'],
    ['id' => 'retention',           'title' => 'Retention Periods'],
    ['id' => 'rights',              'title' => 'Your Rights'],
    ['id' => 'security',            'title' => 'Security Measures'],
    ['id' => 'cookies',             'title' => 'Cookies'],
    ['id' => 'children',            'title' => "Children's Data"],
    ['id' => 'changes',             'title' => 'Changes'],
    ['id' => 'contact',             'title' => 'Contact'],
];

$heading = $isAr ? 'سياسة الخصوصية' : 'Privacy Policy';
$intro = $isAr
    ? 'هذه السياسة توضح كيف تتعامل شركة My-lawyer مع بياناتك الشخصية، بما يتفق مع القانون رقم ١٥١ لسنة ٢٠٢٠ الخاص بحماية البيانات الشخصية في جمهورية مصر العربية.'
    : 'This Policy explains how My-lawyer handles your personal data in compliance with Law No. 151 of 2020 on Personal Data Protection in the Arab Republic of Egypt.';
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

<section id="who">
    <h2><span class="num">١.</span> من نحن</h2>
    <p>My-lawyer (يُشار إليها هنا بـ"نحن"، "خدمتنا"، أو "المنصة") هي مزود خدمة صياغة العقود بالذكاء الاصطناعي للمستشارين القانونيين والمؤسسات في الشرق الأوسط، مقرها الرئيسي القاهرة، جمهورية مصر العربية. عند الإشارة إلى بياناتك الشخصية في هذه السياسة، فإننا نعمل بصفة <strong>المسؤول عن المعالجة</strong> فيما يخص بيانات حسابك، وبصفة <strong>المعالج</strong> فيما يخص بيانات عملائك التي ترفعها عبر المنصة.</p>
</section>

<section id="what-we-collect">
    <h2><span class="num">٢.</span> البيانات التي نجمعها</h2>
    <h3>بيانات الحساب</h3>
    <p>الاسم، عنوان البريد الإلكتروني، كلمة المرور (مُجزّأة)، اسم الجهة، الدور المهني، الولاية القضائية المختارة، اللغة المفضلة.</p>
    <h3>بيانات الفوترة</h3>
    <p>الاسم على البطاقة، عنوان الفوترة، آخر أربعة أرقام من البطاقة (لا نخزن أرقام البطاقات الكاملة — تتم المعالجة عبر مزود دفع معتمد).</p>
    <h3>محتوى المستخدم</h3>
    <p>المسودات والاستفسارات والوثائق التي ترفعها أو تُدخلها للمنصة لأغراض الصياغة أو الترجمة أو الاستشهاد.</p>
    <h3>البيانات التقنية</h3>
    <p>عنوان IP، نوع المتصفح، نظام التشغيل، الصفحات التي تزورها، أوقات الجلسات، أحداث الأخطاء.</p>
</section>

<section id="lawful-bases">
    <h2><span class="num">٣.</span> الأسس القانونية للمعالجة</h2>
    <p>نعتمد على الأسس التالية وفقاً للقانون رقم ١٥١ لسنة ٢٠٢٠:</p>
    <table>
        <thead><tr><th>الغرض</th><th>الأساس القانوني</th></tr></thead>
        <tbody>
            <tr><td>تشغيل حسابك وتقديم الخدمة</td><td>تنفيذ عقد</td></tr>
            <tr><td>الفوترة وإدارة الاشتراك</td><td>تنفيذ عقد + التزام قانوني</td></tr>
            <tr><td>الأمن واكتشاف الاحتيال</td><td>المصلحة المشروعة</td></tr>
            <tr><td>التحليلات والتحسين</td><td>المصلحة المشروعة (مع حق الاعتراض)</td></tr>
            <tr><td>التسويق المباشر</td><td>الموافقة (يمكنك سحبها في أي وقت)</td></tr>
            <tr><td>الامتثال لأمر قضائي أو طلب رسمي</td><td>التزام قانوني</td></tr>
        </tbody>
    </table>
</section>

<section id="how-we-use">
    <h2><span class="num">٤.</span> كيف نستخدم بياناتك</h2>
    <ul>
        <li>إنشاء وإدارة حسابك وتسجيل الدخول.</li>
        <li>تنفيذ طلبات صياغة العقود والترجمة والاستشهاد.</li>
        <li>إرسال الإشعارات الفنية وتحديثات الخدمة.</li>
        <li>اكتشاف الاستخدام غير القانوني أو إساءة الاستخدام.</li>
        <li>تحسين أداء النموذج (على بيانات مجهولة الهوية فقط، ما لم توافق صراحةً على غير ذلك).</li>
    </ul>
    <div class="callout">
        <strong>وعدنا:</strong> لا نستخدم محتواك (مسودات العقود، استفساراتك القانونية) لتدريب نماذج الذكاء الاصطناعي العامة لمزودي الطرف الثالث. اتفاقياتنا مع Anthropic وGoogle وغيرهم تتضمن شروط عدم استخدام بيانات العميل للتدريب.
    </div>
</section>

<section id="sharing">
    <h2><span class="num">٥.</span> مشاركة البيانات</h2>
    <p>لا نبيع بياناتك. نشاركها فقط مع:</p>
    <ul>
        <li><strong>المعالجون من الباطن</strong> الذين نتعاقد معهم لتقديم الخدمة (راجع القسم التالي).</li>
        <li><strong>الجهات الرسمية</strong> عند طلبها بأمر قضائي أو حكم نافذ المفعول، مع إخطارك ما لم يحظر القانون ذلك.</li>
        <li><strong>المشتري في حالة استحواذ</strong> أو دمج، مع التزام مماثل بحماية البيانات.</li>
    </ul>
</section>

<section id="sub-processors">
    <h2><span class="num">٦.</span> المعالجون من الباطن</h2>
    <p>نستخدم مزودي الخدمة التالية لتشغيل المنصة:</p>
    <table>
        <thead><tr><th>المزود</th><th>الغرض</th><th>المنطقة</th></tr></thead>
        <tbody>
            <tr><td>Anthropic</td><td>نموذج Claude لصياغة العقود</td><td>الولايات المتحدة</td></tr>
            <tr><td>Google (Gemini)</td><td>نموذج احتياطي للصياغة</td><td>الولايات المتحدة / الاتحاد الأوروبي</td></tr>
            <tr><td>Groq</td><td>نموذج احتياطي ثالث</td><td>الولايات المتحدة</td></tr>
            <tr><td>Amazon Web Services / DigitalOcean</td><td>الاستضافة وقواعد البيانات</td><td>الاتحاد الأوروبي / الإمارات</td></tr>
            <tr><td>Postmark / Mailgun</td><td>إرسال البريد الإلكتروني</td><td>الولايات المتحدة</td></tr>
            <tr><td>Sentry</td><td>مراقبة الأخطاء</td><td>الولايات المتحدة</td></tr>
        </tbody>
    </table>
    <p>أي تغيير في قائمة المعالجين من الباطن يتم إخطار العملاء به قبل ثلاثين يوماً من السريان.</p>
</section>

<section id="transfers">
    <h2><span class="num">٧.</span> نقل البيانات خارج مصر</h2>
    <p>نظراً لاستخدامنا مزودي خدمات سحابية ونماذج ذكاء اصطناعي مستضافة خارج جمهورية مصر العربية، فإن بياناتك قد تُنقل إلى الولايات المتحدة، الاتحاد الأوروبي، الإمارات العربية المتحدة، أو دول أخرى. نتأكد من وجود ضمانات قانونية مناسبة (شروط تعاقدية معيارية، شهادات الأمن، تشفير البيانات أثناء النقل والتخزين).</p>
    <p>وفقاً للقانون رقم ١٥١ لسنة ٢٠٢٠، يحق لك الاعتراض على هذا النقل والمطالبة بمعالجة بياناتك داخل مصر. اتصل بنا للترتيب.</p>
</section>

<section id="retention">
    <h2><span class="num">٨.</span> مدد الاحتفاظ</h2>
    <table>
        <thead><tr><th>نوع البيانات</th><th>مدة الاحتفاظ</th></tr></thead>
        <tbody>
            <tr><td>بيانات الحساب</td><td>طوال فترة الاشتراك + ٣٠ يوماً</td></tr>
            <tr><td>محتوى المستخدم (مسودات العقود)</td><td>وفق إعدادات حسابك (افتراضياً: ٧ سنوات لتطابق المادة ٤٧ من قانون التجارة)</td></tr>
            <tr><td>سجلات الفوترة</td><td>٧ سنوات (التزام ضريبي)</td></tr>
            <tr><td>سجلات الأمن والتدقيق</td><td>سنتان</td></tr>
            <tr><td>بيانات التحليلات</td><td>١٤ شهراً، ثم تجميع وإخفاء هوية</td></tr>
        </tbody>
    </table>
</section>

<section id="rights">
    <h2><span class="num">٩.</span> حقوقك</h2>
    <p>وفقاً للقانون رقم ١٥١ لسنة ٢٠٢٠، يحق لك:</p>
    <ul>
        <li><strong>الاطلاع</strong> على بياناتك الشخصية المخزنة لدينا.</li>
        <li><strong>التصحيح</strong> عند عدم دقة أو اكتمال البيانات.</li>
        <li><strong>الحذف</strong> ("حق النسيان") إلا فيما يستلزم القانون الاحتفاظ به.</li>
        <li><strong>تقييد المعالجة</strong> عند التشكيك في دقة البيانات.</li>
        <li><strong>قابلية النقل</strong> — الحصول على بياناتك بصيغة منظمة قابلة للقراءة الآلية.</li>
        <li><strong>الاعتراض</strong> على المعالجة المبنية على المصلحة المشروعة أو على التسويق المباشر.</li>
        <li><strong>سحب الموافقة</strong> في أي وقت دون أثر رجعي على المعالجات السابقة.</li>
        <li><strong>التظلم</strong> أمام المركز المصري لحماية البيانات الشخصية.</li>
    </ul>
    <p>للمارسة أي من هذه الحقوق، تواصل مع مسؤول حماية البيانات على: <a href="mailto:dpo@my-lawyer.test">dpo@my-lawyer.test</a>. نلتزم بالرد خلال ثلاثين يوماً.</p>
</section>

<section id="security">
    <h2><span class="num">١٠.</span> الإجراءات الأمنية</h2>
    <ul>
        <li>تشفير البيانات أثناء التخزين باستخدام AES-256 وأثناء النقل باستخدام TLS 1.3.</li>
        <li>عزل بيانات كل عميل في قواعد بيانات منفصلة منطقياً.</li>
        <li>صلاحيات المستخدم بحد أدنى الامتيازات + المصادقة متعددة العوامل (MFA) للحسابات الإدارية.</li>
        <li>سجلات تدقيق ذات سلسلة-تحقق-كاملة (HMAC) لجميع المعالجات الحساسة.</li>
        <li>اختبارات اختراق سنوية + مراجعة كود أمنية مستمرة.</li>
        <li>إخطار خرق البيانات خلال ٧٢ ساعة من اكتشافه.</li>
    </ul>
</section>

<section id="cookies">
    <h2><span class="num">١١.</span> ملفات الارتباط</h2>
    <p>نستخدم ملفات ارتباط ضرورية فقط لتشغيل المنصة (الجلسة، تفضيلات اللغة، حماية CSRF). لا نستخدم ملفات تتبع تسويقية بدون موافقة صريحة منك.</p>
</section>

<section id="children">
    <h2><span class="num">١٢.</span> الأطفال</h2>
    <p>المنصة موجهة للمحامين والمستشارين القانونيين المهنيين. لا تُقدَّم خدماتنا للأشخاص دون الثامنة عشرة. إذا علمنا بجمع بيانات قاصر دون قصد، نحذفها فوراً.</p>
</section>

<section id="changes">
    <h2><span class="num">١٣.</span> التعديلات</h2>
    <p>عند تعديل هذه السياسة جوهرياً، نُخطرك قبل ثلاثين يوماً من السريان عبر البريد الإلكتروني أو إشعار بارز على المنصة.</p>
</section>

<section id="contact">
    <h2><span class="num">١٤.</span> التواصل</h2>
    <p><strong>مسؤول حماية البيانات:</strong> <a href="mailto:dpo@my-lawyer.test">dpo@my-lawyer.test</a></p>
    <p><strong>التواصل العام:</strong> <a href="mailto:privacy@my-lawyer.test">privacy@my-lawyer.test</a></p>
    <p><strong>الجهة الإشرافية:</strong> المركز المصري لحماية البيانات الشخصية، وزارة الاتصالات وتكنولوجيا المعلومات.</p>
</section>

@else

<section id="who">
    <h2><span class="num">1.</span> Who We Are</h2>
    <p>My-lawyer (referred to here as "we," "our service," or "the Platform") is an AI contract-drafting service for legal counsel and corporates across the MENA region, headquartered in Cairo, Arab Republic of Egypt. With respect to your personal data described in this Policy, we act as <strong>data controller</strong> for your account information and as <strong>data processor</strong> for your clients' data that you upload through the Platform.</p>
</section>

<section id="what-we-collect">
    <h2><span class="num">2.</span> What We Collect</h2>
    <h3>Account data</h3>
    <p>Name, email address, password (hashed), organization name, professional role, chosen jurisdiction, language preference.</p>
    <h3>Billing data</h3>
    <p>Cardholder name, billing address, last four digits of card (we do not store full card numbers — these are processed by our certified payment provider).</p>
    <h3>User Content</h3>
    <p>Drafts, queries, and documents you upload or input to the Platform for drafting, translation, or citation purposes.</p>
    <h3>Technical data</h3>
    <p>IP address, browser type, operating system, pages visited, session times, error events.</p>
</section>

<section id="lawful-bases">
    <h2><span class="num">3.</span> Lawful Bases for Processing</h2>
    <p>We rely on the following bases under Law 151/2020:</p>
    <table>
        <thead><tr><th>Purpose</th><th>Lawful basis</th></tr></thead>
        <tbody>
            <tr><td>Operating your account and providing the service</td><td>Performance of contract</td></tr>
            <tr><td>Billing and subscription management</td><td>Performance of contract + legal obligation</td></tr>
            <tr><td>Security and fraud detection</td><td>Legitimate interests</td></tr>
            <tr><td>Analytics and improvement</td><td>Legitimate interests (with right to object)</td></tr>
            <tr><td>Direct marketing</td><td>Consent (you may withdraw at any time)</td></tr>
            <tr><td>Compliance with court orders or official requests</td><td>Legal obligation</td></tr>
        </tbody>
    </table>
</section>

<section id="how-we-use">
    <h2><span class="num">4.</span> How We Use Your Data</h2>
    <ul>
        <li>To create and manage your account and authenticate sign-ins.</li>
        <li>To execute contract-drafting, translation, and citation requests.</li>
        <li>To send technical notices and service updates.</li>
        <li>To detect unlawful use or abuse.</li>
        <li>To improve model performance (on de-identified data only, unless you expressly consent otherwise).</li>
    </ul>
    <div class="callout">
        <strong>Our commitment:</strong> We do not use your content (contract drafts, legal queries) to train third-party AI providers' general models. Our agreements with Anthropic, Google, and others include "no training on customer data" clauses.
    </div>
</section>

<section id="sharing">
    <h2><span class="num">5.</span> Data Sharing</h2>
    <p>We do not sell your data. We share it only with:</p>
    <ul>
        <li><strong>Sub-processors</strong> we engage to provide the service (see next section).</li>
        <li><strong>Authorities</strong> on a court order or valid legal request, with notice to you unless prohibited by law.</li>
        <li><strong>An acquirer</strong> in case of merger or acquisition, under the same data-protection commitments.</li>
    </ul>
</section>

<section id="sub-processors">
    <h2><span class="num">6.</span> Sub-processors</h2>
    <p>We engage the following providers to operate the Platform:</p>
    <table>
        <thead><tr><th>Provider</th><th>Purpose</th><th>Region</th></tr></thead>
        <tbody>
            <tr><td>Anthropic</td><td>Claude model for contract drafting</td><td>United States</td></tr>
            <tr><td>Google (Gemini)</td><td>Backup drafting model</td><td>United States / EU</td></tr>
            <tr><td>Groq</td><td>Tertiary backup model</td><td>United States</td></tr>
            <tr><td>Amazon Web Services / DigitalOcean</td><td>Hosting and databases</td><td>EU / UAE</td></tr>
            <tr><td>Postmark / Mailgun</td><td>Email delivery</td><td>United States</td></tr>
            <tr><td>Sentry</td><td>Error monitoring</td><td>United States</td></tr>
        </tbody>
    </table>
    <p>Any change to the sub-processor list is notified to customers thirty days before it takes effect.</p>
</section>

<section id="transfers">
    <h2><span class="num">7.</span> International Transfers</h2>
    <p>Because we use cloud providers and AI models hosted outside the Arab Republic of Egypt, your data may be transferred to the United States, the EU, the UAE, or other countries. We ensure appropriate safeguards (standard contractual clauses, security certifications, encryption in transit and at rest).</p>
    <p>Under Law 151/2020, you have the right to object to such transfers and request that processing occur within Egypt. Contact us to arrange.</p>
</section>

<section id="retention">
    <h2><span class="num">8.</span> Retention Periods</h2>
    <table>
        <thead><tr><th>Data type</th><th>Retention period</th></tr></thead>
        <tbody>
            <tr><td>Account data</td><td>Subscription term + 30 days</td></tr>
            <tr><td>User Content (contract drafts)</td><td>Per your account settings (default: 7 years to align with Article 47 of the Egyptian Commercial Code)</td></tr>
            <tr><td>Billing records</td><td>7 years (tax obligation)</td></tr>
            <tr><td>Security and audit logs</td><td>2 years</td></tr>
            <tr><td>Analytics data</td><td>14 months, then aggregated and anonymized</td></tr>
        </tbody>
    </table>
</section>

<section id="rights">
    <h2><span class="num">9.</span> Your Rights</h2>
    <p>Under Law 151/2020, you have the right to:</p>
    <ul>
        <li><strong>Access</strong> the personal data we hold about you.</li>
        <li><strong>Rectify</strong> inaccurate or incomplete data.</li>
        <li><strong>Erase</strong> ("right to be forgotten") except where we are required to retain.</li>
        <li><strong>Restrict processing</strong> while accuracy is contested.</li>
        <li><strong>Portability</strong> — receive your data in a structured, machine-readable format.</li>
        <li><strong>Object</strong> to processing based on legitimate interests or to direct marketing.</li>
        <li><strong>Withdraw consent</strong> at any time without retroactive effect on prior processing.</li>
        <li><strong>Lodge a complaint</strong> with the Egyptian Personal Data Protection Center.</li>
    </ul>
    <p>To exercise any of these rights, contact our Data Protection Officer at <a href="mailto:dpo@my-lawyer.test">dpo@my-lawyer.test</a>. We respond within thirty days.</p>
</section>

<section id="security">
    <h2><span class="num">10.</span> Security Measures</h2>
    <ul>
        <li>Encryption at rest using AES-256 and in transit using TLS 1.3.</li>
        <li>Logical isolation of each customer's data in separate database schemas.</li>
        <li>Least-privilege user permissions plus multi-factor authentication for administrative accounts.</li>
        <li>Tamper-evident audit logs with HMAC-chain integrity for all sensitive operations.</li>
        <li>Annual penetration testing plus continuous code review.</li>
        <li>Data-breach notification within 72 hours of discovery.</li>
    </ul>
</section>

<section id="cookies">
    <h2><span class="num">11.</span> Cookies</h2>
    <p>We use only strictly-necessary cookies to operate the Platform (session, language preference, CSRF protection). We do not use marketing-tracking cookies without your express consent.</p>
</section>

<section id="children">
    <h2><span class="num">12.</span> Children's Data</h2>
    <p>The Platform is intended for professional lawyers and legal counsel. We do not provide services to persons under eighteen. If we become aware of inadvertent collection from a minor, we delete it immediately.</p>
</section>

<section id="changes">
    <h2><span class="num">13.</span> Changes</h2>
    <p>For material changes, we notify you thirty days before they take effect by email or by prominent notice on the Platform.</p>
</section>

<section id="contact">
    <h2><span class="num">14.</span> Contact</h2>
    <p><strong>Data Protection Officer:</strong> <a href="mailto:dpo@my-lawyer.test">dpo@my-lawyer.test</a></p>
    <p><strong>General privacy:</strong> <a href="mailto:privacy@my-lawyer.test">privacy@my-lawyer.test</a></p>
    <p><strong>Supervisory authority:</strong> Egyptian Personal Data Protection Center, Ministry of Communications and Information Technology.</p>
</section>

@endif

</x-legal-layout>
