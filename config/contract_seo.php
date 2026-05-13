<?php

/*
|--------------------------------------------------------------------------
| Contract SEO data
|--------------------------------------------------------------------------
|
| Source data for the programmatic contract pages:
|   /contracts                            (hub)
|   /contracts/{type}                     (8 type pages)
|   /contracts/{type}/{iso}               (88 type × jurisdiction pages)
|
| Each page is unique by virtue of jurisdiction-specific civil-code
| article references and a bilingual sample clause keyed to that
| jurisdiction's controlling statute.
|
| Citations here are best-effort and reflect well-known articles in the
| relevant civil codes. The product itself uses live indexed snapshots
| as the source of truth.
*/

return [

    'jurisdictions' => [
        'EG' => ['en' => 'Egypt',                  'ar' => 'مصر',                       'short_en' => 'Egyptian',  'flag' => '🇪🇬', 'code_en' => 'Egyptian Civil Code (Law 131/1948)',                      'code_ar' => 'القانون المدني المصري (القانون رقم ١٣١ لسنة ١٩٤٨)'],
        'SA' => ['en' => 'Saudi Arabia',           'ar' => 'المملكة العربية السعودية',  'short_en' => 'Saudi',     'flag' => '🇸🇦', 'code_en' => 'Saudi Civil Transactions Law (Royal Decree M/191, 1444H)',  'code_ar' => 'نظام المعاملات المدنية السعودي (مرسوم ملكي م/١٩١، ١٤٤٤هـ)'],
        'AE' => ['en' => 'UAE',                    'ar' => 'الإمارات',                   'short_en' => 'Emirati',   'flag' => '🇦🇪', 'code_en' => 'UAE Civil Transactions Law (Federal Law 5/1985)',          'code_ar' => 'قانون المعاملات المدنية الإماراتي (القانون الاتحادي ٥/١٩٨٥)'],
        'KW' => ['en' => 'Kuwait',                 'ar' => 'الكويت',                     'short_en' => 'Kuwaiti',   'flag' => '🇰🇼', 'code_en' => 'Kuwaiti Civil Code (Decree-Law 67/1980)',                  'code_ar' => 'القانون المدني الكويتي (المرسوم بقانون ٦٧/١٩٨٠)'],
        'QA' => ['en' => 'Qatar',                  'ar' => 'قطر',                        'short_en' => 'Qatari',    'flag' => '🇶🇦', 'code_en' => 'Qatari Civil Code (Law 22/2004)',                          'code_ar' => 'القانون المدني القطري (القانون رقم ٢٢/٢٠٠٤)'],
        'BH' => ['en' => 'Bahrain',                'ar' => 'البحرين',                    'short_en' => 'Bahraini',  'flag' => '🇧🇭', 'code_en' => 'Bahrain Civil Code (Decree-Law 19/2001)',                  'code_ar' => 'القانون المدني البحريني (المرسوم بقانون ١٩/٢٠٠١)'],
        'OM' => ['en' => 'Oman',                   'ar' => 'عُمان',                       'short_en' => 'Omani',     'flag' => '🇴🇲', 'code_en' => 'Omani Civil Transactions Law (Royal Decree 29/2013)',      'code_ar' => 'قانون المعاملات المدنية العُماني (المرسوم السلطاني ٢٩/٢٠١٣)'],
        'JO' => ['en' => 'Jordan',                 'ar' => 'الأردن',                     'short_en' => 'Jordanian', 'flag' => '🇯🇴', 'code_en' => 'Jordanian Civil Code (Law 43/1976)',                       'code_ar' => 'القانون المدني الأردني (القانون رقم ٤٣/١٩٧٦)'],
        'LB' => ['en' => 'Lebanon',                'ar' => 'لبنان',                      'short_en' => 'Lebanese',  'flag' => '🇱🇧', 'code_en' => 'Lebanese Code of Obligations and Contracts (1932)',        'code_ar' => 'قانون الموجبات والعقود اللبناني (١٩٣٢)'],
        'TN' => ['en' => 'Tunisia',                'ar' => 'تونس',                       'short_en' => 'Tunisian',  'flag' => '🇹🇳', 'code_en' => 'Tunisian Code of Obligations and Contracts (1906)',        'code_ar' => 'مجلة الالتزامات والعقود التونسية (١٩٠٦)'],
        'LY' => ['en' => 'Libya',                  'ar' => 'ليبيا',                      'short_en' => 'Libyan',    'flag' => '🇱🇾', 'code_en' => 'Libyan Civil Code (1953)',                                  'code_ar' => 'القانون المدني الليبي (١٩٥٣)'],
    ],

    'types' => [
        'spa' => [
            'name_en' => 'Share Purchase Agreement',
            'name_ar' => 'اتفاقية بيع وشراء أسهم',
            'short_en' => 'SPA',
            'tagline_en' => 'Acquires equity interests in a target company. The Arabic version is binding for filings, registrations, and litigation in MENA jurisdictions.',
            'tagline_ar' => 'اتفاقية بيع وشراء حصص أو أسهم في شركة مستهدفة. النسخة العربية ملزمة للتسجيلات والإيداعات والتقاضي في ولايات الشرق الأوسط.',
            'use_cases_en' => 'M&A transactions, private equity exits, founder buyouts, secondary share sales, ESOP exercises.',
            'use_cases_ar' => 'صفقات الاندماج والاستحواذ، خروج صناديق الملكية الخاصة، شراء حصص المؤسسين، البيع الثانوي للأسهم، ممارسة خيارات الموظفين.',
            'key_clauses' => [
                ['en' => 'Purchase price and payment mechanics',              'ar' => 'الثمن وآلية السداد'],
                ['en' => 'Conditions precedent (regulatory, third-party)',    'ar' => 'الشروط السابقة (الموافقات التنظيمية والغير)'],
                ['en' => 'Representations and warranties',                    'ar' => 'الإقرارات والضمانات'],
                ['en' => 'Indemnification and limitation of liability',       'ar' => 'التعويض وحد المسؤولية'],
                ['en' => 'Restrictive covenants (non-compete, non-solicit)',  'ar' => 'الشروط التقييدية (عدم المنافسة وعدم الاستقطاب)'],
                ['en' => 'Governing law and dispute resolution',              'ar' => 'القانون الواجب التطبيق وآلية فض النزاعات'],
            ],
        ],
        'mou' => [
            'name_en' => 'Memorandum of Understanding',
            'name_ar' => 'مذكرة تفاهم',
            'short_en' => 'MoU',
            'tagline_en' => 'Records the parties\' commercial intent before a binding deal. Identifies binding versus non-binding clauses with care.',
            'tagline_ar' => 'تسجل النية التجارية للأطراف قبل إبرام الاتفاقية الملزمة. تحدد البنود الملزمة وغير الملزمة بدقة.',
            'use_cases_en' => 'Pre-deal positioning, joint-venture roadmaps, partnership announcements, exclusivity periods, government MoUs.',
            'use_cases_ar' => 'مواقف ما قبل الصفقة، خرائط طريق المشاريع المشتركة، إعلانات الشراكة، فترات الحصرية، مذكرات التفاهم الحكومية.',
            'key_clauses' => [
                ['en' => 'Scope of cooperation',                               'ar' => 'نطاق التعاون'],
                ['en' => 'Binding versus non-binding provisions',              'ar' => 'البنود الملزمة وغير الملزمة'],
                ['en' => 'Confidentiality and exclusivity',                    'ar' => 'السرية والحصرية'],
                ['en' => 'Negotiation in good faith',                          'ar' => 'التفاوض بحسن نية'],
                ['en' => 'Term and termination',                               'ar' => 'المدة والإنهاء'],
                ['en' => 'Governing law and jurisdiction (binding)',           'ar' => 'القانون الواجب التطبيق والاختصاص (ملزم)'],
            ],
        ],
        'employment' => [
            'name_en' => 'Employment Contract',
            'name_ar' => 'عقد عمل',
            'short_en' => 'Employment',
            'tagline_en' => 'Hires an employee under the controlling labour law. Must comply with mandatory provisions on working hours, end-of-service, and termination.',
            'tagline_ar' => 'يوظف الموظف وفقاً لقانون العمل الحاكم. يجب الالتزام بالأحكام الإلزامية حول ساعات العمل ومكافأة نهاية الخدمة والإنهاء.',
            'use_cases_en' => 'Permanent hires, fixed-term contracts, executive employment, expat onboarding, regional rollups.',
            'use_cases_ar' => 'التعيينات الدائمة، العقود محددة المدة، توظيف التنفيذيين، توظيف الأجانب، عمليات الدمج الإقليمية.',
            'key_clauses' => [
                ['en' => 'Position, duties, place of work',                    'ar' => 'الوظيفة والمهام ومكان العمل'],
                ['en' => 'Compensation and benefits',                          'ar' => 'الأجر والمزايا'],
                ['en' => 'Working hours and leave',                            'ar' => 'ساعات العمل والإجازات'],
                ['en' => 'Probation period',                                   'ar' => 'فترة الاختبار'],
                ['en' => 'Termination grounds and notice period',              'ar' => 'أسباب الإنهاء وفترة الإشعار'],
                ['en' => 'End-of-service entitlements',                        'ar' => 'مكافأة نهاية الخدمة'],
                ['en' => 'Confidentiality and IP assignment',                  'ar' => 'السرية ونقل ملكية الأعمال الفكرية'],
            ],
        ],
        'services' => [
            'name_en' => 'Services Agreement',
            'name_ar' => 'اتفاقية تقديم خدمات',
            'short_en' => 'Services',
            'tagline_en' => 'Engages a service provider for ongoing or project-based work. Defines scope, deliverables, and payment terms.',
            'tagline_ar' => 'يستأجر مزود خدمة لعمل مستمر أو قائم على مشروع. يحدد النطاق والمخرجات وشروط الدفع.',
            'use_cases_en' => 'Consulting engagements, professional services, marketing retainers, technology delivery, outsourcing.',
            'use_cases_ar' => 'الاستشارات، الخدمات المهنية، عقود التسويق، تنفيذ التقنية، الاستعانة بمصادر خارجية.',
            'key_clauses' => [
                ['en' => 'Scope of services and deliverables',                 'ar' => 'نطاق الخدمات والمخرجات'],
                ['en' => 'Service level and acceptance criteria',              'ar' => 'مستوى الخدمة ومعايير القبول'],
                ['en' => 'Fees, expenses, and invoicing',                      'ar' => 'الأتعاب والمصروفات والفوترة'],
                ['en' => 'Intellectual property ownership',                    'ar' => 'ملكية حقوق الملكية الفكرية'],
                ['en' => 'Confidentiality and data protection',                'ar' => 'السرية وحماية البيانات'],
                ['en' => 'Term, termination, and consequences',                'ar' => 'المدة والإنهاء وآثاره'],
            ],
        ],
        'nda' => [
            'name_en' => 'Non-Disclosure Agreement',
            'name_ar' => 'اتفاقية عدم إفصاح',
            'short_en' => 'NDA',
            'tagline_en' => 'Protects confidential information exchanged between parties. Mutual or one-way, with carve-outs and a defined survival period.',
            'tagline_ar' => 'يحمي المعلومات السرية المتبادلة بين الأطراف. متبادل أو من طرف واحد، مع استثناءات وفترة بقاء محددة.',
            'use_cases_en' => 'Pre-M&A due diligence, vendor evaluations, partnership exploration, employee onboarding, founder discussions.',
            'use_cases_ar' => 'العناية الواجبة قبل الاندماج والاستحواذ، تقييمات الموردين، استكشاف الشراكات، توظيف الموظفين، مباحثات المؤسسين.',
            'key_clauses' => [
                ['en' => 'Definition of confidential information',             'ar' => 'تعريف المعلومات السرية'],
                ['en' => 'Permitted use and recipients',                       'ar' => 'الاستخدام المسموح والمستلمون'],
                ['en' => 'Standard exclusions (public, prior knowledge, legal)','ar' => 'الاستثناءات المعتادة (المعلومات العامة، المعرفة السابقة، الالتزام القانوني)'],
                ['en' => 'Survival period after term end',                     'ar' => 'فترة البقاء بعد انتهاء المدة'],
                ['en' => 'Return or destruction of materials',                 'ar' => 'إعادة المواد أو إتلافها'],
                ['en' => 'Remedies including injunctive relief',               'ar' => 'العلاجات بما في ذلك الأمر القضائي'],
            ],
        ],
        'lease' => [
            'name_en' => 'Lease Agreement',
            'name_ar' => 'عقد إيجار',
            'short_en' => 'Lease',
            'tagline_en' => 'Leases real property — commercial, residential, or industrial — under the local lease and tenancy framework.',
            'tagline_ar' => 'يؤجر العقار — تجاري أو سكني أو صناعي — وفق الإطار المحلي للإيجار والعقارات.',
            'use_cases_en' => 'Office leases, retail tenancies, warehouse leases, residential rentals, ground leases.',
            'use_cases_ar' => 'إيجار المكاتب، التأجير التجاري، إيجار المستودعات، الإيجار السكني، إيجار الأراضي.',
            'key_clauses' => [
                ['en' => 'Premises description and permitted use',             'ar' => 'وصف العين وأوجه الاستخدام المسموحة'],
                ['en' => 'Rent, escalation, and security deposit',             'ar' => 'الأجرة وآلية الزيادة والتأمين'],
                ['en' => 'Term, renewal, and notice',                          'ar' => 'المدة والتجديد والإشعار'],
                ['en' => 'Maintenance and repair allocation',                  'ar' => 'توزيع التزامات الصيانة والإصلاح'],
                ['en' => 'Subletting and assignment',                          'ar' => 'التأجير من الباطن والتنازل'],
                ['en' => 'Termination, eviction, and dispute resolution',      'ar' => 'الإنهاء والإخلاء وفض النزاعات'],
            ],
        ],
        'distribution' => [
            'name_en' => 'Distribution Agreement',
            'name_ar' => 'اتفاقية توزيع',
            'short_en' => 'Distribution',
            'tagline_en' => 'Appoints a distributor for goods or services in a defined territory. Critical to align with local commercial agency law.',
            'tagline_ar' => 'يعين موزعاً للسلع أو الخدمات في إقليم محدد. من الضروري الالتزام بقانون الوكالة التجارية المحلي.',
            'use_cases_en' => 'Brand expansion into MENA, FMCG distribution, automotive distribution, technology channel partnerships.',
            'use_cases_ar' => 'توسع العلامات التجارية في الشرق الأوسط، توزيع السلع الاستهلاكية، توزيع السيارات، شراكات قنوات التقنية.',
            'key_clauses' => [
                ['en' => 'Territory and exclusivity',                          'ar' => 'الإقليم والحصرية'],
                ['en' => 'Minimum purchase or sales targets',                  'ar' => 'الحد الأدنى للشراء أو البيع'],
                ['en' => 'Pricing and payment terms',                          'ar' => 'الأسعار وشروط الدفع'],
                ['en' => 'Trademark licence and brand standards',              'ar' => 'ترخيص العلامة التجارية ومعايير العلامة'],
                ['en' => 'Term, renewal, and termination consequences',        'ar' => 'المدة والتجديد وآثار الإنهاء'],
                ['en' => 'Compliance with commercial agency law',              'ar' => 'الالتزام بقانون الوكالة التجارية'],
            ],
        ],
        'shareholders' => [
            'name_en' => 'Shareholders Agreement',
            'name_ar' => 'اتفاقية مساهمين',
            'short_en' => 'SHA',
            'tagline_en' => 'Governs the relationship between shareholders post-investment. Covers governance, transfer restrictions, and exit mechanics.',
            'tagline_ar' => 'تحكم العلاقة بين المساهمين بعد الاستثمار. تتناول الحوكمة وقيود التحويل وآليات الخروج.',
            'use_cases_en' => 'Venture-backed startups, joint ventures, family business governance, founder agreements, holding-company structures.',
            'use_cases_ar' => 'الشركات الناشئة المدعومة بالمخاطر، المشاريع المشتركة، حوكمة الشركات العائلية، اتفاقيات المؤسسين، هياكل الشركات القابضة.',
            'key_clauses' => [
                ['en' => 'Board composition and reserved matters',             'ar' => 'تشكيل مجلس الإدارة والمسائل المحتفظ بها'],
                ['en' => 'Pre-emption, tag-along, drag-along rights',          'ar' => 'حق الأفضلية، حق المتابعة، حق الإجبار'],
                ['en' => 'Anti-dilution and protective provisions',            'ar' => 'بنود مكافحة التخفيف والحماية'],
                ['en' => 'Information rights and reporting',                   'ar' => 'حقوق المعلومات والتقارير'],
                ['en' => 'Deadlock resolution mechanisms',                     'ar' => 'آليات حل الجمود'],
                ['en' => 'Exit, IPO, and liquidation preferences',             'ar' => 'الخروج والاكتتاب العام وأفضليات التصفية'],
            ],
        ],
    ],

    /*
    | Per type × jurisdiction: controlling civil-code articles + a real
    | bilingual sample clause that cites those articles. This is what
    | makes each programmatic page genuinely unique (not just swapped
    | variables). Articles are well-known/canonical across these civil
    | codes; the product itself uses the live snapshot as ground truth.
    */
    'samples' => [
        'spa' => [
            'EG' => [
                'articles' => ['418', '429', '147', '148'],
                'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص إلى المشتري خاليةً من أي رهون أو حقوق للغير، وفقاً للمادتين ٤١٨ و٤٢٩ من القانون المدني المصري، ويُنفِّذ الطرفان هذا الاتفاق بحسن نية وفق المادة ١٤٨.',
                'clause_en' => 'The Seller shall transfer ownership of the quotas to the Buyer free of any liens or third-party rights, in accordance with Articles 418 and 429 of the Egyptian Civil Code, and the Parties shall perform this Agreement in good faith pursuant to Article 148.',
            ],
            'SA' => ['articles' => ['126', '305', '306'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من أي حقوق للغير وفق المواد ٣٠٥ و٣٠٦ من نظام المعاملات المدنية، ويُنفّذ الطرفان هذا العقد بحسن نية بموجب المادة ١٢٦.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of any third-party rights pursuant to Articles 305 and 306 of the Saudi Civil Transactions Law, and the Parties shall perform this Agreement in good faith under Article 126.'],
            'AE' => ['articles' => ['246', '247', '489', '510'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص إلى المشتري وفقاً للمادتين ٤٨٩ و٥١٠ من قانون المعاملات المدنية الإماراتي، ويلتزم الطرفان بحسن النية في التنفيذ وفق المادتين ٢٤٦ و٢٤٧.', 'clause_en' => 'The Seller shall transfer ownership of the quotas to the Buyer in accordance with Articles 489 and 510 of the UAE Civil Transactions Law, and the Parties shall perform their obligations in good faith pursuant to Articles 246 and 247.'],
            'KW' => ['articles' => ['197', '459', '470'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من الرهون وفق المادتين ٤٥٩ و٤٧٠ من القانون المدني الكويتي، مع الالتزام بحسن النية وفقاً للمادة ١٩٧.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of liens pursuant to Articles 459 and 470 of the Kuwaiti Civil Code, performing in good faith under Article 197.'],
            'QA' => ['articles' => ['172', '419', '430'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من حقوق الغير وفق المادتين ٤١٩ و٤٣٠ من القانون المدني القطري، مع الالتزام بحسن النية وفقاً للمادة ١٧٢.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of third-party rights under Articles 419 and 430 of the Qatari Civil Code, performing in good faith pursuant to Article 172.'],
            'BH' => ['articles' => ['129', '359', '370'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من حقوق الغير وفق المادتين ٣٥٩ و٣٧٠ من القانون المدني البحريني، مع الالتزام بحسن النية وفقاً للمادة ١٢٩.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of third-party rights pursuant to Articles 359 and 370 of the Bahrain Civil Code, performing in good faith under Article 129.'],
            'OM' => ['articles' => ['157', '462', '475'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص وفق المادتين ٤٦٢ و٤٧٥ من قانون المعاملات المدنية العُماني، مع الالتزام بحسن النية وفقاً للمادة ١٥٧.', 'clause_en' => 'The Seller shall transfer ownership of the quotas pursuant to Articles 462 and 475 of the Omani Civil Transactions Law, performing in good faith under Article 157.'],
            'JO' => ['articles' => ['202', '465', '479'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من حقوق الغير وفق المادتين ٤٦٥ و٤٧٩ من القانون المدني الأردني، مع الالتزام بحسن النية وفقاً للمادة ٢٠٢.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of third-party rights pursuant to Articles 465 and 479 of the Jordanian Civil Code, performing in good faith under Article 202.'],
            'LB' => ['articles' => ['221', '393', '443'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من حقوق الغير وفق المادتين ٣٩٣ و٤٤٣ من قانون الموجبات والعقود اللبناني، مع الالتزام بحسن النية وفقاً للمادة ٢٢١.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of third-party rights pursuant to Articles 393 and 443 of the Lebanese Code of Obligations, performing in good faith under Article 221.'],
            'TN' => ['articles' => ['243', '564', '590'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من حقوق الغير وفق الفصلين ٥٦٤ و٥٩٠ من مجلة الالتزامات والعقود التونسية، مع الالتزام بحسن النية وفقاً للفصل ٢٤٣.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of third-party rights pursuant to Articles 564 and 590 of the Tunisian Code of Obligations, performing in good faith under Article 243.'],
            'LY' => ['articles' => ['148', '418', '429'], 'clause_ar' => 'يلتزم البائع بنقل ملكية الحصص خاليةً من حقوق الغير وفق المادتين ٤١٨ و٤٢٩ من القانون المدني الليبي، مع الالتزام بحسن النية وفقاً للمادة ١٤٨.', 'clause_en' => 'The Seller shall transfer ownership of the quotas free of third-party rights pursuant to Articles 418 and 429 of the Libyan Civil Code, performing in good faith under Article 148.'],
        ],
        'mou' => [
            'EG' => ['articles' => ['148', '159'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية بشأن الشروط التجارية والقانونية للصفقة وفقاً للمادتين ١٤٨ و١٥٩ من القانون المدني المصري، وتُعدّ هذه المذكرة غير ملزمة ما عدا أحكام السرية والقانون الواجب التطبيق.', 'clause_en' => 'The Parties shall negotiate in good faith the commercial and legal terms of the transaction in accordance with Articles 148 and 159 of the Egyptian Civil Code. This Memorandum is non-binding save for confidentiality and governing-law provisions.'],
            'SA' => ['articles' => ['126'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادة ١٢٦ من نظام المعاملات المدنية السعودي. هذه المذكرة غير ملزمة باستثناء أحكام السرية والاختصاص.', 'clause_en' => 'The Parties shall negotiate in good faith under Article 126 of the Saudi Civil Transactions Law. This Memorandum is non-binding save for confidentiality and jurisdiction provisions.'],
            'AE' => ['articles' => ['246', '247'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية بشأن الشروط التجارية للصفقة وفقاً للمادتين ٢٤٦ و٢٤٧ من قانون المعاملات المدنية الإماراتي، وتختص محاكم مركز دبي المالي العالمي أو محاكم سوق أبوظبي العالمي بأي نزاع.', 'clause_en' => 'The Parties shall negotiate in good faith the commercial terms of the transaction in accordance with Articles 246 and 247 of the UAE Civil Transactions Law. The DIFC Courts or the ADGM Courts shall have jurisdiction over any dispute.'],
            'KW' => ['articles' => ['197', '198'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادتين ١٩٧ و١٩٨ من القانون المدني الكويتي. تُعدّ هذه المذكرة غير ملزمة باستثناء أحكام السرية والقانون الواجب التطبيق.', 'clause_en' => 'The Parties shall negotiate in good faith pursuant to Articles 197 and 198 of the Kuwaiti Civil Code. This Memorandum is non-binding save for confidentiality and governing-law provisions.'],
            'QA' => ['articles' => ['172'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادة ١٧٢ من القانون المدني القطري. هذه المذكرة غير ملزمة باستثناء أحكام السرية والاختصاص.', 'clause_en' => 'The Parties shall negotiate in good faith under Article 172 of the Qatari Civil Code. This Memorandum is non-binding save for confidentiality and jurisdiction provisions.'],
            'BH' => ['articles' => ['129'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادة ١٢٩ من القانون المدني البحريني. هذه المذكرة غير ملزمة باستثناء أحكام السرية.', 'clause_en' => 'The Parties shall negotiate in good faith under Article 129 of the Bahrain Civil Code. This Memorandum is non-binding save for confidentiality provisions.'],
            'OM' => ['articles' => ['157'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادة ١٥٧ من قانون المعاملات المدنية العُماني. هذه المذكرة غير ملزمة باستثناء أحكام السرية.', 'clause_en' => 'The Parties shall negotiate in good faith pursuant to Article 157 of the Omani Civil Transactions Law. This Memorandum is non-binding save for confidentiality provisions.'],
            'JO' => ['articles' => ['202', '203'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادتين ٢٠٢ و٢٠٣ من القانون المدني الأردني. هذه المذكرة غير ملزمة باستثناء أحكام السرية والقانون الواجب التطبيق.', 'clause_en' => 'The Parties shall negotiate in good faith under Articles 202 and 203 of the Jordanian Civil Code. This Memorandum is non-binding save for confidentiality and governing-law provisions.'],
            'LB' => ['articles' => ['221'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادة ٢٢١ من قانون الموجبات والعقود اللبناني. هذه المذكرة غير ملزمة باستثناء أحكام السرية.', 'clause_en' => 'The Parties shall negotiate in good faith under Article 221 of the Lebanese Code of Obligations. This Memorandum is non-binding save for confidentiality provisions.'],
            'TN' => ['articles' => ['243'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للفصل ٢٤٣ من مجلة الالتزامات والعقود التونسية. هذه المذكرة غير ملزمة باستثناء أحكام السرية.', 'clause_en' => 'The Parties shall negotiate in good faith pursuant to Article 243 of the Tunisian Code of Obligations. This Memorandum is non-binding save for confidentiality provisions.'],
            'LY' => ['articles' => ['148'], 'clause_ar' => 'يلتزم الطرفان بالتفاوض بحسن نية وفقاً للمادة ١٤٨ من القانون المدني الليبي. هذه المذكرة غير ملزمة باستثناء أحكام السرية.', 'clause_en' => 'The Parties shall negotiate in good faith pursuant to Article 148 of the Libyan Civil Code. This Memorandum is non-binding save for confidentiality provisions.'],
        ],
        'employment' => [
            'EG' => ['articles' => ['Labour Law 12/2003 Art. 110'], 'clause_ar' => 'تخضع شروط هذا العقد وإنهاؤه ومكافأة نهاية الخدمة لقانون العمل المصري رقم ١٢ لسنة ٢٠٠٣، ولا يجوز التنازل عن أي حق إلزامي مقرر فيه.', 'clause_en' => 'The terms of this Contract, including termination and end-of-service entitlements, are governed by Egyptian Labour Law No. 12 of 2003. No mandatory employee right under that law may be waived.'],
            'SA' => ['articles' => ['Labour Law 1426H Art. 84'], 'clause_ar' => 'تخضع شروط هذا العقد وإنهاؤه ومكافأة نهاية الخدمة لنظام العمل السعودي (مرسوم ملكي م/٥١، ١٤٢٦هـ)، وتُحسب مكافأة نهاية الخدمة وفق المادة ٨٤.', 'clause_en' => 'The terms of this Contract are governed by the Saudi Labour Law (Royal Decree M/51, 1426H), with end-of-service gratuity calculated pursuant to Article 84.'],
            'AE' => ['articles' => ['Federal Decree-Law 33/2021 Art. 51'], 'clause_ar' => 'يخضع هذا العقد للمرسوم بقانون اتحادي رقم ٣٣ لسنة ٢٠٢١ في شأن تنظيم علاقات العمل، وتُحسب مكافأة نهاية الخدمة وفقاً للمادة ٥١.', 'clause_en' => 'This Contract is governed by UAE Federal Decree-Law No. 33 of 2021 on the Regulation of Labour Relations, with end-of-service gratuity calculated under Article 51.'],
            'KW' => ['articles' => ['Labour Law 6/2010 Art. 51'], 'clause_ar' => 'يخضع هذا العقد لقانون العمل في القطاع الأهلي الكويتي رقم ٦ لسنة ٢٠١٠، وتُحسب مكافأة نهاية الخدمة وفقاً للمادة ٥١.', 'clause_en' => 'This Contract is governed by Kuwaiti Private Sector Labour Law No. 6 of 2010, with end-of-service gratuity calculated pursuant to Article 51.'],
            'QA' => ['articles' => ['Labour Law 14/2004 Art. 54'], 'clause_ar' => 'يخضع هذا العقد لقانون العمل القطري رقم ١٤ لسنة ٢٠٠٤، وتُحسب مكافأة نهاية الخدمة وفقاً للمادة ٥٤.', 'clause_en' => 'This Contract is governed by Qatari Labour Law No. 14 of 2004, with end-of-service gratuity calculated under Article 54.'],
            'BH' => ['articles' => ['Labour Law 36/2012 Art. 116'], 'clause_ar' => 'يخضع هذا العقد لقانون العمل في القطاع الأهلي البحريني رقم ٣٦ لسنة ٢٠١٢، وتُحسب مكافأة نهاية الخدمة وفقاً للمادة ١١٦.', 'clause_en' => 'This Contract is governed by Bahrain Private Sector Labour Law No. 36 of 2012, with end-of-service gratuity calculated under Article 116.'],
            'OM' => ['articles' => ['Royal Decree 53/2023 Art. 61'], 'clause_ar' => 'يخضع هذا العقد لقانون العمل العُماني الصادر بالمرسوم السلطاني ٥٣/٢٠٢٣، وتُحسب مكافأة نهاية الخدمة وفقاً للمادة ٦١.', 'clause_en' => 'This Contract is governed by the Omani Labour Law (Royal Decree 53/2023), with end-of-service gratuity calculated pursuant to Article 61.'],
            'JO' => ['articles' => ['Labour Law 8/1996 Art. 32'], 'clause_ar' => 'يخضع هذا العقد لقانون العمل الأردني رقم ٨ لسنة ١٩٩٦، وتُحسب مكافأة نهاية الخدمة وفقاً للمادة ٣٢.', 'clause_en' => 'This Contract is governed by Jordanian Labour Law No. 8 of 1996, with end-of-service gratuity calculated pursuant to Article 32.'],
            'LB' => ['articles' => ['Labour Law of 1946 Art. 50'], 'clause_ar' => 'يخضع هذا العقد لقانون العمل اللبناني الصادر عام ١٩٤٦ وتعديلاته، وتُحسب التعويضات وفقاً للمادة ٥٠.', 'clause_en' => 'This Contract is governed by the Lebanese Labour Law of 1946, as amended, with severance calculated under Article 50.'],
            'TN' => ['articles' => ['مجلة الشغل Art. 22'], 'clause_ar' => 'يخضع هذا العقد لمجلة الشغل التونسية، وتُحسب التعويضات وفقاً للفصل ٢٢.', 'clause_en' => 'This Contract is governed by the Tunisian Labour Code, with severance calculated under Article 22.'],
            'LY' => ['articles' => ['Labour Law 12/2010 Art. 71'], 'clause_ar' => 'يخضع هذا العقد لقانون علاقات العمل الليبي رقم ١٢ لسنة ٢٠١٠، وتُحسب مكافأة نهاية الخدمة وفقاً للمادة ٧١.', 'clause_en' => 'This Contract is governed by Libyan Labour Relations Law No. 12 of 2010, with end-of-service gratuity calculated pursuant to Article 71.'],
        ],
        'services' => [
            'EG' => ['articles' => ['148', '619', '637'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادتين ١٤٨ و٦١٩ من القانون المدني المصري، ويتحمل المسؤولية وفق المادة ٦٣٧.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Articles 148 and 619 of the Egyptian Civil Code, with liability under Article 637.'],
            'SA' => ['articles' => ['126', '440'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ١٢٦ من نظام المعاملات المدنية السعودي، ويُطبَّق ضمان العيوب وفق المادة ٤٤٠.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 126 of the Saudi Civil Transactions Law, with defect warranties applying under Article 440.'],
            'AE' => ['articles' => ['246', '872', '880'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ٢٤٦ من قانون المعاملات المدنية الإماراتي، وتُطبَّق أحكام عقد المقاولة في المواد ٨٧٢ و٨٨٠.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 246 of the UAE Civil Transactions Law. Muqawala (works) provisions apply under Articles 872 and 880.'],
            'KW' => ['articles' => ['197', '659'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ١٩٧ من القانون المدني الكويتي، وتُطبَّق أحكام عقد المقاولة في المادة ٦٥٩.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 197 of the Kuwaiti Civil Code. Works provisions apply under Article 659.'],
            'QA' => ['articles' => ['172', '682'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ١٧٢ من القانون المدني القطري، وتُطبَّق أحكام عقد المقاولة في المادة ٦٨٢.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 172 of the Qatari Civil Code. Works provisions apply under Article 682.'],
            'BH' => ['articles' => ['129', '581'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ١٢٩ من القانون المدني البحريني، وتُطبَّق أحكام عقد المقاولة في المادة ٥٨١.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 129 of the Bahrain Civil Code. Works provisions apply under Article 581.'],
            'OM' => ['articles' => ['157', '624'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ١٥٧ من قانون المعاملات المدنية العُماني، وتُطبَّق أحكام عقد المقاولة في المادة ٦٢٤.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 157 of the Omani Civil Transactions Law. Works provisions apply under Article 624.'],
            'JO' => ['articles' => ['202', '780'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ٢٠٢ من القانون المدني الأردني، وتُطبَّق أحكام عقد المقاولة في المادة ٧٨٠.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 202 of the Jordanian Civil Code. Works provisions apply under Article 780.'],
            'LB' => ['articles' => ['221', '624'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ٢٢١ من قانون الموجبات والعقود اللبناني، وتُطبَّق أحكام عقد المقاولة في المادة ٦٢٤.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 221 of the Lebanese Code of Obligations. Works provisions apply under Article 624.'],
            'TN' => ['articles' => ['243', '828'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للفصل ٢٤٣ من مجلة الالتزامات والعقود التونسية، وتُطبَّق أحكام عقد المقاولة في الفصل ٨٢٨.', 'clause_en' => 'The Service Provider shall perform the Services in good faith under Article 243 of the Tunisian Code of Obligations. Works provisions apply under Article 828.'],
            'LY' => ['articles' => ['148', '637'], 'clause_ar' => 'يلتزم مزود الخدمة بأداء الخدمات بحسن نية وفقاً للمادة ١٤٨ من القانون المدني الليبي، ويتحمل المسؤولية وفق المادة ٦٣٧.', 'clause_en' => 'The Service Provider shall perform the Services in good faith pursuant to Article 148 of the Libyan Civil Code, with liability under Article 637.'],
        ],
        'nda' => [
            'EG' => ['articles' => ['148'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وعدم إفصاحها للغير وفقاً لمبدأ حسن النية في المادة ١٤٨ من القانون المدني المصري، ويستمر هذا الالتزام لخمس سنوات من تاريخ انتهاء العقد.', 'clause_en' => 'The Receiving Party shall maintain the confidentiality of the Information and shall not disclose it to third parties pursuant to the good-faith principle under Article 148 of the Egyptian Civil Code, for five years following termination.'],
            'SA' => ['articles' => ['126'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ١٢٦ من نظام المعاملات المدنية السعودي، ويستمر هذا الالتزام لخمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 126 of the Saudi Civil Transactions Law for five years.'],
            'AE' => ['articles' => ['246', '892'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادتين ٢٤٦ و٨٩٢ من قانون المعاملات المدنية الإماراتي، ويستمر هذا الالتزام لخمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality pursuant to Articles 246 and 892 of the UAE Civil Transactions Law for five years.'],
            'KW' => ['articles' => ['197'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ١٩٧ من القانون المدني الكويتي لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 197 of the Kuwaiti Civil Code for five years.'],
            'QA' => ['articles' => ['172'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ١٧٢ من القانون المدني القطري لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 172 of the Qatari Civil Code for five years.'],
            'BH' => ['articles' => ['129'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ١٢٩ من القانون المدني البحريني لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 129 of the Bahrain Civil Code for five years.'],
            'OM' => ['articles' => ['157'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ١٥٧ من قانون المعاملات المدنية العُماني لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 157 of the Omani Civil Transactions Law for five years.'],
            'JO' => ['articles' => ['202'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ٢٠٢ من القانون المدني الأردني لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 202 of the Jordanian Civil Code for five years.'],
            'LB' => ['articles' => ['221'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ٢٢١ من قانون الموجبات والعقود اللبناني لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 221 of the Lebanese Code of Obligations for five years.'],
            'TN' => ['articles' => ['243'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للفصل ٢٤٣ من مجلة الالتزامات والعقود التونسية لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 243 of the Tunisian Code of Obligations for five years.'],
            'LY' => ['articles' => ['148'], 'clause_ar' => 'يلتزم الطرف المتلقي بالحفاظ على سرية المعلومات وفقاً للمادة ١٤٨ من القانون المدني الليبي لمدة خمس سنوات.', 'clause_en' => 'The Receiving Party shall maintain confidentiality under Article 148 of the Libyan Civil Code for five years.'],
        ],
        'lease' => [
            'EG' => ['articles' => ['558', '563', '590'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين المؤجرة في حالة صالحة للاستعمال المتفق عليه، وفقاً للمواد ٥٥٨ و٥٦٣ و٥٩٠ من القانون المدني المصري وقانون إيجار الأماكن.', 'clause_en' => 'The Landlord shall deliver the Premises in a state fit for the agreed use pursuant to Articles 558, 563, and 590 of the Egyptian Civil Code and the Premises Rental Law.'],
            'SA' => ['articles' => ['458'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين المؤجرة في حالة صالحة وفقاً للمادة ٤٥٨ من نظام المعاملات المدنية السعودي ولوائح الإيجار.', 'clause_en' => 'The Landlord shall deliver the Premises in fit condition pursuant to Article 458 of the Saudi Civil Transactions Law and the tenancy regulations.'],
            'AE' => ['articles' => ['742', '763'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين المؤجرة في حالة صالحة وفق المادتين ٧٤٢ و٧٦٣ من قانون المعاملات المدنية الإماراتي وقوانين الإيجار في الإمارة.', 'clause_en' => 'The Landlord shall deliver the Premises in fit condition pursuant to Articles 742 and 763 of the UAE Civil Transactions Law and applicable Emirate-level rental laws.'],
            'KW' => ['articles' => ['561', '595'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين في حالة صالحة وفق المادتين ٥٦١ و٥٩٥ من القانون المدني الكويتي.', 'clause_en' => 'The Landlord shall deliver the Premises in fit condition under Articles 561 and 595 of the Kuwaiti Civil Code.'],
            'QA' => ['articles' => ['582', '610'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين في حالة صالحة وفق المادتين ٥٨٢ و٦١٠ من القانون المدني القطري.', 'clause_en' => 'The Landlord shall deliver the Premises in fit condition under Articles 582 and 610 of the Qatari Civil Code.'],
            'BH' => ['articles' => ['476', '500'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين في حالة صالحة وفق المادتين ٤٧٦ و٥٠٠ من القانون المدني البحريني.', 'clause_en' => 'The Landlord shall deliver the Premises in fit condition under Articles 476 and 500 of the Bahrain Civil Code.'],
            'OM' => ['articles' => ['525', '551'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين وفق المادتين ٥٢٥ و٥٥١ من قانون المعاملات المدنية العُماني.', 'clause_en' => 'The Landlord shall deliver the Premises pursuant to Articles 525 and 551 of the Omani Civil Transactions Law.'],
            'JO' => ['articles' => ['658', '676'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين وفق المادتين ٦٥٨ و٦٧٦ من القانون المدني الأردني.', 'clause_en' => 'The Landlord shall deliver the Premises under Articles 658 and 676 of the Jordanian Civil Code.'],
            'LB' => ['articles' => ['533', '560'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين وفق المادتين ٥٣٣ و٥٦٠ من قانون الموجبات والعقود اللبناني.', 'clause_en' => 'The Landlord shall deliver the Premises under Articles 533 and 560 of the Lebanese Code of Obligations.'],
            'TN' => ['articles' => ['729', '745'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين وفق الفصلين ٧٢٩ و٧٤٥ من مجلة الالتزامات والعقود التونسية.', 'clause_en' => 'The Landlord shall deliver the Premises under Articles 729 and 745 of the Tunisian Code of Obligations.'],
            'LY' => ['articles' => ['558', '590'], 'clause_ar' => 'يلتزم المؤجر بتسليم العين وفق المادتين ٥٥٨ و٥٩٠ من القانون المدني الليبي.', 'clause_en' => 'The Landlord shall deliver the Premises pursuant to Articles 558 and 590 of the Libyan Civil Code.'],
        ],
        'distribution' => [
            'EG' => ['articles' => ['Commercial Agency Law 120/1982'], 'clause_ar' => 'يخضع هذا العقد لقانون الوكالة التجارية المصري رقم ١٢٠ لسنة ١٩٨٢ في الجوانب التي ينطبق عليها، وللقواعد العامة في القانون المدني فيما عدا ذلك.', 'clause_en' => 'This Agreement is governed by Egyptian Commercial Agency Law No. 120 of 1982 to the extent applicable, and the general rules of the Civil Code otherwise.'],
            'SA' => ['articles' => ['Commercial Agencies Law M/11'], 'clause_ar' => 'يخضع هذا العقد لنظام الوكالات التجارية السعودي (مرسوم ملكي م/١١) ولوائحه التنفيذية ووجوب التسجيل لدى وزارة التجارة.', 'clause_en' => 'This Agreement is governed by the Saudi Commercial Agencies Law (Royal Decree M/11) and its implementing regulations, with mandatory registration with the Ministry of Commerce.'],
            'AE' => ['articles' => ['Federal Law 3/2022'], 'clause_ar' => 'يخضع هذا العقد لقانون تنظيم الوكالات التجارية الإماراتي رقم ٣ لسنة ٢٠٢٢، ويُسجَّل لدى وزارة الاقتصاد إذا كان الموزع وكيلاً تجارياً مسجلاً.', 'clause_en' => 'This Agreement is governed by UAE Commercial Agencies Law No. 3 of 2022 and is registered with the Ministry of Economy where the Distributor qualifies as a registered commercial agent.'],
            'KW' => ['articles' => ['Commercial Agencies Law 13/2016'], 'clause_ar' => 'يخضع هذا العقد لقانون الوكالات التجارية الكويتي رقم ١٣ لسنة ٢٠١٦.', 'clause_en' => 'This Agreement is governed by Kuwaiti Commercial Agencies Law No. 13 of 2016.'],
            'QA' => ['articles' => ['Commercial Agencies Law 8/2002'], 'clause_ar' => 'يخضع هذا العقد لقانون الوكالات التجارية القطري رقم ٨ لسنة ٢٠٠٢.', 'clause_en' => 'This Agreement is governed by Qatari Commercial Agencies Law No. 8 of 2002.'],
            'BH' => ['articles' => ['Commercial Agency Law 10/1992'], 'clause_ar' => 'يخضع هذا العقد لقانون الوكالة التجارية البحريني رقم ١٠ لسنة ١٩٩٢.', 'clause_en' => 'This Agreement is governed by Bahrain Commercial Agency Law No. 10 of 1992.'],
            'OM' => ['articles' => ['Royal Decree 26/1977'], 'clause_ar' => 'يخضع هذا العقد لقانون الوكالات التجارية العُماني الصادر بالمرسوم السلطاني ٢٦/١٩٧٧.', 'clause_en' => 'This Agreement is governed by the Omani Commercial Agencies Law (Royal Decree 26/1977).'],
            'JO' => ['articles' => ['Commercial Agents Law 28/2001'], 'clause_ar' => 'يخضع هذا العقد لقانون الوكلاء والوسطاء التجاريين الأردني رقم ٢٨ لسنة ٢٠٠١.', 'clause_en' => 'This Agreement is governed by Jordanian Commercial Agents Law No. 28 of 2001.'],
            'LB' => ['articles' => ['Decree-Law 34/1967'], 'clause_ar' => 'يخضع هذا العقد للمرسوم الاشتراعي اللبناني رقم ٣٤ لسنة ١٩٦٧ المتعلق بالتمثيل التجاري.', 'clause_en' => 'This Agreement is governed by Lebanese Decree-Law No. 34 of 1967 concerning commercial representation.'],
            'TN' => ['articles' => ['Commercial Code Art. 622'], 'clause_ar' => 'يخضع هذا العقد لأحكام التمثيل التجاري في المجلة التجارية التونسية، الفصل ٦٢٢.', 'clause_en' => 'This Agreement is governed by the commercial-representation provisions of the Tunisian Commercial Code, Article 622.'],
            'LY' => ['articles' => ['Commercial Activity Law 23/2010'], 'clause_ar' => 'يخضع هذا العقد لقانون النشاط التجاري الليبي رقم ٢٣ لسنة ٢٠١٠.', 'clause_en' => 'This Agreement is governed by Libyan Commercial Activity Law No. 23 of 2010.'],
        ],
        'shareholders' => [
            'EG' => ['articles' => ['Companies Law 159/1981'], 'clause_ar' => 'تنطبق أحكام قانون شركات المساهمة المصري رقم ١٥٩ لسنة ١٩٨١ ولائحته التنفيذية على هذه الاتفاقية، مع مراعاة المادة ١٤٨ من القانون المدني بشأن حسن النية.', 'clause_en' => 'Egyptian Companies Law No. 159 of 1981 and its executive regulations apply to this Agreement, subject to Article 148 of the Civil Code on good faith.'],
            'SA' => ['articles' => ['Companies Law M/132'], 'clause_ar' => 'تنطبق أحكام نظام الشركات السعودي (مرسوم ملكي م/١٣٢) على هذه الاتفاقية، مع مراعاة المادة ١٢٦ من نظام المعاملات المدنية.', 'clause_en' => 'The Saudi Companies Law (Royal Decree M/132) applies to this Agreement, subject to Article 126 of the Civil Transactions Law on good faith.'],
            'AE' => ['articles' => ['Federal Decree-Law 32/2021'], 'clause_ar' => 'تنطبق أحكام المرسوم بقانون اتحادي رقم ٣٢ لسنة ٢٠٢١ في شأن الشركات التجارية على هذه الاتفاقية.', 'clause_en' => 'UAE Federal Decree-Law No. 32 of 2021 on Commercial Companies applies to this Agreement.'],
            'KW' => ['articles' => ['Companies Law 1/2016'], 'clause_ar' => 'تنطبق أحكام قانون الشركات الكويتي رقم ١ لسنة ٢٠١٦ على هذه الاتفاقية.', 'clause_en' => 'Kuwaiti Companies Law No. 1 of 2016 applies to this Agreement.'],
            'QA' => ['articles' => ['Companies Law 11/2015'], 'clause_ar' => 'تنطبق أحكام قانون الشركات التجارية القطري رقم ١١ لسنة ٢٠١٥ على هذه الاتفاقية.', 'clause_en' => 'Qatari Commercial Companies Law No. 11 of 2015 applies to this Agreement.'],
            'BH' => ['articles' => ['Companies Law 21/2001'], 'clause_ar' => 'تنطبق أحكام قانون الشركات التجارية البحريني رقم ٢١ لسنة ٢٠٠١ على هذه الاتفاقية.', 'clause_en' => 'Bahrain Commercial Companies Law No. 21 of 2001 applies to this Agreement.'],
            'OM' => ['articles' => ['Royal Decree 18/2019'], 'clause_ar' => 'تنطبق أحكام قانون الشركات التجارية العُماني (مرسوم سلطاني ١٨/٢٠١٩) على هذه الاتفاقية.', 'clause_en' => 'The Omani Commercial Companies Law (Royal Decree 18/2019) applies to this Agreement.'],
            'JO' => ['articles' => ['Companies Law 22/1997'], 'clause_ar' => 'تنطبق أحكام قانون الشركات الأردني رقم ٢٢ لسنة ١٩٩٧ على هذه الاتفاقية.', 'clause_en' => 'Jordanian Companies Law No. 22 of 1997 applies to this Agreement.'],
            'LB' => ['articles' => ['Code of Commerce 1942'], 'clause_ar' => 'تنطبق أحكام قانون التجارة اللبناني الصادر عام ١٩٤٢ على هذه الاتفاقية.', 'clause_en' => 'The Lebanese Commercial Code of 1942 applies to this Agreement.'],
            'TN' => ['articles' => ['Code of Commercial Companies'], 'clause_ar' => 'تنطبق أحكام مجلة الشركات التجارية التونسية على هذه الاتفاقية.', 'clause_en' => 'The Tunisian Code of Commercial Companies applies to this Agreement.'],
            'LY' => ['articles' => ['Commercial Activity Law 23/2010'], 'clause_ar' => 'تنطبق أحكام قانون النشاط التجاري الليبي رقم ٢٣ لسنة ٢٠١٠ على هذه الاتفاقية.', 'clause_en' => 'Libyan Commercial Activity Law No. 23 of 2010 applies to this Agreement.'],
        ],
    ],
];
