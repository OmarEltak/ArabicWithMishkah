<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Eastlaws Snapshot — query × jurisdiction × category matrix
|--------------------------------------------------------------------------
|
| Each query is a structured row: ['q' => 'arabic search', 'category' =>
| 'companies', 'tags' => ['commercial']]. The snapshot command tags every
| ingested doc with the category, enabling scoped RAG retrieval (e.g.
| corporate-law lookups don't pull family-law chunks).
|
| Plain strings still work for backward compat and get a 'civil-general'
| category by default.
|
| 'profiles' lets you select a corpus subset by use case:
|   - 'general'   : the broad sweep covering most lawyer practice areas
|   - 'corporate' : extra companies/M&A/capital-markets/arbitration queries
|                   for a corporate-focused customer
*/

return [

    /*
    | Defaults applied per query unless overridden via CLI flags.
    | Throttled by EASTLAWS_REQUEST_DELAY_MS (default 3000) between every
    | outbound HTTP call.
    */

    'max_documents_per_query' => 15,
    'max_pages_per_query' => 3,

    /*
    | Recognised category vocabulary — the values flow into legal_documents.category.
    | Add new ones here as you expand jurisdictional coverage.
    */
    'categories' => [
        'civil-general'    => 'Civil — general principles',
        'companies'        => 'Companies / corporate',
        'commercial'       => 'Commercial / trade',
        'labour'           => 'Labour & employment',
        'real-estate'      => 'Real estate / property',
        'family'           => 'Family / personal status',
        'procedural'       => 'Civil & commercial procedure',
        'criminal'         => 'Criminal',
        'tax'              => 'Tax & customs',
        'social-insurance' => 'Social insurance / pensions',
        'notarisation'     => 'Notarisation / authentication',
        'arbitration'      => 'Arbitration',
        'capital-markets'  => 'Capital markets / securities',
        'investment'       => 'Investment / FDI',
        'insolvency'       => 'Bankruptcy / insolvency',
        'compliance'       => 'AML / anti-corruption / compliance',
        'banking'          => 'Banking / finance',
        'enforcement'      => 'Enforcement / execution',
    ],

    /*
    | jurisdictions[code]['queries'][profile] → array of query rows.
    */
    'jurisdictions' => [
        'EG' => [
            'country_id' => 1,
            'queries' => [
                'general' => [
                    ['q' => 'القانون المدني',                    'category' => 'civil-general'],
                    ['q' => 'قانون العمل',                       'category' => 'labour'],
                    ['q' => 'قانون المرافعات المدنية والتجارية',  'category' => 'procedural'],
                    ['q' => 'قانون الشهر العقاري',                'category' => 'real-estate'],
                    ['q' => 'قانون التجارة',                     'category' => 'commercial'],
                    ['q' => 'قانون التأمينات الاجتماعية',          'category' => 'social-insurance'],
                    ['q' => 'قانون الشركات',                     'category' => 'companies'],
                    ['q' => 'قانون الأحوال الشخصية',              'category' => 'family'],
                    ['q' => 'قانون التوثيق',                    'category' => 'notarisation'],
                    ['q' => 'قانون الإيجار',                     'category' => 'real-estate'],
                ],
                'corporate' => [
                    ['q' => 'قانون سوق رأس المال',                      'category' => 'capital-markets'],
                    ['q' => 'قانون ضمانات وحوافز الاستثمار',              'category' => 'investment'],
                    ['q' => 'قانون التحكيم في المواد المدنية والتجارية', 'category' => 'arbitration'],
                    ['q' => 'قانون إعادة الهيكلة والصلح والإفلاس',        'category' => 'insolvency'],
                    ['q' => 'قانون مكافحة غسل الأموال',                   'category' => 'compliance'],
                    ['q' => 'قانون البنك المركزي والجهاز المصرفي',         'category' => 'banking'],
                    ['q' => 'قانون حماية المنافسة',                     'category' => 'compliance'],
                    ['q' => 'قانون الضريبة على الدخل',                    'category' => 'tax'],
                ],
                'deep' => [
                    // Corporate sub-domains (granular companies law topics)
                    ['q' => 'شركات المساهمة',                       'category' => 'companies'],
                    ['q' => 'الشركات ذات المسؤولية المحدودة',        'category' => 'companies'],
                    ['q' => 'لائحة تنفيذية قانون الشركات',           'category' => 'companies'],
                    ['q' => 'قانون اعادة هيكلة وافلاس',              'category' => 'insolvency'],
                    // Listed-company regulations
                    ['q' => 'قواعد قيد الأوراق المالية',            'category' => 'capital-markets'],
                    ['q' => 'قواعد الاستحواذ',                     'category' => 'capital-markets'],
                    // Tax / customs
                    ['q' => 'قانون القيمة المضافة',                'category' => 'tax'],
                    ['q' => 'قانون الجمارك',                       'category' => 'tax'],
                    ['q' => 'قانون الضريبة العقارية',               'category' => 'tax'],
                    // IP & technology
                    ['q' => 'قانون حماية حقوق الملكية الفكرية',     'category' => 'commercial'],
                    ['q' => 'قانون مكافحة جرائم تقنية المعلومات',    'category' => 'compliance'],
                    ['q' => 'قانون حماية البيانات الشخصية',         'category' => 'compliance'],
                    // Banking & financial markets
                    ['q' => 'قانون مكافحة الإغراق',                'category' => 'commercial'],
                    ['q' => 'قانون التمويل العقاري',                'category' => 'banking'],
                    // Specialty
                    ['q' => 'قانون المحاماة',                      'category' => 'civil-general'],
                    ['q' => 'قانون السلطة القضائية',                'category' => 'procedural'],
                    ['q' => 'قانون حقوق الملكية الفكرية',           'category' => 'commercial'],
                    ['q' => 'قانون الجنسية المصرية',                'category' => 'civil-general'],

                    // ───── EG-EXTENDED: regulatory & operational layer ─────
                    // These hit the executive-regulations and FRA/GAFI decrees
                    // that corporate counsel actually cite in deal docs and
                    // compliance memos. Eastlaws indexes them by "اللائحة
                    // التنفيذية" prefix or by issuing-authority decree number.

                    // Executive regulations of the major statutes (the
                    // operational layer with all the procedural detail)
                    ['q' => 'اللائحة التنفيذية لقانون الاستثمار',                 'category' => 'investment'],
                    ['q' => 'اللائحة التنفيذية لقانون سوق رأس المال',               'category' => 'capital-markets'],
                    ['q' => 'اللائحة التنفيذية لقانون البنك المركزي والجهاز المصرفي', 'category' => 'banking'],
                    ['q' => 'اللائحة التنفيذية لقانون الضريبة على الدخل',           'category' => 'tax'],
                    ['q' => 'اللائحة التنفيذية لقانون القيمة المضافة',              'category' => 'tax'],
                    ['q' => 'اللائحة التنفيذية لقانون العمل',                     'category' => 'labour'],
                    ['q' => 'اللائحة التنفيذية لقانون حماية البيانات الشخصية',      'category' => 'compliance'],
                    ['q' => 'اللائحة التنفيذية لقانون مكافحة غسل الأموال',          'category' => 'compliance'],

                    // Fintech / digital economy regulatory layer
                    ['q' => 'قانون التوقيع الإلكتروني',                          'category' => 'commercial'],
                    ['q' => 'قواعد التمويل الاستثماري الجماعي',                    'category' => 'capital-markets'],
                    ['q' => 'قواعد التأجير التمويلي والتخصيم',                     'category' => 'banking'],
                    ['q' => 'قواعد المدفوعات الإلكترونية',                        'category' => 'banking'],

                    // Tax sub-domains (high-frequency in corporate transactions)
                    ['q' => 'قانون الإجراءات الضريبية الموحد',                    'category' => 'tax'],
                    ['q' => 'قواعد الفاتورة الإلكترونية',                        'category' => 'tax'],
                    ['q' => 'قواعد سعر التحويل',                                'category' => 'tax'],
                    ['q' => 'قانون ضريبة الدمغة',                                'category' => 'tax'],

                    // Special-zones & infrastructure deals
                    ['q' => 'قانون المناطق الاقتصادية ذات الطبيعة الخاصة',          'category' => 'investment'],
                    ['q' => 'قانون الشراكة بين القطاع الحكومي والقطاع الخاص',         'category' => 'investment'],

                    // Construction & real-estate development
                    ['q' => 'قانون البناء الموحد',                                'category' => 'real-estate'],
                    ['q' => 'قانون التطوير العقاري',                              'category' => 'real-estate'],

                    // Evidence & specialised criminal/procedural
                    ['q' => 'قانون الإثبات في المواد المدنية والتجارية',             'category' => 'procedural'],
                    ['q' => 'قانون الإجراءات الجنائية',                          'category' => 'procedural'],
                    ['q' => 'قانون العقوبات',                                   'category' => 'criminal'],

                    // Insurance & risk
                    ['q' => 'قانون الإشراف والرقابة على التأمين',                  'category' => 'banking'],

                    // Telecom & licensed sectors
                    ['q' => 'قانون تنظيم الاتصالات',                              'category' => 'commercial'],

                    // Court appeals & enforcement specifics
                    ['q' => 'قانون محكمة النقض',                                 'category' => 'procedural'],
                    ['q' => 'قانون التنفيذ',                                    'category' => 'enforcement'],

                    // ───── EG-EXTENDED-2: alternate phrasings + named laws ─────
                    // These target laws that eastlaws indexes under their
                    // canonical title rather than the generic "اللائحة"
                    // prefix. We re-target the prior failures with the
                    // form eastlaws actually uses, plus a handful of
                    // high-value laws not previously queried.

                    // Investment / corporate (re-tries with alternate phrasing)
                    ['q' => 'قانون الاستثمار رقم 72',                            'category' => 'investment'],
                    ['q' => 'قانون المناطق الحرة',                                'category' => 'investment'],

                    // Tax (re-tries)
                    ['q' => 'قانون الإجراءات الضريبية',                          'category' => 'tax'],
                    ['q' => 'قانون ضريبة الدخل رقم 91',                          'category' => 'tax'],
                    ['q' => 'الفاتورة الإلكترونية',                              'category' => 'tax'],

                    // Tech / fintech (re-tries)
                    ['q' => 'قانون التوقيع الإلكتروني رقم 15',                    'category' => 'commercial'],
                    ['q' => 'قانون الاتصالات رقم 10',                            'category' => 'commercial'],
                    ['q' => 'قانون التجارة الإلكترونية',                          'category' => 'commercial'],

                    // Real estate / development (re-tries)
                    ['q' => 'قانون تنظيم التطوير العقاري',                        'category' => 'real-estate'],
                    ['q' => 'قانون الشهر العقاري والتوثيق',                       'category' => 'real-estate'],

                    // Environment & energy (corporate compliance gaps)
                    ['q' => 'قانون البيئة رقم 4',                                'category' => 'compliance'],
                    ['q' => 'قانون الكهرباء',                                    'category' => 'commercial'],
                    ['q' => 'قانون البترول',                                     'category' => 'commercial'],

                    // Healthcare / pharma licensing
                    ['q' => 'قانون مزاولة مهنة الطب',                            'category' => 'civil-general'],
                    ['q' => 'قانون هيئة الدواء المصرية',                          'category' => 'compliance'],

                    // Media & press
                    ['q' => 'قانون تنظيم الصحافة والإعلام',                       'category' => 'commercial'],

                    // Customs & trade detail
                    ['q' => 'قانون الجمارك رقم 207',                             'category' => 'tax'],

                    // Banking by number (CBE 2020 law)
                    ['q' => 'قانون البنك المركزي رقم 194',                        'category' => 'banking'],

                    // Personal status procedures (often cited even by
                    // corporate counsel for shareholder personal-status issues)
                    ['q' => 'قانون الولاية على المال',                            'category' => 'family'],
                ],
            ],
        ],

        'SA' => [
            'country_id' => 9,
            'queries' => [
                'general' => [
                    ['q' => 'نظام المعاملات المدنية',     'category' => 'civil-general'],
                    ['q' => 'نظام العمل',                'category' => 'labour'],
                    ['q' => 'نظام الشركات',              'category' => 'companies'],
                    ['q' => 'نظام التحكيم',              'category' => 'arbitration'],
                    ['q' => 'نظام التنفيذ',              'category' => 'enforcement'],
                    ['q' => 'نظام المرافعات الشرعية',     'category' => 'procedural'],
                    ['q' => 'نظام التأمينات الاجتماعية',  'category' => 'social-insurance'],
                ],
                'corporate' => [
                    ['q' => 'نظام السوق المالية',                         'category' => 'capital-markets'],
                    ['q' => 'نظام الاستثمار',                             'category' => 'investment'],
                    ['q' => 'نظام الإفلاس',                              'category' => 'insolvency'],
                    ['q' => 'نظام مكافحة غسل الأموال',                    'category' => 'compliance'],
                    ['q' => 'نظام مكافحة الرشوة',                        'category' => 'compliance'],
                    ['q' => 'نظام ضريبة الدخل',                          'category' => 'tax'],
                ],
                'deep' => [
                    ['q' => 'نظام الشركات المساهمة',                      'category' => 'companies'],
                    ['q' => 'لائحة طرح الأوراق المالية',                   'category' => 'capital-markets'],
                    ['q' => 'نظام الاستثمار الأجنبي',                      'category' => 'investment'],
                    ['q' => 'نظام ضريبة القيمة المضافة',                   'category' => 'tax'],
                    ['q' => 'نظام مكافحة جرائم المعلوماتية',                'category' => 'compliance'],
                    ['q' => 'نظام حماية حقوق المؤلف',                     'category' => 'commercial'],
                    ['q' => 'نظام البنوك',                                'category' => 'banking'],
                    ['q' => 'نظام السجل التجاري',                         'category' => 'companies'],
                    ['q' => 'نظام مزاولة المهن الصحية',                    'category' => 'civil-general'],
                    ['q' => 'نظام المرور',                                'category' => 'civil-general'],

                    // ───── SA-EXTENDED: named laws by Royal Decree number ─────
                    // Saudi laws are issued under the form م/N/HHHH هـ. Eastlaws
                    // indexes the canonical title with the number — these
                    // queries target the binding citation form Saudi corporate
                    // counsel actually use.

                    // Companies + corporate / capital markets
                    ['q' => 'نظام الشركات م/132',                           'category' => 'companies'],
                    ['q' => 'نظام السوق المالية م/30',                       'category' => 'capital-markets'],
                    ['q' => 'لائحة حوكمة الشركات',                          'category' => 'companies'],

                    // Civil + obligations
                    ['q' => 'نظام المعاملات المدنية م/191',                  'category' => 'civil-general'],
                    ['q' => 'نظام التحكيم م/34',                            'category' => 'arbitration'],
                    ['q' => 'نظام التنفيذ م/53',                            'category' => 'enforcement'],
                    ['q' => 'نظام المرافعات الشرعية م/1',                    'category' => 'procedural'],

                    // Insolvency / restructuring
                    ['q' => 'نظام الإفلاس م/50',                            'category' => 'insolvency'],

                    // Labour / employment
                    ['q' => 'نظام العمل م/51',                              'category' => 'labour'],
                    ['q' => 'نظام التأمينات الاجتماعية م/33',                 'category' => 'social-insurance'],

                    // Tax / Zakat
                    ['q' => 'نظام ضريبة الدخل م/1',                          'category' => 'tax'],
                    ['q' => 'نظام جباية الزكاة',                            'category' => 'tax'],

                    // Data + tech
                    ['q' => 'نظام حماية البيانات الشخصية م/19',              'category' => 'compliance'],
                    ['q' => 'نظام التجارة الإلكترونية',                      'category' => 'commercial'],
                    ['q' => 'نظام التوقيع الإلكتروني',                       'category' => 'commercial'],

                    // Banking + finance
                    ['q' => 'نظام البنك المركزي السعودي',                   'category' => 'banking'],
                    ['q' => 'نظام مراقبة البنوك',                           'category' => 'banking'],
                    ['q' => 'نظام التأجير التمويلي م/48',                    'category' => 'banking'],
                    ['q' => 'نظام الرهن التجاري',                           'category' => 'banking'],

                    // Compliance / sanctions
                    ['q' => 'نظام مكافحة الإرهاب وتمويله',                   'category' => 'compliance'],
                    ['q' => 'نظام مكافحة الرشوة',                           'category' => 'compliance'],
                ],
            ],
        ],

        'AE' => [
            'country_id' => 4,
            'queries' => [
                'general' => [
                    ['q' => 'قانون المعاملات المدنية',        'category' => 'civil-general'],
                    ['q' => 'قانون المعاملات التجارية',         'category' => 'commercial'],
                    ['q' => 'قانون العمل',                  'category' => 'labour'],
                    ['q' => 'قانون الشركات التجارية',          'category' => 'companies'],
                    ['q' => 'قانون الإجراءات المدنية',          'category' => 'procedural'],
                    ['q' => 'قانون الإثبات',                 'category' => 'procedural'],
                ],
                'corporate' => [
                    ['q' => 'قانون الأوراق المالية والسلع',   'category' => 'capital-markets'],
                    ['q' => 'قانون الاستثمار الأجنبي',       'category' => 'investment'],
                    ['q' => 'قانون الإفلاس',              'category' => 'insolvency'],
                    ['q' => 'قانون مواجهة جرائم غسل الأموال', 'category' => 'compliance'],
                    ['q' => 'قانون الضريبة على الشركات',     'category' => 'tax'],
                    ['q' => 'قانون التحكيم',              'category' => 'arbitration'],
                ],
                'deep' => [
                    ['q' => 'قانون مكافحة جرائم تقنية المعلومات', 'category' => 'compliance'],
                    ['q' => 'قانون حماية البيانات الشخصية',     'category' => 'compliance'],
                    ['q' => 'قانون الأحوال الشخصية',           'category' => 'family'],
                    ['q' => 'قانون مزاولة مهنة الطب',           'category' => 'civil-general'],
                    ['q' => 'قانون البنك المركزي',              'category' => 'banking'],
                    ['q' => 'قانون حقوق المؤلف',               'category' => 'commercial'],
                    ['q' => 'قانون العلامات التجارية',          'category' => 'commercial'],
                    ['q' => 'قانون الجنسية',                   'category' => 'civil-general'],
                    ['q' => 'قانون الجمارك الاتحادي',           'category' => 'tax'],
                    ['q' => 'قانون التحكيم الاتحادي',           'category' => 'arbitration'],

                    // ───── AE-EXTENDED: named laws by Federal Decree-Law number ─────
                    // UAE primary legislation is issued as قانون اتحادي N لسنة YYYY
                    // or المرسوم بقانون اتحادي N لسنة YYYY. Eastlaws indexes
                    // by the canonical title — these queries hit the form
                    // UAE corporate counsel cite in deal documents.

                    // Companies + corporate
                    ['q' => 'مرسوم بقانون اتحادي 32 لسنة 2021',              'category' => 'companies'],
                    ['q' => 'قانون الشركات في المناطق الحرة',                'category' => 'companies'],

                    // Civil + commercial
                    ['q' => 'قانون اتحادي 5 لسنة 1985 معاملات مدنية',        'category' => 'civil-general'],
                    ['q' => 'قانون اتحادي 18 لسنة 1993 معاملات تجارية',       'category' => 'commercial'],
                    ['q' => 'مرسوم بقانون اتحادي 42 لسنة 2022 إجراءات مدنية', 'category' => 'procedural'],
                    ['q' => 'قانون اتحادي 6 لسنة 2018 تحكيم',                'category' => 'arbitration'],

                    // Labour + employment
                    ['q' => 'مرسوم بقانون اتحادي 33 لسنة 2021 علاقات العمل',  'category' => 'labour'],
                    ['q' => 'مرسوم بقانون اتحادي 9 لسنة 2024 عمل',           'category' => 'labour'],

                    // Data + IP + tech
                    ['q' => 'مرسوم بقانون اتحادي 45 لسنة 2021 بيانات شخصية',  'category' => 'compliance'],
                    ['q' => 'مرسوم بقانون اتحادي 38 لسنة 2021 حقوق المؤلف',   'category' => 'commercial'],
                    ['q' => 'مرسوم بقانون اتحادي 36 لسنة 2021 علامات تجارية', 'category' => 'commercial'],
                    ['q' => 'مرسوم بقانون اتحادي 11 لسنة 2021 ملكية صناعية',  'category' => 'commercial'],

                    // Insolvency
                    ['q' => 'مرسوم بقانون اتحادي 26 لسنة 2020 إفلاس',         'category' => 'insolvency'],

                    // Banking + finance
                    ['q' => 'مرسوم بقانون اتحادي 14 لسنة 2018 مصرف مركزي',    'category' => 'banking'],

                    // Tax
                    ['q' => 'مرسوم بقانون اتحادي 47 لسنة 2022 ضريبة شركات',   'category' => 'tax'],
                    ['q' => 'مرسوم بقانون اتحادي 8 لسنة 2017 قيمة مضافة',     'category' => 'tax'],
                    ['q' => 'مرسوم بقانون اتحادي 7 لسنة 2017 ضريبة انتقائية', 'category' => 'tax'],

                    // Compliance / AML
                    ['q' => 'مرسوم بقانون اتحادي 20 لسنة 2018 غسل أموال',     'category' => 'compliance'],
                ],
            ],
        ],

        'KW' => [
            'country_id' => 5,
            'queries' => [
                'general' => [
                    ['q' => 'القانون المدني',                       'category' => 'civil-general'],
                    ['q' => 'قانون العمل في القطاع الأهلي',           'category' => 'labour'],
                    ['q' => 'قانون الشركات التجارية',                'category' => 'companies'],
                    ['q' => 'قانون التجارة',                       'category' => 'commercial'],
                    ['q' => 'قانون المرافعات المدنية والتجارية',       'category' => 'procedural'],
                ],
                'corporate' => [
                    ['q' => 'قانون هيئة أسواق المال',           'category' => 'capital-markets'],
                    ['q' => 'قانون التحكيم القضائي',           'category' => 'arbitration'],
                    ['q' => 'قانون مكافحة غسل الأموال',         'category' => 'compliance'],
                ],
                'deep' => [
                    ['q' => 'قانون الإفلاس',                       'category' => 'insolvency'],
                    ['q' => 'قانون ضريبة دخل الشركات',              'category' => 'tax'],
                    ['q' => 'قانون البنوك',                        'category' => 'banking'],
                    ['q' => 'قانون حماية المستهلك',                  'category' => 'commercial'],
                    ['q' => 'قانون مكافحة الفساد',                  'category' => 'compliance'],
                    ['q' => 'قانون الجزاء',                        'category' => 'criminal'],
                    ['q' => 'قانون التأمينات الاجتماعية',            'category' => 'social-insurance'],
                    ['q' => 'قانون مكافحة جرائم تقنية المعلومات',     'category' => 'compliance'],
                    ['q' => 'قانون الجنسية الكويتية',                'category' => 'civil-general'],
                    ['q' => 'قانون الأحوال الشخصية',                 'category' => 'family'],
                ],
            ],
        ],

        'QA' => [
            'country_id' => 7,
            'queries' => [
                'general' => [
                    ['q' => 'القانون المدني',                       'category' => 'civil-general'],
                    ['q' => 'قانون العمل',                          'category' => 'labour'],
                    ['q' => 'قانون الشركات التجارية',                'category' => 'companies'],
                    ['q' => 'قانون المرافعات المدنية والتجارية',       'category' => 'procedural'],
                    ['q' => 'قانون التجارة',                       'category' => 'commercial'],
                ],
                'corporate' => [
                    ['q' => 'قانون هيئة قطر للأسواق المالية', 'category' => 'capital-markets'],
                    ['q' => 'قانون التحكيم',               'category' => 'arbitration'],
                    ['q' => 'قانون مكافحة غسل الأموال',     'category' => 'compliance'],
                ],
                'deep' => [
                    ['q' => 'قانون الإفلاس',                  'category' => 'insolvency'],
                    ['q' => 'قانون البنك المركزي القطري',      'category' => 'banking'],
                    ['q' => 'قانون حماية البيانات الشخصية',    'category' => 'compliance'],
                    ['q' => 'قانون العلامات التجارية',         'category' => 'commercial'],
                    ['q' => 'قانون الأحوال الشخصية',          'category' => 'family'],

                    // ───── QA-EXTENDED: by-number queries Qatari corporate
                    //       counsel cite in deal documents ─────
                    ['q' => 'قانون رقم 22 لسنة 2004 مدني',                'category' => 'civil-general'],
                    ['q' => 'قانون رقم 27 لسنة 2006 تجارة',               'category' => 'commercial'],
                    ['q' => 'قانون رقم 11 لسنة 2015 شركات',               'category' => 'companies'],
                    ['q' => 'قانون رقم 8 لسنة 2021 شركات',                'category' => 'companies'],
                    ['q' => 'قانون رقم 14 لسنة 2004 عمل',                 'category' => 'labour'],
                    ['q' => 'قانون رقم 2 لسنة 2017 تحكيم',                'category' => 'arbitration'],
                    ['q' => 'قانون رقم 13 لسنة 2016 خصوصية البيانات',      'category' => 'compliance'],
                    ['q' => 'قانون رقم 20 لسنة 2019 غسل أموال',           'category' => 'compliance'],
                    ['q' => 'قانون رقم 24 لسنة 2018 ضريبة الدخل',         'category' => 'tax'],
                    ['q' => 'قانون رقم 13 لسنة 2012 مصرف قطر المركزي',   'category' => 'banking'],
                    ['q' => 'قانون مركز قطر للمال',                       'category' => 'companies'],
                    ['q' => 'قانون الاستثمار غير القطري',                 'category' => 'investment'],
                    ['q' => 'قانون رقم 13 لسنة 1990 مرافعات',             'category' => 'procedural'],
                    ['q' => 'قانون رقم 22 لسنة 2006 أسرة',                'category' => 'family'],
                    ['q' => 'لائحة هيئة قطر للأسواق المالية',              'category' => 'capital-markets'],
                ],
            ],
        ],

        // ───── Additional Gulf jurisdictions — coverage for the "any Gulf
        //       client" demo. Per-jurisdiction profiles already exist; these
        //       queries seed the actual KB. ─────

        'BH' => [
            'country_id' => 6,
            'queries' => [
                'general' => [
                    ['q' => 'قانون المعاملات المدنية',          'category' => 'civil-general'],
                    ['q' => 'قانون العمل',                    'category' => 'labour'],
                    ['q' => 'قانون الشركات التجارية',          'category' => 'companies'],
                    ['q' => 'قانون التجارة',                  'category' => 'commercial'],
                    ['q' => 'قانون المرافعات المدنية والتجارية', 'category' => 'procedural'],
                ],
                'corporate' => [
                    ['q' => 'قانون مصرف البحرين المركزي',     'category' => 'banking'],
                    ['q' => 'قانون التحكيم التجاري',         'category' => 'arbitration'],
                    ['q' => 'قانون مكافحة غسل الأموال',       'category' => 'compliance'],
                ],
                'deep' => [
                    ['q' => 'قانون الإفلاس وإعادة التنظيم',     'category' => 'insolvency'],
                    ['q' => 'قانون السوق المالية',             'category' => 'capital-markets'],
                    ['q' => 'قانون الاستثمار',                'category' => 'investment'],
                    ['q' => 'قانون ضريبة القيمة المضافة',       'category' => 'tax'],
                    ['q' => 'قانون حماية البيانات الشخصية',     'category' => 'compliance'],
                    ['q' => 'قانون الإجراءات المدنية',         'category' => 'procedural'],
                    ['q' => 'قانون العقوبات',                 'category' => 'criminal'],
                    ['q' => 'قانون الأحوال الشخصية',           'category' => 'family'],
                    ['q' => 'قانون التأمينات الاجتماعية',       'category' => 'social-insurance'],
                ],
            ],
        ],

        'OM' => [
            'country_id' => 10, // سلطنة عمان
            'queries' => [
                'general' => [
                    ['q' => 'قانون المعاملات المدنية',          'category' => 'civil-general'],
                    ['q' => 'قانون العمل',                    'category' => 'labour'],
                    ['q' => 'قانون الشركات التجارية',          'category' => 'companies'],
                    ['q' => 'قانون التجارة',                  'category' => 'commercial'],
                    ['q' => 'قانون الإجراءات المدنية',         'category' => 'procedural'],
                ],
                'corporate' => [
                    ['q' => 'قانون البنك المركزي العماني',     'category' => 'banking'],
                    ['q' => 'قانون التحكيم',                'category' => 'arbitration'],
                ],
                'deep' => [
                    ['q' => 'قانون سوق رأس المال',             'category' => 'capital-markets'],
                    ['q' => 'قانون الإفلاس',                  'category' => 'insolvency'],
                    ['q' => 'قانون الاستثمار الأجنبي',         'category' => 'investment'],
                    ['q' => 'قانون ضريبة الدخل',              'category' => 'tax'],
                    ['q' => 'قانون مكافحة غسل الأموال',         'category' => 'compliance'],
                    ['q' => 'قانون حماية البيانات',            'category' => 'compliance'],
                    ['q' => 'قانون مكافحة جرائم تقنية المعلومات', 'category' => 'compliance'],
                    ['q' => 'قانون السجل التجاري',             'category' => 'companies'],
                    ['q' => 'قانون التأمينات الاجتماعية',       'category' => 'social-insurance'],
                    ['q' => 'قانون الأحوال الشخصية',           'category' => 'family'],
                ],
            ],
        ],

        'JO' => [
            'country_id' => 2, // المملكة الأردنية الهاشمية
            'queries' => [
                'general' => [
                    ['q' => 'القانون المدني',                  'category' => 'civil-general'],
                    ['q' => 'قانون العمل',                    'category' => 'labour'],
                    ['q' => 'قانون الشركات',                  'category' => 'companies'],
                    ['q' => 'قانون التجارة',                  'category' => 'commercial'],
                    ['q' => 'قانون أصول المحاكمات المدنية',     'category' => 'procedural'],
                ],
                'corporate' => [
                    ['q' => 'قانون هيئة الأوراق المالية',     'category' => 'capital-markets'],
                    ['q' => 'قانون البنك المركزي الأردني',     'category' => 'banking'],
                    ['q' => 'قانون التحكيم',                'category' => 'arbitration'],
                ],
                'deep' => [
                    ['q' => 'قانون الإعسار',                  'category' => 'insolvency'],
                    ['q' => 'قانون تشجيع الاستثمار',           'category' => 'investment'],
                    ['q' => 'قانون ضريبة الدخل',              'category' => 'tax'],
                    ['q' => 'قانون ضريبة المبيعات',             'category' => 'tax'],
                    ['q' => 'قانون مكافحة غسل الأموال',         'category' => 'compliance'],
                    ['q' => 'قانون حماية البيانات الشخصية',     'category' => 'compliance'],
                    ['q' => 'قانون الجرائم الإلكترونية',         'category' => 'compliance'],
                    ['q' => 'قانون الضمان الاجتماعي',           'category' => 'social-insurance'],
                    ['q' => 'قانون العقوبات',                 'category' => 'criminal'],
                    ['q' => 'قانون الأحوال الشخصية',           'category' => 'family'],
                ],
            ],
        ],

        'LB' => [
            'country_id' => 19, // الجمهورية اللبنانية
            'queries' => [
                'general' => [
                    ['q' => 'قانون الموجبات والعقود',         'category' => 'civil-general'],
                    ['q' => 'قانون العمل',                    'category' => 'labour'],
                    ['q' => 'قانون التجارة البرية',          'category' => 'commercial'],
                    ['q' => 'قانون أصول المحاكمات المدنية',     'category' => 'procedural'],
                ],
                'corporate' => [
                    ['q' => 'قانون النقد والتسليف',          'category' => 'banking'],
                    ['q' => 'قانون مكافحة تبييض الأموال',     'category' => 'compliance'],
                    ['q' => 'قانون التحكيم',                'category' => 'arbitration'],
                ],
                'deep' => [
                    ['q' => 'قانون التجارة البحرية',           'category' => 'commercial'],
                    ['q' => 'قانون الشركات',                  'category' => 'companies'],
                    ['q' => 'قانون الأسواق المالية',            'category' => 'capital-markets'],
                    ['q' => 'قانون الإفلاس',                  'category' => 'insolvency'],
                    ['q' => 'قانون ضريبة الدخل',              'category' => 'tax'],
                    ['q' => 'قانون التأمين',                  'category' => 'banking'],
                    ['q' => 'قانون الضمان الاجتماعي',           'category' => 'social-insurance'],
                    ['q' => 'قانون العقوبات',                 'category' => 'criminal'],
                    ['q' => 'قانون الأحوال الشخصية',           'category' => 'family'],
                ],
            ],
        ],

        // ───── North Africa ─────

        'TN' => [
            'country_id' => 12, // الجمهورية التونسية
            'queries' => [
                'general' => [
                    ['q' => 'مجلة الالتزامات والعقود',         'category' => 'civil-general'],
                    ['q' => 'مجلة الشغل',                     'category' => 'labour'],
                    ['q' => 'مجلة الشركات التجارية',           'category' => 'companies'],
                    ['q' => 'المجلة التجارية',                'category' => 'commercial'],
                    ['q' => 'مجلة المرافعات المدنية والتجارية', 'category' => 'procedural'],
                    ['q' => 'مجلة الحقوق العينية',             'category' => 'real-estate'],
                ],
                'corporate' => [
                    ['q' => 'قانون السوق المالية',             'category' => 'capital-markets'],
                    ['q' => 'مجلة تشجيع الاستثمارات',           'category' => 'investment'],
                    ['q' => 'قانون التحكيم',                  'category' => 'arbitration'],
                    ['q' => 'قانون مكافحة الإرهاب وغسل الأموال', 'category' => 'compliance'],
                    ['q' => 'مجلة الضريبة على الدخل',           'category' => 'tax'],
                    ['q' => 'قانون البنك المركزي التونسي',      'category' => 'banking'],
                ],
                'deep' => [
                    ['q' => 'قانون إنقاذ المؤسسات',            'category' => 'insolvency'],
                    ['q' => 'مجلة حقوق الملكية الفكرية',         'category' => 'commercial'],
                    ['q' => 'قانون حماية المعطيات الشخصية',      'category' => 'compliance'],
                    ['q' => 'قانون البنوك والمؤسسات المالية',    'category' => 'banking'],
                    ['q' => 'مجلة الأداءات على القيمة المضافة',  'category' => 'tax'],
                    ['q' => 'قانون الأحوال الشخصية',           'category' => 'family'],
                    ['q' => 'مجلة الجزاء',                     'category' => 'criminal'],
                    ['q' => 'قانون حماية المستهلك',             'category' => 'commercial'],
                    ['q' => 'قانون الضمان الاجتماعي',           'category' => 'social-insurance'],
                ],
            ],
        ],

        'LY' => [
            'country_id' => 11, // ليبيا
            'queries' => [
                'general' => [
                    ['q' => 'القانون المدني',                  'category' => 'civil-general'],
                    ['q' => 'قانون العمل',                    'category' => 'labour'],
                    ['q' => 'قانون الشركات',                  'category' => 'companies'],
                    ['q' => 'قانون النشاط التجاري',            'category' => 'commercial'],
                    ['q' => 'قانون المرافعات المدنية والتجارية', 'category' => 'procedural'],
                ],
                'corporate' => [
                    ['q' => 'قانون مصرف ليبيا المركزي',         'category' => 'banking'],
                    ['q' => 'قانون التحكيم',                  'category' => 'arbitration'],
                    ['q' => 'قانون تشجيع الاستثمار',           'category' => 'investment'],
                    ['q' => 'قانون مكافحة غسل الأموال',         'category' => 'compliance'],
                ],
                'deep' => [
                    ['q' => 'قانون ضريبة الدخل',              'category' => 'tax'],
                    ['q' => 'قانون الجمارك',                  'category' => 'tax'],
                    ['q' => 'قانون الإفلاس',                  'category' => 'insolvency'],
                    ['q' => 'قانون النشاط المصرفي',            'category' => 'banking'],
                    ['q' => 'قانون العقوبات',                 'category' => 'criminal'],
                    ['q' => 'قانون الأحوال الشخصية',           'category' => 'family'],
                    ['q' => 'قانون الضمان الاجتماعي',           'category' => 'social-insurance'],
                ],
            ],
        ],
    ],

];
