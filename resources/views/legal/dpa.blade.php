<?php
$locale = app()->getLocale();
$isAr = $locale === 'ar';
$effectiveDate = '2026-05-10';
$canonical = url('/legal/dpa');

$pageTitle = $isAr
    ? 'اتفاقية معالجة البيانات · My-lawyer'
    : 'Data Processing Agreement · My-lawyer';
$pageDescription = $isAr
    ? 'اتفاقية معالجة البيانات المعتمدة بين My-lawyer وعملاء الأعمال (مكاتب المحاماة والشركات) الذين يعالجون بيانات شخصية لعملائهم عبر المنصة.'
    : 'Standard Data Processing Agreement between My-lawyer and business customers (law firms and corporates) who process personal data of their own clients through the Platform.';

$sections = $isAr ? [
    ['id' => 'parties',         'title' => 'الأطراف والنطاق'],
    ['id' => 'roles',           'title' => 'أدوار الأطراف'],
    ['id' => 'obligations',     'title' => 'التزامات المعالج'],
    ['id' => 'controller-obs',  'title' => 'التزامات المسؤول'],
    ['id' => 'sub-processors',  'title' => 'المعالجون من الباطن'],
    ['id' => 'transfers',       'title' => 'النقل الدولي'],
    ['id' => 'audit',           'title' => 'حقوق التدقيق'],
    ['id' => 'breach',          'title' => 'الإخطار بخروقات البيانات'],
    ['id' => 'data-subject',    'title' => 'دعم طلبات أصحاب البيانات'],
    ['id' => 'termination',     'title' => 'الإرجاع والحذف عند الإنهاء'],
    ['id' => 'liability',       'title' => 'المسؤولية والتعويض'],
    ['id' => 'annexes',         'title' => 'الملاحق'],
] : [
    ['id' => 'parties',         'title' => 'Parties & Scope'],
    ['id' => 'roles',           'title' => 'Roles of the Parties'],
    ['id' => 'obligations',     'title' => 'Processor Obligations'],
    ['id' => 'controller-obs',  'title' => 'Controller Obligations'],
    ['id' => 'sub-processors',  'title' => 'Sub-processors'],
    ['id' => 'transfers',       'title' => 'International Transfers'],
    ['id' => 'audit',           'title' => 'Audit Rights'],
    ['id' => 'breach',          'title' => 'Data-Breach Notification'],
    ['id' => 'data-subject',    'title' => 'Data-Subject Request Support'],
    ['id' => 'termination',     'title' => 'Return and Deletion on Termination'],
    ['id' => 'liability',       'title' => 'Liability and Indemnity'],
    ['id' => 'annexes',         'title' => 'Annexes'],
];

$heading = $isAr ? 'اتفاقية معالجة البيانات' : 'Data Processing Agreement';
$intro = $isAr
    ? 'هذه الاتفاقية تنظم معالجة My-lawyer للبيانات الشخصية لأطراف العميل (المسؤول)، وفقاً للقانون رقم ١٥١ لسنة ٢٠٢٠ بشأن حماية البيانات الشخصية في جمهورية مصر العربية.'
    : 'This Agreement governs My-lawyer\'s processing of personal data on behalf of the Customer (Controller), in compliance with Law No. 151 of 2020 on Personal Data Protection in the Arab Republic of Egypt.';
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

<section id="parties">
    <h2><span class="num">١.</span> الأطراف والنطاق</h2>
    <p>هذه الاتفاقية مكملة لاتفاقية الخدمة الرئيسية بين My-lawyer (المعالج) والعميل التجاري الذي وقع عليها (المسؤول عن المعالجة). تنطبق على أي بيانات شخصية يقوم المسؤول برفعها أو إدخالها أو معالجتها عبر منصة My-lawyer.</p>
</section>

<section id="roles">
    <h2><span class="num">٢.</span> أدوار الأطراف</h2>
    <ul>
        <li><strong>المسؤول عن المعالجة:</strong> العميل، الذي يحدد أغراض ووسائل معالجة بيانات أطرافه.</li>
        <li><strong>المعالج:</strong> My-lawyer، التي تعالج البيانات نيابة عن المسؤول وفقاً لتعليماته الموثقة فقط.</li>
    </ul>
</section>

<section id="obligations">
    <h2><span class="num">٣.</span> التزامات المعالج</h2>
    <ol>
        <li>معالجة البيانات الشخصية وفق التعليمات الموثقة من المسؤول فقط، إلا فيما يلزم القانون.</li>
        <li>ضمان أن جميع الأشخاص المخولين بمعالجة البيانات ملزمون بسرية تعاقدية أو قانونية.</li>
        <li>تطبيق التدابير التقنية والتنظيمية المنصوص عليها في الملحق الثاني.</li>
        <li>عدم نقل البيانات لخارج مصر إلا بضمانات مناسبة (راجع القسم ٦).</li>
        <li>تقديم المساعدة المعقولة للمسؤول في الامتثال لطلبات أصحاب البيانات.</li>
        <li>إخطار المسؤول بأي خرق للبيانات الشخصية خلال ٧٢ ساعة من اكتشافه.</li>
        <li>حذف أو إعادة جميع البيانات الشخصية للمسؤول عند إنهاء الخدمة.</li>
        <li>إتاحة المعلومات اللازمة لإثبات الامتثال + الخضوع لعمليات تدقيق وفقاً للقسم ٧.</li>
    </ol>
</section>

<section id="controller-obs">
    <h2><span class="num">٤.</span> التزامات المسؤول</h2>
    <ol>
        <li>التأكد من أن لديه الأساس القانوني المناسب لمعالجة البيانات قبل رفعها.</li>
        <li>توفير معلومات الخصوصية المناسبة لأصحاب البيانات.</li>
        <li>إصدار تعليمات معالجة قانونية وموثقة.</li>
        <li>سداد رسوم الخدمة بانتظام، مع العلم أن انقطاع السداد لا يُعفي المعالج من التزامات حماية البيانات الجارية.</li>
    </ol>
</section>

<section id="sub-processors">
    <h2><span class="num">٥.</span> المعالجون من الباطن</h2>
    <p>يمنح المسؤول موافقة عامة على استخدام المعالجين من الباطن المدرجين في <a href="{{ route('legal.privacy') }}#sub-processors">سياسة الخصوصية</a>. أي إضافة أو تغيير يُخطَر به المسؤول قبل ٣٠ يوماً من السريان، مع حق الاعتراض.</p>
    <p>يفرض المعالج على المعالجين من الباطن التزامات حماية بيانات لا تقل عن المنصوص عليها في هذه الاتفاقية.</p>
</section>

<section id="transfers">
    <h2><span class="num">٦.</span> النقل الدولي</h2>
    <p>عند نقل البيانات لخارج جمهورية مصر العربية، يطبق المعالج إحدى الضمانات التالية:</p>
    <ul>
        <li>شروط تعاقدية معيارية معتمدة من المركز المصري لحماية البيانات الشخصية.</li>
        <li>تشفير end-to-end للبيانات الحساسة قبل النقل.</li>
        <li>اتفاقيات السحاب-الإقليمية (Region-pinning) عند طلبها صراحةً.</li>
    </ul>
    <p>يحق للمسؤول طلب الاحتفاظ بالبيانات داخل مصر مقابل رسوم إضافية تحدد بالاتفاق.</p>
</section>

<section id="audit">
    <h2><span class="num">٧.</span> حقوق التدقيق</h2>
    <p>للمسؤول الحق في:</p>
    <ul>
        <li>الاطلاع على تقارير التدقيق المستقلة (SOC 2 / ISO 27001) سنوياً.</li>
        <li>إجراء تدقيق على نفقته الخاصة بإشعار مسبق ٣٠ يوماً، مرة واحدة سنوياً، خلال ساعات العمل العادية، وبشكل لا يعطل عمليات المعالج.</li>
        <li>توسيع حق التدقيق ليشمل المعالجين من الباطن عند طلب الجهة الإشرافية المصرية.</li>
    </ul>
</section>

<section id="breach">
    <h2><span class="num">٨.</span> الإخطار بخروقات البيانات</h2>
    <p>عند علم المعالج بخرق بيانات شخصية يخص بيانات المسؤول:</p>
    <ol>
        <li>إخطار المسؤول كتابةً خلال <strong>٧٢ ساعة</strong> من الاكتشاف.</li>
        <li>تقديم وصف للخرق، طبيعته، البيانات المتأثرة، وأعداد أصحاب البيانات المتأثرين.</li>
        <li>تقديم خطة الاحتواء والتخفيف.</li>
        <li>التعاون الكامل في إخطار المركز المصري لحماية البيانات والأشخاص المعنيين عند الاقتضاء.</li>
    </ol>
</section>

<section id="data-subject">
    <h2><span class="num">٩.</span> دعم طلبات أصحاب البيانات</h2>
    <p>عند تلقي المعالج لطلب من صاحب بيانات (وصول، تصحيح، حذف، نقل، اعتراض)، يحيله للمسؤول دون تأخير ويقدم المساعدة التقنية اللازمة لتنفيذه دون تكلفة إضافية على المسؤول.</p>
</section>

<section id="termination">
    <h2><span class="num">١٠.</span> الإرجاع والحذف عند الإنهاء</h2>
    <p>عند انتهاء الخدمة، يلتزم المعالج بـ:</p>
    <ul>
        <li>إعادة جميع البيانات الشخصية للمسؤول بصيغة آلية القراءة خلال ٣٠ يوماً، أو</li>
        <li>حذفها بشكل آمن وفقاً لتعليمات المسؤول.</li>
        <li>تقديم شهادة كتابية بإتمام الحذف.</li>
        <li>الاحتفاظ فقط بالبيانات التي يُلزم القانون بحفظها (سجلات الفوترة لأغراض ضريبية).</li>
    </ul>
</section>

<section id="liability">
    <h2><span class="num">١١.</span> المسؤولية والتعويض</h2>
    <p>المسؤولية المتبادلة بين الطرفين عن الإخلال بهذه الاتفاقية محدودة وفقاً لاتفاقية الخدمة الرئيسية.</p>
    <p>كل طرف يُعوّض الطرف الآخر عن أي غرامات تفرضها الجهة الإشرافية بسبب إخلال ذلك الطرف بالتزاماته بموجب هذه الاتفاقية.</p>
</section>

<section id="annexes">
    <h2><span class="num">١٢.</span> الملاحق</h2>
    <h3>الملحق الأول — وصف المعالجة</h3>
    <ul>
        <li><strong>طبيعة المعالجة:</strong> صياغة عقود، ترجمة قانونية، استرجاع استشهادات قانونية.</li>
        <li><strong>أغراض المعالجة:</strong> أداء الخدمة بناءً على طلب المسؤول.</li>
        <li><strong>فئات أصحاب البيانات:</strong> أطراف العقود التي يصوغها المسؤول، موظفو المسؤول، الأطراف المقابلة.</li>
        <li><strong>أنواع البيانات:</strong> أسماء، عناوين، أرقام تعريف الشركات، شروط تجارية، شروط مالية، أحياناً بيانات حساسة (السجل الجنائي في عقود التوظيف، الصحة في التأمين).</li>
        <li><strong>مدة المعالجة:</strong> طوال فترة الاشتراك + فترات الاحتفاظ المتفق عليها.</li>
    </ul>

    <h3>الملحق الثاني — التدابير التقنية والتنظيمية</h3>
    <ul>
        <li>تشفير في الراحة AES-256، وأثناء النقل TLS 1.3.</li>
        <li>عزل منطقي لقواعد البيانات لكل عميل.</li>
        <li>صلاحيات المستخدم بحد أدنى الامتيازات + المصادقة متعددة العوامل.</li>
        <li>سجلات تدقيق ذات سلسلة-تحقق-كاملة (HMAC).</li>
        <li>نسخ احتياطي يومي + اختبار استعادة فصلي.</li>
        <li>اختبار اختراق سنوي بواسطة طرف ثالث معتمد.</li>
        <li>تدريب أمن البيانات الإلزامي للموظفين كل ٦ أشهر.</li>
    </ul>

    <h3>الملحق الثالث — قائمة المعالجين من الباطن</h3>
    <p>راجع <a href="{{ route('legal.privacy') }}#sub-processors">قسم المعالجين من الباطن في سياسة الخصوصية</a>.</p>
</section>

@else

<section id="parties">
    <h2><span class="num">1.</span> Parties & Scope</h2>
    <p>This Agreement supplements the master service agreement between My-lawyer (the Processor) and the business Customer that signed it (the Controller). It applies to any personal data the Controller uploads, inputs, or processes through the My-lawyer Platform.</p>
</section>

<section id="roles">
    <h2><span class="num">2.</span> Roles of the Parties</h2>
    <ul>
        <li><strong>Controller:</strong> the Customer, which determines the purposes and means of processing its parties' data.</li>
        <li><strong>Processor:</strong> My-lawyer, which processes data on behalf of the Controller and only on its documented instructions.</li>
    </ul>
</section>

<section id="obligations">
    <h2><span class="num">3.</span> Processor Obligations</h2>
    <ol>
        <li>Process personal data only on documented instructions from the Controller, save where required by law.</li>
        <li>Ensure that all persons authorized to process data are bound by contractual or statutory confidentiality.</li>
        <li>Implement the technical and organizational measures set out in Annex II.</li>
        <li>Not transfer data outside Egypt without appropriate safeguards (see Section 6).</li>
        <li>Provide reasonable assistance to the Controller in handling data-subject requests.</li>
        <li>Notify the Controller of any personal-data breach within 72 hours of discovery.</li>
        <li>Delete or return all personal data on termination of the service.</li>
        <li>Make available the information necessary to demonstrate compliance, and submit to audits as set out in Section 7.</li>
    </ol>
</section>

<section id="controller-obs">
    <h2><span class="num">4.</span> Controller Obligations</h2>
    <ol>
        <li>Ensure it has an appropriate lawful basis to process the data before uploading.</li>
        <li>Provide appropriate privacy information to data subjects.</li>
        <li>Issue lawful, documented processing instructions.</li>
        <li>Pay service fees on time, noting that failure to pay does not relieve the Processor of ongoing data-protection obligations.</li>
    </ol>
</section>

<section id="sub-processors">
    <h2><span class="num">5.</span> Sub-processors</h2>
    <p>The Controller grants general authorization to use the sub-processors listed in our <a href="{{ route('legal.privacy') }}#sub-processors">Privacy Policy</a>. Any addition or change is notified to the Controller thirty days before it takes effect, with a right to object.</p>
    <p>The Processor imposes on each sub-processor data-protection obligations no less protective than those in this Agreement.</p>
</section>

<section id="transfers">
    <h2><span class="num">6.</span> International Transfers</h2>
    <p>When transferring data outside the Arab Republic of Egypt, the Processor applies one of the following safeguards:</p>
    <ul>
        <li>Standard contractual clauses approved by the Egyptian Personal Data Protection Center.</li>
        <li>End-to-end encryption of sensitive data prior to transfer.</li>
        <li>Region-pinning agreements with cloud providers, where expressly requested.</li>
    </ul>
    <p>The Controller may request that data remain within Egypt for an additional fee to be agreed.</p>
</section>

<section id="audit">
    <h2><span class="num">7.</span> Audit Rights</h2>
    <p>The Controller is entitled to:</p>
    <ul>
        <li>Receive copies of independent audit reports (SOC 2 / ISO 27001) annually.</li>
        <li>Conduct an audit at its own expense, on thirty days' prior written notice, once per year, during normal business hours, in a manner that does not disrupt the Processor's operations.</li>
        <li>Extend audit rights to sub-processors when required by the Egyptian supervisory authority.</li>
    </ul>
</section>

<section id="breach">
    <h2><span class="num">8.</span> Data-Breach Notification</h2>
    <p>On becoming aware of a personal-data breach affecting Controller data, the Processor:</p>
    <ol>
        <li>Notifies the Controller in writing within <strong>72 hours</strong> of discovery.</li>
        <li>Provides a description of the breach, its nature, the data affected, and approximate number of data subjects affected.</li>
        <li>Provides a containment and mitigation plan.</li>
        <li>Cooperates fully with notifications to the Egyptian Personal Data Protection Center and to data subjects where required.</li>
    </ol>
</section>

<section id="data-subject">
    <h2><span class="num">9.</span> Data-Subject Request Support</h2>
    <p>On receipt of a data-subject request (access, correction, deletion, portability, objection), the Processor refers it to the Controller without undue delay and provides the technical assistance needed to action it at no additional cost to the Controller.</p>
</section>

<section id="termination">
    <h2><span class="num">10.</span> Return and Deletion on Termination</h2>
    <p>On termination of the service, the Processor will:</p>
    <ul>
        <li>Return all personal data to the Controller in machine-readable format within thirty days, or</li>
        <li>Securely delete it on the Controller's instructions.</li>
        <li>Provide a written certificate of completion of deletion.</li>
        <li>Retain only data that law requires to be kept (billing records for tax purposes).</li>
    </ul>
</section>

<section id="liability">
    <h2><span class="num">11.</span> Liability and Indemnity</h2>
    <p>Mutual liability for breach of this Agreement is limited as set out in the master service agreement.</p>
    <p>Each party indemnifies the other against fines imposed by the supervisory authority that result from that party's breach of its obligations under this Agreement.</p>
</section>

<section id="annexes">
    <h2><span class="num">12.</span> Annexes</h2>
    <h3>Annex I — Description of Processing</h3>
    <ul>
        <li><strong>Nature of processing:</strong> contract drafting, legal translation, citation retrieval.</li>
        <li><strong>Purposes:</strong> providing the service at the Controller's request.</li>
        <li><strong>Categories of data subjects:</strong> parties to contracts the Controller drafts, the Controller's employees, counterparties.</li>
        <li><strong>Types of data:</strong> names, addresses, company identifiers, commercial terms, financial terms, occasionally sensitive data (criminal record in employment contracts, health in insurance).</li>
        <li><strong>Duration:</strong> subscription term plus agreed retention windows.</li>
    </ul>

    <h3>Annex II — Technical and Organizational Measures</h3>
    <ul>
        <li>Encryption at rest using AES-256 and in transit using TLS 1.3.</li>
        <li>Logical isolation of each customer's databases.</li>
        <li>Least-privilege user permissions plus multi-factor authentication.</li>
        <li>Tamper-evident audit logs (HMAC chain).</li>
        <li>Daily backups with quarterly restore testing.</li>
        <li>Annual third-party penetration testing.</li>
        <li>Mandatory data-security training for staff every six months.</li>
    </ul>

    <h3>Annex III — Sub-processor List</h3>
    <p>See the <a href="{{ route('legal.privacy') }}#sub-processors">sub-processors section of the Privacy Policy</a>.</p>
</section>

@endif

</x-legal-layout>
