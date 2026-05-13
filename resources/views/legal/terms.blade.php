<?php
$locale = app()->getLocale();
$isAr = $locale === 'ar';
$effectiveDate = '2026-05-10';
$canonical = url('/legal/terms');

$pageTitle = $isAr
    ? 'شروط الاستخدام · My-lawyer'
    : 'Terms of Service · My-lawyer';
$pageDescription = $isAr
    ? 'الشروط القانونية لاستخدام منصة My-lawyer لصياغة العقود بالذكاء الاصطناعي. يحكمها القانون المصري.'
    : 'Legal terms for using the My-lawyer AI contract-drafting platform. Governed by the law of the Arab Republic of Egypt.';

$sections = $isAr ? [
    ['id' => 'definitions',         'title' => 'التعريفات'],
    ['id' => 'eligibility',         'title' => 'الأهلية والحساب'],
    ['id' => 'license',             'title' => 'الترخيص الممنوح لك'],
    ['id' => 'ai-disclaimer',       'title' => 'إخلاء مسؤولية الذكاء الاصطناعي'],
    ['id' => 'ip',                  'title' => 'الملكية الفكرية'],
    ['id' => 'acceptable-use',      'title' => 'الاستخدام المقبول'],
    ['id' => 'fees',                'title' => 'الرسوم والاشتراك'],
    ['id' => 'data',                'title' => 'بياناتك'],
    ['id' => 'liability',           'title' => 'حدود المسؤولية'],
    ['id' => 'warranty',            'title' => 'إخلاء الضمانات'],
    ['id' => 'termination',         'title' => 'إنهاء الخدمة'],
    ['id' => 'force-majeure',       'title' => 'القوة القاهرة'],
    ['id' => 'governing-law',       'title' => 'القانون الواجب التطبيق'],
    ['id' => 'changes',             'title' => 'التعديلات'],
    ['id' => 'contact',             'title' => 'التواصل'],
] : [
    ['id' => 'definitions',         'title' => 'Definitions'],
    ['id' => 'eligibility',         'title' => 'Eligibility & Account'],
    ['id' => 'license',             'title' => 'License Granted to You'],
    ['id' => 'ai-disclaimer',       'title' => 'AI Output Disclaimer'],
    ['id' => 'ip',                  'title' => 'Intellectual Property'],
    ['id' => 'acceptable-use',      'title' => 'Acceptable Use'],
    ['id' => 'fees',                'title' => 'Fees & Subscription'],
    ['id' => 'data',                'title' => 'Your Data'],
    ['id' => 'liability',           'title' => 'Limitation of Liability'],
    ['id' => 'warranty',            'title' => 'Disclaimer of Warranties'],
    ['id' => 'termination',         'title' => 'Termination'],
    ['id' => 'force-majeure',       'title' => 'Force Majeure'],
    ['id' => 'governing-law',       'title' => 'Governing Law'],
    ['id' => 'changes',             'title' => 'Changes to These Terms'],
    ['id' => 'contact',             'title' => 'Contact'],
];

$heading = $isAr ? 'شروط الاستخدام' : 'Terms of Service';
$intro = $isAr
    ? 'هذه الشروط تنظم العلاقة بين شركة My-lawyer وبين المستخدمين الذين يصلون إلى المنصة أو يستخدمونها. باستخدامك المنصة فإنك توافق على الالتزام بهذه الشروط بكامل أحكامها.'
    : 'These Terms govern the relationship between My-lawyer and any person who accesses or uses the platform. By using the platform, you agree to be bound by these Terms in their entirety.';
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

<section id="definitions">
    <h2><span class="num">١.</span> التعريفات</h2>
    <div class="definition"><span class="term">"المنصة"</span> تعني الموقع الإلكتروني والتطبيقات والخدمات المقدمة تحت اسم My-lawyer.</div>
    <div class="definition"><span class="term">"المستخدم"</span> أو <span class="term">"أنت"</span> يعني أي شخص طبيعي أو اعتباري يصل إلى المنصة، سواء كان مسجلاً أو زائراً.</div>
    <div class="definition"><span class="term">"المخرجات"</span> تعني العقود والمسودات والترجمات والملخصات والاستشهادات التي تنتجها المنصة بناءً على إدخالاتك.</div>
    <div class="definition"><span class="term">"المحتوى المُدخَل"</span> يعني أي نص أو ملف أو معلومة ترفعها أو تُدخلها أو تشاركها مع المنصة.</div>
    <div class="definition"><span class="term">"القانون رقم ١٥١ لسنة ٢٠٢٠"</span> يعني قانون حماية البيانات الشخصية المصري.</div>
</section>

<section id="eligibility">
    <h2><span class="num">٢.</span> الأهلية والحساب</h2>
    <p>للتسجيل في المنصة، يجب أن تكون قد بلغت الثامنة عشرة من العمر وتتمتع بالأهلية القانونية للتعاقد وفقاً لأحكام القانون المدني المصري. إذا كنت تتعاقد بالنيابة عن جهة اعتبارية، فإنك تقر بأن لديك الصلاحية اللازمة لإلزامها بهذه الشروط.</p>
    <h3>أمن الحساب</h3>
    <p>أنت مسؤول عن الحفاظ على سرية بيانات اعتماد حسابك. أي نشاط يتم باستخدام حسابك يُعد منسوباً إليك ما لم تُخطرنا فوراً بأي استخدام غير مصرح به.</p>
</section>

<section id="license">
    <h2><span class="num">٣.</span> الترخيص الممنوح لك</h2>
    <p>تمنحك My-lawyer ترخيصاً غير حصري وغير قابل للتنازل ومحدوداً باشتراكك ساري المفعول، لاستخدام المنصة وفقاً لهذه الشروط ولأغراضك المهنية المشروعة فقط. لا يحق لك إعادة بيع المنصة أو إعادة هندستها العكسية أو نسخ خوارزمياتها.</p>
</section>

<section id="ai-disclaimer">
    <h2><span class="num">٤.</span> إخلاء مسؤولية الذكاء الاصطناعي</h2>
    <div class="callout">
        <strong>تنبيه جوهري:</strong> المنصة تستخدم نماذج ذكاء اصطناعي لإنشاء مسودات عقود واستشهادات قانونية. <strong>المخرجات لا تشكل استشارة قانونية</strong> ولا تُغني عن مراجعة محامٍ مرخص. أي اعتماد على مخرجات المنصة يقع على مسؤوليتك وحدك. يُلزم على المستخدم مراجعة كل بند وكل استشهاد قبل استخدام أي مخرج في معاملة فعلية أو تقاضي.
    </div>
    <p>على وجه التحديد:</p>
    <ul>
        <li>قد تخطئ نماذج الذكاء الاصطناعي في تفسير القانون أو في الاستشهاد بمواد غير دقيقة.</li>
        <li>قد تكون النصوص القانونية المخزنة لدينا قديمة بالنسبة لأحدث التعديلات التشريعية.</li>
        <li>الترجمة بين العربية والإنجليزية قد تنطوي على فروق دلالية تؤثر على المعنى القانوني.</li>
        <li>المنصة لا تأخذ في اعتبارها الظروف الخاصة بكل صفقة أو نزاع.</li>
    </ul>
</section>

<section id="ip">
    <h2><span class="num">٥.</span> الملكية الفكرية</h2>
    <h3>ملكيتك للمخرجات</h3>
    <p>المخرجات التي تنتجها المنصة بناءً على إدخالاتك تعد ملكاً لك. تتنازل My-lawyer عن أي حقوق ملكية فكرية في هذه المخرجات، باستثناء حقوقها في النموذج الذكي ذاته وفي البنية التحتية والشيفرة المصدرية.</p>
    <h3>ملكيتنا للنظام</h3>
    <p>تحتفظ My-lawyer بكامل حقوق الملكية الفكرية في الشيفرة المصدرية والشعارات والأسماء التجارية والقوالب والمسارد والبنية التحتية للمنصة.</p>
    <h3>ترخيص لتحسين الخدمة</h3>
    <p>تمنحنا ترخيصاً غير حصري ومحدود الزمن لاستخدام المحتوى المُدخَل والمخرجات بشكل مجهول الهوية لتحسين أداء المنصة، إلا في حال اشتراكك في خطة تتيح إيقاف هذا الاستخدام صراحةً.</p>
</section>

<section id="acceptable-use">
    <h2><span class="num">٦.</span> الاستخدام المقبول</h2>
    <p>يحظر استخدام المنصة لأي من الأغراض التالية:</p>
    <ul>
        <li>إنتاج وثائق احتيالية أو مضللة أو تنتهك القانون.</li>
        <li>تجاوز حدود الاستخدام المقررة لخطتك أو محاولة استخراج بيانات تدريب النموذج.</li>
        <li>تحميل محتوى ينتهك حقوق الغير أو يحتوي على برمجيات خبيثة.</li>
        <li>استخدام المخرجات لأغراض غير قانونية أو لتقديم استشارة قانونية بدون ترخيص محاماة.</li>
    </ul>
    <p>للاطلاع على القائمة الكاملة، راجع <a href="{{ route('legal.aup') }}">سياسة الاستخدام المقبول</a>.</p>
</section>

<section id="fees">
    <h2><span class="num">٧.</span> الرسوم والاشتراك</h2>
    <p>الرسوم الجارية مبيّنة على صفحة الأسعار. الاشتراكات الشهرية تتجدد تلقائياً ما لم تقم بإلغائها قبل نهاية فترة الفوترة. الرسوم لا تشمل الضريبة على القيمة المضافة المصرية إلا إذا نُص على غير ذلك صراحةً.</p>
    <p>لا تُسترد الرسوم المدفوعة عن فترات اشتراك سابقة، إلا إذا اقتضى القانون المصري خلاف ذلك.</p>
</section>

<section id="data">
    <h2><span class="num">٨.</span> بياناتك</h2>
    <p>تخضع معالجة بياناتك الشخصية ومحتواك لـ <a href="{{ route('legal.privacy') }}">سياسة الخصوصية</a> الخاصة بنا، التي تمتثل لأحكام القانون رقم ١٥١ لسنة ٢٠٢٠ بشأن حماية البيانات الشخصية.</p>
    <p>إذا كنت تستخدم المنصة لمعالجة بيانات شخصية لعملائك، فيجب توقيع <a href="{{ route('legal.dpa') }}">اتفاقية معالجة البيانات</a> معنا.</p>
</section>

<section id="liability">
    <h2><span class="num">٩.</span> حدود المسؤولية</h2>
    <p>إلى أقصى حد يسمح به القانون المصري:</p>
    <ul>
        <li>إجمالي مسؤوليتنا الناشئة عن أو المتعلقة بهذه الشروط في أي عام محدودة بإجمالي الرسوم التي دفعتها لنا في الاثني عشر شهراً السابقة على نشوء الادعاء.</li>
        <li>لا نتحمل أي مسؤولية عن الأضرار التبعية أو غير المباشرة، بما في ذلك على سبيل المثال لا الحصر: فقدان الأرباح، فقدان البيانات، أو الإضرار بالسمعة.</li>
        <li>لا نتحمل أي مسؤولية عن قرارات قانونية أو تجارية اتخذتها استناداً إلى مخرجات المنصة بدون مراجعة من محامٍ مرخص.</li>
    </ul>
</section>

<section id="warranty">
    <h2><span class="num">١٠.</span> إخلاء الضمانات</h2>
    <p>تُقدَّم المنصة "كما هي" و"بحسب التوفر". نخلي مسؤوليتنا عن جميع الضمانات الصريحة والضمنية بقدر ما يسمح به القانون، بما في ذلك ضمانات الملاءمة لغرض معين والقيمة التجارية وعدم الانتهاك.</p>
</section>

<section id="termination">
    <h2><span class="num">١١.</span> إنهاء الخدمة</h2>
    <h3>إنهاؤك للحساب</h3>
    <p>يمكنك إنهاء حسابك في أي وقت من خلال إعدادات الحساب أو بالتواصل معنا.</p>
    <h3>إنهاؤنا للحساب</h3>
    <p>يحق لنا تعليق أو إنهاء حسابك في حال الإخلال الجوهري بهذه الشروط، أو الاستخدام غير القانوني، أو عدم سداد الرسوم لمدة ثلاثين يوماً. سنقدم إشعاراً مسبقاً قدر الإمكان.</p>
    <h3>أثر الإنهاء</h3>
    <p>عند إنهاء الاشتراك يحق لك الاحتفاظ بنسخ من المخرجات التي أنشأتها. سنحذف بياناتك خلال تسعين يوماً من تاريخ الإنهاء، إلا إذا اقتضى القانون الاحتفاظ بها لفترة أطول.</p>
</section>

<section id="force-majeure">
    <h2><span class="num">١٢.</span> القوة القاهرة</h2>
    <p>لا يُسأل أي طرف عن أي تأخير أو فشل في التنفيذ نتيجة ظروف خارجة عن إرادته المعقولة، بما في ذلك الكوارث الطبيعية، الإجراءات الحكومية، الحرب، انقطاعات الإنترنت، أو فشل مزودي خدمات الذكاء الاصطناعي الأساسيين، وذلك وفقاً للمواد ١٥٩ و١٦٠ من القانون المدني المصري.</p>
</section>

<section id="governing-law">
    <h2><span class="num">١٣.</span> القانون الواجب التطبيق</h2>
    <p>تخضع هذه الشروط لأحكام القانون المصري وتُفسَّر وفقاً له. تختص محاكم القاهرة الاقتصادية بنظر أي نزاع ينشأ عن أو يتعلق بهذه الشروط، باستثناء حق العميل المستهلك في الالتجاء إلى القضاء العادي طبقاً لقانون حماية المستهلك.</p>
</section>

<section id="changes">
    <h2><span class="num">١٤.</span> التعديلات</h2>
    <p>يحق لنا تعديل هذه الشروط من وقت لآخر. التعديلات الجوهرية تُخطر بها قبل ثلاثين يوماً من سريانها عبر بريدك الإلكتروني المسجل أو عبر إشعار بارز على المنصة. استمرارك في استخدام المنصة بعد سريان التعديلات يعد قبولاً لها.</p>
</section>

<section id="contact">
    <h2><span class="num">١٥.</span> التواصل</h2>
    <p>للأسئلة المتعلقة بهذه الشروط، تواصل معنا على: <a href="mailto:legal@my-lawyer.test">legal@my-lawyer.test</a></p>
</section>

@else

<section id="definitions">
    <h2><span class="num">1.</span> Definitions</h2>
    <div class="definition"><span class="term">"Platform"</span> means the website, applications, and services offered under the My-lawyer name.</div>
    <div class="definition"><span class="term">"User"</span> or <span class="term">"you"</span> means any natural or legal person who accesses the Platform, whether registered or as a visitor.</div>
    <div class="definition"><span class="term">"Output"</span> means contracts, drafts, translations, summaries, and citations the Platform produces in response to your inputs.</div>
    <div class="definition"><span class="term">"User Content"</span> means any text, file, or information you upload, input, or share with the Platform.</div>
    <div class="definition"><span class="term">"Law 151/2020"</span> means the Egyptian Personal Data Protection Law No. 151 of 2020.</div>
</section>

<section id="eligibility">
    <h2><span class="num">2.</span> Eligibility & Account</h2>
    <p>To register, you must be at least eighteen years old and have legal capacity to contract under the Egyptian Civil Code. If you are contracting on behalf of a legal entity, you represent that you have the authority to bind that entity to these Terms.</p>
    <h3>Account security</h3>
    <p>You are responsible for keeping your account credentials confidential. Any activity carried out using your account is attributed to you unless you notify us promptly of unauthorized use.</p>
</section>

<section id="license">
    <h2><span class="num">3.</span> License Granted to You</h2>
    <p>My-lawyer grants you a non-exclusive, non-transferable license, limited to the term of your active subscription, to use the Platform in accordance with these Terms and only for your lawful professional purposes. You may not resell the Platform, reverse-engineer it, or copy its algorithms.</p>
</section>

<section id="ai-disclaimer">
    <h2><span class="num">4.</span> AI Output Disclaimer</h2>
    <div class="callout">
        <strong>Important notice:</strong> The Platform uses AI models to generate contract drafts and legal citations. <strong>Output does not constitute legal advice</strong> and is not a substitute for review by a licensed lawyer. Reliance on Output is at your sole risk. You must verify every clause and every citation before using Output in any actual transaction or proceeding.
    </div>
    <p>Specifically:</p>
    <ul>
        <li>AI models can misinterpret law or cite articles incorrectly.</li>
        <li>The legal texts in our corpus may be out of date relative to the most recent legislative amendments.</li>
        <li>Translation between Arabic and English may involve subtle differences that affect legal meaning.</li>
        <li>The Platform does not account for the specific facts, circumstances, or strategy of your matter.</li>
    </ul>
</section>

<section id="ip">
    <h2><span class="num">5.</span> Intellectual Property</h2>
    <h3>Your ownership of Output</h3>
    <p>Output produced in response to your inputs is yours. My-lawyer waives any intellectual-property rights in such Output, except for its rights in the AI model itself, the underlying infrastructure, and the source code.</p>
    <h3>Our ownership of the system</h3>
    <p>My-lawyer retains all intellectual-property rights in the source code, logos, trademarks, templates, glossaries, and infrastructure of the Platform.</p>
    <h3>License to improve the service</h3>
    <p>You grant us a non-exclusive, time-limited license to use User Content and Output on a de-identified basis to improve the Platform, except where you are on a plan that expressly opts out of such use.</p>
</section>

<section id="acceptable-use">
    <h2><span class="num">6.</span> Acceptable Use</h2>
    <p>You may not use the Platform to:</p>
    <ul>
        <li>Produce fraudulent, misleading, or unlawful documents.</li>
        <li>Exceed the usage caps of your plan or attempt to extract model training data.</li>
        <li>Upload content that infringes third-party rights or contains malware.</li>
        <li>Use Output for unlawful purposes or to provide legal advice without holding a current bar licence.</li>
    </ul>
    <p>For the full list, see our <a href="{{ route('legal.aup') }}">Acceptable Use Policy</a>.</p>
</section>

<section id="fees">
    <h2><span class="num">7.</span> Fees & Subscription</h2>
    <p>Current fees are listed on the pricing page. Monthly subscriptions auto-renew unless cancelled before the end of the billing period. Fees do not include Egyptian VAT unless expressly stated.</p>
    <p>Fees paid for prior subscription periods are not refundable, except where Egyptian law requires otherwise.</p>
</section>

<section id="data">
    <h2><span class="num">8.</span> Your Data</h2>
    <p>Processing of your personal data and content is governed by our <a href="{{ route('legal.privacy') }}">Privacy Policy</a>, which complies with Law 151/2020 on personal-data protection.</p>
    <p>If you use the Platform to process personal data of your own clients, you must execute a <a href="{{ route('legal.dpa') }}">Data Processing Agreement</a> with us.</p>
</section>

<section id="liability">
    <h2><span class="num">9.</span> Limitation of Liability</h2>
    <p>To the maximum extent permitted by Egyptian law:</p>
    <ul>
        <li>Our total liability arising from or relating to these Terms in any year is capped at the total fees you paid us in the twelve months preceding the claim.</li>
        <li>We are not liable for consequential or indirect damages, including without limitation lost profits, lost data, or reputational harm.</li>
        <li>We are not liable for legal or commercial decisions you make based on Output without review by a licensed lawyer.</li>
    </ul>
</section>

<section id="warranty">
    <h2><span class="num">10.</span> Disclaimer of Warranties</h2>
    <p>The Platform is provided <em>"as is"</em> and <em>"as available."</em> To the extent permitted by law, we disclaim all express and implied warranties, including warranties of fitness for a particular purpose, merchantability, and non-infringement.</p>
</section>

<section id="termination">
    <h2><span class="num">11.</span> Termination</h2>
    <h3>Termination by you</h3>
    <p>You may terminate your account at any time through your account settings or by contacting us.</p>
    <h3>Termination by us</h3>
    <p>We may suspend or terminate your account on material breach of these Terms, unlawful use, or non-payment of fees for thirty days. We will provide advance notice where reasonably possible.</p>
    <h3>Effect of termination</h3>
    <p>On termination you may retain copies of Output you have generated. We will delete your data within ninety days of termination, except where law requires longer retention.</p>
</section>

<section id="force-majeure">
    <h2><span class="num">12.</span> Force Majeure</h2>
    <p>Neither party is liable for any delay or failure to perform caused by circumstances beyond its reasonable control, including natural disasters, government action, war, internet outages, or failure of upstream AI providers, in accordance with Articles 159 and 160 of the Egyptian Civil Code.</p>
</section>

<section id="governing-law">
    <h2><span class="num">13.</span> Governing Law</h2>
    <p>These Terms are governed by and construed in accordance with the laws of the Arab Republic of Egypt. The Cairo Economic Courts have jurisdiction over any dispute arising out of or relating to these Terms, save for the consumer's right to bring proceedings before ordinary courts under the Consumer Protection Law where applicable.</p>
</section>

<section id="changes">
    <h2><span class="num">14.</span> Changes to These Terms</h2>
    <p>We may amend these Terms from time to time. Material changes will be notified at least thirty days before they take effect, by email to the address on your account or by prominent notice on the Platform. Continued use of the Platform after the effective date constitutes acceptance.</p>
</section>

<section id="contact">
    <h2><span class="num">15.</span> Contact</h2>
    <p>For questions about these Terms: <a href="mailto:legal@my-lawyer.test">legal@my-lawyer.test</a></p>
</section>

@endif

</x-legal-layout>
