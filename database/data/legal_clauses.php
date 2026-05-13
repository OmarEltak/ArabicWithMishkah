<?php

/*
|--------------------------------------------------------------------------
| Practical Clauses Bank
|--------------------------------------------------------------------------
|
| Reusable contract clause patterns that the AI drafter can retrieve and
| adapt instead of inventing wording. Each entry is a single LegalDocument
| (source = 'clauses-bank') with one chunk per clause variant.
|
| Coverage is Egyptian-civil-code-tradition by default, with English
| equivalents for bilingual drafting. The clauses are NOT lawyer-vetted
| boilerplate — they are starting points the AI uses as anchors so it
| doesn't fabricate phrasing. The product's corpus-citation gate still
| applies on output.
|
| ============================================================================
| ACCURACY NOTE: These clauses are common-form, Egypt-style, and reflect
| widely-used contract patterns. They are NOT a substitute for review by
| local counsel; they are training anchors for the drafter so phrasing
| stays consistent and idiomatic.
| ============================================================================
*/

return [

    /* ─────── Governing Law & Forum ─────── */
    [
        'title_en'    => 'Clauses Bank — Governing Law (Egypt)',
        'title_ar'    => 'مكتبة البنود — القانون الحاكم (مصر)',
        'jurisdiction'=> 'EG',
        'category'    => 'governing-law',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer practical-clauses bank, EG section',
        'citation_ar' => 'مكتبة البنود العملية، قسم مصر',
        'tags'        => ['boilerplate', 'governing-law'],
        'articles'    => [
            ['num' => 'GL-1', 'heading_en' => 'Governing law clause — Egyptian, exclusive Cairo Economic Courts', 'text_en' => 'This Agreement is governed by and construed in accordance with the laws of the Arab Republic of Egypt. The parties submit to the exclusive jurisdiction of the Cairo Economic Courts for the determination of any dispute arising out of or in connection with this Agreement.', 'text_ar' => 'يخضع هذا العقد لأحكام قوانين جمهورية مصر العربية ويُفسَّر وفقاً لها. ويتفق الطرفان على الاختصاص الحصري لمحاكم القاهرة الاقتصادية بالفصل في أي نزاع ينشأ عن أو يتعلق بهذا العقد.'],
            ['num' => 'GL-2', 'heading_en' => 'Governing law clause — Egyptian, CRCICA arbitration', 'text_en' => 'This Agreement is governed by the laws of the Arab Republic of Egypt. Any dispute arising out of or in connection with this Agreement, including any question regarding its existence, validity, or termination, shall be finally resolved by arbitration under the Rules of Arbitration of the Cairo Regional Centre for International Commercial Arbitration (CRCICA). The seat of arbitration shall be Cairo; the language shall be Arabic; the tribunal shall consist of three arbitrators.', 'text_ar' => 'يخضع هذا العقد لقوانين جمهورية مصر العربية. ويتم تسوية أي نزاع ينشأ عن أو يتعلق بهذا العقد نهائياً عن طريق التحكيم وفقاً لقواعد التحكيم لمركز القاهرة الإقليمي للتحكيم التجاري الدولي. ويكون مقر التحكيم القاهرة، ولغة التحكيم العربية، وتتكون هيئة التحكيم من ثلاثة محكمين.'],
            ['num' => 'GL-3', 'heading_en' => 'Language-prevailing clause (bilingual)', 'text_en' => 'This Agreement is executed in Arabic and English versions. In the event of any discrepancy between the two versions, the Arabic version shall prevail.', 'text_ar' => 'حُرِّر هذا العقد بنسختين إحداهما باللغة العربية والأخرى باللغة الإنجليزية. وفي حالة وجود أي تعارض بين النسختين، تسود النسخة العربية.'],
        ],
    ],

    /* ─────── Force Majeure ─────── */
    [
        'title_en'    => 'Clauses Bank — Force Majeure',
        'title_ar'    => 'مكتبة البنود — القوة القاهرة',
        'jurisdiction'=> 'EG',
        'category'    => 'force-majeure',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — force majeure variants',
        'citation_ar' => 'مكتبة البنود — القوة القاهرة',
        'tags'        => ['boilerplate', 'force-majeure'],
        'articles'    => [
            ['num' => 'FM-1', 'heading_en' => 'Force majeure — standard clause', 'text_en' => 'Neither party shall be liable for any failure or delay in performing its obligations under this Agreement to the extent that the failure or delay is caused by force majeure — meaning an event beyond the reasonable control of that party that could not have been foreseen or avoided, including natural disasters, war, civil disturbance, government action, epidemic, or unavailability of essential utilities. The affected party shall promptly notify the other party in writing of the force-majeure event, its likely duration, and its impact on performance, and shall use reasonable efforts to mitigate. If the force-majeure event continues for more than ninety (90) days, either party may terminate this Agreement on written notice without further liability.', 'text_ar' => 'لا يكون أيٌّ من الطرفين مسؤولاً عن أي إخلال أو تأخير في تنفيذ التزاماته بموجب هذا العقد إذا كان ذلك راجعاً إلى قوة قاهرة — وهي حدث خارج عن الإرادة المعقولة لذلك الطرف ولم يكن في وسعه توقعه أو تجنبه، ويشمل الكوارث الطبيعية والحرب والاضطرابات المدنية والإجراءات الحكومية والأوبئة وانقطاع المرافق الأساسية. ويلتزم الطرف المتأثر بإخطار الطرف الآخر كتابةً فور وقوع الحدث، مبيِّناً مدته المحتملة وأثره على التنفيذ، ويبذل جهوداً معقولة للحد من الأضرار. وإذا استمرت القوة القاهرة لأكثر من تسعين (٩٠) يوماً، جاز لأي من الطرفين إنهاء العقد بإخطار كتابي دون أي مسؤولية إضافية.'],
        ],
    ],

    /* ─────── Confidentiality ─────── */
    [
        'title_en'    => 'Clauses Bank — Confidentiality',
        'title_ar'    => 'مكتبة البنود — السرية',
        'jurisdiction'=> 'EG',
        'category'    => 'confidentiality',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — confidentiality variants',
        'citation_ar' => 'مكتبة البنود — السرية',
        'tags'        => ['boilerplate', 'confidentiality'],
        'articles'    => [
            ['num' => 'C-1', 'heading_en' => 'Confidentiality — standard mutual', 'text_en' => 'Each party undertakes to treat as confidential all information of a confidential or proprietary nature disclosed to it by the other party in connection with this Agreement, and not to disclose such information to any third party or to use it for any purpose other than the performance of this Agreement, save (a) with the prior written consent of the disclosing party, (b) where required by law or by an order of a competent court, or (c) to professional advisors bound by equivalent confidentiality duties. This obligation survives termination of this Agreement for a period of five (5) years.', 'text_ar' => 'يلتزم كل طرف بالحفاظ على سرية جميع المعلومات ذات الطبيعة السرية أو الخاصة التي يفصح له عنها الطرف الآخر بمناسبة هذا العقد، وعدم إفشائها لأي طرف ثالث أو استخدامها لأي غرض غير تنفيذ هذا العقد، إلا (أ) بموافقة كتابية مسبقة من الطرف المفصح، (ب) عند الاقتضاء بموجب القانون أو بأمر من محكمة مختصة، (ج) إلى المستشارين المهنيين الملتزمين بواجب سرية مماثل. ويستمر هذا الالتزام لمدة خمس (٥) سنوات بعد انتهاء العقد.'],
            ['num' => 'C-2', 'heading_en' => 'Confidentiality — exclusions', 'text_en' => 'The confidentiality obligation does not apply to information that: (a) is or becomes generally available to the public other than by breach of this Agreement; (b) was rightfully in the receiving party\'s possession before disclosure, without obligation of confidentiality; (c) is independently developed by the receiving party without use of the disclosing party\'s confidential information; or (d) is rightfully received from a third party without obligation of confidentiality.', 'text_ar' => 'لا يسري التزام السرية على المعلومات التي: (أ) أصبحت أو صارت متاحة للعموم بطريقة لا تشكل إخلالاً بهذا العقد؛ (ب) كانت في حوزة الطرف المستلم قبل الإفصاح، دون التزام بالسرية؛ (ج) طورها الطرف المستلم بشكل مستقل دون استخدام المعلومات السرية للطرف المفصح؛ (د) تلقاها بشكل مشروع من طرف ثالث دون التزام بالسرية.'],
        ],
    ],

    /* ─────── Indemnification ─────── */
    [
        'title_en'    => 'Clauses Bank — Indemnification',
        'title_ar'    => 'مكتبة البنود — التعويض',
        'jurisdiction'=> 'EG',
        'category'    => 'indemnification',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — indemnification variants',
        'citation_ar' => 'مكتبة البنود — التعويض',
        'tags'        => ['boilerplate', 'indemnification', 'liability'],
        'articles'    => [
            ['num' => 'I-1', 'heading_en' => 'Indemnification — standard mutual', 'text_en' => 'Each party shall indemnify, defend, and hold harmless the other party from and against any direct losses, damages, costs, and reasonable legal fees actually incurred and arising from: (a) any breach by the indemnifying party of its representations, warranties, or covenants under this Agreement; (b) any third-party claim alleging that the indemnifying party\'s acts or omissions caused harm; or (c) any gross negligence or willful misconduct by the indemnifying party. This indemnity is subject to the limitations of liability set out in Article [X].', 'text_ar' => 'يلتزم كل طرف بتعويض الطرف الآخر والدفاع عنه وإبقائه آمناً من أي خسائر مباشرة أو أضرار أو تكاليف أو أتعاب محاماة معقولة فعلية ناشئة عن: (أ) أي إخلال من الطرف المعوِّض بإقراراته أو ضماناته أو تعهداته بموجب هذا العقد؛ (ب) أي ادعاء من طرف ثالث يزعم أن أفعال الطرف المعوِّض أو امتناعه عن الفعل تسبب في ضرر؛ (ج) أي إهمال جسيم أو سوء سلوك متعمد من جانب الطرف المعوِّض. ويخضع هذا التعويض لقيود المسؤولية المنصوص عليها في البند [X].'],
            ['num' => 'I-2', 'heading_en' => 'Indemnification — cap at fees paid', 'text_en' => 'The aggregate liability of either party under this Agreement, whether in contract, tort, or otherwise, shall not exceed the total fees actually paid by the customer to the supplier under this Agreement in the twelve (12) months immediately preceding the event giving rise to the claim. This cap does not apply to: (a) breaches of the confidentiality obligations; (b) wilful misconduct or fraud; (c) liability that cannot lawfully be limited.', 'text_ar' => 'لا يتجاوز إجمالي مسؤولية أيٍّ من الطرفين بموجب هذا العقد، سواء بموجب العقد أو الفعل الضار أو غيرهما، إجمالي المبالغ التي دفعها العميل فعلياً للمورد بموجب هذا العقد خلال الاثني عشر (١٢) شهراً السابقة مباشرة على الحدث المؤدي إلى المطالبة. ولا يسري هذا الحد على: (أ) إخلال الالتزامات بالسرية؛ (ب) سوء السلوك المتعمد أو الاحتيال؛ (ج) المسؤولية التي لا يجوز قانوناً تحديدها.'],
        ],
    ],

    /* ─────── Termination ─────── */
    [
        'title_en'    => 'Clauses Bank — Termination',
        'title_ar'    => 'مكتبة البنود — الإنهاء',
        'jurisdiction'=> 'EG',
        'category'    => 'termination',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — termination variants',
        'citation_ar' => 'مكتبة البنود — الإنهاء',
        'tags'        => ['boilerplate', 'termination'],
        'articles'    => [
            ['num' => 'T-1', 'heading_en' => 'Termination for convenience — 30-day notice', 'text_en' => 'Either party may terminate this Agreement for convenience by giving the other party not less than thirty (30) days\' prior written notice. Termination does not affect any rights or liabilities accrued before the effective date of termination.', 'text_ar' => 'يجوز لأي من الطرفين إنهاء هذا العقد لأي سبب وذلك بإخطار كتابي مسبق إلى الطرف الآخر لا تقل مدته عن ثلاثين (٣٠) يوماً. ولا يؤثر الإنهاء على أي حقوق أو التزامات نشأت قبل تاريخ نفاذه.'],
            ['num' => 'T-2', 'heading_en' => 'Termination for cause — material breach + cure', 'text_en' => 'A party may terminate this Agreement with immediate effect by written notice if the other party: (a) commits a material breach that is not capable of remedy; or (b) commits a material breach capable of remedy and fails to remedy it within fifteen (15) days of receiving a written request to do so; or (c) becomes insolvent, files for bankruptcy, or has a liquidator appointed.', 'text_ar' => 'يجوز لأي طرف إنهاء هذا العقد فوراً بإخطار كتابي إذا قام الطرف الآخر بـ: (أ) ارتكاب إخلال جوهري لا يمكن إصلاحه؛ (ب) ارتكاب إخلال جوهري يمكن إصلاحه ولم يقم بإصلاحه خلال خمسة عشر (١٥) يوماً من تسلمه طلباً كتابياً بذلك؛ (ج) إعساره أو تقديم طلب إفلاسه أو تعيين مصفٍّ له.'],
            ['num' => 'T-3', 'heading_en' => 'Post-termination obligations', 'text_en' => 'Upon termination of this Agreement, each party shall promptly: (a) return or destroy, at the disclosing party\'s option, all confidential information of the other party; (b) pay all amounts due and accrued up to the effective date of termination; (c) cooperate in the orderly transition of any ongoing matters. Articles relating to confidentiality, intellectual-property ownership, indemnification, and dispute resolution survive termination.', 'text_ar' => 'عند انتهاء هذا العقد، يلتزم كل طرف فوراً بـ: (أ) إعادة أو إتلاف جميع المعلومات السرية للطرف الآخر، حسب اختيار الطرف المفصح؛ (ب) سداد جميع المبالغ المستحقة حتى تاريخ نفاذ الإنهاء؛ (ج) التعاون في الانتقال المنظم لأي مسائل قائمة. وتظل البنود المتعلقة بالسرية وملكية حقوق الملكية الفكرية والتعويض وفض النزاعات سارية بعد الإنهاء.'],
        ],
    ],

    /* ─────── IP Assignment ─────── */
    [
        'title_en'    => 'Clauses Bank — Intellectual Property Assignment',
        'title_ar'    => 'مكتبة البنود — نقل ملكية الأعمال الفكرية',
        'jurisdiction'=> 'EG',
        'category'    => 'ip',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — IP assignment variants',
        'citation_ar' => 'مكتبة البنود — نقل ملكية الأعمال الفكرية',
        'tags'        => ['boilerplate', 'ip'],
        'articles'    => [
            ['num' => 'IP-1', 'heading_en' => 'Work-for-hire IP assignment (employment)', 'text_en' => 'All intellectual property, inventions, works of authorship, designs, and improvements conceived, created, or first reduced to practice by the Employee in the course of employment with the Company, and arising from or related to the Company\'s business, shall be the sole and exclusive property of the Company. The Employee assigns to the Company all such intellectual-property rights, and agrees to execute any further documents reasonably required to perfect the Company\'s title.', 'text_ar' => 'تكون جميع حقوق الملكية الفكرية والاختراعات والمصنفات الإبداعية والتصميمات والتحسينات التي يبتكرها أو يطورها أو يقوم بتطبيقها لأول مرة الموظف أثناء عمله لدى الشركة، وتنشأ عن أو تتعلق بأعمال الشركة، ملكاً خالصاً وحصرياً للشركة. ويتنازل الموظف للشركة عن جميع حقوق الملكية الفكرية تلك، ويوافق على توقيع أي مستندات أخرى يلزم توقيعها لإتمام تسجيل ملكية الشركة.'],
            ['num' => 'IP-2', 'heading_en' => 'Service-provider IP — work product transfer + licence-back', 'text_en' => 'Upon payment in full of the fees due under this Agreement, all rights, title, and interest in any deliverables specifically created for the Customer ("Work Product") shall vest in the Customer. The Service Provider retains ownership of any pre-existing intellectual property and any general-purpose tools, libraries, or methodologies; the Service Provider grants the Customer a perpetual, worldwide, royalty-free licence to use such pre-existing items to the extent necessary to use the Work Product.', 'text_ar' => 'عند سداد كامل الأتعاب المستحقة بموجب هذا العقد، تنتقل جميع الحقوق والملكية والمصلحة في أي مخرجات تم إنتاجها خصيصاً للعميل ("المخرجات") إلى العميل. ويحتفظ مزود الخدمة بملكية أي حقوق ملكية فكرية سابقة وأي أدوات أو مكتبات أو منهجيات عامة، ويمنح مزود الخدمة العميل ترخيصاً دائماً عالمياً مجانياً لاستخدام تلك العناصر السابقة بالقدر اللازم لاستخدام المخرجات.'],
        ],
    ],

    /* ─────── Non-Compete + Non-Solicit ─────── */
    [
        'title_en'    => 'Clauses Bank — Restrictive Covenants',
        'title_ar'    => 'مكتبة البنود — الشروط التقييدية',
        'jurisdiction'=> 'EG',
        'category'    => 'employment',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — non-compete + non-solicit',
        'citation_ar' => 'مكتبة البنود — عدم المنافسة وعدم الاستقطاب',
        'tags'        => ['boilerplate', 'employment', 'restrictive-covenants'],
        'articles'    => [
            ['num' => 'RC-1', 'heading_en' => 'Non-compete — limited scope (EG-enforceable)', 'text_en' => 'The Employee agrees that, for a period of twelve (12) months following the termination of employment, the Employee shall not directly or indirectly engage in any business that competes with the Company\'s actual business as of the termination date, within the Arab Republic of Egypt. This restriction is intended to protect the legitimate interests of the Company in its trade secrets, customer relationships, and confidential information; it is limited in time, scope, and geography to what is reasonable for that purpose.', 'text_ar' => 'يوافق الموظف على أنه لمدة اثني عشر (١٢) شهراً تالية لانتهاء العلاقة الوظيفية، لن يقوم بشكل مباشر أو غير مباشر بمزاولة أي عمل ينافس النشاط الفعلي للشركة كما هو قائم وقت الإنهاء، داخل جمهورية مصر العربية. ويُقصد بهذا القيد حماية المصالح المشروعة للشركة في أسرارها التجارية وعلاقاتها بعملائها ومعلوماتها السرية؛ وهو محدود في الزمن والنطاق والجغرافيا بما هو معقول لهذا الغرض.'],
            ['num' => 'RC-2', 'heading_en' => 'Non-solicit of employees + customers', 'text_en' => 'For a period of eighteen (18) months following termination of this Agreement, neither party shall directly or indirectly: (a) solicit for employment any individual who is, or in the six (6) months preceding the date of the solicitation was, an employee of the other party in a senior role; (b) solicit for business any customer of the other party with whom such party had material dealings during the term of this Agreement. General advertising not specifically targeted at such persons does not constitute solicitation.', 'text_ar' => 'لمدة ثمانية عشر (١٨) شهراً تالية لانتهاء هذا العقد، لا يقوم أيٌّ من الطرفين بشكل مباشر أو غير مباشر بـ: (أ) محاولة استقطاب أي شخص يكون، أو خلال الستة (٦) أشهر السابقة على تاريخ المحاولة كان، موظفاً لدى الطرف الآخر في منصب قيادي؛ (ب) محاولة جذب أي عميل من عملاء الطرف الآخر الذي تعامل معه ذلك الطرف تعاملاً جوهرياً خلال مدة هذا العقد. ولا يُعدُّ الإعلان العام غير المستهدف لأولئك الأشخاص استقطاباً.'],
        ],
    ],

    /* ─────── Payment Terms ─────── */
    [
        'title_en'    => 'Clauses Bank — Payment Terms',
        'title_ar'    => 'مكتبة البنود — شروط الدفع',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — payment terms',
        'citation_ar' => 'مكتبة البنود — شروط الدفع',
        'tags'        => ['boilerplate', 'commercial', 'payments'],
        'articles'    => [
            ['num' => 'PT-1', 'heading_en' => 'Payment + late-payment interest', 'text_en' => 'Invoices shall be paid in full within thirty (30) days of the invoice date. Any sum not paid by its due date shall bear interest at the rate of the Central Bank of Egypt\'s discount rate plus two percent (2%) per annum, calculated daily from the due date until payment in full. The accrual of interest does not waive any other right or remedy.', 'text_ar' => 'تُسدَّد الفواتير بالكامل خلال ثلاثين (٣٠) يوماً من تاريخ الفاتورة. وأي مبلغ لا يُسدَّد في تاريخ استحقاقه يستحق عليه فائدة بسعر خصم البنك المركزي المصري زائداً اثنين بالمئة (٢٪) سنوياً، تُحسَب يومياً من تاريخ الاستحقاق حتى السداد الكامل. ولا يتنازل تحصيل الفائدة عن أي حق أو وسيلة انتصاف أخرى.'],
            ['num' => 'PT-2', 'heading_en' => 'Tax + withholding clause', 'text_en' => 'All amounts payable under this Agreement are exclusive of any applicable value-added tax, stamp duty, withholding tax, or similar tax, which shall be added to the invoice if applicable. Where Egyptian law requires the payer to withhold tax from a payment, the payer shall (a) deduct the required amount, (b) account for it to the Egyptian Tax Authority, (c) deliver a tax-deduction certificate to the payee within the statutory period.', 'text_ar' => 'جميع المبالغ المستحقة بموجب هذا العقد لا تشمل أي ضريبة قيمة مضافة أو رسوم دمغة أو ضرائب خصم أو ضرائب مماثلة، وتُضاف إلى الفاتورة إن وُجدت. وحيث يلزم القانون المصري المدفوع إليه بخصم ضريبة من الدفعة، يلتزم الدافع بـ (أ) خصم المبلغ المطلوب، (ب) توريده لمصلحة الضرائب المصرية، (ج) تسليم شهادة بخصم الضريبة للمتلقي خلال المدة القانونية.'],
        ],
    ],

    /* ─────── Representations & Warranties ─────── */
    [
        'title_en'    => 'Clauses Bank — Representations & Warranties',
        'title_ar'    => 'مكتبة البنود — الإقرارات والضمانات',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — reps & warranties',
        'citation_ar' => 'مكتبة البنود — الإقرارات والضمانات',
        'tags'        => ['boilerplate', 'commercial', 'corporate'],
        'articles'    => [
            ['num' => 'RW-1', 'heading_en' => 'Standard corporate reps & warranties', 'text_en' => 'Each party represents and warrants to the other that: (a) it is duly incorporated, validly existing, and in good standing under the laws of its jurisdiction of incorporation; (b) it has full power and authority to enter into and perform this Agreement; (c) execution and performance of this Agreement have been duly authorised by all necessary corporate action; (d) this Agreement constitutes a legal, valid, and binding obligation enforceable against it in accordance with its terms; (e) execution and performance do not breach any law, judgment, or agreement binding on it.', 'text_ar' => 'يقر كل طرف ويضمن للطرف الآخر أن: (أ) تأسيسه قانوني وقائم بصورة سليمة وفي وضع قانوني سليم وفقاً لقوانين دولة تأسيسه؛ (ب) يتمتع بكامل الصلاحية والسلطة لإبرام هذا العقد وتنفيذه؛ (ج) إبرام هذا العقد وتنفيذه قد تم تفويضه على النحو الواجب من قبل جميع الأجهزة المؤسسية المختصة؛ (د) يشكل هذا العقد التزاماً قانونياً صحيحاً وملزماً قابلاً للنفاذ ضده وفقاً لشروطه؛ (هـ) لا يتعارض إبرامه أو تنفيذه مع أي قانون أو حكم قضائي أو اتفاقية ملزمة له.'],
        ],
    ],

    /* ─────── Notices ─────── */
    [
        'title_en'    => 'Clauses Bank — Notices',
        'title_ar'    => 'مكتبة البنود — الإخطارات',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — notices',
        'citation_ar' => 'مكتبة البنود — الإخطارات',
        'tags'        => ['boilerplate', 'commercial'],
        'articles'    => [
            ['num' => 'N-1', 'heading_en' => 'Notices clause', 'text_en' => 'Any notice under this Agreement shall be in writing and delivered: (a) by hand against signed acknowledgment; or (b) by registered mail with return receipt; or (c) by email to the address designated by the recipient in writing — in which case the notice is deemed delivered on the next business day. A change of address requires fifteen (15) days\' prior written notice. Notices to legal counsel substitute for, but do not in addition to, notices to the principal.', 'text_ar' => 'تكون أي إخطارات بموجب هذا العقد كتابية وتسليم: (أ) باليد مقابل إقرار موقع بالاستلام؛ (ب) أو ببريد مسجل بعلم الوصول؛ (ج) أو بالبريد الإلكتروني إلى العنوان الذي يحدده المستلم كتابةً — وفي هذه الحالة يُعتبر الإخطار مستلماً في يوم العمل التالي. ويستلزم تغيير العنوان إخطاراً كتابياً مسبقاً بخمسة عشر (١٥) يوماً. ويحل الإخطار الموجَّه إلى المستشار القانوني محل الإخطار الموجَّه إلى الطرف الأصيل ولا يضاف إليه.'],
        ],
    ],

    /* ─────── General Provisions ─────── */
    [
        'title_en'    => 'Clauses Bank — General Provisions',
        'title_ar'    => 'مكتبة البنود — أحكام عامة',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — boilerplate',
        'citation_ar' => 'مكتبة البنود — أحكام عامة',
        'tags'        => ['boilerplate', 'commercial'],
        'articles'    => [
            ['num' => 'GP-1', 'heading_en' => 'Entire agreement', 'text_en' => 'This Agreement constitutes the entire agreement between the parties on its subject matter and supersedes all prior agreements, understandings, and negotiations — whether oral or written. No variation of this Agreement is effective unless it is in writing and signed by both parties.', 'text_ar' => 'يشكل هذا العقد كامل الاتفاق بين الطرفين بشأن موضوعه ويُلغي جميع الاتفاقيات والتفاهمات والمفاوضات السابقة، سواء كانت شفهية أو كتابية. ولا يكون أي تعديل لهذا العقد نافذاً إلا إذا كان كتابياً وموقعاً من الطرفين.'],
            ['num' => 'GP-2', 'heading_en' => 'Severability', 'text_en' => 'If any provision of this Agreement is held to be invalid, illegal, or unenforceable in any respect, the validity, legality, and enforceability of the remaining provisions are not in any way affected, and the parties shall replace the invalid provision with a valid one that most closely reflects their original intent.', 'text_ar' => 'إذا تقرر أن أي بند من هذا العقد غير صحيح أو غير قانوني أو غير قابل للتنفيذ في أي وجه، لا تتأثر صحة وقانونية وقابلية تنفيذ البنود المتبقية، ويستبدل الطرفان البند غير الصحيح ببند صحيح يعكس بأقرب صورة نيتهما الأصلية.'],
            ['num' => 'GP-3', 'heading_en' => 'No waiver', 'text_en' => 'A failure or delay by either party to exercise any right or remedy under this Agreement does not constitute a waiver of that right or remedy. A waiver of any breach is not a waiver of any subsequent breach.', 'text_ar' => 'لا يُعتبر إخفاق أي طرف أو تأخره في ممارسة أي حق أو وسيلة انتصاف بموجب هذا العقد تنازلاً عن ذلك الحق أو وسيلة الانتصاف. ولا يُعتبر التنازل عن أي إخلال تنازلاً عن أي إخلال لاحق.'],
            ['num' => 'GP-4', 'heading_en' => 'Assignment', 'text_en' => 'Neither party may assign or transfer this Agreement or any of its rights or obligations under it without the prior written consent of the other party, save that a party may assign to an affiliate, or to a successor in connection with a merger or sale of substantially all of its business, on prior written notice.', 'text_ar' => 'لا يجوز لأي من الطرفين التنازل عن أو نقل هذا العقد أو أي من حقوقه أو التزاماته بموجبه دون موافقة كتابية مسبقة من الطرف الآخر، باستثناء التنازل لشركة تابعة أو إلى خلف قانوني في إطار اندماج أو بيع لكامل أعماله الجوهرية، بإخطار كتابي مسبق.'],
        ],
    ],

    /* ─────── Service Level (SLA) ─────── */
    [
        'title_en'    => 'Clauses Bank — Service Level Agreement',
        'title_ar'    => 'مكتبة البنود — اتفاقية مستوى الخدمة',
        'jurisdiction'=> 'EG',
        'category'    => 'service-level',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — SLA variants',
        'citation_ar' => 'مكتبة البنود — مستوى الخدمة',
        'tags'        => ['boilerplate', 'sla', 'service-credits'],
        'articles'    => [
            ['num' => 'SLA-1', 'heading_en' => 'Uptime commitment with service credits', 'text_en' => 'The supplier shall use commercially reasonable efforts to make the Services available not less than ninety-nine and nine tenths per cent (99.9%) of the time in each calendar month, measured excluding scheduled maintenance windows notified at least seventy-two (72) hours in advance and force-majeure events. If monthly uptime falls below the commitment, the customer is entitled, on written request within thirty (30) days of month-end, to service credits as follows: 99.0%–99.9%: 5% of monthly fees; 95.0%–98.99%: 10%; below 95.0%: 25%. Service credits are the customer\'s sole remedy for availability failures save in cases of repeated material breach.', 'text_ar' => 'يلتزم المورد ببذل العناية التجارية المعقولة لإتاحة الخدمات بنسبة لا تقل عن تسعة وتسعين وتسعة أعشار في المائة (٩٩٫٩٪) من الزمن في كل شهر تقويمي، محسوبةً باستثناء فترات الصيانة المجدولة المُخطر بها قبل اثنتين وسبعين (٧٢) ساعة على الأقل وأحداث القوة القاهرة. وإذا انخفض مستوى الإتاحة الشهري دون النسبة المتعاقد عليها، يحق للعميل ـ بطلب كتابي خلال ثلاثين (٣٠) يوماً من نهاية الشهر ـ الحصول على أرصدة خدمة كالآتي: ٩٩٫٠٪ ـ ٩٩٫٩٪: ٥٪ من رسوم الشهر؛ ٩٥٫٠٪ ـ ٩٨٫٩٩٪: ١٠٪؛ أقل من ٩٥٫٠٪: ٢٥٪. وتُعد أرصدة الخدمة هي وسيلة الانتصاف الوحيدة للعميل عن إخلالات الإتاحة فيما عدا حالات الإخلال الجوهري المتكرر.'],
            ['num' => 'SLA-2', 'heading_en' => 'Incident response times by severity', 'text_en' => 'The supplier shall classify incidents as follows: Severity 1 (Services entirely unavailable in production): initial response within thirty (30) minutes, status updates hourly, target resolution within four (4) hours. Severity 2 (material degradation): response within two (2) hours, updates every four (4) hours, resolution within one (1) business day. Severity 3 (minor): response within one (1) business day, resolution within five (5) business days. Severity 4 (cosmetic/feature request): response within three (3) business days, resolution at next reasonable release.', 'text_ar' => 'يصنف المورد الحوادث على النحو الآتي: المستوى ١ (توقف الخدمة كلياً في بيئة الإنتاج): الاستجابة الأولية خلال ثلاثين (٣٠) دقيقة، وتحديثات الحالة كل ساعة، والحل المستهدف خلال أربع (٤) ساعات. المستوى ٢ (تدهور جوهري): الاستجابة خلال ساعتين، وتحديثات كل أربع (٤) ساعات، والحل خلال يوم عمل واحد. المستوى ٣ (طفيف): الاستجابة خلال يوم عمل واحد، والحل خلال خمسة (٥) أيام عمل. المستوى ٤ (شكلي أو طلب ميزة): الاستجابة خلال ثلاثة (٣) أيام عمل، والحل في الإصدار المعقول التالي.'],
            ['num' => 'SLA-3', 'heading_en' => 'Maintenance windows and notice', 'text_en' => 'The supplier may perform scheduled maintenance during the maintenance window of [02:00–06:00 Cairo time on Sundays] with prior written notice. Emergency maintenance may be performed at any time on shortest practicable notice. The supplier shall use commercially reasonable efforts to minimise disruption.', 'text_ar' => 'يجوز للمورد إجراء الصيانة المجدولة خلال نافذة الصيانة [الساعة ٠٢:٠٠ ـ ٠٦:٠٠ بتوقيت القاهرة أيام الأحد] بإخطار كتابي مسبق. ويجوز إجراء الصيانة الطارئة في أي وقت بأقصر إخطار ممكن. ويبذل المورد العناية التجارية المعقولة لتقليل الانقطاع.'],
        ],
    ],

    /* ─────── Data Protection ─────── */
    [
        'title_en'    => 'Clauses Bank — Data Protection (Egypt PDPL)',
        'title_ar'    => 'مكتبة البنود — حماية البيانات الشخصية (قانون ١٥١/٢٠٢٠)',
        'jurisdiction'=> 'EG',
        'category'    => 'data-protection',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — EG PDPL clause set',
        'citation_ar' => 'مكتبة البنود — حماية البيانات',
        'tags'        => ['boilerplate', 'data-protection', 'pdpl'],
        'articles'    => [
            ['num' => 'DP-1', 'heading_en' => 'Processor obligations (Egyptian PDPL)', 'text_en' => 'The processor undertakes to process personal data only on the documented instructions of the controller and for the purposes set out in this Agreement; to implement technical and organisational measures appropriate to the risk; to ensure that personnel authorised to process the personal data are bound by confidentiality; to assist the controller in responding to data-subject requests; to notify the controller of any personal-data breach without undue delay and no later than forty-eight (48) hours from awareness; and on termination, at the controller\'s option, to return or delete all personal data and certify the deletion.', 'text_ar' => 'يتعهد المعالج بألا يعالج البيانات الشخصية إلا بناءً على تعليمات موثقة من المتحكم وللأغراض المبينة في هذا العقد؛ وباتخاذ التدابير التقنية والتنظيمية المناسبة لمستوى المخاطر؛ وضمان التزام موظفيه المرخص لهم بالمعالجة بواجب السرية؛ ومساعدة المتحكم في الرد على طلبات أصحاب البيانات؛ وإخطار المتحكم بأي اختراق للبيانات الشخصية دون تأخير غير مبرر وفي مدة لا تتجاوز ثماني وأربعين (٤٨) ساعة من العلم به؛ وعند انتهاء العقد ـ وفق اختيار المتحكم ـ إعادة جميع البيانات الشخصية أو حذفها مع تقديم شهادة بالحذف.'],
            ['num' => 'DP-2', 'heading_en' => 'Cross-border transfers consent', 'text_en' => 'The parties acknowledge that the processor may transfer personal data outside the Arab Republic of Egypt for the purposes of providing the Services. The controller authorises such transfers conditional on (a) the existence of a licence from the Personal Data Protection Center where required by Law 151/2020, or (b) the destination jurisdiction offering protection no less than that provided under Egyptian law, and (c) appropriate contractual safeguards being in place.', 'text_ar' => 'يقر الطرفان بأن المعالج قد ينقل البيانات الشخصية إلى خارج جمهورية مصر العربية لأغراض تقديم الخدمات. ويأذن المتحكم بهذا النقل شريطة (أ) وجود ترخيص من المركز المصري لحماية البيانات الشخصية متى تطلب القانون رقم ١٥١ لسنة ٢٠٢٠ ذلك، أو (ب) أن توفر الدولة المستقبلة مستوى حماية لا يقل عن المقرر بموجب القانون المصري، (ج) وضع ضمانات تعاقدية ملائمة.'],
            ['num' => 'DP-3', 'heading_en' => 'Sub-processor approval', 'text_en' => 'The processor may engage sub-processors only with the prior general or specific written authorisation of the controller. In case of general authorisation, the processor shall notify the controller of any intended addition or replacement of a sub-processor at least thirty (30) days in advance, and the controller may object on reasonable grounds. Each sub-processor must be bound by data-protection obligations no less protective than those of this Agreement.', 'text_ar' => 'لا يجوز للمعالج الاستعانة بمعالجين فرعيين إلا بترخيص كتابي عام أو خاص مسبق من المتحكم. وفي حال الترخيص العام، يلتزم المعالج بإخطار المتحكم بأي إضافة أو استبدال لمعالج فرعي قبل ثلاثين (٣٠) يوماً على الأقل، وللمتحكم الاعتراض لأسباب معقولة. ويلتزم كل معالج فرعي بواجبات حماية البيانات لا تقل عن المنصوص عليها في هذا العقد.'],
        ],
    ],

    /* ─────── Change Orders ─────── */
    [
        'title_en'    => 'Clauses Bank — Change Orders',
        'title_ar'    => 'مكتبة البنود — أوامر التغيير',
        'jurisdiction'=> 'EG',
        'category'    => 'change-management',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — change-order procedure',
        'citation_ar' => 'مكتبة البنود — أوامر التغيير',
        'tags'        => ['boilerplate', 'change-orders', 'scope'],
        'articles'    => [
            ['num' => 'CO-1', 'heading_en' => 'Change-order procedure', 'text_en' => 'Either party may at any time propose a change to the scope, deliverables, schedule, or fees by issuing a written change request. Within ten (10) business days of receipt, the supplier shall respond with the impact of the proposed change on scope, schedule, fees, and dependencies. No change becomes binding until both parties have executed a written change order. Pending execution, the parties shall continue performance under the existing Agreement. Either party\'s refusal to agree to a proposed change does not, of itself, constitute a breach of this Agreement.', 'text_ar' => 'يجوز لأي من الطرفين في أي وقت اقتراح تغيير في النطاق أو المخرجات أو الجدول الزمني أو الرسوم بتوجيه طلب تغيير كتابي. ويلتزم المورد بالرد خلال عشرة (١٠) أيام عمل من الاستلام بأثر التغيير المقترح على النطاق والجدول والرسوم والاعتمادات. ولا يصير أي تغيير ملزماً إلا بعد توقيع الطرفين على أمر تغيير كتابي. وإلى حين توقيع أمر التغيير، يستمر الطرفان في تنفيذ العقد الأصلي. ولا يُعد رفض أي طرف الموافقة على تغيير مقترح، في حد ذاته، إخلالاً بالعقد.'],
        ],
    ],

    /* ─────── Audit Rights ─────── */
    [
        'title_en'    => 'Clauses Bank — Audit Rights',
        'title_ar'    => 'مكتبة البنود — حق التدقيق',
        'jurisdiction'=> 'EG',
        'category'    => 'audit',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — audit-rights variants',
        'citation_ar' => 'مكتبة البنود — حق التدقيق',
        'tags'        => ['boilerplate', 'audit', 'compliance'],
        'articles'    => [
            ['num' => 'AR-1', 'heading_en' => 'Books-and-records audit', 'text_en' => 'On not less than fifteen (15) business days\' written notice and not more than once per calendar year, the customer (or a reputable third-party auditor engaged by it and bound by confidentiality obligations) may audit the supplier\'s books and records relating to fees charged and services performed under this Agreement, during normal business hours and without unreasonable disruption to the supplier\'s operations. If the audit reveals overcharges in excess of five per cent (5%) of fees for the audited period, the supplier shall bear the reasonable cost of the audit and refund the overcharged amount with interest at the CBE rate plus two per cent (2%) per annum.', 'text_ar' => 'يجوز للعميل (أو لمدقق خارجي مرموق تستعين به ويلتزم بواجب السرية) ـ بإخطار كتابي مسبق لا يقل عن خمسة عشر (١٥) يوم عمل وبما لا يجاوز مرة واحدة في كل سنة تقويمية ـ التدقيق على دفاتر المورد وسجلاته المتعلقة بالرسوم المستوفاة والخدمات المؤداة بموجب هذا العقد، خلال ساعات العمل المعتادة ودون تعطيل غير معقول لأعمال المورد. وإذا أظهر التدقيق تجاوزات في الفواتير تزيد على خمسة في المائة (٥٪) من رسوم الفترة محل التدقيق، يتحمل المورد التكلفة المعقولة للتدقيق ويرد المبالغ الزائدة مع فائدة بمعدل البنك المركزي المصري زائداً اثنين في المائة (٢٪) سنوياً.'],
        ],
    ],

    /* ─────── Dispute-Resolution Escalation ─────── */
    [
        'title_en'    => 'Clauses Bank — Multi-Tier Dispute Resolution',
        'title_ar'    => 'مكتبة البنود — تسوية المنازعات المتدرجة',
        'jurisdiction'=> 'EG',
        'category'    => 'dispute-resolution',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — escalation ladder',
        'citation_ar' => 'مكتبة البنود — التدرج التفاوضي',
        'tags'        => ['boilerplate', 'dispute-resolution', 'mediation'],
        'articles'    => [
            ['num' => 'DR-1', 'heading_en' => 'Negotiation → mediation → arbitration', 'text_en' => 'Any dispute arising out of or in connection with this Agreement shall first be referred for amicable negotiation between authorised representatives of the parties for a period of thirty (30) days. If the dispute is not resolved by negotiation, it shall be referred to mediation administered by the Cairo Regional Centre for International Commercial Arbitration (CRCICA) Mediation Rules for a further thirty (30) days. If the dispute remains unresolved, it shall be finally settled by arbitration under the CRCICA Rules. The seat of arbitration shall be Cairo; the language shall be Arabic; the tribunal shall consist of three arbitrators. Nothing in this clause prevents a party from seeking interim or conservatory relief from a competent court.', 'text_ar' => 'يُحال أي نزاع ينشأ عن أو يتعلق بهذا العقد أولاً إلى التفاوض الودي بين ممثلين مفوضين من الطرفين لمدة ثلاثين (٣٠) يوماً. فإذا لم يُحل النزاع بالتفاوض، يُحال إلى الوساطة وفق قواعد الوساطة لمركز القاهرة الإقليمي للتحكيم التجاري الدولي لمدة ثلاثين (٣٠) يوماً أخرى. فإذا ظل النزاع قائماً، يتم تسويته نهائياً بالتحكيم وفق قواعد التحكيم لمركز القاهرة الإقليمي. ويكون مقر التحكيم القاهرة، ولغة التحكيم العربية، وتتكون هيئة التحكيم من ثلاثة محكمين. ولا يمنع هذا الشرط أي طرف من اللجوء إلى محكمة مختصة لطلب إجراءات وقتية أو تحفظية.'],
        ],
    ],

    /* ─────── Anti-Corruption / Anti-Bribery ─────── */
    [
        'title_en'    => 'Clauses Bank — Anti-Corruption & Anti-Bribery',
        'title_ar'    => 'مكتبة البنود — مكافحة الفساد والرشوة',
        'jurisdiction'=> 'EG',
        'category'    => 'compliance',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — anti-corruption covenants',
        'citation_ar' => 'مكتبة البنود — تعهدات مكافحة الفساد',
        'tags'        => ['boilerplate', 'anti-corruption', 'anti-bribery', 'compliance'],
        'articles'    => [
            ['num' => 'AC-1', 'heading_en' => 'Compliance with anti-bribery laws', 'text_en' => 'Each party represents and warrants that it has not, and shall not, directly or indirectly, offer, promise, give, request, or accept any undue advantage to or from any public official or private counterparty in connection with this Agreement, and that it complies with the Egyptian Penal Code provisions on bribery (Articles 103–111 bis), the U.S. Foreign Corrupt Practices Act where applicable, the U.K. Bribery Act where applicable, and all other applicable anti-corruption laws. A material breach of this representation gives the other party the right to terminate this Agreement with immediate effect, without prejudice to any other remedy.', 'text_ar' => 'يُقر كل طرف ويضمن أنه لم يقم ولن يقوم، بطريقة مباشرة أو غير مباشرة، بعرض أو وعد أو تقديم أو طلب أو قبول أي ميزة غير مستحقة من أو إلى أي موظف عام أو طرف خاص بمناسبة هذا العقد، وأنه ملتزم بأحكام قانون العقوبات المصري في شأن الرشوة (المواد ١٠٣ ـ ١١١ مكرر)، وبقانون الممارسات الأجنبية الفاسدة الأمريكي حيث ينطبق، وبقانون الرشوة البريطاني حيث ينطبق، وبكافة قوانين مكافحة الفساد الواجبة التطبيق. ويُخول الإخلال الجوهري بهذا الإقرار الطرف الآخر حق إنهاء العقد بأثر فوري دون إخلال بأي وسيلة انتصاف أخرى.'],
        ],
    ],

    /* ─────── Insurance Covenants ─────── */
    [
        'title_en'    => 'Clauses Bank — Insurance Covenants',
        'title_ar'    => 'مكتبة البنود — التعهدات التأمينية',
        'jurisdiction'=> 'EG',
        'category'    => 'insurance',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — insurance maintenance',
        'citation_ar' => 'مكتبة البنود — الالتزامات التأمينية',
        'tags'        => ['boilerplate', 'insurance', 'liability'],
        'articles'    => [
            ['num' => 'INS-1', 'heading_en' => 'Insurance to be maintained by supplier', 'text_en' => 'Throughout the term of this Agreement, the supplier shall maintain at its own cost the following insurance with reputable insurers licensed by the Financial Regulatory Authority: (a) professional indemnity insurance with a per-claim limit not less than one million (1,000,000) US dollars; (b) commercial general liability insurance with a limit not less than two million (2,000,000) US dollars; (c) workmen\'s compensation as required by law; and (d) cyber-liability insurance where the supplier processes personal data. The supplier shall on request provide certificates of insurance evidencing the foregoing.', 'text_ar' => 'يلتزم المورد طوال مدة العقد بأن يحتفظ على نفقته بالتأمينات الآتية مع شركات تأمين مرموقة مرخصة من الهيئة العامة للرقابة المالية: (أ) تأمين المسؤولية المهنية بحد لا يقل عن مليون (١٫٠٠٠٫٠٠٠) دولار أمريكي لكل مطالبة؛ (ب) تأمين المسؤولية المدنية العامة بحد لا يقل عن مليوني (٢٫٠٠٠٫٠٠٠) دولار أمريكي؛ (ج) تأمين إصابات العمل وفق ما يستلزمه القانون؛ (د) تأمين المسؤولية السيبرانية إذا كان المورد يعالج بيانات شخصية. ويلتزم المورد عند الطلب بتقديم شهادات تأمين مثبتة لما تقدم.'],
        ],
    ],

    /* ─────── Most-Favoured-Nation & Exclusivity ─────── */
    [
        'title_en'    => 'Clauses Bank — Most-Favoured Customer',
        'title_ar'    => 'مكتبة البنود — شرط الدولة الأولى بالرعاية',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — MFN and exclusivity',
        'citation_ar' => 'مكتبة البنود — أفضل العملاء والحصرية',
        'tags'        => ['boilerplate', 'mfn', 'exclusivity'],
        'articles'    => [
            ['num' => 'MFN-1', 'heading_en' => 'Most-favoured-customer pricing', 'text_en' => 'The supplier represents that the fees charged to the customer under this Agreement are no less favourable than those charged by the supplier to any other customer of comparable size and scope for substantially similar services in Egypt. If during the term the supplier offers better terms to a comparable customer, the supplier shall extend the same terms to the customer for the remainder of the term.', 'text_ar' => 'يُقر المورد بأن الرسوم المُحملة على العميل بموجب هذا العقد ليست أقل تفضيلاً من تلك التي يتقاضاها المورد من أي عميل آخر مماثل في الحجم والنطاق عن خدمات مماثلة جوهرياً في مصر. وإذا قدم المورد خلال المدة شروطاً أفضل لعميل مماثل، يلتزم بمدّ نفس الشروط للعميل لباقي المدة.'],
            ['num' => 'EX-1', 'heading_en' => 'Exclusivity — limited territorial', 'text_en' => 'During the term, the supplier shall not directly or indirectly provide services substantially similar to the Services to any competitor of the customer in the Arab Republic of Egypt, as the parties may identify in writing from time to time. This exclusivity does not extend beyond the term and does not prevent the supplier from serving customers outside the agreed competitor list or outside Egypt.', 'text_ar' => 'خلال مدة العقد، يلتزم المورد بألا يقدم بطريقة مباشرة أو غير مباشرة خدمات مماثلة جوهرياً للخدمات لأي منافس للعميل داخل جمهورية مصر العربية، وفقاً لما يُحدده الطرفان كتابةً من حين لآخر. ولا تمتد هذه الحصرية لما بعد انتهاء المدة ولا تمنع المورد من خدمة عملاء خارج قائمة المنافسين المتفق عليها أو خارج مصر.'],
        ],
    ],

    /* ─────── Subcontracting ─────── */
    [
        'title_en'    => 'Clauses Bank — Subcontracting',
        'title_ar'    => 'مكتبة البنود — المقاولة من الباطن',
        'jurisdiction'=> 'EG',
        'category'    => 'subcontracting',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — subcontracting',
        'citation_ar' => 'مكتبة البنود — المقاولة من الباطن',
        'tags'        => ['boilerplate', 'subcontract'],
        'articles'    => [
            ['num' => 'SUB-1', 'heading_en' => 'Subcontracting requires consent and pass-through obligations', 'text_en' => 'The supplier may subcontract any of its obligations under this Agreement only with the prior written consent of the customer (such consent not to be unreasonably withheld), save for routine support, hosting, or back-office services. The supplier remains fully liable for the acts and omissions of its subcontractors as if they were its own, and shall ensure each subcontractor is bound by obligations no less protective of the customer than those of this Agreement, in particular as to confidentiality, data protection, and intellectual property.', 'text_ar' => 'لا يجوز للمورد إسناد أي من التزاماته بموجب هذا العقد من الباطن إلا بموافقة كتابية مسبقة من العميل (لا تُحجب هذه الموافقة دون سبب معقول)، فيما عدا خدمات الدعم الروتينية والاستضافة والخدمات الإدارية المساندة. ويظل المورد مسؤولاً مسؤولية كاملة عن أفعال وامتناعات مقاوليه من الباطن كأنها أفعاله، ويلتزم بأن يكون كل مقاول من الباطن مرتبطاً بالتزامات لا تقل حماية للعميل عن المنصوص عليها في هذا العقد، خاصةً فيما يتعلق بالسرية وحماية البيانات والملكية الفكرية.'],
        ],
    ],

    /* ─────── Limitation of Liability ─────── */
    [
        'title_en'    => 'Clauses Bank — Limitation of Liability',
        'title_ar'    => 'مكتبة البنود — تحديد المسؤولية',
        'jurisdiction'=> 'EG',
        'category'    => 'liability',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — liability caps',
        'citation_ar' => 'مكتبة البنود — تحديد المسؤولية',
        'tags'        => ['boilerplate', 'liability', 'cap'],
        'articles'    => [
            ['num' => 'LL-1', 'heading_en' => 'No indirect or consequential damages', 'text_en' => 'Neither party shall be liable to the other for any indirect, incidental, special, or consequential damages, including loss of profits, loss of revenue, loss of business, loss of goodwill, or loss of data, even if advised of the possibility of such damages and regardless of the cause of action. This limitation does not apply to: (a) breaches of confidentiality; (b) breaches of intellectual-property obligations; (c) wilful misconduct or fraud; (d) indemnification obligations for third-party claims; (e) liability that cannot lawfully be limited.', 'text_ar' => 'لا يكون أيٌّ من الطرفين مسؤولاً قبل الآخر عن أي أضرار غير مباشرة أو عارضة أو خاصة أو تبعية، بما فيها فوات الكسب أو الإيرادات أو الأعمال أو السمعة التجارية أو فقدان البيانات، حتى وإن أُخطر بإمكان وقوعها وبصرف النظر عن سبب الدعوى. ولا يسري هذا التحديد على: (أ) الإخلال بالسرية؛ (ب) الإخلال بالتزامات الملكية الفكرية؛ (ج) سوء السلوك المتعمد أو الاحتيال؛ (د) التزامات التعويض عن مطالبات الغير؛ (هـ) المسؤولية التي لا يجوز تحديدها قانوناً.'],
            ['num' => 'LL-2', 'heading_en' => 'Aggregate cap at 12 months fees', 'text_en' => 'Subject to the carve-outs above, the aggregate liability of each party under or in connection with this Agreement, regardless of cause of action, shall not exceed the total fees paid or payable by the customer to the supplier under this Agreement during the twelve (12) months immediately preceding the first event giving rise to the claim.', 'text_ar' => 'مع مراعاة الاستثناءات السابقة، لا تتجاوز مسؤولية كل طرف الإجمالية بموجب هذا العقد أو فيما يتعلق به، أياً كان سبب الدعوى، إجمالي الرسوم المدفوعة أو المستحقة من العميل للمورد بموجب هذا العقد خلال الاثني عشر (١٢) شهراً السابقة مباشرة على أول حدث أدى إلى المطالبة.'],
        ],
    ],

    /* ─────── Employment-Specific Clauses ─────── */
    [
        'title_en'    => 'Clauses Bank — Employment Contract Essentials',
        'title_ar'    => 'مكتبة البنود — جوهر عقد العمل',
        'jurisdiction'=> 'EG',
        'category'    => 'employment',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — employment essentials',
        'citation_ar' => 'مكتبة البنود — عقد العمل',
        'tags'        => ['boilerplate', 'employment', 'hr'],
        'articles'    => [
            ['num' => 'EMP-1', 'heading_en' => 'Probation period (EG, 3 months)', 'text_en' => 'The first three (3) months of employment constitute a probation period during which either party may terminate the contract without notice and without compensation, save in cases of arbitrary dismissal. The employee shall be subject to probation only once with the same employer for the same role. Successful completion of probation does not, of itself, convert the contract from fixed-term to indefinite.', 'text_ar' => 'تُعد الأشهر الثلاثة (٣) الأولى من العمل فترة اختبار يجوز خلالها لأي من الطرفين إنهاء العقد دون إخطار ودون تعويض، فيما عدا حالات الفصل التعسفي. ولا يخضع العامل لفترة الاختبار إلا مرة واحدة لدى صاحب العمل ذاته وفي الوظيفة ذاتها. ولا يحول اجتياز فترة الاختبار، في حد ذاته، العقد من محدد المدة إلى غير محدد المدة.'],
            ['num' => 'EMP-2', 'heading_en' => 'Working hours and overtime', 'text_en' => 'The employee\'s standard working hours are eight (8) hours per day or forty-eight (48) hours per week, exclusive of breaks. Work performed beyond standard hours at the employer\'s request entitles the employee to additional pay calculated at one hundred and thirty-five per cent (135%) of the basic hourly wage for day overtime, one hundred and seventy per cent (170%) for night overtime, and double the basic wage for rest-day or public-holiday work, in accordance with Egyptian Labour Law.', 'text_ar' => 'ساعات عمل العامل المعتادة ثمان (٨) ساعات يومياً أو ثمان وأربعون (٤٨) ساعة أسبوعياً، باستثناء فترات الراحة. ويستحق العامل عن الساعات الإضافية المؤداة بطلب صاحب العمل أجراً إضافياً يعادل مائة وخمسة وثلاثين في المائة (١٣٥٪) من أجر الساعة الأساسي للعمل النهاري الإضافي، ومائة وسبعين في المائة (١٧٠٪) للعمل الليلي الإضافي، وضعف الأجر الأساسي عن العمل في أيام الراحة الأسبوعية أو العطلات الرسمية، وفقاً لأحكام قانون العمل المصري.'],
            ['num' => 'EMP-3', 'heading_en' => 'Confidentiality and IP assignment (employment)', 'text_en' => 'The employee acknowledges that all confidential information of the employer disclosed to or learned by the employee in the course of employment is the property of the employer and shall not be used or disclosed save for the performance of duties. Any invention, work, software, design, or other intellectual-property output created by the employee during employment and within its scope is the property of the employer ab initio, without further consideration. The employee shall execute any documents the employer reasonably requires to perfect this ownership.', 'text_ar' => 'يقر العامل بأن جميع المعلومات السرية لصاحب العمل التي يُفصح له عنها أو يعلم بها بمناسبة العمل ملك لصاحب العمل، ولا يجوز استخدامها أو إفشاؤها إلا لأداء واجبات الوظيفة. وتؤول إلى صاحب العمل منذ نشأتها ودون مقابل إضافي أي اختراع أو عمل أو برنامج أو تصميم أو ناتج آخر من الملكية الفكرية ينشأ عن العامل خلال علاقة العمل وفي نطاقها. ويلتزم العامل بتوقيع أي مستندات يطلبها صاحب العمل بشكل معقول لاستكمال هذا التملك.'],
        ],
    ],

    /* ─────── Commercial Lease ─────── */
    [
        'title_en'    => 'Clauses Bank — Commercial Lease',
        'title_ar'    => 'مكتبة البنود — الإيجار التجاري',
        'jurisdiction'=> 'EG',
        'category'    => 'real-estate',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — commercial-lease essentials',
        'citation_ar' => 'مكتبة البنود — الإيجار التجاري',
        'tags'        => ['boilerplate', 'lease', 'real-estate'],
        'articles'    => [
            ['num' => 'LSE-1', 'heading_en' => 'Term and renewal', 'text_en' => 'The term of this lease is [N] years commencing on the handover date, renewable for additional periods of the same duration by tacit agreement unless either party notifies the other in writing of intention not to renew at least ninety (90) days before the end of the then-current term. Renewal is subject to a rent adjustment as set out in the rent-review clause.', 'text_ar' => 'مدة هذا الإيجار [ن] سنة تبدأ من تاريخ التسليم، وتتجدد لمدد مماثلة بالتراضي الضمني ما لم يخطر أيٌّ من الطرفين الآخر كتابةً برغبته في عدم التجديد قبل انتهاء المدة الجارية بتسعين (٩٠) يوماً على الأقل. ويخضع التجديد لمراجعة القيمة الإيجارية وفق شرط مراجعة الأجرة.'],
            ['num' => 'LSE-2', 'heading_en' => 'Rent review (CPI-based)', 'text_en' => 'On each anniversary of the commencement date, the annual rent shall be adjusted upward by the annual rate of inflation as published by the Central Agency for Public Mobilization and Statistics (CAPMAS) for the immediately preceding calendar year, capped at seven per cent (7%) per annum. The new rent applies from the anniversary date without need for further amendment.', 'text_ar' => 'في الذكرى السنوية لتاريخ البداية، تُعدَّل القيمة الإيجارية السنوية بمعدل التضخم السنوي المنشور من الجهاز المركزي للتعبئة العامة والإحصاء عن السنة التقويمية السابقة مباشرة، بحد أقصى سبعة في المائة (٧٪) سنوياً. وتسري القيمة الجديدة من تاريخ الذكرى السنوية دون حاجة إلى تعديل إضافي.'],
            ['num' => 'LSE-3', 'heading_en' => 'Repairs and improvements', 'text_en' => 'The lessor is responsible for major structural repairs and for repairs necessary to keep the premises in a condition fit for the agreed commercial use. The lessee is responsible for ordinary maintenance, day-to-day repairs, and any damage caused by misuse. Improvements to the premises by the lessee require prior written consent of the lessor and become the property of the lessor at the end of the lease, without compensation, unless the parties agree otherwise.', 'text_ar' => 'يلتزم المؤجر بالإصلاحات الإنشائية الكبرى وبالإصلاحات اللازمة لإبقاء العين في حالة تصلح للاستخدام التجاري المتفق عليه. ويلتزم المستأجر بالصيانة العادية والإصلاحات اليومية وأي تلفيات ناشئة عن سوء الاستخدام. ويستلزم إجراء المستأجر تحسينات على العين الموافقة الكتابية المسبقة من المؤجر، وتؤول هذه التحسينات إلى المؤجر عند انتهاء الإيجار دون تعويض ما لم يتفق الطرفان على غير ذلك.'],
            ['num' => 'LSE-4', 'heading_en' => 'Sub-letting and assignment', 'text_en' => 'The lessee may not sub-let the premises in whole or in part, nor assign this lease, without the prior written consent of the lessor. Permitted sub-letting or assignment shall require the sub-tenant or assignee to be bound by the obligations of this lease, and the lessee remains jointly liable with them for performance.', 'text_ar' => 'لا يجوز للمستأجر تأجير العين من الباطن كلياً أو جزئياً، أو التنازل عن هذا الإيجار، دون موافقة كتابية مسبقة من المؤجر. ويُشترط في التأجير من الباطن أو التنازل المسموح به التزام المستأجر من الباطن أو المتنازل إليه بأحكام هذا الإيجار، ويظل المستأجر مسؤولاً معه بالتضامن عن التنفيذ.'],
        ],
    ],

    /* ─────── Software Licence ─────── */
    [
        'title_en'    => 'Clauses Bank — Software Licence',
        'title_ar'    => 'مكتبة البنود — ترخيص البرمجيات',
        'jurisdiction'=> 'EG',
        'category'    => 'ip',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — software licence variants',
        'citation_ar' => 'مكتبة البنود — ترخيص البرمجيات',
        'tags'        => ['boilerplate', 'software', 'licence', 'saas'],
        'articles'    => [
            ['num' => 'LIC-1', 'heading_en' => 'Limited licence grant (SaaS)', 'text_en' => 'Subject to payment of fees and compliance with this Agreement, the licensor grants the licensee a non-exclusive, non-transferable, non-sublicensable, revocable licence to access and use the Software during the term solely for the licensee\'s internal business purposes and within the scope of users, modules, and limits set out in the order form. The licence does not include any right to copy, modify, reverse-engineer, distribute, or create derivative works of the Software.', 'text_ar' => 'مع مراعاة سداد الرسوم والامتثال لأحكام هذا العقد، يمنح المرخِّص المرخَّص له ترخيصاً غير حصري وغير قابل للتنازل أو الترخيص من الباطن وقابلاً للإلغاء للوصول إلى البرنامج واستخدامه طوال مدة العقد لأغراض الأعمال الداخلية للمرخَّص له فقط، ضمن حدود المستخدمين والوحدات المنصوص عليها في طلب الخدمة. ولا يشمل الترخيص أي حق في نسخ البرنامج أو تعديله أو إجراء هندسة عكسية له أو توزيعه أو إنشاء أعمال مشتقة منه.'],
            ['num' => 'LIC-2', 'heading_en' => 'IP retention by licensor', 'text_en' => 'All intellectual-property rights in the Software, including any updates, upgrades, derivative works, and documentation, are and remain the exclusive property of the licensor. Nothing in this Agreement transfers any IP rights to the licensee save the limited use right expressly granted. Licensee feedback may be used by the licensor without compensation or attribution.', 'text_ar' => 'تظل جميع حقوق الملكية الفكرية في البرنامج، بما في ذلك أي تحديثات أو ترقيات أو أعمال مشتقة أو وثائق، مملوكة حصرياً للمرخِّص. ولا ينقل هذا العقد أي حقوق ملكية فكرية إلى المرخَّص له فيما عدا حق الاستخدام المحدود الممنوح صراحةً. ويجوز للمرخِّص استخدام ملاحظات المرخَّص له دون مقابل أو إسناد.'],
            ['num' => 'LIC-3', 'heading_en' => 'Open-source compliance', 'text_en' => 'The Software may include open-source components subject to their own licences, listed in the documentation. To the extent of any inconsistency between this Agreement and an applicable open-source licence as to those components, the open-source licence shall prevail for the component concerned. The licensor warrants that its use of open-source components does not impose copyleft obligations on the licensee\'s own software unless explicitly disclosed.', 'text_ar' => 'قد يشتمل البرنامج على مكونات مفتوحة المصدر خاضعة لتراخيصها الخاصة المبينة في الوثائق. وعند وجود تعارض بين هذا العقد وترخيص مفتوح المصدر بشأن هذه المكونات، يسود الترخيص المفتوح المصدر فيما يخص المكون المعني. ويضمن المرخِّص ألا يترتب على استخدامه للمكونات المفتوحة المصدر فرض التزامات نسخ-يسار على برمجيات المرخَّص له ما لم يُفصح عن ذلك صراحة.'],
        ],
    ],

    /* ─────── Distribution / Agency ─────── */
    [
        'title_en'    => 'Clauses Bank — Distribution & Commercial Agency',
        'title_ar'    => 'مكتبة البنود — التوزيع والوكالة التجارية',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — distribution and agency',
        'citation_ar' => 'مكتبة البنود — التوزيع والوكالة',
        'tags'        => ['boilerplate', 'distribution', 'agency'],
        'articles'    => [
            ['num' => 'DST-1', 'heading_en' => 'Exclusive distribution — territorial', 'text_en' => 'The supplier appoints the distributor as its exclusive distributor of the Products within the Territory during the term. The supplier shall not, directly or indirectly, sell the Products in the Territory or appoint another distributor for the Territory. The distributor shall not, directly or indirectly, sell competing products or actively solicit customers outside the Territory.', 'text_ar' => 'يعين المورد الموزع موزعاً حصرياً للمنتجات داخل النطاق الإقليمي طوال مدة العقد. ويلتزم المورد بألا يقوم بصورة مباشرة أو غير مباشرة ببيع المنتجات داخل النطاق الإقليمي أو تعيين موزع آخر له. كما يلتزم الموزع بألا يقوم بصورة مباشرة أو غير مباشرة ببيع منتجات منافسة أو السعي النشط للحصول على عملاء خارج النطاق الإقليمي.'],
            ['num' => 'DST-2', 'heading_en' => 'Minimum purchase commitments', 'text_en' => 'The distributor undertakes to purchase Products of a minimum value as set out in Schedule [X] for each calendar year. Failure to meet eighty per cent (80%) of the annual minimum, save for force-majeure causes, entitles the supplier at its option to (a) convert the distribution to non-exclusive, (b) reduce the Territory, or (c) terminate this Agreement on sixty (60) days\' notice.', 'text_ar' => 'يتعهد الموزع بشراء منتجات بقيمة لا تقل عن الحد الأدنى المبين في الملحق [X] لكل سنة تقويمية. وعدم تحقيق ثمانين في المائة (٨٠٪) من الحد الأدنى السنوي، فيما عدا أسباب القوة القاهرة، يخول المورد بحسب اختياره (أ) تحويل التوزيع إلى غير حصري، أو (ب) تقليص النطاق الإقليمي، أو (ج) إنهاء العقد بإخطار ستين (٦٠) يوماً.'],
            ['num' => 'DST-3', 'heading_en' => 'Commercial-agent termination indemnity (EG)', 'text_en' => 'Where this Agreement constitutes a commercial-agency relationship registered under Egyptian Commercial Code Articles 177–198, the agent is entitled, on termination by the principal without sufficient fault or expiry without renewal, to an indemnity in compensation for the customers and goodwill developed, calculated by reference to a number of months\' average commission having regard to the duration of the relationship and the customers brought.', 'text_ar' => 'حيث يشكل هذا العقد علاقة وكالة تجارية مسجلة وفقاً للمواد ١٧٧ ـ ١٩٨ من قانون التجارة المصري، يستحق الوكيل عند إنهاء الموكِّل العقد دون خطأ كافٍ أو انتهائه دون تجديد تعويضاً عن العملاء والشهرة التجارية المكتسبين، يُحسب بالرجوع إلى عدد من الأشهر من متوسط العمولة بمراعاة مدة العلاقة والعملاء الذين جلبهم الوكيل.'],
        ],
    ],

    /* ─────── Escrow ─────── */
    [
        'title_en'    => 'Clauses Bank — Escrow Arrangements',
        'title_ar'    => 'مكتبة البنود — الإيداع لدى الغير',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — escrow / holdback',
        'citation_ar' => 'مكتبة البنود — الإيداع لدى الغير',
        'tags'        => ['boilerplate', 'escrow', 'holdback'],
        'articles'    => [
            ['num' => 'ESC-1', 'heading_en' => 'Purchase-price holdback in escrow', 'text_en' => 'A portion of the purchase price equal to [N]% shall be deposited on closing into an escrow account at [Bank Name] for a period of [N] months, to secure the seller\'s indemnification obligations under Article [X]. The escrow agent releases the held amount to the seller at the end of the holdback period less any amount validly claimed by the buyer in accordance with the escrow agreement.', 'text_ar' => 'يُودع جزء من الثمن يعادل [ن]٪ عند الإقفال في حساب إيداع لدى [اسم البنك] لمدة [ن] شهراً، لضمان التزامات البائع بالتعويض بموجب البند [X]. ويفرج وكيل الإيداع عن المبلغ المحتجز لصالح البائع عند نهاية فترة الحجز مخصوماً منه أي مبلغ صحت مطالبة المشتري به وفقاً لاتفاق الإيداع.'],
        ],
    ],

    /* ─────── Sanctions & Export Controls ─────── */
    [
        'title_en'    => 'Clauses Bank — Sanctions & Export Controls',
        'title_ar'    => 'مكتبة البنود — العقوبات الدولية وضوابط التصدير',
        'jurisdiction'=> 'EG',
        'category'    => 'compliance',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — sanctions covenants',
        'citation_ar' => 'مكتبة البنود — تعهدات العقوبات',
        'tags'        => ['boilerplate', 'sanctions', 'export-controls'],
        'articles'    => [
            ['num' => 'SNC-1', 'heading_en' => 'Sanctions compliance representation', 'text_en' => 'Each party represents and warrants that neither it, nor any of its directors, officers, controlling shareholders, or subsidiaries, is (a) listed on any sanctions list maintained by the United Nations Security Council, the European Union, the United Kingdom, the United States Office of Foreign Assets Control, or any other applicable authority; (b) located, organised, or resident in a comprehensively sanctioned jurisdiction; or (c) acting for the benefit of any such person. Each party shall comply with all applicable sanctions and export-control laws in the performance of this Agreement. Material breach of this representation gives the non-breaching party the right to terminate with immediate effect.', 'text_ar' => 'يُقر كل طرف ويضمن أنه ـ ولا أيٌّ من أعضاء مجلس إدارته أو مسؤوليه التنفيذيين أو مساهميه المسيطرين أو شركاته التابعة ـ ليس (أ) مدرجاً على أي قائمة عقوبات صادرة عن مجلس الأمن التابع للأمم المتحدة أو الاتحاد الأوروبي أو المملكة المتحدة أو مكتب مراقبة الأصول الأجنبية الأمريكي أو أي جهة أخرى مختصة؛ (ب) موجوداً أو منظماً أو مقيماً في دولة خاضعة لعقوبات شاملة؛ (ج) متصرفاً لصالح أي من هؤلاء. ويلتزم كل طرف بالامتثال لكافة قوانين العقوبات وضوابط التصدير الواجبة التطبيق عند تنفيذ هذا العقد. ويُخوّل الإخلال الجوهري بهذا الإقرار الطرف الآخر حق الإنهاء بأثر فوري.'],
        ],
    ],

    /* ─────── Force Majeure — Pandemic & Cyber Extended ─────── */
    [
        'title_en'    => 'Clauses Bank — Force Majeure (Extended)',
        'title_ar'    => 'مكتبة البنود — القوة القاهرة (موسعة)',
        'jurisdiction'=> 'EG',
        'category'    => 'force-majeure',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — extended force-majeure (post-COVID, cyber)',
        'citation_ar' => 'مكتبة البنود — القوة القاهرة الموسعة',
        'tags'        => ['boilerplate', 'force-majeure', 'pandemic', 'cyber'],
        'articles'    => [
            ['num' => 'FM-2', 'heading_en' => 'Pandemic and public-health measures', 'text_en' => 'For the avoidance of doubt, pandemic, epidemic, quarantine, lockdown, mandatory closure, or any other public-health measure imposed by a competent authority qualifies as a force-majeure event under this Agreement, provided the affected party demonstrates the causal link between the measure and its inability to perform.', 'text_ar' => 'تأكيداً ودفعاً للالتباس، تُعد الجائحة والوباء والحجر الصحي والإغلاق وأي تدبير صحة عامة آخر تفرضه جهة مختصة من قبيل أحداث القوة القاهرة بموجب هذا العقد، بشرط أن يثبت الطرف المتأثر العلاقة السببية بين التدبير وعدم قدرته على التنفيذ.'],
            ['num' => 'FM-3', 'heading_en' => 'Cyber attack and infrastructure outage', 'text_en' => 'A cyber attack on the affected party\'s systems, or an outage of essential third-party infrastructure (cloud providers, internet backbone, energy grid) beyond the reasonable control of the affected party and despite implementation of industry-standard safeguards, qualifies as a force-majeure event. The affected party shall provide promptly all available information on the event and on mitigation actions.', 'text_ar' => 'يُعد الهجوم السيبراني على أنظمة الطرف المتأثر، أو انقطاع البنية التحتية الجوهرية المقدمة من الغير (مقدمو الحوسبة السحابية، شبكة الإنترنت الأساسية، شبكة الطاقة) خارج الإرادة المعقولة للطرف المتأثر ورغم تطبيقه لضمانات بمستوى المعيار الصناعي، من قبيل القوة القاهرة. ويلتزم الطرف المتأثر بتقديم جميع المعلومات المتاحة بشأن الحدث وإجراءات الحد منه على وجه السرعة.'],
        ],
    ],

    /* ─────── Notice Periods / Survival ─────── */
    [
        'title_en'    => 'Clauses Bank — Survival of Obligations',
        'title_ar'    => 'مكتبة البنود — استمرار الالتزامات',
        'jurisdiction'=> 'EG',
        'category'    => 'general',
        'source'      => 'clauses-bank',
        'language'    => 'ar',
        'citation_en' => 'My-lawyer clauses bank — survival',
        'citation_ar' => 'مكتبة البنود — الاستمرار',
        'tags'        => ['boilerplate', 'survival'],
        'articles'    => [
            ['num' => 'SUR-1', 'heading_en' => 'Survival of post-termination obligations', 'text_en' => 'The following obligations survive termination or expiry of this Agreement for the periods stated, or otherwise indefinitely: (a) confidentiality — five (5) years; (b) intellectual-property ownership and licence-back — indefinitely; (c) indemnification for events occurring during the term — until the applicable limitation period expires; (d) limitation of liability — indefinitely as to events during the term; (e) dispute-resolution clause and governing-law clause — indefinitely.', 'text_ar' => 'تستمر الالتزامات الآتية بعد انتهاء العقد أو انقضاء مدته للفترات المبينة أو بصورة دائمة بحسب الحال: (أ) السرية ـ لمدة خمس (٥) سنوات؛ (ب) ملكية الملكية الفكرية وحق الترخيص الرجعي ـ بصورة دائمة؛ (ج) التعويض عن الأحداث التي وقعت خلال المدة ـ حتى انتهاء فترة التقادم المنطبقة؛ (د) تحديد المسؤولية ـ بصورة دائمة بشأن الأحداث الواقعة خلال المدة؛ (هـ) شرط تسوية المنازعات والقانون الحاكم ـ بصورة دائمة.'],
        ],
    ],
];
