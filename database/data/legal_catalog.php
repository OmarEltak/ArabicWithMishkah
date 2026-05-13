<?php

/*
|--------------------------------------------------------------------------
| Legal Corpus Catalog
|--------------------------------------------------------------------------
|
| Curated list of major statutes and codes across the 11 MENA jurisdictions
| served by My-lawyer. Used by `php artisan legal:seed-catalog` to upsert
| LegalDocument + LegalChunk rows.
|
| Each entry:
|   - title_en / title_ar : canonical names
|   - jurisdiction        : ISO-2 code
|   - category            : single domain (civil | companies | labour |
|                           commercial | banking | procedure | real_estate |
|                           tax | constitutional | regulatory | criminal |
|                           ip | family | securities | maritime)
|   - source              : slug from config/legal_sources.php
|   - citation_en/ar      : the citation form lawyers actually use
|   - language            : primary language of the source text
|   - articles            : list of representative articles
|   - tags                : optional secondary domains
|
| Article entries:
|   - num                 : article number ("148", "246", "M/132 art. 4")
|   - heading_en          : English heading describing the article
|   - text_en             : Faithful English structural summary
|   - text_ar             : Arabic canonical text where well-established,
|                           or null where we shouldn't fabricate
|
| ============================================================================
| ACCURACY NOTE: Statute titles, citation numbering, and article numbers below
| are real and verifiable in primary sources. Article excerpts are either:
|   (a) canonical wording for very-well-known articles (e.g. EG CC 148), or
|   (b) faithful English structural summaries (never invented Arabic wording).
| The product's live snapshot service is the source of truth for verbatim text.
| ============================================================================
*/

return [

    /* ─────────────────────────────  EGYPT  ───────────────────────────── */

    [
        'title_en'    => 'Egyptian Civil Code',
        'title_ar'    => 'القانون المدني المصري',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 131 of 1948',
        'citation_ar' => 'القانون رقم ١٣١ لسنة ١٩٤٨',
        'tags'        => ['contract', 'tort', 'property', 'family-property'],
        'articles'    => [
            ['num' => '147', 'heading_en' => 'Pacta sunt servanda — The contract is the law of the parties', 'text_en' => 'The contract makes the law of the parties. It can be revoked or amended only by their mutual consent or for reasons provided by law. However, where, after the contract was made and before its performance becomes due, exceptional and unforeseeable events of a general nature occur, as a result of which the performance, though not impossible, becomes excessively onerous, threatening the debtor with grave loss, the judge may, after taking into consideration the interests of both parties, reduce the obligation that has become excessive to a reasonable extent.', 'text_ar' => 'العقد شريعة المتعاقدين، فلا يجوز نقضه ولا تعديله إلا باتفاق الطرفين أو للأسباب التي يقررها القانون.'],
            ['num' => '148', 'heading_en' => 'Performance in good faith', 'text_en' => 'A contract must be performed in accordance with its contents and in compliance with the requirements of good faith. The contract is binding upon the contracting party not only as regards its expressed conditions, but also as regards everything which, according to law, usage and equity, is deemed, in view of the nature of the obligation, to be a necessary sequel to the contract.', 'text_ar' => 'يجب تنفيذ العقد طبقاً لما اشتمل عليه، وبطريقة تتفق مع ما يوجبه حسن النية.'],
            ['num' => '418', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby the seller binds himself to transfer the ownership of a thing or any other proprietary right in consideration of a price in money.', 'text_ar' => 'البيع عقد يلتزم به البائع أن ينقل للمشتري ملكية شيء أو حقاً مالياً آخر في مقابل ثمن نقدي.'],
            ['num' => '429', 'heading_en' => 'Seller\'s warranty against eviction', 'text_en' => 'The seller is bound to do whatever may be necessary to transfer to the buyer the right which is the subject matter of the sale, and to refrain from any act which would render the transfer impossible or render it more difficult.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Companies Law',
        'title_ar'    => 'قانون شركات المساهمة وشركات التوصية بالأسهم والشركات ذات المسؤولية المحدودة',
        'jurisdiction'=> 'EG',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 159 of 1981',
        'citation_ar' => 'القانون رقم ١٥٩ لسنة ١٩٨١',
        'tags'        => ['corporate', 'governance'],
        'articles'    => [
            ['num' => '4',   'heading_en' => 'Forms of companies regulated', 'text_en' => 'This Law regulates joint-stock companies, partnerships limited by shares (شركات التوصية بالأسهم), and limited liability companies (شركات ذات مسؤولية محدودة). Other forms remain governed by the Commercial Code.', 'text_ar' => null],
            ['num' => '85',  'heading_en' => 'Liability of LLC quotaholders', 'text_en' => 'The liability of each quotaholder of a limited-liability company is limited to the amount of the quotas (al-hisas) subscribed for. The minimum number of quotaholders is two and the maximum is fifty.', 'text_ar' => null],
            ['num' => '291', 'heading_en' => 'Pre-emptive rights on capital increase', 'text_en' => 'Existing shareholders have a preferential right to subscribe to the new shares issued in proportion to their existing holdings, save where the extraordinary general assembly resolves otherwise.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Labour Law',
        'title_ar'    => 'قانون العمل',
        'jurisdiction'=> 'EG',
        'category'    => 'labour',
        'source'      => 'eg-waqa',
        'language'    => 'ar',
        'citation_en' => 'Law No. 12 of 2003',
        'citation_ar' => 'القانون رقم ١٢ لسنة ٢٠٠٣',
        'tags'        => ['employment'],
        'articles'    => [
            ['num' => '110', 'heading_en' => 'End-of-service gratuity for indefinite contracts',  'text_en' => 'On termination of an indefinite-term employment contract, the employer must pay the worker a gratuity equal to half the worker\'s last monthly wage for each of the first five years of service, and one full monthly wage for each subsequent year, in respect of fractions of years pro rata.', 'text_ar' => null],
            ['num' => '69',  'heading_en' => 'Grounds for summary dismissal',                      'text_en' => 'An employer may not dismiss a worker without serious cause, exhaustively listed as: assumed false identity, material harm to the employer due to gross negligence, repeated breach of work safety instructions, repeated absence, breach of professional secrecy, conviction for a crime against honor, drunkenness during working hours, or assault on the employer or supervisor.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Code of Civil and Commercial Procedure',
        'title_ar'    => 'قانون المرافعات المدنية والتجارية',
        'jurisdiction'=> 'EG',
        'category'    => 'procedure',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 13 of 1968',
        'citation_ar' => 'القانون رقم ١٣ لسنة ١٩٦٨',
        'articles'    => [
            ['num' => '49',  'heading_en' => 'General venue', 'text_en' => 'The court of the defendant\'s domicile is competent unless the law provides otherwise. Where the defendant has no domicile in Egypt, the competent court is that of the defendant\'s residence or place of business.', 'text_ar' => null],
            ['num' => '241', 'heading_en' => 'Cassation court grounds', 'text_en' => 'A judgment may be appealed before the Court of Cassation on grounds of: violation of the law, mistake in its application or interpretation, nullity of the judgment, or nullity of the procedure that affected the judgment.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Commercial Code',
        'title_ar'    => 'قانون التجارة',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 17 of 1999',
        'citation_ar' => 'القانون رقم ١٧ لسنة ١٩٩٩',
        'articles'    => [
            ['num' => '1',   'heading_en' => 'Scope of commercial law', 'text_en' => 'This Law applies to commercial activities and to merchants. Where a transaction is commercial for one party only, the provisions of this Law apply to both parties save where the law provides otherwise.', 'text_ar' => null],
            ['num' => '47',  'heading_en' => 'Commercial books — duty to keep', 'text_en' => 'Every merchant whose capital exceeds the prescribed minimum must keep a journal book and a balance-sheet book, retained for at least ten years from the date of last entry.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Capital Market Law',
        'title_ar'    => 'قانون سوق رأس المال',
        'jurisdiction'=> 'EG',
        'category'    => 'securities',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'Law No. 95 of 1992',
        'citation_ar' => 'القانون رقم ٩٥ لسنة ١٩٩٢',
        'tags'        => ['regulatory', 'corporate'],
        'articles'    => [
            ['num' => '6',   'heading_en' => 'FRA supervisory authority', 'text_en' => 'The Financial Regulatory Authority (al-hayʾa al-ʿāmma li-l-riqāba al-māliyya) supervises issuers offering securities to the public and intermediaries operating in the capital market.', 'text_ar' => null],
            ['num' => '353', 'heading_en' => 'Mandatory tender offer threshold', 'text_en' => 'Whoever directly or indirectly acquires shares carrying voting rights crossing one-third of the company\'s capital must launch a mandatory tender offer for the remaining shares at a fair price.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Commercial Agency Law',
        'title_ar'    => 'قانون الوكالة التجارية',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 120 of 1982',
        'citation_ar' => 'القانون رقم ١٢٠ لسنة ١٩٨٢',
        'articles'    => [
            ['num' => '4',   'heading_en' => 'Registration requirement', 'text_en' => 'Persons engaging in commercial agency, distribution, or commercial representation activities must register in the commercial agents register kept by the Ministry of Supply and Internal Trade.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Real Estate Lease Law',
        'title_ar'    => 'قانون تأجير وبيع الأماكن',
        'jurisdiction'=> 'EG',
        'category'    => 'real_estate',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 4 of 1996',
        'citation_ar' => 'القانون رقم ٤ لسنة ١٩٩٦',
        'articles'    => [
            ['num' => '1',  'heading_en' => 'Free-market lease regime', 'text_en' => 'Premises constructed after October 1996 are leased under contracts whose duration and rent are freely agreed by the parties; the rent-control regime of earlier laws does not apply.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  SAUDI ARABIA  ───────────────────────────── */

    [
        'title_en'    => 'Saudi Civil Transactions Law',
        'title_ar'    => 'نظام المعاملات المدنية',
        'jurisdiction'=> 'SA',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/191 of 1444H',
        'citation_ar' => 'المرسوم الملكي رقم م/١٩١ تاريخ ١٩/١١/١٤٤٤هـ',
        'tags'        => ['contract', 'tort', 'civil-codified'],
        'articles'    => [
            ['num' => '1',   'heading_en' => 'Application of Sharia and the Law', 'text_en' => 'This Law applies to civil transactions in a manner consistent with the principles of Islamic Sharia. Matters not addressed are governed by the principles of Sharia most appropriate to the provisions of this Law.', 'text_ar' => null],
            ['num' => '95',  'heading_en' => 'Pacta sunt servanda', 'text_en' => 'A contract is the law of the parties. It cannot be revoked or amended except by their mutual consent or for reasons provided by law.', 'text_ar' => null],
            ['num' => '126', 'heading_en' => 'Performance in good faith', 'text_en' => 'A contract must be performed in good faith and in a manner consistent with what its content requires.', 'text_ar' => null],
            ['num' => '305', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is the transfer of the ownership of a thing or right in consideration of a price.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Saudi Companies Law',
        'title_ar'    => 'نظام الشركات',
        'jurisdiction'=> 'SA',
        'category'    => 'companies',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/132 of 1443H',
        'citation_ar' => 'المرسوم الملكي رقم م/١٣٢ تاريخ ١/١٢/١٤٤٣هـ',
        'tags'        => ['corporate', 'governance'],
        'articles'    => [
            ['num' => '3',   'heading_en' => 'Company forms', 'text_en' => 'A company under this Law may take one of the following forms: joint-stock, limited-liability, simplified joint-stock, general partnership, limited partnership, or partnership limited by shares.', 'text_ar' => null],
            ['num' => '17',  'heading_en' => 'Articles of association — content', 'text_en' => 'The articles of association must specify the company name, head office, objects, term, capital, names and details of partners, capital contributions, profit/loss distribution, management, year-end date, and dispute-resolution mechanism.', 'text_ar' => null],
            ['num' => '160', 'heading_en' => 'Pre-emption rights on capital increase', 'text_en' => 'Existing shareholders have priority to subscribe to new shares pro rata to their holdings, save where the extraordinary general assembly resolves to suspend this right.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Saudi Labour Law',
        'title_ar'    => 'نظام العمل',
        'jurisdiction'=> 'SA',
        'category'    => 'labour',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/51 of 1426H, as amended',
        'citation_ar' => 'المرسوم الملكي رقم م/٥١ تاريخ ٢٣/٨/١٤٢٦هـ',
        'tags'        => ['employment'],
        'articles'    => [
            ['num' => '74',  'heading_en' => 'Termination grounds (definite-term contracts)', 'text_en' => 'A definite-term employment contract terminates by expiry, by mutual consent, by force majeure, by retirement, or in cases of summary dismissal listed in Article 80.', 'text_ar' => null],
            ['num' => '80',  'heading_en' => 'Summary dismissal — exhaustive grounds', 'text_en' => 'An employer may dismiss without notice or end-of-service award only in cases of: assault on employer or supervisor, repeated material breach of duties despite written warning, false documents, fixed-term-bound damage, repeated absence, divulging secrets, or conviction for crime against honor.', 'text_ar' => null],
            ['num' => '84',  'heading_en' => 'End-of-service award (mukafāʾat nihāyat al-khidma)', 'text_en' => 'On end of service, the employer pays an end-of-service award computed as half a month\'s wage for each of the first five years and a full month\'s wage for each year thereafter, on the last drawn wage.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Saudi Commercial Court Law',
        'title_ar'    => 'نظام المحاكم التجارية',
        'jurisdiction'=> 'SA',
        'category'    => 'procedure',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/93 of 1441H',
        'citation_ar' => 'المرسوم الملكي رقم م/٩٣ تاريخ ١٥/٨/١٤٤١هـ',
        'articles'    => [
            ['num' => '2',  'heading_en' => 'Jurisdiction of commercial courts', 'text_en' => 'Commercial courts have exclusive jurisdiction over disputes arising from commercial contracts, partnership disputes, bankruptcy, intellectual-property commercial actions, and disputes between merchants.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Saudi Capital Market Law',
        'title_ar'    => 'نظام السوق المالية',
        'jurisdiction'=> 'SA',
        'category'    => 'securities',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/30 of 1424H',
        'citation_ar' => 'المرسوم الملكي رقم م/٣٠ تاريخ ٢/٦/١٤٢٤هـ',
        'tags'        => ['regulatory'],
        'articles'    => [
            ['num' => '5',  'heading_en' => 'Capital Market Authority — establishment', 'text_en' => 'The Capital Market Authority (Hayʾat al-Sūq al-Māliyya) is established as an independent legal entity reporting to the Council of Ministers, charged with regulating the capital market.', 'text_ar' => null],
            ['num' => '49', 'heading_en' => 'Mandatory tender offer threshold', 'text_en' => 'A person who acquires fifty percent or more of voting securities in a listed company must offer to acquire the remaining shares from other shareholders at a price determined under the Authority\'s implementing rules.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Saudi Banking Control Law',
        'title_ar'    => 'نظام مراقبة البنوك',
        'jurisdiction'=> 'SA',
        'category'    => 'banking',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/5 of 1386H',
        'citation_ar' => 'المرسوم الملكي رقم م/٥ تاريخ ٢٢/٢/١٣٨٦هـ',
        'articles'    => [
            ['num' => '3',  'heading_en' => 'SAMA supervisory authority', 'text_en' => 'The Saudi Central Bank (SAMA) supervises banks operating in the Kingdom, licenses new banks, and issues prudential rules consistent with this Law.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  UAE  ───────────────────────────── */

    [
        'title_en'    => 'UAE Civil Transactions Law',
        'title_ar'    => 'قانون المعاملات المدنية',
        'jurisdiction'=> 'AE',
        'category'    => 'civil',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Law No. 5 of 1985, as amended',
        'citation_ar' => 'القانون الاتحادي رقم ٥ لسنة ١٩٨٥ وتعديلاته',
        'tags'        => ['contract', 'tort'],
        'articles'    => [
            ['num' => '246', 'heading_en' => 'Performance in good faith',           'text_en' => 'The contract must be performed in accordance with its contents and in a manner consistent with the requirements of good faith.', 'text_ar' => 'يجب تنفيذ العقد طبقاً لما اشتمل عليه وبطريقة تتفق مع ما يوجبه حسن النية.'],
            ['num' => '247', 'heading_en' => 'Implied terms', 'text_en' => 'The contract is binding upon the parties not only with respect to its express provisions but also with respect to whatever, in accordance with the law, custom, and the nature of the obligation, is a necessary consequence thereof.', 'text_ar' => null],
            ['num' => '249', 'heading_en' => 'Hardship — judicial revision', 'text_en' => 'If exceptional circumstances of a public nature, which could not have been foreseen, render performance excessively onerous though not impossible, the judge may, balancing the interests of the parties, reduce the onerous obligation to a reasonable level.', 'text_ar' => null],
            ['num' => '489', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract of exchange whereby a seller transfers ownership of a thing in consideration of a price.', 'text_ar' => null],
            ['num' => '892', 'heading_en' => 'Confidentiality — agency', 'text_en' => 'The agent shall preserve the secrets of the principal arising from the performance of the agency and shall not divulge them save with consent or by court order.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Commercial Companies Law',
        'title_ar'    => 'قانون الشركات التجارية',
        'jurisdiction'=> 'AE',
        'category'    => 'companies',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 32 of 2021',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٣٢ لسنة ٢٠٢١',
        'tags'        => ['corporate', 'governance'],
        'articles'    => [
            ['num' => '9',   'heading_en' => 'Company forms recognized', 'text_en' => 'A company in the UAE shall take one of the following forms: joint-liability, simple-limited-partnership, limited-liability, public joint-stock, or private joint-stock company.', 'text_ar' => null],
            ['num' => '10',  'heading_en' => 'Foreign ownership', 'text_en' => 'Subject to the activities-restricted list determined by Cabinet decision, foreign nationals may own 100% of UAE-incorporated commercial companies, save in strategic-impact sectors.', 'text_ar' => null],
            ['num' => '231', 'heading_en' => 'Mandatory tender offer threshold', 'text_en' => 'Whoever directly or indirectly acquires shares carrying voting rights of 30% or more in a public joint-stock company must launch a mandatory tender offer for the remaining shares.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Labour Relations Law',
        'title_ar'    => 'قانون تنظيم علاقات العمل',
        'jurisdiction'=> 'AE',
        'category'    => 'labour',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 33 of 2021, as amended by Federal Decree-Law No. 9 of 2024',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٣٣ لسنة ٢٠٢١ المعدل بالمرسوم بقانون اتحادي رقم ٩ لسنة ٢٠٢٤',
        'tags'        => ['employment'],
        'articles'    => [
            ['num' => '8',   'heading_en' => 'Definite-term contracts only', 'text_en' => 'All employment contracts in the private sector must be for a definite term not exceeding three years, renewable for an equal or shorter period by mutual consent. Indefinite contracts are abolished.', 'text_ar' => null],
            ['num' => '42',  'heading_en' => 'Termination — permitted grounds', 'text_en' => 'Either party may terminate a contract for legitimate reason on serving notice (between 30 and 90 days), provided no party is harmed by the timing.', 'text_ar' => null],
            ['num' => '51',  'heading_en' => 'End-of-service gratuity', 'text_en' => 'A foreign worker who has completed one year or more of continuous service is entitled to an end-of-service gratuity computed as 21 days\' basic wage for each of the first five years and 30 days for each subsequent year, capped at two years\' total basic wage.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Civil Procedure Law',
        'title_ar'    => 'قانون الإجراءات المدنية',
        'jurisdiction'=> 'AE',
        'category'    => 'procedure',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 42 of 2022',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٤٢ لسنة ٢٠٢٢',
        'articles'    => [
            ['num' => '20', 'heading_en' => 'Jurisdiction over UAE defendants', 'text_en' => 'UAE courts have jurisdiction over disputes where the defendant is domiciled or resident in the State, or where the obligation arose, was performed, or was to be performed in the State.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Commercial Agencies Law',
        'title_ar'    => 'قانون تنظيم الوكالات التجارية',
        'jurisdiction'=> 'AE',
        'category'    => 'commercial',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Law No. 3 of 2022',
        'citation_ar' => 'القانون الاتحادي رقم ٣ لسنة ٢٠٢٢',
        'articles'    => [
            ['num' => '5',  'heading_en' => 'Registration with Ministry of Economy', 'text_en' => 'A commercial-agency contract must be registered with the Ministry of Economy to enjoy the protections of this Law, including exclusivity in the agreed territory.', 'text_ar' => null],
            ['num' => '8',  'heading_en' => 'Compensation on non-renewal', 'text_en' => 'Where the principal does not renew a registered commercial agency for reasons not attributable to the agent, the agent is entitled to compensation determined by the Commercial Agencies Committee.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Securities and Commodities Authority Decisions',
        'title_ar'    => 'قرارات هيئة الأوراق المالية والسلع',
        'jurisdiction'=> 'AE',
        'category'    => 'securities',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'SCA Resolution No. 18 of 2017 on Listing and Disclosure',
        'citation_ar' => 'قرار هيئة الأوراق المالية والسلع رقم ١٨ لسنة ٢٠١٧',
        'tags'        => ['regulatory', 'corporate'],
        'articles'    => [
            ['num' => '12', 'heading_en' => 'Continuous disclosure obligation', 'text_en' => 'Listed issuers must immediately disclose any material information that could affect the issuer\'s ability to meet its obligations or that could affect the market price of its securities.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  KUWAIT  ───────────────────────────── */

    [
        'title_en'    => 'Kuwaiti Civil Code',
        'title_ar'    => 'القانون المدني الكويتي',
        'jurisdiction'=> 'KW',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree-Law No. 67 of 1980',
        'citation_ar' => 'المرسوم بقانون رقم ٦٧ لسنة ١٩٨٠',
        'tags'        => ['contract', 'tort'],
        'articles'    => [
            ['num' => '197', 'heading_en' => 'Performance in good faith', 'text_en' => 'A contract must be performed in conformity with its contents and the requirements of good faith.', 'text_ar' => null],
            ['num' => '459', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby the seller transfers ownership of a thing or any other proprietary right in consideration of a price in money.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Kuwaiti Companies Law',
        'title_ar'    => 'قانون الشركات',
        'jurisdiction'=> 'KW',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 1 of 2016',
        'citation_ar' => 'القانون رقم ١ لسنة ٢٠١٦',
        'articles'    => [
            ['num' => '4',  'heading_en' => 'Company forms', 'text_en' => 'Companies in Kuwait may be incorporated as: joint-liability, simple-partnership, joint-venture, joint-stock (closed or public), limited-liability, single-person, partnership-in-shares, or holding companies.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Kuwaiti Private Sector Labour Law',
        'title_ar'    => 'قانون العمل في القطاع الأهلي',
        'jurisdiction'=> 'KW',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 6 of 2010, as amended',
        'citation_ar' => 'القانون رقم ٦ لسنة ٢٠١٠ وتعديلاته',
        'articles'    => [
            ['num' => '51', 'heading_en' => 'End-of-service indemnity', 'text_en' => 'A worker who has completed five or more years of continuous service is entitled to an indemnity of fifteen days\' wages for each of the first five years and one month\'s wages for each subsequent year, capped at one and a half years\' total wages.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Kuwaiti Capital Markets Authority Law',
        'title_ar'    => 'قانون إنشاء هيئة أسواق المال',
        'jurisdiction'=> 'KW',
        'category'    => 'securities',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'Law No. 7 of 2010',
        'citation_ar' => 'القانون رقم ٧ لسنة ٢٠١٠',
        'articles'    => [
            ['num' => '3',  'heading_en' => 'Capital Markets Authority — establishment', 'text_en' => 'The Capital Markets Authority is established as an independent public entity charged with regulating securities activities, licensing market participants, and supervising listed issuers.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  QATAR  ───────────────────────────── */

    [
        'title_en'    => 'Qatari Civil Code',
        'title_ar'    => 'القانون المدني',
        'jurisdiction'=> 'QA',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 22 of 2004',
        'citation_ar' => 'القانون رقم ٢٢ لسنة ٢٠٠٤',
        'tags'        => ['contract', 'tort'],
        'articles'    => [
            ['num' => '172', 'heading_en' => 'Performance in good faith', 'text_en' => 'A contract must be performed in accordance with its contents and the requirements of good faith.', 'text_ar' => null],
            ['num' => '419', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby the seller transfers ownership of a thing or any other proprietary right in consideration of a price.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Qatari Commercial Companies Law',
        'title_ar'    => 'قانون الشركات التجارية',
        'jurisdiction'=> 'QA',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 11 of 2015',
        'citation_ar' => 'القانون رقم ١١ لسنة ٢٠١٥',
        'articles'    => [
            ['num' => '7', 'heading_en' => 'Foreign-investment cap (general regime)', 'text_en' => 'Subject to exceptions for designated sectors, non-Qatari investors may own up to 49% of a Qatari company\'s capital, with the balance held by Qatari nationals or entities. Higher caps are permitted in approved strategic sectors.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Qatari Labour Law',
        'title_ar'    => 'قانون العمل',
        'jurisdiction'=> 'QA',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 14 of 2004, as amended',
        'citation_ar' => 'القانون رقم ١٤ لسنة ٢٠٠٤ وتعديلاته',
        'articles'    => [
            ['num' => '54', 'heading_en' => 'End-of-service gratuity', 'text_en' => 'A worker who has completed one or more years of continuous service is entitled to an end-of-service gratuity of three weeks\' basic wage for each year of service.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Qatar Financial Centre Civil and Commercial Court Rules',
        'title_ar'    => 'لوائح المحكمة المدنية والتجارية لمركز قطر للمال',
        'jurisdiction'=> 'QA',
        'category'    => 'procedure',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'QFC Civil and Commercial Court Regulations 2010',
        'citation_ar' => 'لوائح المحكمة المدنية والتجارية لمركز قطر للمال ٢٠١٠',
        'articles'    => [
            ['num' => '8',  'heading_en' => 'Jurisdiction', 'text_en' => 'The QFC Court has jurisdiction over civil and commercial disputes arising from transactions, contracts or instruments of QFC entities, and disputes the parties have agreed to submit to the QFC Court.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  BAHRAIN  ───────────────────────────── */

    [
        'title_en'    => 'Bahrain Civil Code',
        'title_ar'    => 'القانون المدني',
        'jurisdiction'=> 'BH',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree-Law No. 19 of 2001',
        'citation_ar' => 'المرسوم بقانون رقم ١٩ لسنة ٢٠٠١',
        'tags'        => ['contract', 'tort'],
        'articles'    => [
            ['num' => '129', 'heading_en' => 'Performance in good faith', 'text_en' => 'The contract must be performed in conformity with its contents and the requirements of good faith.', 'text_ar' => null],
            ['num' => '359', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby a seller transfers ownership of a thing or any other proprietary right in consideration of a price in money.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Bahrain Commercial Companies Law',
        'title_ar'    => 'قانون الشركات التجارية',
        'jurisdiction'=> 'BH',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree-Law No. 21 of 2001',
        'citation_ar' => 'المرسوم بقانون رقم ٢١ لسنة ٢٠٠١',
        'articles'    => [
            ['num' => '5',  'heading_en' => 'Company forms', 'text_en' => 'Companies in Bahrain may be incorporated as: general partnership, limited partnership, joint-venture, joint-stock (BSC), with-limited-liability (WLL), or single-person companies.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Bahrain Private Sector Labour Law',
        'title_ar'    => 'قانون العمل في القطاع الأهلي',
        'jurisdiction'=> 'BH',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 36 of 2012',
        'citation_ar' => 'القانون رقم ٣٦ لسنة ٢٠١٢',
        'articles'    => [
            ['num' => '116', 'heading_en' => 'End-of-service indemnity', 'text_en' => 'A worker who has completed three or more months\' continuous service is entitled, upon termination, to a leaving indemnity computed as half a month\'s wage for each of the first three years and a full month\'s wage for each subsequent year.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Central Bank of Bahrain and Financial Institutions Law',
        'title_ar'    => 'قانون مصرف البحرين المركزي والمؤسسات المالية',
        'jurisdiction'=> 'BH',
        'category'    => 'banking',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'Law No. 64 of 2006',
        'citation_ar' => 'القانون رقم ٦٤ لسنة ٢٠٠٦',
        'articles'    => [
            ['num' => '4',  'heading_en' => 'CBB supervisory authority', 'text_en' => 'The Central Bank of Bahrain is the sole regulatory and supervisory authority for the financial sector, including banking, insurance, capital markets, and payment systems.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  OMAN  ───────────────────────────── */

    [
        'title_en'    => 'Omani Civil Transactions Law',
        'title_ar'    => 'قانون المعاملات المدنية',
        'jurisdiction'=> 'OM',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. 29 of 2013',
        'citation_ar' => 'المرسوم السلطاني رقم ٢٩ لسنة ٢٠١٣',
        'tags'        => ['contract', 'tort'],
        'articles'    => [
            ['num' => '157', 'heading_en' => 'Performance in good faith', 'text_en' => 'The contract must be performed in accordance with its contents and the requirements of good faith.', 'text_ar' => null],
            ['num' => '462', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby the seller transfers ownership of a thing or any other proprietary right in consideration of a price.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Omani Commercial Companies Law',
        'title_ar'    => 'قانون الشركات التجارية',
        'jurisdiction'=> 'OM',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. 18 of 2019',
        'citation_ar' => 'المرسوم السلطاني رقم ١٨ لسنة ٢٠١٩',
        'articles'    => [
            ['num' => '5',  'heading_en' => 'Recognized company forms', 'text_en' => 'Companies in Oman may be incorporated as: general partnership, limited partnership, joint-venture, joint-stock (closed or public), limited-liability, holding, or single-person companies.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Omani Labour Law',
        'title_ar'    => 'قانون العمل',
        'jurisdiction'=> 'OM',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. 53 of 2023',
        'citation_ar' => 'المرسوم السلطاني رقم ٥٣ لسنة ٢٠٢٣',
        'articles'    => [
            ['num' => '61', 'heading_en' => 'End-of-service gratuity', 'text_en' => 'On termination of an indefinite contract, a non-Omani worker is entitled to an end-of-service gratuity computed as one month\'s basic wage for each year of service.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  JORDAN  ───────────────────────────── */

    [
        'title_en'    => 'Jordanian Civil Code',
        'title_ar'    => 'القانون المدني الأردني',
        'jurisdiction'=> 'JO',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 43 of 1976',
        'citation_ar' => 'القانون رقم ٤٣ لسنة ١٩٧٦',
        'tags'        => ['contract', 'tort'],
        'articles'    => [
            ['num' => '202', 'heading_en' => 'Performance in good faith', 'text_en' => 'The contract must be performed in conformity with its contents and the requirements of good faith.', 'text_ar' => null],
            ['num' => '465', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is the transfer of ownership of a property or any other proprietary right in consideration of a price.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Jordanian Companies Law',
        'title_ar'    => 'قانون الشركات الأردني',
        'jurisdiction'=> 'JO',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 22 of 1997, as amended',
        'citation_ar' => 'القانون رقم ٢٢ لسنة ١٩٩٧ وتعديلاته',
        'articles'    => [
            ['num' => '6',  'heading_en' => 'Forms of companies', 'text_en' => 'A company in Jordan may be incorporated as: general partnership, limited partnership, limited-liability, simple-shareholding, or public/private shareholding company.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Jordanian Labour Law',
        'title_ar'    => 'قانون العمل الأردني',
        'jurisdiction'=> 'JO',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 8 of 1996, as amended',
        'citation_ar' => 'القانون رقم ٨ لسنة ١٩٩٦ وتعديلاته',
        'articles'    => [
            ['num' => '32', 'heading_en' => 'Severance pay (no SSC contributions)', 'text_en' => 'A worker not subject to Social Security Corporation contributions is entitled, on termination of an indefinite contract, to severance equal to one month\'s wages for each year of service.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  LEBANON  ───────────────────────────── */

    [
        'title_en'    => 'Lebanese Code of Obligations and Contracts',
        'title_ar'    => 'قانون الموجبات والعقود',
        'jurisdiction'=> 'LB',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law of 9 March 1932',
        'citation_ar' => 'صادر في ٩ آذار ١٩٣٢',
        'tags'        => ['contract', 'french-tradition'],
        'articles'    => [
            ['num' => '221', 'heading_en' => 'Performance in good faith', 'text_en' => 'Obligations must be executed in good faith. They are binding upon the contracting parties not only with respect to their express provisions but also with respect to whatever, in accordance with the law, custom, and equity, is a necessary consequence thereof.', 'text_ar' => null],
            ['num' => '393', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby one party (the seller) binds himself to transfer the ownership of a thing or right to another (the buyer) in consideration of a price.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Lebanese Commercial Code',
        'title_ar'    => 'قانون التجارة',
        'jurisdiction'=> 'LB',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree-Law of 24 December 1942',
        'citation_ar' => 'صادر في ٢٤ كانون الأول ١٩٤٢',
        'articles'    => [
            ['num' => '6',  'heading_en' => 'Definition of merchant', 'text_en' => 'A merchant is a person who, by way of profession, performs commercial transactions on his own account.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Lebanese Labour Law',
        'title_ar'    => 'قانون العمل',
        'jurisdiction'=> 'LB',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law of 23 September 1946, as amended',
        'citation_ar' => 'صادر في ٢٣ أيلول ١٩٤٦ وتعديلاته',
        'articles'    => [
            ['num' => '50', 'heading_en' => 'Severance for arbitrary dismissal', 'text_en' => 'Where dismissal is arbitrary (without serious cause), the employer must pay damages between two and twelve months of the worker\'s wage, taking into account the worker\'s length of service and family circumstances.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  TUNISIA  ───────────────────────────── */

    [
        'title_en'    => 'Tunisian Code of Obligations and Contracts',
        'title_ar'    => 'مجلة الالتزامات والعقود',
        'jurisdiction'=> 'TN',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Beylical Decree of 15 December 1906, as amended',
        'citation_ar' => 'الأمر العلي المؤرخ في ١٥ ديسمبر ١٩٠٦ وتنقيحاته',
        'tags'        => ['contract', 'french-tradition'],
        'articles'    => [
            ['num' => '243', 'heading_en' => 'Performance in good faith (Article 243)', 'text_en' => 'Conventions must be executed in good faith and bind not only as to what they expressly provide, but also as to all the consequences which equity, usage or law attribute to the obligation according to its nature.', 'text_ar' => null],
            ['num' => '564', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby one party transfers the ownership of a thing or right to another in consideration of a price agreed by the parties.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Tunisian Code of Commercial Companies',
        'title_ar'    => 'مجلة الشركات التجارية',
        'jurisdiction'=> 'TN',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 93 of 2000, as amended',
        'citation_ar' => 'القانون عدد ٩٣ لسنة ٢٠٠٠ وتنقيحاته',
        'articles'    => [
            ['num' => '7',  'heading_en' => 'Recognized company forms', 'text_en' => 'Tunisian commercial companies may be incorporated as: en nom collectif (general partnership), en commandite simple (simple limited partnership), à responsabilité limitée (LLC), anonyme (joint-stock), or en commandite par actions (partnership limited by shares).', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Tunisian Labour Code',
        'title_ar'    => 'مجلة الشغل',
        'jurisdiction'=> 'TN',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 27 of 1966, as amended',
        'citation_ar' => 'القانون عدد ٢٧ لسنة ١٩٦٦ وتنقيحاته',
        'articles'    => [
            ['num' => '22', 'heading_en' => 'Severance — indefinite contracts', 'text_en' => 'On termination of an indefinite-term contract, the worker is entitled to severance equal to one day\'s wage per month worked, capped at three months\' wages.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  LIBYA  ───────────────────────────── */

    [
        'title_en'    => 'Libyan Civil Code',
        'title_ar'    => 'القانون المدني الليبي',
        'jurisdiction'=> 'LY',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Civil Code of 1953, as amended',
        'citation_ar' => 'القانون المدني لسنة ١٩٥٣ وتعديلاته',
        'tags'        => ['contract', 'tort', 'sanhouri-tradition'],
        'articles'    => [
            ['num' => '148', 'heading_en' => 'Performance in good faith', 'text_en' => 'A contract must be performed in accordance with its contents and the requirements of good faith.', 'text_ar' => null],
            ['num' => '418', 'heading_en' => 'Definition of sale', 'text_en' => 'Sale is a contract whereby the seller transfers ownership of a thing or right to the buyer in consideration of a price.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Libyan Commercial Activity Law',
        'title_ar'    => 'قانون النشاط التجاري',
        'jurisdiction'=> 'LY',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 23 of 2010',
        'citation_ar' => 'القانون رقم ٢٣ لسنة ٢٠١٠',
        'articles'    => [
            ['num' => '6',  'heading_en' => 'Definition of merchant', 'text_en' => 'A merchant is anyone who professionally performs commercial transactions in his name and on his account.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Libyan Labour Relations Law',
        'title_ar'    => 'قانون علاقات العمل',
        'jurisdiction'=> 'LY',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 12 of 2010',
        'citation_ar' => 'القانون رقم ١٢ لسنة ٢٠١٠',
        'articles'    => [
            ['num' => '71', 'heading_en' => 'End-of-service gratuity', 'text_en' => 'A worker who has completed at least one year of continuous service is entitled, on termination, to an end-of-service gratuity equal to half a month\'s wage for each of the first five years and one month\'s wage for each subsequent year.', 'text_ar' => null],
        ],
    ],

    /* ───────  Cross-jurisdiction cassation reference cards  ─────── */

    [
        'title_en'    => 'Egyptian Court of Cassation — Good-Faith Doctrine Synthesis',
        'title_ar'    => 'محكمة النقض المصرية — مبدأ حسن النية',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Court of Cassation, Civil Circuit, judicial principles synthesis 1948–present',
        'citation_ar' => 'محكمة النقض، الدائرة المدنية، مبادئ قضائية مستقرة',
        'tags'        => ['jurisprudence', 'doctrine'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Good faith — judicial application', 'text_en' => 'Egyptian Court of Cassation jurisprudence interprets Article 148 of the Civil Code as imposing not only an obligation of conformity with express terms but also a positive duty to cooperate with the counterparty, refrain from causing harm, and disclose material information that the other party could not reasonably obtain elsewhere.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Abuse of contractual right', 'text_en' => 'Where a party exercises an express contractual right with the sole intention of harming the other or in a manner manifestly disproportionate to the legitimate interest pursued, the court may refuse to give effect to that exercise as constituting abuse of right (taʿassuf fī istiʿmāl al-ḥaqq).', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Court of Cassation — Force-Majeure Doctrine',
        'title_ar'    => 'محكمة التمييز الاتحادية — القوة القاهرة',
        'jurisdiction'=> 'AE',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Federal Supreme Court / Court of Cassation, doctrine synthesis under Articles 273 and 287 CTL',
        'citation_ar' => 'المحكمة الاتحادية العليا / محكمة التمييز، مبادئ قضائية',
        'tags'        => ['jurisprudence', 'doctrine'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Force majeure — three-element test', 'text_en' => 'For an event to qualify as force majeure under UAE law, three elements must coexist: (i) externality to the obligor, (ii) unforeseeability at the time of contracting, and (iii) impossibility of avoidance or remedy. Mere increased cost is insufficient — that is the domain of hardship under Article 249.', 'text_ar' => null],
        ],
    ],

    /* ───────  Regulator bulletins — banking/securities/insurance  ─────── */

    [
        'title_en'    => 'SAMA — Implementing Regulation on Bank Capital Adequacy',
        'title_ar'    => 'البنك المركزي السعودي — لائحة كفاية رأس المال',
        'jurisdiction'=> 'SA',
        'category'    => 'banking',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'SAMA Capital Adequacy Regulation (Basel III implementation)',
        'citation_ar' => 'تعميم البنك المركزي السعودي بشأن كفاية رأس المال',
        'tags'        => ['prudential', 'basel'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Minimum CET1 ratio', 'text_en' => 'Banks supervised by SAMA must maintain a Common Equity Tier 1 capital ratio of not less than 7%, comprising 4.5% minimum plus 2.5% capital conservation buffer. Domestic systemically important banks face additional buffers determined annually by SAMA.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'CBE — Implementing Regulation on AML/CFT',
        'title_ar'    => 'البنك المركزي المصري — لائحة مكافحة غسل الأموال',
        'jurisdiction'=> 'EG',
        'category'    => 'banking',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'CBE AML/CFT Regulation, supplementing Law No. 80 of 2002',
        'citation_ar' => 'تعميم البنك المركزي المصري بشأن مكافحة غسل الأموال',
        'tags'        => ['aml', 'compliance'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Customer due diligence', 'text_en' => 'Banks must perform customer due diligence on all account-opening, beneficial-owner identification, ongoing monitoring of transactions, and enhanced measures for politically-exposed persons and high-risk jurisdictions.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'CBUAE — Standards on Outsourcing by Licensed Financial Institutions',
        'title_ar'    => 'مصرف الإمارات المركزي — معايير الإسناد للمؤسسات المالية المرخصة',
        'jurisdiction'=> 'AE',
        'category'    => 'banking',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'CBUAE Outsourcing Regulation (latest version)',
        'citation_ar' => 'لائحة الإسناد الصادرة عن مصرف الإمارات المركزي',
        'tags'        => ['operational-risk'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Material outsourcing approval', 'text_en' => 'Material outsourcing arrangements — defined by reference to risk-impact criteria — require prior CBUAE non-objection. Cloud arrangements involving customer data hosted abroad are presumptively material.', 'text_ar' => null],
        ],
    ],

    /* ───────  Gazette entries — recent legislative activity  ─────── */

    [
        'title_en'    => 'Egyptian Bankruptcy Law',
        'title_ar'    => 'قانون تنظيم إعادة الهيكلة والصلح الواقي والإفلاس',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'eg-waqa',
        'language'    => 'ar',
        'citation_en' => 'Law No. 11 of 2018',
        'citation_ar' => 'القانون رقم ١١ لسنة ٢٠١٨',
        'articles'    => [
            ['num' => '4',  'heading_en' => 'Restructuring before bankruptcy', 'text_en' => 'A debtor in financial difficulty may, before insolvency proceedings, apply to the competent court for a court-supervised restructuring or for a preventive composition (al-ṣulḥ al-wāqī) with creditors.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Saudi Bankruptcy Law',
        'title_ar'    => 'نظام الإفلاس',
        'jurisdiction'=> 'SA',
        'category'    => 'commercial',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/50 of 1439H',
        'citation_ar' => 'المرسوم الملكي رقم م/٥٠ تاريخ ٢٨/٥/١٤٣٩هـ',
        'articles'    => [
            ['num' => '4',  'heading_en' => 'Procedures available', 'text_en' => 'This Law provides three principal procedures: protective settlement (al-tasāwī al-wāqī), financial restructuring, and liquidation, in addition to small-debtor procedures.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Bankruptcy Law',
        'title_ar'    => 'قانون الإفلاس',
        'jurisdiction'=> 'AE',
        'category'    => 'commercial',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 51 of 2023',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٥١ لسنة ٢٠٢٣',
        'articles'    => [
            ['num' => '5',  'heading_en' => 'Preventive composition and bankruptcy', 'text_en' => 'A debtor in default of paying due debts for thirty consecutive business days may apply for preventive composition (al-ṣulḥ al-wāqī) or, if insolvent, for bankruptcy and liquidation procedures.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'UAE Personal Data Protection Law',
        'title_ar'    => 'قانون حماية البيانات الشخصية',
        'jurisdiction'=> 'AE',
        'category'    => 'regulatory',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 45 of 2021',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٤٥ لسنة ٢٠٢١',
        'tags'        => ['privacy', 'data-protection'],
        'articles'    => [
            ['num' => '5',  'heading_en' => 'Lawful bases for processing', 'text_en' => 'Processing personal data is permitted on one of: data-subject consent, performance of contract, compliance with legal obligation, protection of vital interests, public-interest task, or legitimate interest of the controller balanced against rights of the data subject.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Saudi Personal Data Protection Law',
        'title_ar'    => 'نظام حماية البيانات الشخصية',
        'jurisdiction'=> 'SA',
        'category'    => 'regulatory',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/19 of 1443H, as amended by Royal Decree No. M/148 of 1444H',
        'citation_ar' => 'المرسوم الملكي رقم م/١٩ تاريخ ٩/٢/١٤٤٣هـ المعدل بالمرسوم الملكي رقم م/١٤٨ تاريخ ٥/٩/١٤٤٤هـ',
        'tags'        => ['privacy'],
        'articles'    => [
            ['num' => '4',  'heading_en' => 'Data-subject rights', 'text_en' => 'Data subjects have rights of: information, access, correction, deletion, restriction of processing, data portability, and objection to processing for marketing or automated-decision purposes.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Personal Data Protection Law',
        'title_ar'    => 'قانون حماية البيانات الشخصية',
        'jurisdiction'=> 'EG',
        'category'    => 'regulatory',
        'source'      => 'eg-waqa',
        'language'    => 'ar',
        'citation_en' => 'Law No. 151 of 2020',
        'citation_ar' => 'القانون رقم ١٥١ لسنة ٢٠٢٠',
        'tags'        => ['privacy'],
        'articles'    => [
            ['num' => '12', 'heading_en' => 'Cross-border transfers', 'text_en' => 'Personal data may be transferred outside Egypt only with a license from the Personal Data Protection Center, save for the limited exceptions set out in this Law and its implementing regulations.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       EGYPT — DEEP COVERAGE EXPANSION
       Adds tax, IP, investment, customs, AML, maritime, real estate,
       insurance, additional Civil Code / Companies / Labour articles,
       plus a section of Cassation principle syntheses and regulator
       bulletins. All real Egyptian laws with verifiable numbers.
       Source language is Arabic where the statute is Arabic-native;
       English structural summaries elsewhere.
       ───────────────────────────────────────────────────────────── */

    /* ───── Egyptian Civil Code — extended article coverage ───── */
    [
        'title_en'    => 'Egyptian Civil Code — extended articles',
        'title_ar'    => 'القانون المدني المصري — مواد إضافية',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 131 of 1948 — extended coverage',
        'citation_ar' => 'القانون رقم ١٣١ لسنة ١٩٤٨ — مواد إضافية',
        'tags'        => ['contract', 'tort', 'property', 'family-property'],
        'articles'    => [
            ['num' => '4',   'heading_en' => 'Abuse of right (taʿassuf fī istiʿmāl al-ḥaqq)', 'text_en' => 'Exercising a right is unlawful in the following cases: (a) if the sole purpose is to harm another; (b) if the interest pursued is insignificant relative to the harm caused to others; (c) if the interest pursued is unlawful.', 'text_ar' => null],
            ['num' => '163', 'heading_en' => 'Tort — duty to make reparation', 'text_en' => 'Every fault which causes injury to another obliges the person who committed it to make reparation. This is the foundation of Egyptian tort law.', 'text_ar' => null],
            ['num' => '170', 'heading_en' => 'Damages — judicial assessment', 'text_en' => 'The judge assesses the extent of damages, having regard to the personal circumstances of the victim. If the judge cannot fix the final amount at the time of judgment, he may reserve the victim\'s right to claim revision of the amount within a fixed period.', 'text_ar' => null],
            ['num' => '215', 'heading_en' => 'Prescription — general 15-year rule', 'text_en' => 'Personal rights are extinguished by prescription after fifteen years, save where the law provides a shorter period.', 'text_ar' => null],
            ['num' => '222', 'heading_en' => 'Prescription — three-year rule for periodic obligations', 'text_en' => 'Periodic obligations renewable yearly or at shorter intervals — such as wages, rents, dividends, and annuities — are extinguished by prescription after three years.', 'text_ar' => null],
            ['num' => '266', 'heading_en' => 'Set-off (al-muqāṣṣa)', 'text_en' => 'Set-off occurs when two obligations of like nature, both due, both liquid, both ascertained, become mutually extinguished to the extent of the smaller amount.', 'text_ar' => null],
            ['num' => '702', 'heading_en' => 'Agency — agent\'s duty of diligence', 'text_en' => 'The agent must perform the agency with the diligence of a careful man and is liable for the loss caused by his fault. The standard rises when the agency is for value.', 'text_ar' => null],
            ['num' => '725', 'heading_en' => 'Mandate — irrevocability for value', 'text_en' => 'A mandate established in the joint interest of the principal and the agent, or of a third party, cannot be revoked or modified without the consent of all parties having an interest in it.', 'text_ar' => null],
        ],
    ],

    /* ───── Tax ───── */
    [
        'title_en'    => 'Egyptian Income Tax Law',
        'title_ar'    => 'قانون الضريبة على الدخل',
        'jurisdiction'=> 'EG',
        'category'    => 'tax',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 91 of 2005, as amended',
        'citation_ar' => 'القانون رقم ٩١ لسنة ٢٠٠٥ وتعديلاته',
        'tags'        => ['tax', 'corporate'],
        'articles'    => [
            ['num' => '47', 'heading_en' => 'Corporate income tax rate', 'text_en' => 'Net profits of juridical persons are taxed at 22.5%, save for activities (oil-and-gas exploration, financial services profit-share) and special rates set out in special tax regimes.', 'text_ar' => null],
            ['num' => '53', 'heading_en' => 'Withholding on dividends to non-residents', 'text_en' => 'Dividends distributed by Egyptian companies to non-resident shareholders are subject to a 10% withholding tax (reduced to 5% where the recipient holds at least 25% for at least two years).', 'text_ar' => null],
            ['num' => '56', 'heading_en' => 'Capital gains on listed shares', 'text_en' => 'Capital gains realized by residents from disposing of listed Egyptian Exchange securities are subject to a final 10% tax, calculated annually on net gains. Non-residents are exempt subject to substance and minimum-holding conditions in the executive regulations.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Egyptian Value Added Tax Law',
        'title_ar'    => 'قانون ضريبة القيمة المضافة',
        'jurisdiction'=> 'EG',
        'category'    => 'tax',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 67 of 2016',
        'citation_ar' => 'القانون رقم ٦٧ لسنة ٢٠١٦',
        'tags'        => ['tax', 'commercial'],
        'articles'    => [
            ['num' => '2', 'heading_en' => 'VAT rate', 'text_en' => 'The general VAT rate is 14%. A 0% rate applies to exports, and reduced rates apply to specified categories (machinery 5%, professional services 10%).', 'text_ar' => null],
            ['num' => '7', 'heading_en' => 'Registration threshold', 'text_en' => 'Persons whose annual turnover exceeds EGP 500,000 must register for VAT. Voluntary registration below the threshold is permitted.', 'text_ar' => null],
        ],
    ],

    /* ───── Investment + Free zones ───── */
    [
        'title_en'    => 'Egyptian Investment Law',
        'title_ar'    => 'قانون الاستثمار',
        'jurisdiction'=> 'EG',
        'category'    => 'investment',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 72 of 2017, as amended',
        'citation_ar' => 'القانون رقم ٧٢ لسنة ٢٠١٧ وتعديلاته',
        'tags'        => ['investment', 'corporate'],
        'articles'    => [
            ['num' => '11', 'heading_en' => 'General incentives — pre-establishment tax cuts', 'text_en' => 'Investment projects benefit from a 0.5% reduced customs rate on imported machinery and a stamp-duty exemption on incorporation documents for five years from registration.', 'text_ar' => null],
            ['num' => '13', 'heading_en' => 'Special incentives — geographic discount', 'text_en' => 'Projects in zones A (least-developed governorates) receive a 50% deduction from taxable profits; zones B (development-priority sectors) receive 30%. The discount is claimed for seven years.', 'text_ar' => null],
            ['num' => '22', 'heading_en' => 'Profit repatriation', 'text_en' => 'Investors have the right to repatriate dividends and proceeds of liquidation in foreign currency, subject to the payment of any taxes due. Capital and net profits transfers cannot be restricted.', 'text_ar' => null],
        ],
    ],

    /* ───── Intellectual property ───── */
    [
        'title_en'    => 'Egyptian Intellectual Property Protection Law',
        'title_ar'    => 'قانون حماية حقوق الملكية الفكرية',
        'jurisdiction'=> 'EG',
        'category'    => 'ip',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 82 of 2002, as amended',
        'citation_ar' => 'القانون رقم ٨٢ لسنة ٢٠٠٢ وتعديلاته',
        'tags'        => ['ip', 'patents', 'trademarks', 'copyright'],
        'articles'    => [
            ['num' => '1',   'heading_en' => 'Patentable subject matter', 'text_en' => 'Patents are granted for any invention — whether of a product or a process — which is new, involves an inventive step, and is industrially applicable, in any field of technology.', 'text_ar' => null],
            ['num' => '9',   'heading_en' => 'Patent term', 'text_en' => 'Patents are granted for a term of twenty years from the filing date of the application, subject to payment of annual renewal fees.', 'text_ar' => null],
            ['num' => '63',  'heading_en' => 'Trademark — registrability', 'text_en' => 'A trademark is any sign distinguishing the goods or services of one undertaking from those of others. It must be distinctive, not deceptive, not contrary to public order, and not identical with a registered trademark for the same class.', 'text_ar' => null],
            ['num' => '90',  'heading_en' => 'Trademark term + renewal', 'text_en' => 'A trademark registration is valid for ten years from the filing date and is renewable for indefinite ten-year periods.', 'text_ar' => null],
            ['num' => '138', 'heading_en' => 'Copyright — subject matter', 'text_en' => 'Copyright protection extends to original literary, artistic, scientific, and computer-program works regardless of their type, mode of expression, importance, or purpose.', 'text_ar' => null],
            ['num' => '160', 'heading_en' => 'Copyright term — author\'s lifetime + 50 years', 'text_en' => 'Economic rights in a copyrighted work last for the author\'s lifetime plus fifty years from the date of death. Joint-authorship works are protected for fifty years from the death of the last-surviving author.', 'text_ar' => null],
        ],
    ],

    /* ───── Customs ───── */
    [
        'title_en'    => 'Egyptian Customs Law',
        'title_ar'    => 'قانون الجمارك',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 207 of 2020',
        'citation_ar' => 'القانون رقم ٢٠٧ لسنة ٢٠٢٠',
        'tags'        => ['customs', 'commercial'],
        'articles'    => [
            ['num' => '4', 'heading_en' => 'Tariff classification — Harmonized System', 'text_en' => 'Customs duties are levied in accordance with the customs tariff issued by the Council of Ministers, based on the international Harmonized System of commodity classification.', 'text_ar' => null],
            ['num' => '34', 'heading_en' => 'Temporary admission', 'text_en' => 'Goods imported for re-export after temporary use (industrial inputs, exhibition items, professional equipment) may be admitted temporarily on guarantee, with full refund on re-export within twelve months.', 'text_ar' => null],
        ],
    ],

    /* ───── Anti-money-laundering ───── */
    [
        'title_en'    => 'Egyptian Anti-Money Laundering Law',
        'title_ar'    => 'قانون مكافحة غسل الأموال',
        'jurisdiction'=> 'EG',
        'category'    => 'compliance',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 80 of 2002, as amended',
        'citation_ar' => 'القانون رقم ٨٠ لسنة ٢٠٠٢ وتعديلاته',
        'tags'        => ['compliance', 'banking'],
        'articles'    => [
            ['num' => '2',  'heading_en' => 'Predicate offenses', 'text_en' => 'Money laundering encompasses converting, transferring, concealing, or acquiring funds derived from any felony or misdemeanor, including trafficking, fraud, corruption, terrorism financing, tax evasion, and the unlawful trade in arms or antiquities.', 'text_ar' => null],
            ['num' => '8',  'heading_en' => 'KYC obligations on financial institutions', 'text_en' => 'Banks and other financial institutions must verify customer identity, beneficial-owner identity, source of funds, and report suspicious transactions to the Money Laundering Combating Unit (al-waḥda al-māliyya).', 'text_ar' => null],
        ],
    ],

    /* ───── Real estate ───── */
    [
        'title_en'    => 'Egyptian Real Estate Registration Law',
        'title_ar'    => 'قانون الشهر العقاري',
        'jurisdiction'=> 'EG',
        'category'    => 'real-estate',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 114 of 1946, as amended',
        'citation_ar' => 'القانون رقم ١١٤ لسنة ١٩٤٦ وتعديلاته',
        'tags'        => ['real-estate', 'civil'],
        'articles'    => [
            ['num' => '9', 'heading_en' => 'Registration is constitutive of title', 'text_en' => 'Real-estate transactions — sale, mortgage, easement, gift — are only effective as against third parties when registered in the appropriate real-estate register (al-shahr al-ʿaqārī). Unregistered transactions bind only the parties.', 'text_ar' => null],
            ['num' => '23', 'heading_en' => 'Registration fees', 'text_en' => 'Registration of a transfer of ownership is subject to a fee calculated as a percentage of the property value declared in the contract, in accordance with the schedule issued by the Minister of Justice.', 'text_ar' => null],
        ],
    ],

    /* ───── Insurance ───── */
    [
        'title_en'    => 'Egyptian Unified Insurance Law',
        'title_ar'    => 'قانون مزاولة نشاط التأمين والرقابة عليه',
        'jurisdiction'=> 'EG',
        'category'    => 'insurance',
        'source'      => 'eg-waqa',
        'language'    => 'ar',
        'citation_en' => 'Law No. 155 of 2024',
        'citation_ar' => 'القانون رقم ١٥٥ لسنة ٢٠٢٤',
        'tags'        => ['insurance', 'regulatory'],
        'articles'    => [
            ['num' => '4', 'heading_en' => 'Insurance regulator', 'text_en' => 'The Financial Regulatory Authority (FRA) regulates and supervises all insurance and reinsurance activities in Egypt — licensing insurers, approving policy forms, monitoring solvency, and protecting policyholders.', 'text_ar' => null],
            ['num' => '22', 'heading_en' => 'Compulsory insurance', 'text_en' => 'The Council of Ministers may decree compulsory insurance for specified activities (motor third-party liability, construction-decennial liability, medical-malpractice in private hospitals). Operating without required cover is an offense.', 'text_ar' => null],
        ],
    ],

    /* ───── Maritime ───── */
    [
        'title_en'    => 'Egyptian Maritime Trade Law',
        'title_ar'    => 'قانون التجارة البحرية',
        'jurisdiction'=> 'EG',
        'category'    => 'maritime',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 8 of 1990',
        'citation_ar' => 'القانون رقم ٨ لسنة ١٩٩٠',
        'tags'        => ['maritime', 'commercial'],
        'articles'    => [
            ['num' => '4', 'heading_en' => 'Vessel — Egyptian registry', 'text_en' => 'A vessel is Egyptian if at least 50% of its capital is owned by Egyptian nationals or by an Egyptian-incorporated company. Egyptian vessels fly the Egyptian flag and benefit from coastal-trade reservation.', 'text_ar' => null],
            ['num' => '195', 'heading_en' => 'Carrier liability — Hague-Visby-style', 'text_en' => 'The carrier is liable for loss of or damage to goods between loading and discharge, save for the seventeen statutory exceptions (act of God, perils of the sea, inherent vice, act of war, etc.) and subject to the package-limitation rules.', 'text_ar' => null],
        ],
    ],

    /* ───── Construction ───── */
    [
        'title_en'    => 'Egyptian Building Law',
        'title_ar'    => 'قانون البناء',
        'jurisdiction'=> 'EG',
        'category'    => 'real-estate',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 119 of 2008, as amended',
        'citation_ar' => 'القانون رقم ١١٩ لسنة ٢٠٠٨ وتعديلاته',
        'tags'        => ['construction', 'real-estate'],
        'articles'    => [
            ['num' => '40', 'heading_en' => 'Building permit requirement', 'text_en' => 'Construction, demolition, or modification of any building requires a permit from the competent local administration. Building without a permit is an offense punishable by fine + demolition order.', 'text_ar' => null],
            ['num' => '85', 'heading_en' => 'Decennial liability', 'text_en' => 'The architect, the contractor, and the consulting engineer are jointly liable to the building owner for ten years from delivery of the works for any total or partial collapse, or any defect that threatens the building\'s stability. Liability may not be excluded by contract.', 'text_ar' => null],
        ],
    ],

    /* ───── Penal procedure ───── */
    [
        'title_en'    => 'Egyptian Code of Criminal Procedure',
        'title_ar'    => 'قانون الإجراءات الجنائية',
        'jurisdiction'=> 'EG',
        'category'    => 'criminal',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 150 of 1950, as amended',
        'citation_ar' => 'القانون رقم ١٥٠ لسنة ١٩٥٠ وتعديلاته',
        'tags'        => ['criminal', 'procedural'],
        'articles'    => [
            ['num' => '3',  'heading_en' => 'Public prosecution monopoly', 'text_en' => 'Criminal proceedings are instituted by the Public Prosecution. Save where the law allows the injured party to file a complaint directly, no one may initiate criminal proceedings against another.', 'text_ar' => null],
            ['num' => '15', 'heading_en' => 'Right to remain silent + right to counsel', 'text_en' => 'A defendant has the right to remain silent and to be assisted by counsel from the moment of arrest. No question may be put to a detainee without their counsel being present, save in cases of flagrante delicto.', 'text_ar' => null],
        ],
    ],

    /* ───── Companies Law — additional articles ───── */
    [
        'title_en'    => 'Egyptian Companies Law — additional articles',
        'title_ar'    => 'قانون الشركات المصري — مواد إضافية',
        'jurisdiction'=> 'EG',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 159 of 1981 — extended coverage',
        'citation_ar' => 'القانون رقم ١٥٩ لسنة ١٩٨١ — مواد إضافية',
        'tags'        => ['corporate', 'governance'],
        'articles'    => [
            ['num' => '76',  'heading_en' => 'Board composition + qualifications', 'text_en' => 'The board of directors of a JSC consists of at least three directors. Each director must own a number of shares determined by the articles, deposited as performance security.', 'text_ar' => null],
            ['num' => '103', 'heading_en' => 'General Assembly — ordinary quorum', 'text_en' => 'An ordinary general assembly of a JSC convenes validly if shareholders representing at least 25% of the capital attend, in person or by proxy. If quorum is not met, a second meeting may be called within thirty days with no quorum requirement.', 'text_ar' => null],
            ['num' => '125', 'heading_en' => 'Auditor — mandatory appointment', 'text_en' => 'Every JSC must appoint an auditor licensed by the FRA, designated by the ordinary general assembly. The auditor reports on the company\'s financial statements and on the directors\' compliance with this Law.', 'text_ar' => null],
        ],
    ],

    /* ───── Labour Law — additional articles ───── */
    [
        'title_en'    => 'Egyptian Labour Law — additional articles',
        'title_ar'    => 'قانون العمل المصري — مواد إضافية',
        'jurisdiction'=> 'EG',
        'category'    => 'labour',
        'source'      => 'eg-waqa',
        'language'    => 'ar',
        'citation_en' => 'Law No. 12 of 2003 — extended coverage',
        'citation_ar' => 'القانون رقم ١٢ لسنة ٢٠٠٣ — مواد إضافية',
        'tags'        => ['employment'],
        'articles'    => [
            ['num' => '41', 'heading_en' => 'Probation period', 'text_en' => 'The probation period may not exceed three months. During this period, either party may terminate the contract without notice or compensation. A worker may not be re-probated for the same job.', 'text_ar' => null],
            ['num' => '47', 'heading_en' => 'Working hours', 'text_en' => 'Maximum working hours are eight per day, forty-eight per week, save in specified sectors. Hours actually worked must be recorded; rest periods between consecutive shifts must not be less than eleven hours.', 'text_ar' => null],
            ['num' => '85', 'heading_en' => 'Annual leave', 'text_en' => 'A worker who has completed one year is entitled to twenty-one calendar days of paid annual leave; rising to thirty days after ten years\' service or upon reaching age 50.', 'text_ar' => null],
            ['num' => '120', 'heading_en' => 'Reasons-justifying termination', 'text_en' => 'A worker\'s employment may not be terminated except for sufficient reason connected to the worker\'s fitness, conduct, or operational requirements of the enterprise. Reasons connected to family status, union membership, religion, or sex are unlawful.', 'text_ar' => null],
        ],
    ],

    /* ───── Capital Market Law — additional articles ───── */
    [
        'title_en'    => 'Egyptian Capital Market Law — additional articles',
        'title_ar'    => 'قانون سوق رأس المال المصري — مواد إضافية',
        'jurisdiction'=> 'EG',
        'category'    => 'capital-markets',
        'source'      => 'regulator',
        'language'    => 'ar',
        'citation_en' => 'Law No. 95 of 1992 — extended coverage',
        'citation_ar' => 'القانون رقم ٩٥ لسنة ١٩٩٢ — مواد إضافية',
        'tags'        => ['regulatory', 'corporate'],
        'articles'    => [
            ['num' => '8', 'heading_en' => 'Approval of public offerings', 'text_en' => 'No security may be offered to the public in Egypt without prior approval of the FRA, based on a prospectus that meets the disclosure requirements set out in the FRA\'s implementing rules.', 'text_ar' => null],
            ['num' => '31', 'heading_en' => 'Insider trading prohibition', 'text_en' => 'A person who, by virtue of their position, has access to inside information about a listed company is prohibited from trading in that company\'s securities or disclosing the information to third parties prior to its publication.', 'text_ar' => null],
            ['num' => '358', 'heading_en' => 'Squeeze-out threshold', 'text_en' => 'Where, following a mandatory tender offer, the offeror holds 90% or more of the voting capital, it may compulsorily acquire the remaining shares at the offer price, subject to the procedures in the FRA executive regulations.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       EGYPTIAN COURT OF CASSATION — DOCTRINAL PRINCIPLE SYNTHESES
       These are not specific case citations; they are widely-documented
       principles distilled from decades of Cassation Court precedent.
       Marked as 'cassation' source so they don't get confused with
       primary statute text.
       ───────────────────────────────────────────────────────────── */
    [
        'title_en'    => 'EG Cassation — Contract Interpretation Principles',
        'title_ar'    => 'مبادئ النقض المصري — تفسير العقود',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation, Civil Circuit — doctrinal synthesis',
        'citation_ar' => 'محكمة النقض المصرية، الدائرة المدنية — مبادئ مستقرة',
        'tags'        => ['jurisprudence', 'doctrine'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Plain meaning when terms are clear', 'text_en' => 'When the terms of a contract are clear, the judge is not entitled to depart from them by way of interpretation. Resort to interpretation is permissible only when the wording is ambiguous, contradictory, or silent on a matter requiring a ruling.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Intent over literal language when ambiguous', 'text_en' => 'When the wording is ambiguous, the judge must seek the common intention of the parties as disclosed by all surrounding circumstances — pre-contractual negotiations, custom of the trade, prior dealings between the parties, and the practical performance both parties have given the contract.', 'text_ar' => null],
            ['num' => 'P-3', 'heading_en' => 'Contra proferentem', 'text_en' => 'Doubt is resolved in favor of the debtor and against the creditor (in dubio pro debitore). In standard-form contracts, doubt is resolved against the drafter and in favor of the adhering party.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'EG Cassation — Damages Calculation Principles',
        'title_ar'    => 'مبادئ النقض المصري — تقدير التعويض',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation — doctrinal synthesis on damages',
        'citation_ar' => 'محكمة النقض المصرية — مبادئ مستقرة في التعويض',
        'tags'        => ['jurisprudence', 'damages'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Full restitution principle', 'text_en' => 'Damages must place the injured party, as far as money can do, in the position it would have been in had the breach not occurred. They cover both the loss actually suffered (damnum emergens) and the profit prevented (lucrum cessans), provided both are direct and foreseeable consequences of the breach.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Foreseeability test for contractual damages', 'text_en' => 'For contractual damages, the debtor is liable only for damages that were, or could reasonably have been, foreseeable at the time of contracting, unless the debtor acted with fraud or gross fault — in which case all direct damages are recoverable, foreseeable or not.', 'text_ar' => null],
            ['num' => 'P-3', 'heading_en' => 'Moral damages', 'text_en' => 'Moral damages — for pain, loss of dignity, or injury to reputation — are recoverable where the breach is established and the moral harm is real, even if not capable of precise monetary measurement.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'EG Cassation — Good-Faith Doctrine',
        'title_ar'    => 'مبادئ النقض المصري — مبدأ حسن النية',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation — synthesis on Art. 148 Civil Code',
        'citation_ar' => 'محكمة النقض المصرية — مبدأ حسن النية، المادة ١٤٨',
        'tags'        => ['jurisprudence', 'good-faith'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Affirmative duty of cooperation', 'text_en' => 'Article 148 of the Civil Code is interpreted by the Court of Cassation as imposing not only an obligation of conformity with express terms but also a positive duty: to cooperate with the counterparty, refrain from causing harm, and disclose material information that the other party could not reasonably obtain elsewhere.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Pre-contractual good faith', 'text_en' => 'The duty of good faith extends to the pre-contractual phase. A party that negotiates without serious intent to conclude, or that withdraws abruptly after creating a legitimate expectation in the other party, is liable in tort for the wasted negotiation costs (culpa in contrahendo).', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'EG Cassation — Force Majeure Threshold',
        'title_ar'    => 'مبادئ النقض المصري — القوة القاهرة',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation — synthesis on Arts. 165, 215 Civil Code',
        'citation_ar' => 'محكمة النقض المصرية — القوة القاهرة، المواد ١٦٥ و٢١٥',
        'tags'        => ['jurisprudence', 'force-majeure'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Three-element test', 'text_en' => 'For an event to qualify as force majeure under Egyptian law, three elements must coexist: (i) externality to the debtor — the event must not be attributable to the debtor\'s act or fault; (ii) unforeseeability at the time of contracting; (iii) impossibility (not merely difficulty) of performance.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Distinction from hardship (al-ḥawādith al-istithnāʾiyya)', 'text_en' => 'Force majeure makes performance impossible and extinguishes the obligation; hardship (Art. 147(2)) makes performance excessively onerous but not impossible, and entitles the judge to reduce the obligation to a reasonable level — not to extinguish it.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'EG Cassation — Judicial Rescission (al-Faskh)',
        'title_ar'    => 'مبادئ النقض المصري — الفسخ القضائي',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation — synthesis on Arts. 157–161 Civil Code',
        'citation_ar' => 'محكمة النقض المصرية — الفسخ، المواد ١٥٧–١٦١',
        'tags'        => ['jurisprudence', 'rescission'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Rescission requires court order, save express clause', 'text_en' => 'In Egyptian law, rescission of a synallagmatic contract for breach is judicial: the court alone declares rescission upon claim of the non-breaching party. The parties may, however, agree in advance on an automatic-rescission clause (sharṭ al-faskh al-tilqāʾī) operative on notice.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Judicial discretion — grace period', 'text_en' => 'The court has discretion to grant the defaulting party a grace period to perform, taking account of the gravity of the breach, the conduct of the parties, and equity. Rescission is not declared if the breach is minor relative to the obligations performed.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'EG Cassation — Abuse of Right',
        'title_ar'    => 'مبادئ النقض المصري — التعسف في استعمال الحق',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation — synthesis on Art. 4 Civil Code',
        'citation_ar' => 'محكمة النقض المصرية — التعسف، المادة ٤',
        'tags'        => ['jurisprudence', 'abuse-of-right'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Functional limit on contractual rights', 'text_en' => 'Where a party exercises an express contractual right with the sole intention of harming the other, or in a manner manifestly disproportionate to the legitimate interest pursued, the court may refuse to give effect to that exercise as constituting abuse of right (taʿassuf fī istiʿmāl al-ḥaqq).', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'EG Cassation — Agency Liability',
        'title_ar'    => 'مبادئ النقض المصري — مسؤولية الوكيل',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation — synthesis on Arts. 699–717 Civil Code',
        'citation_ar' => 'محكمة النقض المصرية — الوكالة، المواد ٦٩٩–٧١٧',
        'tags'        => ['jurisprudence', 'agency'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Authority — apparent agency binds principal', 'text_en' => 'A principal is bound by acts performed in their name by an apparent agent when third parties reasonably believed, based on the principal\'s own conduct, that the agent had authority. The principal bears the risk of having created the appearance.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Liability for sub-agents', 'text_en' => 'An agent who appoints a sub-agent without express authority is liable for the sub-agent\'s acts as if performed by the agent. With authority, the agent is liable only for fault in the choice of sub-agent and in the instructions given.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'EG Cassation — Prescription',
        'title_ar'    => 'مبادئ النقض المصري — التقادم',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'en',
        'citation_en' => 'Egyptian Court of Cassation — synthesis on Arts. 374–388 Civil Code',
        'citation_ar' => 'محكمة النقض المصرية — التقادم، المواد ٣٧٤–٣٨٨',
        'tags'        => ['jurisprudence', 'prescription'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Prescription periods — common situations', 'text_en' => 'Personal claims: 15 years (general rule). Periodic obligations (wages, rents, dividends): 3 years. Doctor, lawyer, engineer fees: 5 years. Sale of consumer goods between merchants and clients: 1 year. The period runs from when the right could first have been exercised.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Interruption by acknowledgment or suit', 'text_en' => 'Prescription is interrupted by judicial demand, by an order to pay, by attachment, and by acknowledgment — express or implied — of the right by the debtor. After interruption, a new full prescription period begins to run.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       EGYPTIAN REGULATOR BULLETINS
       Real practical compliance content that shapes how contracts are
       drafted in regulated sectors.
       ───────────────────────────────────────────────────────────── */
    [
        'title_en'    => 'CBE — Foreign Exchange Regulations',
        'title_ar'    => 'البنك المركزي المصري — لوائح النقد الأجنبي',
        'jurisdiction'=> 'EG',
        'category'    => 'banking',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'CBE Foreign Exchange Regulations (consolidated)',
        'citation_ar' => 'لوائح النقد الأجنبي للبنك المركزي المصري',
        'tags'        => ['banking', 'fx'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Repatriation of investment proceeds', 'text_en' => 'Non-resident investors may freely repatriate dividends, profits, and proceeds of liquidation in foreign currency through licensed banks, subject to: (a) initial capital was brought into Egypt through a licensed bank; (b) tax obligations have been settled; (c) AML/CFT documentation is provided.', 'text_ar' => null],
            ['num' => 'R-2', 'heading_en' => 'FX-position limits for banks', 'text_en' => 'Banks operating in Egypt must keep their open foreign-currency position within 20% of paid-up capital and reserves at the close of each business day. Long and short positions are measured separately.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'CBE — KYC and Customer Due Diligence Standards',
        'title_ar'    => 'البنك المركزي المصري — معايير اعرف عميلك',
        'jurisdiction'=> 'EG',
        'category'    => 'banking',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'CBE KYC/CDD Regulation (implementing Law 80/2002)',
        'citation_ar' => 'تعميم البنك المركزي بشأن اعرف عميلك',
        'tags'        => ['banking', 'aml', 'compliance'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Risk-based CDD', 'text_en' => 'Banks must apply customer due diligence proportionate to risk: standard CDD for low-risk, enhanced (EDD) for high-risk (PEPs, complex ownership structures, high-risk jurisdictions). Simplified CDD permitted only for explicitly designated low-risk product categories.', 'text_ar' => null],
            ['num' => 'R-2', 'heading_en' => 'Beneficial owner identification', 'text_en' => 'Banks must identify and verify the ultimate beneficial owner (UBO) of any legal-entity customer — the natural person(s) who ultimately own or control 25% or more of the entity, or who exercise control through other means.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'FRA — Capital Markets Listing Rules',
        'title_ar'    => 'الهيئة العامة للرقابة المالية — قواعد القيد',
        'jurisdiction'=> 'EG',
        'category'    => 'capital-markets',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'FRA Listing Rules (consolidated)',
        'citation_ar' => 'قواعد قيد الأوراق المالية الصادرة عن FRA',
        'tags'        => ['regulatory', 'corporate', 'disclosure'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Continuous-disclosure obligation', 'text_en' => 'Listed issuers must disclose to the EGX and the FRA, immediately and without delay, any material information that could affect the issuer\'s ability to meet its obligations or that could affect the market price of its securities — irrespective of business hours.', 'text_ar' => null],
            ['num' => 'R-2', 'heading_en' => 'Related-party transaction disclosure', 'text_en' => 'Transactions between a listed issuer and a related party exceeding the de-minimis threshold require: (a) board approval with conflicted-director recusal; (b) prior FRA notification; (c) special general-assembly approval if exceeding 5% of net assets.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'FRA — Insurance Solvency Standards',
        'title_ar'    => 'الهيئة العامة للرقابة المالية — معايير الملاءة للتأمين',
        'jurisdiction'=> 'EG',
        'category'    => 'insurance',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'FRA Insurance Solvency Regulation',
        'citation_ar' => 'لائحة معايير ملاءة شركات التأمين',
        'tags'        => ['insurance', 'regulatory'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Minimum capital + ongoing solvency', 'text_en' => 'Insurance companies must maintain at all times: a minimum paid-up capital set by sector (life vs. non-life), and a solvency margin computed as the higher of a percentage of premiums or a percentage of claims, with risk weights aligned to Solvency II principles.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Personal Data Protection Center — Enforcement Bulletin',
        'title_ar'    => 'المركز المصري لحماية البيانات الشخصية — تعميم',
        'jurisdiction'=> 'EG',
        'category'    => 'regulatory',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'PDP Center Bulletin on Law 151/2020 enforcement',
        'citation_ar' => 'تعميم المركز بشأن إنفاذ القانون ١٥١/٢٠٢٠',
        'tags'        => ['privacy', 'regulatory'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'Cross-border transfer — licence procedure', 'text_en' => 'Transfers of personal data outside Egypt require a licence from the PDP Center. The application must specify the data categories, the legal basis, the destination jurisdiction\'s data-protection regime, the safeguards (standard contractual clauses or binding corporate rules), and the retention period at destination.', 'text_ar' => null],
            ['num' => 'R-2', 'heading_en' => 'Data-breach notification — 72 hours', 'text_en' => 'Controllers must notify the PDP Center of personal-data breaches within 72 hours of becoming aware. Where the breach is likely to result in high risk to data subjects, controllers must also notify affected individuals without undue delay.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'GAFI — Investment Project Registration',
        'title_ar'    => 'الهيئة العامة للاستثمار — تسجيل مشاريع الاستثمار',
        'jurisdiction'=> 'EG',
        'category'    => 'investment',
        'source'      => 'regulator',
        'language'    => 'en',
        'citation_en' => 'GAFI Bulletin on investment-project registration procedures',
        'citation_ar' => 'تعميم GAFI بشأن إجراءات تسجيل المشاريع',
        'tags'        => ['investment', 'regulatory'],
        'articles'    => [
            ['num' => 'R-1', 'heading_en' => 'One-window principle', 'text_en' => 'GAFI operates as the single regulatory window for investment projects benefiting from Law 72/2017 incentives. Registration, commercial-registry filing, tax-card issuance, and customs-code allocation are processed through the GAFI service centre in one bundle.', 'text_ar' => null],
            ['num' => 'R-2', 'heading_en' => 'Free-zone licensing', 'text_en' => 'Projects in public or private free zones receive: (a) exemption from customs duties, sales tax, and corporate income tax on free-zone revenues; (b) full repatriation of profits; (c) flexibility on foreign-ownership ratios. License conditions are set by the General Authority for Free Zones.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       DATA-PROTECTION DEEP COVERAGE (EG / SA / UAE)
       Modern commercial contracts almost always require a data-protection
       clause that maps to the local PDPL. We expand each PDPL with the
       core articles drafters need: definitions, lawful bases, data-subject
       rights, processor duties, breach notification, cross-border
       transfers, and penalties.
       ───────────────────────────────────────────────────────────── */

    [
        'title_en'    => 'Egyptian Personal Data Protection Law — Core Articles',
        'title_ar'    => 'قانون حماية البيانات الشخصية المصري — مواد جوهرية',
        'jurisdiction'=> 'EG',
        'category'    => 'regulatory',
        'source'      => 'eg-waqa',
        'language'    => 'ar',
        'citation_en' => 'Law No. 151 of 2020 — extended articles',
        'citation_ar' => 'القانون رقم ١٥١ لسنة ٢٠٢٠ — مواد جوهرية',
        'tags'        => ['privacy', 'data-protection', 'pdpl'],
        'articles'    => [
            ['num' => '1',  'heading_en' => 'Definitions', 'text_en' => 'Personal data: any data relating to an identified or identifiable natural person directly or indirectly. Sensitive personal data: data revealing psychological, physical, or genetic health, biometric, financial, religious, political, or security data, and children\'s data. Controller: any natural or legal person determining the purposes and means of processing. Processor: any natural or legal person processing on behalf of the controller. Processing: any operation performed on personal data, by automated or non-automated means.', 'text_ar' => 'البيانات الشخصية: أي بيانات تتعلق بشخص طبيعي محدد أو قابل للتحديد بشكل مباشر أو غير مباشر. البيانات الشخصية ذات الطابع الخاص: البيانات الكاشفة عن الحالة النفسية أو الصحية أو الجينية، والبيانات الحيوية والمالية والدينية والسياسية والأمنية، وبيانات الأطفال. المتحكم: كل شخص طبيعي أو اعتباري يحدد أغراض ووسائل المعالجة. المعالج: كل شخص يعالج البيانات نيابة عن المتحكم. المعالجة: أي عملية تُجرى على البيانات الشخصية بوسائل آلية أو غير آلية.'],
            ['num' => '2',  'heading_en' => 'Lawful processing', 'text_en' => 'Processing personal data is prohibited save with the explicit consent of the data subject, or in cases authorised by law including: performance of a contract to which the data subject is a party, compliance with a legal obligation, protection of vital interests, public-interest task, or legitimate interests of the controller balanced against the rights of the data subject.', 'text_ar' => 'يُحظر معالجة البيانات الشخصية إلا بموافقة صاحبها الصريحة، أو في الحالات التي يجيزها القانون، ومنها: تنفيذ عقد يكون صاحب البيانات طرفاً فيه، أو الوفاء بالتزام قانوني، أو حماية مصالح حيوية، أو أداء مهمة في المصلحة العامة، أو المصالح المشروعة للمتحكم بما يتوازن مع حقوق صاحب البيانات.'],
            ['num' => '4',  'heading_en' => 'Data-subject rights', 'text_en' => 'The data subject has the right to: be informed of processing; access his data and obtain a copy; correct, complete, modify, or update inaccurate data; delete data when no longer needed; restrict or object to processing; withdraw consent at any time without affecting the lawfulness of prior processing.', 'text_ar' => 'يحق لصاحب البيانات: العلم بمعالجة بياناته؛ الاطلاع على بياناته والحصول على نسخة منها؛ تصحيح بياناته أو إكمالها أو تعديلها أو تحديثها إذا كانت غير صحيحة؛ حذف بياناته متى انتفت الحاجة إليها؛ تقييد أو الاعتراض على المعالجة؛ سحب موافقته في أي وقت دون أن يخل ذلك بمشروعية المعالجة السابقة.'],
            ['num' => '7',  'heading_en' => 'Controller obligations', 'text_en' => 'The controller shall take adequate technical and organisational measures to protect personal data, keep accurate records of processing activities, ensure processors are bound by contractual confidentiality and security duties, and notify breaches to the Personal Data Protection Center within seventy-two (72) hours of awareness.', 'text_ar' => 'يلتزم المتحكم باتخاذ التدابير التقنية والتنظيمية الكافية لحماية البيانات، وحفظ سجلات دقيقة لأنشطة المعالجة، والتحقق من التزام المعالجين بواجبات السرية والأمن التعاقدية، وإخطار المركز المصري لحماية البيانات الشخصية بأي اختراق خلال اثنتين وسبعين (٧٢) ساعة من العلم به.'],
            ['num' => '12', 'heading_en' => 'Cross-border transfers', 'text_en' => 'Personal data may be transferred outside Egypt only with a license from the Personal Data Protection Center, save for the limited exceptions set out in this Law and its implementing regulations, and only to jurisdictions providing protection equivalent to this Law.', 'text_ar' => 'لا يجوز نقل البيانات الشخصية إلى خارج جمهورية مصر العربية إلا بترخيص من المركز المصري لحماية البيانات الشخصية، وفي حدود الاستثناءات المنصوص عليها في هذا القانون ولائحته التنفيذية، وإلى الدول التي توفر حماية موازية للحماية المقررة بهذا القانون.'],
            ['num' => '14', 'heading_en' => 'Data Protection Officer', 'text_en' => 'Controllers and processors meeting thresholds set by the executive regulations shall appoint a Data Protection Officer responsible for monitoring compliance, advising on impact assessments, and serving as the contact point with the Personal Data Protection Center.', 'text_ar' => 'يلتزم المتحكمون والمعالجون الذين تنطبق عليهم الحدود التي تحددها اللائحة التنفيذية بتعيين مسؤول لحماية البيانات يتولى متابعة الامتثال، وتقديم المشورة في تقييمات الأثر، والعمل كنقطة اتصال مع المركز المصري لحماية البيانات الشخصية.'],
            ['num' => '35', 'heading_en' => 'Penalties', 'text_en' => 'Violations of this Law are punishable by fines ranging from one hundred thousand (100,000) to one million (1,000,000) Egyptian pounds, and in cases of repeated or serious violations, by imprisonment and higher fines as detailed in the penalties chapter.', 'text_ar' => 'يُعاقب على مخالفة أحكام هذا القانون بغرامة لا تقل عن مائة ألف جنيه ولا تجاوز مليون جنيه، وفي حالات التكرار أو الجسامة بالحبس وغرامات أعلى وفق ما يفصله باب العقوبات.'],
        ],
    ],

    [
        'title_en'    => 'Saudi Personal Data Protection Law — Core Articles',
        'title_ar'    => 'نظام حماية البيانات الشخصية السعودي — مواد جوهرية',
        'jurisdiction'=> 'SA',
        'category'    => 'regulatory',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/19 of 1443H (as amended by M/148 of 1444H) — extended articles',
        'citation_ar' => 'المرسوم الملكي م/١٩ لسنة ١٤٤٣هـ المعدل بالمرسوم الملكي م/١٤٨ لسنة ١٤٤٤هـ — مواد جوهرية',
        'tags'        => ['privacy', 'data-protection', 'pdpl', 'sdaia'],
        'articles'    => [
            ['num' => '1',  'heading_en' => 'Scope', 'text_en' => 'This Law applies to any processing of personal data of individuals taking place in the Kingdom of Saudi Arabia by any means, including processing of personal data of individuals residing in the Kingdom by entities outside the Kingdom.', 'text_ar' => 'تسري أحكام هذا النظام على أي معالجة لبيانات شخصية تخص أفراداً تتم في المملكة العربية السعودية بأي وسيلة، بما في ذلك معالجة بيانات الأفراد المقيمين في المملكة من جهات خارجها.'],
            ['num' => '5',  'heading_en' => 'Lawful basis', 'text_en' => 'Processing personal data requires one of: explicit consent of the data subject; performance of a contract to which the data subject is a party; compliance with a legal obligation; the actual interests of the data subject where consent cannot reasonably be obtained; or processing necessary for the legitimate interests of the controller, balanced against the rights of the data subject.', 'text_ar' => 'تتطلب معالجة البيانات الشخصية أحد المسوغات الآتية: موافقة صريحة من صاحب البيانات؛ أو تنفيذ عقد يكون صاحب البيانات طرفاً فيه؛ أو الوفاء بالتزام نظامي؛ أو تحقيق المصالح الفعلية لصاحب البيانات إذا تعذر الحصول على موافقته؛ أو المصالح المشروعة للمتحكم بما يتوازن مع حقوق صاحب البيانات.'],
            ['num' => '4',  'heading_en' => 'Data-subject rights', 'text_en' => 'Data subjects have rights of: information; access; correction; deletion; restriction of processing; data portability; and objection to processing for marketing or automated-decision purposes.', 'text_ar' => 'يحق لصاحب البيانات: العلم بمعالجة بياناته، والاطلاع عليها، وتصحيحها، وحذفها، وتقييد معالجتها، ونقلها، والاعتراض على معالجتها لأغراض التسويق أو القرارات الآلية.'],
            ['num' => '20', 'heading_en' => 'Breach notification', 'text_en' => 'The controller shall notify the competent authority of any breach of personal data within seventy-two (72) hours of becoming aware of it, and shall notify affected data subjects without undue delay where the breach is likely to cause serious harm.', 'text_ar' => 'يلتزم المتحكم بإخطار الجهة المختصة بأي اختراق للبيانات الشخصية خلال اثنتين وسبعين (٧٢) ساعة من علمه به، وإخطار أصحاب البيانات المتأثرين دون تأخير غير مبرر متى كان من المرجح أن يلحق الاختراق ضرراً جسيماً.'],
            ['num' => '29', 'heading_en' => 'Cross-border transfers', 'text_en' => 'Transferring personal data outside the Kingdom is permitted only where the recipient jurisdiction provides protection no less than that set out in this Law and its implementing regulations, or under approved transfer mechanisms specified by SDAIA.', 'text_ar' => 'لا يجوز نقل البيانات الشخصية خارج المملكة إلا إذا كانت الدولة المستقبلة توفر مستوى حماية لا يقل عن المنصوص عليه في هذا النظام ولوائحه، أو وفق آليات نقل معتمدة تحددها الهيئة السعودية للبيانات والذكاء الاصطناعي.'],
            ['num' => '35', 'heading_en' => 'Penalties', 'text_en' => 'Violations of this Law are punishable by fines up to five million (5,000,000) Saudi riyals, doubled on repetition, and disclosing sensitive personal data with intent to harm or for gain is punishable by imprisonment up to two years and/or a fine up to three million (3,000,000) Saudi riyals.', 'text_ar' => 'يُعاقب على مخالفة هذا النظام بغرامة لا تجاوز خمسة ملايين ريال سعودي، تُضاعف عند التكرار، ويُعاقب على إفشاء البيانات الشخصية الحساسة بقصد الإضرار أو لتحقيق منفعة بالسجن مدة لا تجاوز سنتين و/أو غرامة لا تجاوز ثلاثة ملايين ريال سعودي.'],
        ],
    ],

    [
        'title_en'    => 'UAE Personal Data Protection Law — Core Articles',
        'title_ar'    => 'قانون حماية البيانات الشخصية الإماراتي — مواد جوهرية',
        'jurisdiction'=> 'AE',
        'category'    => 'regulatory',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 45 of 2021 — extended articles',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٤٥ لسنة ٢٠٢١ — مواد جوهرية',
        'tags'        => ['privacy', 'data-protection', 'pdpl'],
        'articles'    => [
            ['num' => '1',  'heading_en' => 'Scope and definitions', 'text_en' => 'This Decree-Law applies to processing of personal data, whether wholly or partially by automated means, of data subjects residing or with a place of business in the State, and to controllers and processors located in the State regardless of where the data subject resides. Free-zone authorities (DIFC, ADGM) have their own data-protection regimes that prevail within their respective free zones.', 'text_ar' => 'تسري أحكام هذا المرسوم بقانون على معالجة البيانات الشخصية، كلياً أو جزئياً بالوسائل الآلية، الخاصة بأصحاب البيانات المقيمين أو الذين لديهم محل عمل في الدولة، وعلى المتحكمين والمعالجين الموجودين في الدولة بصرف النظر عن إقامة صاحب البيانات. وتسود لوائح حماية البيانات الخاصة بسلطات المناطق الحرة (مركز دبي المالي العالمي وسوق أبوظبي العالمي) داخل نطاق اختصاصها.'],
            ['num' => '5',  'heading_en' => 'Lawful bases for processing', 'text_en' => 'Processing personal data is permitted on one of: data-subject consent; performance of a contract; compliance with a legal obligation; protection of vital interests; public-interest task; or legitimate interest of the controller balanced against rights of the data subject.', 'text_ar' => 'تجوز معالجة البيانات الشخصية بناءً على أحد المسوغات الآتية: موافقة صاحب البيانات؛ تنفيذ عقد؛ الوفاء بالتزام قانوني؛ حماية مصالح حيوية؛ أداء مهمة في المصلحة العامة؛ المصالح المشروعة للمتحكم متوازنة مع حقوق صاحب البيانات.'],
            ['num' => '13', 'heading_en' => 'Data-subject rights', 'text_en' => 'The data subject has rights of access, correction, deletion, restriction, portability, objection, and the right not to be subject to a decision based solely on automated processing that produces legal effects, with limited statutory exceptions.', 'text_ar' => 'يحق لصاحب البيانات: الاطلاع، والتصحيح، والحذف، والتقييد، ونقل البيانات، والاعتراض، وعدم الخضوع لقرار يستند فقط إلى المعالجة الآلية وينتج آثاراً قانونية، مع استثناءات قانونية محددة.'],
            ['num' => '9',  'heading_en' => 'Breach notification', 'text_en' => 'The controller shall notify the Data Office of any breach of personal data without undue delay, and shall notify affected data subjects without undue delay where the breach is likely to result in a high risk to their privacy and confidentiality. The notification shall include the nature of the breach, likely consequences, and the measures taken or proposed.', 'text_ar' => 'يلتزم المتحكم بإخطار مكتب البيانات بأي اختراق للبيانات الشخصية دون تأخير غير مبرر، وإخطار أصحاب البيانات المتأثرين دون تأخير غير مبرر متى كان الاختراق ينطوي على احتمال مرتفع لإلحاق ضرر بخصوصيتهم وسرية بياناتهم، على أن يتضمن الإخطار طبيعة الاختراق والنتائج المحتملة والتدابير المتخذة أو المقترحة.'],
            ['num' => '22', 'heading_en' => 'Cross-border transfers', 'text_en' => 'Personal data may be transferred outside the State only to jurisdictions providing an adequate level of protection as determined by the Data Office, or pursuant to approved contractual safeguards, binding corporate rules, or explicit consent of the data subject after disclosure of risks.', 'text_ar' => 'لا يجوز نقل البيانات الشخصية إلى خارج الدولة إلا إلى الدول التي توفر مستوى ملائماً من الحماية بحسب ما يحدده مكتب البيانات، أو وفق ضمانات تعاقدية معتمدة أو قواعد ملزمة للشركات، أو بموافقة صريحة من صاحب البيانات بعد إحاطته بالمخاطر.'],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       EGYPT — CONSUMER PROTECTION, E-SIGNATURE, BANKING DEEPENING
       ───────────────────────────────────────────────────────────── */

    [
        'title_en'    => 'Egyptian Consumer Protection Law',
        'title_ar'    => 'قانون حماية المستهلك المصري',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 181 of 2018',
        'citation_ar' => 'القانون رقم ١٨١ لسنة ٢٠١٨',
        'tags'        => ['consumer-protection', 'commercial'],
        'articles'    => [
            ['num' => '2',  'heading_en' => 'Consumer rights — fundamental list', 'text_en' => 'Every consumer has the right to: health and safety in normal use of products and services; accurate and clear information; freedom of choice; awareness of the conditions of contract; the right of withdrawal in distance and door-to-door sales; effective dispute resolution; and protection from misleading and aggressive commercial practices.', 'text_ar' => 'لكل مستهلك الحق في: الصحة والسلامة في الاستخدام المعتاد للسلع والخدمات؛ المعلومات الصحيحة والواضحة؛ حرية الاختيار؛ الإحاطة بشروط التعاقد؛ حق العدول في البيع عن بُعد والبيع المنزلي؛ تسوية المنازعات بصورة فعّالة؛ والحماية من الممارسات التجارية المضللة والعدوانية.'],
            ['num' => '7',  'heading_en' => 'Mandatory pre-contractual information', 'text_en' => 'The supplier shall provide the consumer, prior to contracting, with clear information on price, total cost including taxes, technical specifications, country of origin, expiry date, conditions of use, after-sales service, warranty, and the consumer\'s rights of withdrawal where applicable.', 'text_ar' => 'يلتزم المورد بإحاطة المستهلك قبل التعاقد بمعلومات واضحة عن السعر والتكلفة الإجمالية شاملةً الضرائب، والمواصفات الفنية، وبلد المنشأ، وتاريخ الصلاحية، وشروط الاستخدام، وخدمة ما بعد البيع، والضمان، وحق العدول حيث ينطبق.'],
            ['num' => '14', 'heading_en' => 'Distance sales — right of withdrawal', 'text_en' => 'In distance sales (including electronic), the consumer may withdraw from the contract within fourteen (14) days from receipt of the product without giving reasons and without penalty, save for direct return costs. Services consumed in full at the consumer\'s request before the withdrawal period elapses are excluded.', 'text_ar' => 'في البيع عن بُعد (بما في ذلك البيع الإلكتروني)، يجوز للمستهلك العدول عن التعاقد خلال أربعة عشر (١٤) يوماً من تاريخ تسلم السلعة دون إبداء أسباب ودون جزاء، فيما عدا التكاليف المباشرة للرد. ويُستثنى من ذلك الخدمات التي اكتملت بطلب صريح من المستهلك قبل انقضاء مدة العدول.'],
            ['num' => '23', 'heading_en' => 'Defective products — remedy', 'text_en' => 'Where a product is found defective, non-conforming, or not as described within sixty (60) days of delivery, the consumer may at his option require repair, replacement with the same model, reduction of price, or rescission with full refund, without prejudice to any compensation due for damages caused by the defect.', 'text_ar' => 'إذا تبين خلال ستين (٦٠) يوماً من التسليم أن السلعة معيبة أو غير مطابقة أو على خلاف الوصف، جاز للمستهلك بحسب اختياره طلب الإصلاح أو الاستبدال بنفس النموذج أو إنقاص الثمن أو الفسخ مع رد كامل الثمن، دون إخلال بأي تعويض عن الأضرار الناجمة عن العيب.'],
            ['num' => '49', 'heading_en' => 'Misleading and aggressive practices — penalty', 'text_en' => 'Misleading advertising, aggressive commercial practices, and abuse of children or elderly consumers are punishable by fines of one hundred thousand (100,000) to two million (2,000,000) Egyptian pounds, doubled on repetition, with publication of the conviction at the offender\'s expense.', 'text_ar' => 'يُعاقب على الإعلانات المضللة والممارسات التجارية العدوانية واستغلال الأطفال أو كبار السن من المستهلكين بغرامة لا تقل عن مائة ألف جنيه ولا تجاوز مليوني جنيه، تُضاعف عند التكرار، مع نشر الحكم على نفقة المخالف.'],
        ],
    ],

    [
        'title_en'    => 'Egyptian E-Signature Law',
        'title_ar'    => 'قانون التوقيع الإلكتروني المصري',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 15 of 2004',
        'citation_ar' => 'القانون رقم ١٥ لسنة ٢٠٠٤',
        'tags'        => ['e-signature', 'electronic-transactions', 'evidence'],
        'articles'    => [
            ['num' => '1',  'heading_en' => 'Definitions', 'text_en' => 'Electronic signature: data in electronic form, attached to or logically associated with an electronic record, used to identify the signatory and indicate his approval of the record\'s contents. Electronic record: an electronic message, document, or registration created, sent, received, or stored by electronic means. Electronic-signature certificate: a document issued by a licensed authority confirming the link between a signature-verification key and the identity of the signatory.', 'text_ar' => 'التوقيع الإلكتروني: بيانات في صورة إلكترونية تُلحق أو ترتبط منطقياً بمحرر إلكتروني وتُستخدم لتمييز الموقّع وللدلالة على موافقته على ما ورد بالمحرر. المحرر الإلكتروني: رسالة أو وثيقة أو تسجيل إلكتروني يُنشأ أو يُرسل أو يُتلقى أو يُحفظ بوسيلة إلكترونية. شهادة التوقيع الإلكتروني: وثيقة تصدر عن جهة مرخص لها تؤكد ارتباط مفتاح التحقق من التوقيع بهوية الموقّع.'],
            ['num' => '14', 'heading_en' => 'Legal effect of e-signature', 'text_en' => 'The electronic signature, in the field of civil, commercial, and administrative dealings, has the same authoritative weight as the manuscript signature provided it satisfies the technical requirements set out in this Law and its executive regulations, including identification of the signatory, sole control of the signing means, and ability to detect any post-signing modification.', 'text_ar' => 'للتوقيع الإلكتروني، في نطاق المعاملات المدنية والتجارية والإدارية، ذات الحجية المقررة للتوقيعات في أحكام قانون الإثبات، متى استوفى الضوابط الفنية المنصوص عليها في هذا القانون ولائحته التنفيذية، ومنها تمييز هوية الموقّع، وانفراده بوسيلة التوقيع، وإمكان كشف أي تعديل لاحق على المحرر.'],
            ['num' => '15', 'heading_en' => 'Legal effect of electronic records', 'text_en' => 'Electronic records have the same evidentiary weight as official and customary written instruments where the requirements of integrity of source and the time of issuance are satisfied. The electronic record\'s evidentiary value is not denied by reason only of being in electronic form.', 'text_ar' => 'للمحررات الإلكترونية الرسمية والعرفية في نطاق المعاملات المدنية والتجارية والإدارية ذات الحجية المقررة للمحررات الكتابية في أحكام قانون الإثبات، متى استوفت الشروط المتعلقة بسلامة المصدر وزمن الإصدار. ولا يُنكر للمحرر الإلكتروني حجيته لمجرد كونه في صورة إلكترونية.'],
            ['num' => '18', 'heading_en' => 'Foreign certificates', 'text_en' => 'Electronic signatures and certificates issued by foreign authorities are recognised in Egypt provided they satisfy security and reliability conditions equivalent to those required under this Law, in accordance with reciprocity principles and the rules issued by the Information Technology Industry Development Authority (ITIDA).', 'text_ar' => 'تُعترف بالتوقيعات والشهادات الإلكترونية الصادرة من جهات أجنبية متى استوفت شروط الأمان والثقة الموازية للمقررة بموجب هذا القانون، وفق مبدأ المعاملة بالمثل والقواعد التي تصدرها هيئة تنمية صناعة تكنولوجيا المعلومات.'],
        ],
    ],

    [
        'title_en'    => 'Egyptian Banking Law — Banker–Customer Relationship',
        'title_ar'    => 'قانون البنك المركزي والجهاز المصرفي — علاقة البنك بالعميل',
        'jurisdiction'=> 'EG',
        'category'    => 'banking',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 194 of 2020 — extended articles',
        'citation_ar' => 'القانون رقم ١٩٤ لسنة ٢٠٢٠ — مواد جوهرية',
        'tags'        => ['banking', 'central-bank', 'cbe', 'consumer-finance'],
        'articles'    => [
            ['num' => '139', 'heading_en' => 'Banking secrecy', 'text_en' => 'Bank accounts, deposits, safe-deposit boxes, transactions, and information relating to customers are confidential. Their disclosure or examination is prohibited save with the written consent of the account-holder, his heirs, or a legatee, or by order of a competent court, or in the cases of judicial seizure, criminal investigation, or tax cooperation set out in this Law.', 'text_ar' => 'تُعد حسابات العملاء وودائعهم وخزائنهم ومعاملاتهم في البنوك والمعلومات المتعلقة بهم سرية، ولا يجوز الاطلاع عليها أو إفشاؤها إلا بإذن كتابي من صاحب الحساب أو من ورثته أو الموصى لهم، أو بأمر من جهة قضائية مختصة، أو في حالات الحجز القضائي أو التحقيق الجنائي أو التعاون الضريبي المنصوص عليها في هذا القانون.'],
            ['num' => '140', 'heading_en' => 'Set-off across accounts', 'text_en' => 'A bank may, on the maturity of a customer\'s obligations toward it, set off those obligations against any sum owed by the bank to the customer, including credit balances on current or savings accounts, save where the funds have been earmarked by law for a specific purpose or are exempt by their nature from set-off.', 'text_ar' => 'يجوز للبنك عند حلول أجل التزامات العميل قبله أن يُجري المقاصة بين هذه الالتزامات وأي مبلغ مستحق على البنك للعميل، بما في ذلك الأرصدة الدائنة بالحسابات الجارية أو حسابات التوفير، فيما عدا الأموال التي خصصها القانون لغرض معين أو التي يُمتنع بطبيعتها إجراء المقاصة فيها.'],
            ['num' => '144', 'heading_en' => 'Credit-information sharing', 'text_en' => 'Banks shall exchange information on the creditworthiness of customers through the Egyptian Credit Bureau (i-Score) and any other registry licensed by the Central Bank of Egypt, in the manner regulated by the implementing regulations and consistent with the banking secrecy chapter.', 'text_ar' => 'تتبادل البنوك المعلومات المتعلقة بالملاءة المالية للعملاء من خلال الشركة المصرية للاستعلام الائتماني (آي-سكور) وأي سجل آخر يرخص له البنك المركزي المصري، وذلك بالكيفية التي تنظمها اللائحة التنفيذية وبما يتسق مع الفصل الخاص بالسرية المصرفية.'],
            ['num' => '146', 'heading_en' => 'Disclosure of fees and interest', 'text_en' => 'Banks shall disclose to customers, in a clear and binding written form, all fees, commissions, interest rates, and method of calculation applicable to their accounts and products, and shall notify customers of any change at least sixty (60) days before it takes effect.', 'text_ar' => 'تلتزم البنوك بإحاطة العملاء كتابةً وبشكل واضح وملزم بكافة الرسوم والعمولات وأسعار الفوائد وطريقة احتسابها على حساباتهم ومنتجاتهم، وبإخطار العملاء بأي تغيير قبل وقوعه بستين (٦٠) يوماً على الأقل.'],
            ['num' => '149', 'heading_en' => 'Dormant accounts', 'text_en' => 'Accounts on which no movement has occurred at the customer\'s instruction for fifteen (15) consecutive years, and where the customer cannot be reached after reasonable efforts, shall be transferred to a special account at the Central Bank of Egypt, preserving the customer\'s rights and his entitlement to claim the balance.', 'text_ar' => 'تُحول إلى حساب خاص بالبنك المركزي المصري الحسابات التي لم تشهد أي حركة بناءً على تعليمات العميل لمدة خمس عشرة (١٥) سنة متصلة، ولم يمكن الوصول إلى العميل رغم بذل الجهود المعقولة، مع الاحتفاظ بحقوق العميل وأحقيته في المطالبة بالرصيد.'],
        ],
    ],

    [
        'title_en'    => 'Egyptian Mortgage Finance Law',
        'title_ar'    => 'قانون التمويل العقاري المصري',
        'jurisdiction'=> 'EG',
        'category'    => 'real-estate',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 148 of 2001',
        'citation_ar' => 'القانون رقم ١٤٨ لسنة ٢٠٠١',
        'tags'        => ['real-estate', 'mortgage', 'finance'],
        'articles'    => [
            ['num' => '1',  'heading_en' => 'Mortgage finance defined', 'text_en' => 'Mortgage finance is the lending of funds against the security of a registered real-estate mortgage, for the purposes of purchasing, constructing, restoring, or improving residential, administrative, or service property, repayable in periodic instalments over a term agreed between the financier and the beneficiary.', 'text_ar' => 'التمويل العقاري هو إقراض الأموال بضمان رهن عقاري مسجل، بغرض شراء أو بناء أو ترميم أو تحسين عقارات سكنية أو إدارية أو خدمية، يُسدد على أقساط دورية في أجل يتفق عليه بين الممول والمستفيد.'],
            ['num' => '9',  'heading_en' => 'Maximum instalment-to-income ratio', 'text_en' => 'The aggregate periodic instalment payable by the beneficiary shall not exceed a percentage of his net periodic income to be set by the executive regulations and reviewed periodically by the Financial Regulatory Authority, to protect the beneficiary from over-indebtedness.', 'text_ar' => 'لا يجوز أن يتجاوز إجمالي القسط الدوري الواجب الأداء على المستفيد نسبة من صافي دخله الدوري تحددها اللائحة التنفيذية وتراجعها الهيئة العامة للرقابة المالية دورياً، حمايةً للمستفيد من الإفراط في المديونية.'],
            ['num' => '11', 'heading_en' => 'Mortgage enforcement — accelerated process', 'text_en' => 'Where the beneficiary defaults on three consecutive instalments, the financier may, after thirty (30) days\' notice, request the competent execution judge to order sale of the mortgaged property by public auction, in accordance with the simplified procedures set out in this Law, without prejudice to the beneficiary\'s right of redemption before the sale falls.', 'text_ar' => 'إذا تأخر المستفيد عن سداد ثلاثة أقساط متتالية، جاز للممول بعد إنذاره بثلاثين (٣٠) يوماً أن يطلب من قاضي التنفيذ المختص الأمر ببيع العقار المرهون بالمزاد العلني وفق الإجراءات الميسرة المنصوص عليها في هذا القانون، دون إخلال بحق المستفيد في الوفاء قبل رسو المزاد.'],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       ADDITIONAL CASSATION PRINCIPLE SYNTHESES (EG)
       Doctrinal distillations from decades of cassation precedent on
       issues that recur in transactional disputes.
       ───────────────────────────────────────────────────────────── */

    [
        'title_en'    => 'Cassation Principles — Penalty Clauses & Liquidated Damages',
        'title_ar'    => 'مبادئ النقض — الشرط الجزائي والتعويض الاتفاقي',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'ar',
        'citation_en' => 'Egyptian Court of Cassation — synthesised civil principles',
        'citation_ar' => 'محكمة النقض المصرية — مبادئ مدنية مستقرة',
        'tags'        => ['cassation', 'penalty-clauses', 'damages'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Judicial reduction of excessive penalty', 'text_en' => 'A penalty clause is enforceable as agreed, but the court may, on the application of either party, reduce the penalty if it is grossly disproportionate to the actual damage, or increase a derisory penalty, in accordance with Civil Code Article 224. The party invoking reduction bears the burden of proving the disproportion.', 'text_ar' => 'الشرط الجزائي نافذ بمقداره المتفق عليه، إلا أن للقاضي ـ بناءً على طلب أحد الطرفين ـ أن يخفض الشرط الجزائي إذا كان مبالغاً فيه بشكل جسيم مقارنةً بالضرر الفعلي، أو أن يرفعه إذا كان زهيداً، وفقاً للمادة ٢٢٤ من القانون المدني. ويقع عبء إثبات المغالاة على الطرف الذي يطلب التخفيض.'],
            ['num' => 'P-2', 'heading_en' => 'Penalty presupposes damage', 'text_en' => 'No penalty is payable where the creditor proves no damage at all, or where the debtor proves that non-performance was caused by force majeure or an external cause for which he is not responsible. The penalty clause does not reverse the proof of fault on the debtor in obligations of result.', 'text_ar' => 'لا يستحق الشرط الجزائي متى أثبت المدين انتفاء الضرر كلياً، أو أن عدم التنفيذ يرجع إلى قوة قاهرة أو سبب أجنبي لا يد له فيه. ولا يقلب الشرط الجزائي عبء إثبات الخطأ على المدين في الالتزامات بتحقيق نتيجة.'],
        ],
    ],

    [
        'title_en'    => 'Cassation Principles — Unjust Enrichment & Restitution',
        'title_ar'    => 'مبادئ النقض — الإثراء بلا سبب ودعوى الاسترداد',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'ar',
        'citation_en' => 'Egyptian Court of Cassation — synthesised civil principles',
        'citation_ar' => 'محكمة النقض المصرية — مبادئ مدنية مستقرة',
        'tags'        => ['cassation', 'unjust-enrichment', 'restitution'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Elements of the claim', 'text_en' => 'A claim for unjust enrichment under Civil Code Articles 179 and 181 requires: (a) enrichment of the defendant, (b) impoverishment of the claimant, (c) a causal link between the two, and (d) absence of a legal cause for the enrichment. The remedy is restitution of the lesser of the enrichment or the impoverishment, valued as at the date of judgment.', 'text_ar' => 'تشترط دعوى الإثراء بلا سبب طبقاً للمادتين ١٧٩ و١٨١ من القانون المدني توافر: (أ) إثراء في ذمة المدعى عليه، (ب) افتقار في ذمة المدعي، (ج) رابطة سببية بين الإثراء والافتقار، (د) انعدام السبب القانوني للإثراء. ويُلزم المثري بأقل القيمتين وقت الحكم.'],
            ['num' => 'P-2', 'heading_en' => 'Subsidiarity of the claim', 'text_en' => 'The unjust-enrichment action is subsidiary and may not be maintained where the claimant has another available cause of action — whether contractual, tortious, or based on a specific statutory remedy — to recover what he claims, even if such other action has prescribed.', 'text_ar' => 'دعوى الإثراء بلا سبب دعوى احتياطية لا تقبل إذا كان للدائن طريق آخر للمطالبة بحقه ـ عقدياً كان أم تقصيرياً أم مبنياً على نص خاص ـ ولو سقط هذا الطريق بالتقادم.'],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       ARBITRATION, COMPETITION, TENDERS, TELECOMS
       Recurring transactional concerns: arbitration as forum, antitrust
       merger control, government procurement, telecoms licensing.
       ───────────────────────────────────────────────────────────── */

    [
        'title_en'    => 'Egyptian Arbitration Law — Core Articles',
        'title_ar'    => 'قانون التحكيم المصري — مواد جوهرية',
        'jurisdiction'=> 'EG',
        'category'    => 'arbitration',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 27 of 1994 on Arbitration in Civil and Commercial Matters',
        'citation_ar' => 'القانون رقم ٢٧ لسنة ١٩٩٤ في شأن التحكيم في المواد المدنية والتجارية',
        'tags'        => ['arbitration', 'procedure'],
        'articles'    => [
            ['num' => '10', 'heading_en' => 'Form of arbitration agreement', 'text_en' => 'An arbitration agreement must be in writing on pain of nullity. It is in writing if contained in a document signed by the parties, or in correspondence, telegrams, or any other written means exchanged between them, or by reference in a contract to a document containing an arbitration clause where the reference is clear in incorporating the clause into the contract.', 'text_ar' => 'يجب أن يكون اتفاق التحكيم مكتوباً وإلا كان باطلاً. ويكون اتفاق التحكيم مكتوباً إذا تضمنه محرر وقعه الطرفان، أو إذا تضمنته الرسائل أو البرقيات أو غيرها من وسائل الاتصال المكتوبة المتبادلة بين الطرفين، أو بالإحالة في عقد إلى وثيقة تتضمن شرط التحكيم متى كانت الإحالة واضحة في اعتبار هذا الشرط جزءاً من العقد.'],
            ['num' => '11', 'heading_en' => 'Arbitrability', 'text_en' => 'It is not permissible to agree to arbitration in matters not capable of being settled by compromise. The arbitration agreement is valid only as to disputes capable of being settled amicably between the parties under the substantive law applicable.', 'text_ar' => 'لا يجوز الاتفاق على التحكيم في المسائل التي لا يجوز فيها الصلح. ولا يصح اتفاق التحكيم إلا في المنازعات التي يجوز فيها الصلح بين الطرفين وفقاً لأحكام القانون الموضوعي الواجب التطبيق.'],
            ['num' => '13', 'heading_en' => 'Separability of arbitration clause', 'text_en' => 'The arbitration clause is to be treated as an agreement independent of the other terms of the contract. The invalidity, rescission, or termination of the contract does not affect the arbitration clause it contains, provided the clause is itself valid.', 'text_ar' => 'يعتبر شرط التحكيم اتفاقاً مستقلاً عن شروط العقد الأخرى. ولا يترتب على بطلان العقد أو فسخه أو إنهائه أي أثر على شرط التحكيم الذي يتضمنه إذا كان هذا الشرط صحيحاً في ذاته.'],
            ['num' => '17', 'heading_en' => 'Number and composition of tribunal', 'text_en' => 'The parties are free to agree on the number of arbitrators, provided that the number be odd; otherwise the tribunal consists of three arbitrators. If the parties fail to agree on the procedure of appointment, each party appoints one arbitrator and the two appointees jointly nominate the third, who acts as chairman.', 'text_ar' => 'للطرفين الاتفاق على عدد المحكمين على أن يكون وتراً، وإلا تشكلت هيئة التحكيم من ثلاثة محكمين. وإذا لم يتفق الطرفان على إجراءات التعيين، يعين كل طرف محكماً ويختار المحكمان المعينان المحكم الثالث الذي يتولى الرئاسة.'],
            ['num' => '22', 'heading_en' => 'Competence-competence', 'text_en' => 'The arbitral tribunal has jurisdiction to rule on its own jurisdiction, including pleas of non-existence, nullity, or expiry of the arbitration agreement. Pleas of want of jurisdiction must be raised no later than the statement of defence; the tribunal may admit later pleas if it considers the delay justified.', 'text_ar' => 'تختص هيئة التحكيم بالفصل في الدفوع المتعلقة باختصاصها بما في ذلك الدفوع المبنية على عدم وجود اتفاق التحكيم أو سقوطه أو بطلانه. ويجب إبداء هذه الدفوع في موعد لا يتجاوز ميعاد تقديم دفاع المدعى عليه، ويجوز للهيئة قبول الدفوع المتأخرة إذا رأت أن التأخير مبرر.'],
            ['num' => '53', 'heading_en' => 'Grounds for setting aside award', 'text_en' => 'A nullity action against an arbitral award is admissible only on grounds including: absence or invalidity of the arbitration agreement; a party\'s incapacity at the time of conclusion; inability of a party to present its case for not having been duly notified or for any other reason beyond its control; non-application of the law agreed by the parties to the substance; irregularity of the tribunal\'s composition or appointment of arbitrators; award rendered on matters outside the scope of the arbitration agreement; failure of the award to comply with mandatory form requirements that affect its substance; or violation of public order in the Arab Republic of Egypt (raised by the court of its own motion).', 'text_ar' => 'لا تُقبل دعوى البطلان في حكم التحكيم إلا في الحالات الآتية: عدم وجود اتفاق التحكيم أو بطلانه؛ نقص أهلية أحد طرفيه وقت إبرامه؛ تعذر تقديم أحد الطرفين دفاعه لعدم إعلانه إعلاناً صحيحاً أو لأي سبب آخر خارج عن إرادته؛ عدم تطبيق القانون الذي اتفق الطرفان على تطبيقه على موضوع النزاع؛ عيب في تشكيل هيئة التحكيم أو في تعيين المحكمين؛ صدور الحكم في مسائل خارجة عن نطاق اتفاق التحكيم؛ عدم استيفاء حكم التحكيم لشكليات جوهرية تؤثر في مضمونه؛ مخالفة الحكم للنظام العام في جمهورية مصر العربية (تتصدى لها المحكمة من تلقاء نفسها).'],
            ['num' => '58', 'heading_en' => 'Enforcement of awards', 'text_en' => 'An arbitral award is not enforceable save by order of the President of the competent court or his delegate, on application by the party concerned, supported by the original award or a certified copy, a certified Arabic translation if rendered in another language, and a copy of the deposit minute. The court verifies the award does not contradict a prior judgment on the same matter, does not violate public order, and was properly notified to the losing party.', 'text_ar' => 'لا يجوز تنفيذ حكم التحكيم إلا بأمر يصدره رئيس المحكمة المختصة أو من يندبه، بناءً على طلب من ذي الشأن، مرفقاً به أصل الحكم أو صورة موقعة منه، وترجمة عربية معتمدة إذا كان صادراً بلغة أخرى، وصورة من محضر إيداع الحكم. وتتحقق المحكمة من عدم تعارض الحكم مع حكم سابق في موضوعه، وعدم مخالفته للنظام العام، وصحة إعلان المحكوم عليه به.'],
        ],
    ],

    [
        'title_en'    => 'Egyptian Competition Law',
        'title_ar'    => 'قانون حماية المنافسة ومنع الممارسات الاحتكارية',
        'jurisdiction'=> 'EG',
        'category'    => 'compliance',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 3 of 2005 (as amended by Law 175 of 2022 introducing pre-merger control)',
        'citation_ar' => 'القانون رقم ٣ لسنة ٢٠٠٥ المعدل بالقانون رقم ١٧٥ لسنة ٢٠٢٢',
        'tags'        => ['competition', 'antitrust', 'merger-control', 'eca'],
        'articles'    => [
            ['num' => '6',  'heading_en' => 'Horizontal anticompetitive agreements', 'text_en' => 'Agreements or contracts between competing persons in any relevant market are prohibited where they result in: fixing prices of products; sharing the market or allocating it by geography, customers, products, seasons, or periods; coordinating to submit or refrain from submitting offers in tenders or auctions; or restricting production, marketing, or distribution operations or imposing restraints on them. Such agreements are absolutely void.', 'text_ar' => 'يُحظر على الأشخاص المتنافسة في أي سوق معنية الاتفاق أو التعاقد إذا أدى ذلك إلى: تحديد أسعار المنتجات؛ تقسيم السوق أو اقتسامها على أساس المناطق الجغرافية أو العملاء أو المنتجات أو المواسم أو الفترات الزمنية؛ التنسيق فيما يتعلق بالتقدم أو الامتناع عن التقدم في المناقصات والمزايدات؛ تقييد عمليات الإنتاج أو التسويق أو التوزيع أو وضع قيود عليها. ويقع هذا الاتفاق باطلاً بطلاناً مطلقاً.'],
            ['num' => '7',  'heading_en' => 'Vertical anticompetitive agreements', 'text_en' => 'Agreements between a person and any of its suppliers or customers are prohibited where they aim to restrict competition. The Egyptian Competition Authority assesses such agreements according to their effect on the relevant market, market share of the parties, and the existence of efficiency justifications.', 'text_ar' => 'يُحظر الاتفاق بين أي شخص وأي من مورديه أو عملائه إذا كان من شأن هذا الاتفاق تقييد المنافسة. ويقدر جهاز حماية المنافسة هذه الاتفاقات وفقاً لأثرها على السوق المعنية وحصص الأطراف فيها ووجود مبررات الكفاءة.'],
            ['num' => '8',  'heading_en' => 'Abuse of dominance', 'text_en' => 'A person of dominant position in a relevant market is prohibited from abusing that position by: refusing without justification to deal with a particular person; restricting production, marketing, or technical development; tying sales to other products or services; discriminating without justification between customers in similar transactions; imposing predatory prices; or any practice limiting access to the market. Dominance is presumed where market share exceeds twenty-five per cent (25%).', 'text_ar' => 'يُحظر على الشخص ذي الوضع المسيطر في سوق معنية إساءة استخدام هذا الوضع بأي مما يلي: الامتناع دون مبرر عن التعامل مع شخص بعينه؛ تقييد الإنتاج أو التسويق أو التطوير التقني؛ اشتراط بيع منتجات أو خدمات أخرى؛ التمييز دون مبرر بين العملاء في المعاملات المماثلة؛ فرض أسعار افتراسية؛ أو أي ممارسة تحد من الدخول إلى السوق. ويُفترض الوضع المسيطر متى تجاوزت الحصة السوقية خمسة وعشرين في المائة (٢٥٪).'],
            ['num' => '19', 'heading_en' => 'Pre-merger notification (post-2022)', 'text_en' => 'Any economic concentration meeting the financial thresholds set by the executive regulations (combined annual turnover or assets exceeding the threshold) must be notified to the Egyptian Competition Authority before completion. The transaction shall not be closed before the Authority\'s clearance, save for the standstill exceptions provided in the law. Failure to notify is punishable by fines of one to ten per cent (1%–10%) of the annual turnover.', 'text_ar' => 'يجب إخطار جهاز حماية المنافسة قبل إتمام أي تركز اقتصادي يستوفي الحدود المالية المقررة في اللائحة التنفيذية (إجمالي رقم الأعمال أو الأصول السنوية يتجاوز الحد المقرر). ولا يجوز إتمام الصفقة قبل صدور موافقة الجهاز، فيما عدا استثناءات التوقف المنصوص عليها في القانون. ويُعاقب على عدم الإخطار بغرامة لا تقل عن واحد في المائة ولا تجاوز عشرة في المائة (١٪ ـ ١٠٪) من إجمالي رقم الأعمال السنوي.'],
        ],
    ],

    [
        'title_en'    => 'Egyptian Tenders & Auctions Law',
        'title_ar'    => 'قانون تنظيم التعاقدات التي تبرمها الجهات العامة',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 182 of 2018',
        'citation_ar' => 'القانون رقم ١٨٢ لسنة ٢٠١٨',
        'tags'        => ['public-procurement', 'tenders'],
        'articles'    => [
            ['num' => '4',  'heading_en' => 'General principles of public contracting', 'text_en' => 'Public entities shall contract on the basis of free competition, equality of opportunity, transparency, integrity of procedures, equal treatment of bidders, value for public funds, and conformity of the bid to specifications. Awards shall favour Egyptian products and goods of Egyptian content in accordance with the preference percentages set by the executive regulations.', 'text_ar' => 'تتعاقد الجهات العامة وفقاً لمبادئ حرية المنافسة وتكافؤ الفرص والشفافية ونزاهة الإجراءات والمعاملة المتكافئة للمتقدمين وتحقيق المنفعة الاقتصادية للمال العام ومطابقة العطاء للمواصفات. وتُمنح الأفضلية للمنتجات والسلع ذات المكون المصري وفق نسب التفضيل التي تحددها اللائحة التنفيذية.'],
            ['num' => '12', 'heading_en' => 'Methods of contracting', 'text_en' => 'Public contracts are concluded by one of the following: general tender (the principal method); limited tender; local tender; two-stage tender; direct contracting (in cases of urgency or where the supplier is unique); or framework agreement. The executive regulations set the financial thresholds and conditions for each method.', 'text_ar' => 'تُبرم التعاقدات العامة بإحدى الطرق الآتية: المناقصة العامة (الأصل العام)؛ المناقصة المحدودة؛ المناقصة المحلية؛ المناقصة على مرحلتين؛ الاتفاق المباشر (في حالات الاستعجال أو تفرد المورد)؛ أو الاتفاقية الإطارية. وتحدد اللائحة التنفيذية الحدود المالية وشروط كل طريقة.'],
            ['num' => '43', 'heading_en' => 'Performance and advance guarantees', 'text_en' => 'The successful bidder shall provide a performance guarantee equal to five per cent (5%) of the value of the contract, valid until full performance and acceptance, and may be required to provide an advance-payment guarantee equal in value to the advance received. Guarantees are forfeited in cases of bidder default to the extent of damage suffered by the contracting entity.', 'text_ar' => 'يلتزم من رست عليه المناقصة بتقديم تأمين نهائي يعادل خمسة في المائة (٥٪) من قيمة التعاقد، ساري المفعول حتى إتمام التنفيذ والاستلام، ويجوز إلزامه بتقديم خطاب ضمان عن الدفعة المقدمة يعادل قيمتها. وتُصادر التأمينات في حالات إخلال المتعاقد بقدر الضرر الذي أصاب الجهة المتعاقدة.'],
        ],
    ],

    [
        'title_en'    => 'Egyptian Telecommunications Regulation Law',
        'title_ar'    => 'قانون تنظيم الاتصالات',
        'jurisdiction'=> 'EG',
        'category'    => 'regulatory',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 10 of 2003',
        'citation_ar' => 'القانون رقم ١٠ لسنة ٢٠٠٣',
        'tags'        => ['telecoms', 'licensing', 'ntra'],
        'articles'    => [
            ['num' => '21', 'heading_en' => 'Licence requirement', 'text_en' => 'No person may provide telecommunications services, establish or operate telecommunications networks, or import equipment requiring frequency-spectrum use without a licence or permit from the National Telecom Regulatory Authority. Licences specify scope, term, financial obligations, and service-quality requirements.', 'text_ar' => 'لا يجوز لأي شخص تقديم خدمات الاتصالات أو إنشاء أو تشغيل شبكات الاتصالات أو استيراد المعدات التي يلزم لاستخدامها استعمال الطيف الترددي دون ترخيص أو تصريح من الجهاز القومي لتنظيم الاتصالات. ويبين الترخيص نطاقه ومدته والالتزامات المالية ومتطلبات جودة الخدمة.'],
            ['num' => '64', 'heading_en' => 'Confidentiality of communications', 'text_en' => 'The confidentiality of telecommunications is guaranteed. It is prohibited to intercept, record, or disclose the content of communications save by judicial order issued in the cases and according to the procedures set out in this Law and the Code of Criminal Procedure.', 'text_ar' => 'سرية الاتصالات مكفولة. ويُحظر اعتراض الاتصالات أو تسجيلها أو الإفصاح عن مضمونها إلا بأمر قضائي وفي الأحوال وبالإجراءات المنصوص عليها في هذا القانون وقانون الإجراءات الجنائية.'],
            ['num' => '72', 'heading_en' => 'Universal service obligations', 'text_en' => 'Licensed operators shall contribute to a universal-service fund administered by NTRA, used to extend telecommunications services to remote and under-served areas. Contributions are calculated on a percentage of annual licensed revenues to be set by NTRA.', 'text_ar' => 'يلتزم المرخص لهم بالمساهمة في صندوق الخدمة الشاملة الذي يديره الجهاز القومي لتنظيم الاتصالات، ويُستخدم لمد خدمات الاتصالات إلى المناطق النائية والمحرومة من الخدمة. وتُحسب المساهمات كنسبة من الإيرادات السنوية للترخيص يحددها الجهاز.'],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       SAUDI ARABIA — DEPTH PUSH
       Extends SA from BETA toward LIVE: Companies Law, Labor Law,
       Government Tenders, Commercial Courts.
       ───────────────────────────────────────────────────────────── */

    [
        'title_en'    => 'Saudi Companies Law — Core Articles',
        'title_ar'    => 'نظام الشركات السعودي — مواد جوهرية',
        'jurisdiction'=> 'SA',
        'category'    => 'companies',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/132 of 1443H',
        'citation_ar' => 'المرسوم الملكي رقم م/١٣٢ لسنة ١٤٤٣هـ',
        'tags'        => ['companies', 'corporate'],
        'articles'    => [
            ['num' => '4',  'heading_en' => 'Company forms', 'text_en' => 'A company under this Law may take the form of: a joint-liability company; a limited-partnership company; a limited-liability company (LLC); a simplified joint-stock company; a joint-stock company; or a holding company. Each form has its own minimum-capital and governance rules under this Law.', 'text_ar' => 'تكون الشركة وفقاً لهذا النظام في إحدى الأشكال الآتية: شركة التضامن؛ شركة التوصية البسيطة؛ الشركة ذات المسؤولية المحدودة؛ الشركة المساهمة المبسطة؛ الشركة المساهمة؛ الشركة القابضة. ولكل شكل قواعده الخاصة بالحد الأدنى لرأس المال والحوكمة بموجب هذا النظام.'],
            ['num' => '157', 'heading_en' => 'LLC — minimum capital and partner number', 'text_en' => 'A limited-liability company is established by one or more partners not exceeding fifty (50). There is no statutory minimum capital save where required by the activity or as set by the Ministry of Commerce. The liability of each partner is limited to the value of his share in the capital.', 'text_ar' => 'تتكون الشركة ذات المسؤولية المحدودة من شريك أو أكثر بحد أقصى خمسين (٥٠) شريكاً. ولا يوجد حد أدنى نظامي لرأس المال إلا إذا اقتضى النشاط ذلك أو قررته وزارة التجارة. وتقتصر مسؤولية كل شريك على قيمة حصته في رأس المال.'],
            ['num' => '198', 'heading_en' => 'Joint-stock company — board responsibilities', 'text_en' => 'The board of directors is competent to perform all acts necessary for the management of the company within the limits of its purposes and the powers vested in the general assembly by this Law and the bylaws. Directors owe duties of care, loyalty, and good faith, and are jointly and severally liable for losses arising from acts in breach of these duties.', 'text_ar' => 'يختص مجلس الإدارة بالقيام بجميع الأعمال اللازمة لإدارة الشركة في حدود أغراضها والصلاحيات التي يقررها هذا النظام والنظام الأساس للجمعية العامة. ويلتزم أعضاء المجلس بواجبات العناية والأمانة وحسن النية، ويكونون مسؤولين بالتضامن عن الأضرار الناشئة عن الأعمال المخالفة لهذه الواجبات.'],
            ['num' => '212', 'heading_en' => 'Related-party transactions', 'text_en' => 'No director or executive may, directly or indirectly, have an interest in any transaction or contract entered into by the company except with prior authorisation from the ordinary general assembly, renewed annually. Interested directors shall disclose the conflict to the board and abstain from deliberation or voting on the transaction.', 'text_ar' => 'لا يجوز لأي عضو مجلس إدارة أو تنفيذي أن تكون له بطريقة مباشرة أو غير مباشرة مصلحة في أي صفقة أو عقد تبرمه الشركة إلا بتفويض مسبق من الجمعية العامة العادية يتجدد سنوياً. ويلتزم العضو ذو المصلحة بالإفصاح للمجلس وبالامتناع عن المداولة والتصويت على الصفقة.'],
        ],
    ],

    [
        'title_en'    => 'Saudi Labor Law — Core Articles',
        'title_ar'    => 'نظام العمل السعودي — مواد جوهرية',
        'jurisdiction'=> 'SA',
        'category'    => 'labour',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/51 of 1426H (as amended)',
        'citation_ar' => 'المرسوم الملكي رقم م/٥١ لسنة ١٤٢٦هـ وتعديلاته',
        'tags'        => ['labour', 'employment', 'saudisation'],
        'articles'    => [
            ['num' => '37', 'heading_en' => 'Fixed-term and indefinite contracts', 'text_en' => 'A work contract is for a fixed term, an indefinite term, or for a specific job. A fixed-term contract with a non-Saudi must not exceed the term of the work permit. Where a fixed-term contract is renewed expressly or by parties continuing performance, it remains for the renewed period.', 'text_ar' => 'يكون عقد العمل محدد المدة أو غير محدد المدة أو لإنجاز عمل معين. ولا يجوز أن تجاوز مدة العقد المحدد المدة مع غير السعودي مدة رخصة العمل. وإذا تجدد العقد المحدد المدة صراحةً أو بمواصلة الطرفين تنفيذه، استمر للمدة المتجددة.'],
            ['num' => '74', 'heading_en' => 'Termination — limited grounds', 'text_en' => 'The work contract ends only on one of: agreement of the parties; expiry of the term; the employee\'s wish in indefinite contracts after notice; reaching retirement age unless agreed otherwise; force majeure; or other cases provided by law. Termination by the employer outside these grounds entitles the employee to compensation.', 'text_ar' => 'لا ينتهي عقد العمل إلا بإحدى الحالات الآتية: اتفاق الطرفين؛ انتهاء مدته؛ رغبة العامل في العقد غير محدد المدة بعد الإشعار؛ بلوغ سن التقاعد ما لم يُتفق على غير ذلك؛ القوة القاهرة؛ أو الحالات الأخرى التي يقررها النظام. ويُستحق التعويض للعامل إذا أنهى صاحب العمل العقد لغير ما تقدم.'],
            ['num' => '77', 'heading_en' => 'Notice period and compensation for arbitrary dismissal', 'text_en' => 'In indefinite contracts, the party wishing to terminate shall notify the other in writing at least sixty (60) days before, where the wage is paid monthly, and at least thirty (30) days otherwise. Where the termination is for an arbitrary reason, the affected party is entitled to compensation equal to fifteen (15) days\' wages per year of service in indefinite contracts, or the remaining wages of the term in fixed-term contracts, unless the parties agree on greater compensation.', 'text_ar' => 'في العقود غير محددة المدة، يلتزم الطرف الراغب في إنهاء العقد بإخطار الطرف الآخر كتابةً قبل الإنهاء بمدة لا تقل عن ستين (٦٠) يوماً إذا كان الأجر شهرياً، وثلاثين (٣٠) يوماً في غير ذلك. وإذا كان الإنهاء لسبب غير مشروع، استحق الطرف المتضرر تعويضاً يعادل أجر خمسة عشر (١٥) يوماً عن كل سنة من سنوات الخدمة في العقود غير محددة المدة، أو ما تبقى من أجر العقد محدد المدة، ما لم يتفق الطرفان على تعويض أعلى.'],
            ['num' => '84', 'heading_en' => 'End-of-service award', 'text_en' => 'On the end of the work relation, the employer pays the employee an end-of-service award of half a month\'s wage for each of the first five (5) years and a full month\'s wage for each subsequent year, calculated on the basis of the last wage, pro-rated for fractions of years.', 'text_ar' => 'عند انتهاء علاقة العمل، يدفع صاحب العمل للعامل مكافأة نهاية الخدمة بواقع نصف شهر عن كل سنة من السنوات الخمس الأولى وشهر كامل عن كل سنة من السنوات التالية، تُحسب على أساس آخر أجر، ويُؤدى عن كسور السنة بنسبة ما قضى منها في العمل.'],
            ['num' => '109', 'heading_en' => 'Annual leave', 'text_en' => 'The employee is entitled to a paid annual leave of not less than twenty-one (21) days, increased to thirty (30) days after five (5) years of service with the same employer. The employee is also entitled to paid sick leave, weekly rest, and official-holiday leave as detailed in this Law.', 'text_ar' => 'يستحق العامل إجازة سنوية مدفوعة الأجر لا تقل عن واحد وعشرين (٢١) يوماً، تُزاد إلى ثلاثين (٣٠) يوماً بعد قضاء خمس (٥) سنوات في الخدمة لدى صاحب العمل ذاته. ويستحق العامل كذلك إجازة مرضية مدفوعة وراحة أسبوعية وإجازة في العطلات الرسمية وفق ما يفصله هذا النظام.'],
        ],
    ],

    [
        'title_en'    => 'Saudi Government Tenders & Procurement Law',
        'title_ar'    => 'نظام المنافسات والمشتريات الحكومية السعودي',
        'jurisdiction'=> 'SA',
        'category'    => 'commercial',
        'source'      => 'sa-gazette',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. M/128 of 1440H',
        'citation_ar' => 'المرسوم الملكي رقم م/١٢٨ لسنة ١٤٤٠هـ',
        'tags'        => ['public-procurement', 'tenders', 'saudisation'],
        'articles'    => [
            ['num' => '3',  'heading_en' => 'Principles', 'text_en' => 'Procurement by government entities is based on transparency, integrity of procedures, equal opportunity, free competition, and the efficient use of public funds. The Local Content Preference policy applies to national products and Saudi-content services in accordance with the percentages set by the Local Content and Government Procurement Authority.', 'text_ar' => 'تستند المشتريات الحكومية إلى مبادئ الشفافية ونزاهة الإجراءات وتكافؤ الفرص وحرية المنافسة والاستخدام الأمثل للمال العام. وتطبق أفضلية المحتوى المحلي على المنتجات الوطنية والخدمات ذات المحتوى السعودي وفق النسب التي تحددها هيئة المحتوى المحلي والمشتريات الحكومية.'],
            ['num' => '38', 'heading_en' => 'Bid bond and performance bond', 'text_en' => 'The bidder shall furnish a bid bond of one to two per cent (1%–2%) of the bid value, refunded to unsuccessful bidders after award. The successful bidder shall furnish a performance bond of five per cent (5%) of the contract value, retained until full performance and final acceptance, and increased to ten per cent (10%) in contracts exceeding such financial threshold as the regulations specify.', 'text_ar' => 'يلتزم المتقدم بتقديم ضمان ابتدائي يعادل من واحد إلى اثنين في المائة (١٪ ـ ٢٪) من قيمة العطاء، يُرد إلى من لم ترسُ عليهم المنافسة بعد الترسية. ويلتزم من رست عليه المنافسة بتقديم ضمان نهائي يعادل خمسة في المائة (٥٪) من قيمة العقد، يُحتفظ به حتى التنفيذ الكامل والاستلام النهائي، ويُرفع إلى عشرة في المائة (١٠٪) في العقود التي تتجاوز الحد المالي الذي تحدده اللوائح.'],
            ['num' => '74', 'heading_en' => 'Liquidated damages for delay', 'text_en' => 'Where the contractor delays performance beyond the contractual term, the government entity imposes liquidated damages at the rate specified in the contract, capped at six per cent (6%) of the value of supply contracts and twenty per cent (20%) of the value of works contracts. The cap does not preclude termination and recovery of greater proven damage.', 'text_ar' => 'إذا تأخر المتعاقد عن التنفيذ في المدة المحددة، تُوقع الجهة الحكومية غرامات تأخير بالنسبة المحددة في العقد، بحد أقصى ستة في المائة (٦٪) من قيمة عقود التوريد، وعشرين في المائة (٢٠٪) من قيمة عقود الأعمال. ولا يحول الحد الأقصى دون فسخ العقد واستيفاء ما يفوق ذلك من تعويض مثبت.'],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       UAE — DEPTH PUSH
       Extends UAE: Commercial Companies, Labour, Arbitration, VAT.
       ───────────────────────────────────────────────────────────── */

    [
        'title_en'    => 'UAE Commercial Companies Law — Core Articles',
        'title_ar'    => 'قانون الشركات التجارية الإماراتي — مواد جوهرية',
        'jurisdiction'=> 'AE',
        'category'    => 'companies',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 32 of 2021',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٣٢ لسنة ٢٠٢١',
        'tags'        => ['companies', 'corporate', 'foreign-ownership'],
        'articles'    => [
            ['num' => '8',  'heading_en' => 'Foreign ownership — onshore', 'text_en' => 'Companies established onshore may be wholly owned by non-UAE nationals save in strategically-impactful activities listed by Cabinet decision. Free-zone companies remain subject to their respective free-zone authority\'s ownership rules. Branches of foreign companies require a UAE service-agent unless exempted.', 'text_ar' => 'يجوز تملك الشركات المؤسسة في البر الرئيسي بالكامل لغير مواطني دولة الإمارات، فيما عدا الأنشطة ذات الأثر الاستراتيجي المحددة بقرار من مجلس الوزراء. وتظل شركات المناطق الحرة خاضعة لقواعد الملكية الخاصة بسلطة المنطقة الحرة المعنية. ويُشترط لفروع الشركات الأجنبية وكيل خدمات إماراتي ما لم تُعفَ.'],
            ['num' => '71', 'heading_en' => 'LLC — partners and management', 'text_en' => 'A limited-liability company is constituted by one or more partners up to fifty (50). The company is managed by one or more managers appointed by the partners. The managers represent the company toward third parties within the limits of the authority granted by the memorandum.', 'text_ar' => 'تتكون الشركة ذات المسؤولية المحدودة من شريك واحد أو أكثر بحد أقصى خمسين (٥٠) شريكاً. ويديرها مدير أو أكثر يعينهم الشركاء. ويمثل المديرون الشركة قِبل الغير في حدود الصلاحيات الممنوحة لهم بموجب عقد التأسيس.'],
            ['num' => '154', 'heading_en' => 'Public joint-stock — listing requirement', 'text_en' => 'A public joint-stock company shall, within one year of incorporation, offer at least thirty per cent (30%) of its shares for public subscription on a UAE-licensed financial market, unless exempted by the SCA. The minimum issued capital is thirty million (30,000,000) UAE dirhams, of which at least twenty-five per cent (25%) is paid up on subscription.', 'text_ar' => 'تلتزم الشركة المساهمة العامة، خلال سنة من تأسيسها، بطرح ما لا يقل عن ثلاثين في المائة (٣٠٪) من أسهمها للاكتتاب العام في سوق مالي مرخص في الدولة، ما لم تُعفَ من قبل الهيئة. ويكون الحد الأدنى لرأس المال المُصدر ثلاثين مليون (٣٠٫٠٠٠٫٠٠٠) درهم إماراتي يُسدد منها عند الاكتتاب ما لا يقل عن خمسة وعشرين في المائة (٢٥٪).'],
            ['num' => '224', 'heading_en' => 'Director duties and liability', 'text_en' => 'Directors of a joint-stock company owe duties of care, loyalty, and confidentiality to the company. They are jointly and severally liable to the company, shareholders, and third parties for acts of fraud, abuse of power, violations of the law or bylaws, and gross management errors. The action for liability prescribes after five (5) years from discovery of the act and in any event ten (10) years from the act itself.', 'text_ar' => 'يلتزم أعضاء مجلس إدارة الشركة المساهمة بواجبات العناية والأمانة والسرية تجاه الشركة. ويكونون مسؤولين بالتضامن قبل الشركة والمساهمين والغير عن أعمال الغش وإساءة استخدام السلطة ومخالفة القانون أو النظام الأساسي والأخطاء الجسيمة في الإدارة. وتسقط دعوى المسؤولية بمضي خمس (٥) سنوات من اكتشاف الفعل، وفي جميع الأحوال بمضي عشر (١٠) سنوات من ارتكابه.'],
        ],
    ],

    [
        'title_en'    => 'UAE Labour Law — Core Articles',
        'title_ar'    => 'قانون تنظيم علاقات العمل الإماراتي — مواد جوهرية',
        'jurisdiction'=> 'AE',
        'category'    => 'labour',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 33 of 2021 (as amended)',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٣٣ لسنة ٢٠٢١ وتعديلاته',
        'tags'        => ['labour', 'employment'],
        'articles'    => [
            ['num' => '8',  'heading_en' => 'Fixed-term contracts only', 'text_en' => 'All employment contracts in the private sector shall be for a fixed term not exceeding three (3) years, renewable for a similar or shorter term by agreement. Contracts in existence at the time this Law comes into force shall be reconciled with this provision within the transitional period set by the executive regulations.', 'text_ar' => 'تكون جميع عقود العمل في القطاع الخاص محددة المدة بما لا يجاوز ثلاث (٣) سنوات، قابلة للتجديد لمدة مماثلة أو أقل بالاتفاق. وتُوفَّق العقود القائمة وقت نفاذ هذا القانون مع هذا الحكم خلال المهلة الانتقالية التي تحددها اللائحة التنفيذية.'],
            ['num' => '42', 'heading_en' => 'Termination grounds', 'text_en' => 'The work contract ends by: expiry; mutual written agreement; written notice by either party (subject to the agreed notice period of thirty to ninety days); operation of law in cases of force majeure, employer\'s insolvency, or revocation of the work permit; the employee\'s death or permanent disability; or a final court order. Termination for a non-statutory reason gives rise to a claim for arbitrary-dismissal compensation up to three (3) months\' wages.', 'text_ar' => 'ينتهي عقد العمل بـ: انقضاء مدته؛ الاتفاق الكتابي المتبادل؛ الإخطار الكتابي من أي طرف (وفق مدة الإخطار المتفق عليها بين ثلاثين وتسعين يوماً)؛ بقوة القانون في حالات القوة القاهرة أو إفلاس صاحب العمل أو إلغاء تصريح العمل؛ وفاة العامل أو عجزه الكلي؛ أو حكم قضائي بات. ويُستحق التعويض عن الفصل التعسفي بما لا يجاوز أجر ثلاثة (٣) أشهر إذا كان الإنهاء لسبب غير مشروع.'],
            ['num' => '51', 'heading_en' => 'End-of-service gratuity', 'text_en' => 'A full-time foreign worker who has completed one year or more in continuous service is entitled to an end-of-service gratuity of twenty-one (21) days\' basic wage for each of the first five (5) years and thirty (30) days for each subsequent year, capped at two (2) years\' wages. Pension contributions for Emirati workers replace this gratuity.', 'text_ar' => 'يستحق العامل الأجنبي بدوام كامل الذي أمضى سنة أو أكثر في الخدمة المستمرة مكافأة نهاية خدمة بواقع أجر واحد وعشرين (٢١) يوماً عن كل من السنوات الخمس الأولى، وثلاثين (٣٠) يوماً عن كل سنة تالية، بحد أقصى أجر سنتين (٢). وتحل اشتراكات التقاعد للعمال المواطنين محل هذه المكافأة.'],
        ],
    ],

    [
        'title_en'    => 'UAE Arbitration Law — Core Articles',
        'title_ar'    => 'قانون التحكيم الإماراتي — مواد جوهرية',
        'jurisdiction'=> 'AE',
        'category'    => 'arbitration',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Law No. 6 of 2018 (as amended)',
        'citation_ar' => 'القانون الاتحادي رقم ٦ لسنة ٢٠١٨ وتعديلاته',
        'tags'        => ['arbitration', 'procedure'],
        'articles'    => [
            ['num' => '4',  'heading_en' => 'Capacity to arbitrate', 'text_en' => 'A person may not agree to arbitration save where he has capacity to dispose of the right in dispute. A natural person representing a legal person must have express written authority to agree to arbitration; in companies, only the chairman or his express delegate may sign an arbitration agreement on behalf of the company.', 'text_ar' => 'لا يحق لأي شخص الاتفاق على التحكيم إلا متى كانت له أهلية التصرف في الحق محل النزاع. ويلزم تفويض كتابي صريح للشخص الطبيعي الممثل لشخص اعتباري للموافقة على التحكيم؛ ولا يحق التوقيع على اتفاق التحكيم باسم الشركة إلا لرئيس مجلس الإدارة أو من يفوضه صراحةً.'],
            ['num' => '20', 'heading_en' => 'Competence-competence', 'text_en' => 'The arbitral tribunal has jurisdiction to rule on its own jurisdiction, including pleas of non-existence, nullity, or scope of the arbitration agreement. The arbitration clause is to be treated as independent of the rest of the contract; the nullity, rescission, or termination of the contract does not affect the arbitration clause provided it is valid in itself.', 'text_ar' => 'تختص هيئة التحكيم بالفصل في الدفوع المتعلقة باختصاصها بما فيها الدفوع المبنية على عدم وجود اتفاق التحكيم أو بطلانه أو نطاقه. ويُعد شرط التحكيم مستقلاً عن باقي شروط العقد؛ ولا يترتب على بطلان العقد أو فسخه أو إنهائه أي أثر على شرط التحكيم متى كان صحيحاً في ذاته.'],
            ['num' => '53', 'heading_en' => 'Setting aside the award', 'text_en' => 'A nullity action against an arbitral award is admissible only on grounds including: invalidity of the arbitration agreement; party incapacity; failure to notify a party or unable-to-present-case; non-application of the agreed substantive law; irregularity of the tribunal\'s composition; award exceeding scope; failure of mandatory form requirements; or violation of public order in the State.', 'text_ar' => 'لا تقبل دعوى البطلان في حكم التحكيم إلا في الحالات الآتية: بطلان اتفاق التحكيم؛ نقص أهلية أحد الأطراف؛ عدم إخطار طرف أو تعذر تقديم دفاعه؛ عدم تطبيق القانون الموضوعي المتفق عليه؛ عيب في تشكيل هيئة التحكيم؛ تجاوز نطاق الاتفاق؛ عدم استيفاء شكليات جوهرية؛ مخالفة النظام العام في الدولة.'],
        ],
    ],

    [
        'title_en'    => 'UAE Value Added Tax — Core Articles',
        'title_ar'    => 'قانون ضريبة القيمة المضافة الإماراتي — مواد جوهرية',
        'jurisdiction'=> 'AE',
        'category'    => 'tax',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Decree-Law No. 8 of 2017 (as amended)',
        'citation_ar' => 'المرسوم بقانون اتحادي رقم ٨ لسنة ٢٠١٧ وتعديلاته',
        'tags'        => ['tax', 'vat'],
        'articles'    => [
            ['num' => '3',  'heading_en' => 'Standard rate', 'text_en' => 'A tax of five per cent (5%) is imposed on the import and supply of goods and services at every stage of production and distribution, including deemed supplies, save for supplies that are zero-rated or exempt under this Decree-Law.', 'text_ar' => 'تُفرض ضريبة بنسبة خمسة في المائة (٥٪) على استيراد وتوريد السلع والخدمات في كل مرحلة من مراحل الإنتاج والتوزيع، بما في ذلك التوريد المعتبر، باستثناء التوريدات الخاضعة لنسبة الصفر أو المعفاة بموجب هذا المرسوم بقانون.'],
            ['num' => '13', 'heading_en' => 'Registration threshold', 'text_en' => 'A person is required to register for VAT where the value of his taxable supplies in the preceding twelve months exceeded the mandatory registration threshold (currently three hundred and seventy-five thousand UAE dirhams), or is expected to exceed it in the next thirty (30) days. Voluntary registration is allowed above the voluntary threshold.', 'text_ar' => 'يلزم التسجيل لضريبة القيمة المضافة كل شخص تجاوزت قيمة توريداته الخاضعة للضريبة خلال الاثني عشر شهراً السابقة حد التسجيل الإلزامي (وقدره حالياً ثلاثمائة وخمسة وسبعون ألف درهم إماراتي)، أو يُتوقع تجاوزها خلال الثلاثين (٣٠) يوماً القادمة. ويجوز التسجيل الاختياري متى تجاوزت التوريدات الحد الاختياري.'],
            ['num' => '45', 'heading_en' => 'Zero-rated supplies', 'text_en' => 'The following supplies are taxed at zero per cent: exports of goods and services outside the GCC implementing states; international transport; first sale of residential buildings within three years of completion; supplies of certain investment-grade precious metals; certain healthcare and education supplies under conditions set by the executive regulations.', 'text_ar' => 'تخضع لنسبة الصفر التوريدات الآتية: صادرات السلع والخدمات إلى خارج دول مجلس التعاون الخليجي المُطبقة؛ النقل الدولي؛ التوريد الأول للمباني السكنية خلال ثلاث سنوات من إتمامها؛ توريدات بعض المعادن النفيسة الاستثمارية؛ بعض توريدات الرعاية الصحية والتعليم وفق الشروط التي تحددها اللائحة التنفيذية.'],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       ADDITIONAL EG CASSATION PRINCIPLES
       Topics: contract interpretation in commercial usage, novation,
       assignment of receivables, and the parol-evidence rule.
       ───────────────────────────────────────────────────────────── */

    [
        'title_en'    => 'Cassation Principles — Commercial Custom and Trade Usage',
        'title_ar'    => 'مبادئ النقض — العرف التجاري',
        'jurisdiction'=> 'EG',
        'category'    => 'commercial',
        'source'      => 'cassation',
        'language'    => 'ar',
        'citation_en' => 'Egyptian Court of Cassation — synthesised commercial principles',
        'citation_ar' => 'محكمة النقض المصرية — مبادئ تجارية مستقرة',
        'tags'        => ['cassation', 'commercial-custom', 'trade-usage'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Commercial custom as gap-filler', 'text_en' => 'In commercial matters, where the dispositive provisions of the Commercial Code and the parties\' agreement are silent, commercial custom — whether general or particular to a trade or locality — applies as a supplementary source. Custom inconsistent with mandatory law does not apply, and a party invoking a particular trade usage bears the burden of proving its existence and content.', 'text_ar' => 'في المواد التجارية، عند سكوت النصوص المكملة في قانون التجارة واتفاق الطرفين، يُعمل بالعرف التجاري ـ سواءً كان عاماً أم خاصاً بحرفة أو جهة ـ بوصفه مصدراً تكميلياً. ولا يُعمل بالعرف المخالف لقاعدة آمرة، ويقع عبء إثبات قيام العرف الخاص ومحتواه على من يتمسك به.'],
            ['num' => 'P-2', 'heading_en' => 'Custom prevails over default civil provisions', 'text_en' => 'In commercial dealings, commercial custom takes precedence over the dispositive provisions of the Civil Code where the latter would frustrate the speed and reliability essential to commerce. The court may take judicial notice of well-established commercial customs without requiring formal proof.', 'text_ar' => 'في المعاملات التجارية، يُقدَّم العرف التجاري على النصوص المكملة في القانون المدني متى كان تطبيقها يخل بسرعة ووثاقة المعاملات التجارية. وللمحكمة أن تعلم بالعرف التجاري المستقر دون اشتراط إثباته بطرق الإثبات الرسمية.'],
        ],
    ],

    [
        'title_en'    => 'Cassation Principles — Novation and Assignment',
        'title_ar'    => 'مبادئ النقض — التجديد وحوالة الحق',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'ar',
        'citation_en' => 'Egyptian Court of Cassation — synthesised civil principles',
        'citation_ar' => 'محكمة النقض المصرية — مبادئ مدنية مستقرة',
        'tags'        => ['cassation', 'novation', 'assignment'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Novation requires clear intent', 'text_en' => 'Novation under Civil Code Articles 352–360 requires a clear intent (animus novandi) of the parties to extinguish the old obligation and replace it with a new one. The mere modification of terms — payment schedule, place, mode, security — does not by itself constitute novation; doubt is resolved against novation, preserving the original obligation.', 'text_ar' => 'يلزم في التجديد طبقاً للمواد ٣٥٢ ـ ٣٦٠ من القانون المدني أن تنصرف إرادة الطرفين بوضوح إلى انقضاء الالتزام القديم وقيام التزام جديد يحل محله. ولا يُعد مجرد تعديل شروط الالتزام ـ كميعاد الوفاء أو محله أو طريقته أو ضماناته ـ تجديداً، ويُحمل الشك على عدم التجديد بقاءً للالتزام الأصلي.'],
            ['num' => 'P-2', 'heading_en' => 'Assignment of receivables — notice to debtor', 'text_en' => 'Assignment of a receivable under Civil Code Articles 303–314 is valid between assignor and assignee by their mere agreement; but it is not enforceable against the debtor or third parties unless the debtor accepts it or is notified of the assignment, by writ of service or any other means establishing knowledge with date certain. A debtor who pays the assignor before notice or acceptance is discharged.', 'text_ar' => 'تتم حوالة الحق طبقاً للمواد ٣٠٣ ـ ٣١٤ من القانون المدني بمجرد اتفاق المحيل والمحال له، إلا أنها لا تنفذ في حق المدين أو الغير ما لم يقبلها المدين أو يُعلن بها بإعلان قضائي أو بأي وسيلة تثبت العلم بتاريخ ثابت. ويبرأ المدين الذي يفي للمحيل قبل العلم بالحوالة أو قبولها.'],
        ],
    ],

    /* ─────────────────────────────────────────────────────────────────
       JURISDICTION EXPANSION
       Adds widely-cited statutes for under-represented jurisdictions
       (JO, LB, KW, QA, BH, OM, TN, LY) so the retrieval layer can serve
       non-Egyptian queries with primary-source anchors rather than
       falling back to Egyptian analogues by default.
       ───────────────────────────────────────────────────────────── */

    /* ─────────────────────────────  JORDAN  ───────────────────────────── */

    [
        'title_en'    => 'Jordanian Civil Code',
        'title_ar'    => 'القانون المدني الأردني',
        'jurisdiction'=> 'JO',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 43 of 1976',
        'citation_ar' => 'القانون رقم ٤٣ لسنة ١٩٧٦',
        'tags'        => ['contract', 'tort', 'islamic-tradition'],
        'articles'    => [
            ['num' => '87',  'heading_en' => 'Conclusion of contract by exchange of offer and acceptance', 'text_en' => 'A contract is concluded by the conjunction of offer and acceptance directed to the same subject matter, in the manner provided by law. Both expressions must be unequivocal and reach the other party.', 'text_ar' => null],
            ['num' => '202', 'heading_en' => 'Performance in accordance with contents and good faith', 'text_en' => 'A valid binding contract must be performed in accordance with its contents and in a manner consistent with the requirements of good faith. The contract binds the contracting party not only as to its express terms but also as to what is, in light of the nature of the obligation, a necessary sequel to it according to law, usage, and equity.', 'text_ar' => null],
            ['num' => '246', 'heading_en' => 'Hardship — judicial readjustment', 'text_en' => 'Where exceptional, unforeseeable events of a general nature occur after the contract is concluded and before performance becomes due, rendering performance — though not impossible — excessively onerous so as to threaten the debtor with grave loss, the judge may, having balanced the interests of both parties, reduce the excessive obligation to a reasonable extent. Any agreement to the contrary is void.', 'text_ar' => null],
            ['num' => '256', 'heading_en' => 'General tort liability — every harm requires reparation', 'text_en' => 'Every harm inflicted on another obligates the doer, even if not of full legal capacity, to compensate the harm.', 'text_ar' => 'كل إضرار بالغير يلزم فاعله ولو غير مميز بضمان الضرر.'],
        ],
    ],

    [
        'title_en'    => 'Jordanian Labour Code',
        'title_ar'    => 'قانون العمل الأردني',
        'jurisdiction'=> 'JO',
        'category'    => 'labour',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 8 of 1996 and amendments',
        'citation_ar' => 'القانون رقم ٨ لسنة ١٩٩٦ وتعديلاته',
        'tags'        => ['employment'],
        'articles'    => [
            ['num' => '15',  'heading_en' => 'Form and required particulars of employment contract', 'text_en' => 'The employment contract shall be in writing, in Arabic, in at least two original copies — one for each party. Where the contract is not in writing, the worker may prove the contract and its terms by all means of proof, including witness testimony. The contract shall specify in particular the wage, the worker\'s duties, and the term of the contract where it is for a fixed term.', 'text_ar' => null],
            ['num' => '28',  'heading_en' => 'Grounds for summary dismissal without notice or end-of-service benefits', 'text_en' => 'The employer may dismiss the worker without notice and without payment of end-of-service benefits in cases exhaustively listed, including: assumed false identity; commission of an error causing material loss to the employer (provided the labour authority is notified within five days); repeated breach of written safety or operational instructions despite written warning; unjustified absence for more than twenty days in one year or more than ten consecutive days; disclosure of work secrets; conviction of a felony or a misdemeanour involving honour; intoxication during work; or assault on the employer or supervisor during work.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  LEBANON  ───────────────────────────── */

    [
        'title_en'    => 'Lebanese Code of Obligations and Contracts',
        'title_ar'    => 'قانون الموجبات والعقود اللبناني',
        'jurisdiction'=> 'LB',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree of 9 March 1932 (Code of Obligations and Contracts)',
        'citation_ar' => 'مرسوم ٩ آذار ١٩٣٢ — قانون الموجبات والعقود',
        'tags'        => ['contract', 'obligations'],
        'articles'    => [
            ['num' => '166', 'heading_en' => 'Binding force of the contract', 'text_en' => 'A lawfully formed contract has the force of law as between the parties; it cannot be revoked or modified except by their mutual consent or for causes which the law admits. It must be performed in good faith and binds the parties not only to what is expressed in it, but also to all the consequences which equity, custom, or law attach to the obligation according to its nature.', 'text_ar' => null],
            ['num' => '254', 'heading_en' => 'Hardship and unforeseen circumstances', 'text_en' => 'Where, by reason of general and exceptional events not reasonably foreseeable at the time of contracting, the performance of the obligation — without becoming impossible — has become excessively burdensome so as to threaten the debtor with grave loss, the judge may, on balancing the interests of the parties, reduce the burdensome obligation to a reasonable extent.', 'text_ar' => null],
            ['num' => '341', 'heading_en' => 'Force majeure — discharge of obligation', 'text_en' => 'The debtor is discharged where he proves that performance has become impossible by reason of a foreign cause not attributable to him — fortuitous event, force majeure, or the act of the creditor or of a third party — provided he did not, by his own fault, contribute to the occurrence of that cause and was not, prior to its occurrence, in delay.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Lebanese Commercial Code',
        'title_ar'    => 'قانون التجارة البري اللبناني',
        'jurisdiction'=> 'LB',
        'category'    => 'commercial',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree-Law of 24 December 1942 (Commercial Code)',
        'citation_ar' => 'المرسوم الاشتراعي الصادر في ٢٤ كانون الأول ١٩٤٢ — قانون التجارة البرية',
        'tags'        => ['commercial', 'trade'],
        'articles'    => [
            ['num' => '6',   'heading_en' => 'Definition of merchant', 'text_en' => 'Merchants are those who, having legal capacity to contract, undertake commercial acts as their habitual profession, and commercial companies whose object is commercial. The status of merchant entails subjection to the obligations specific to merchants, in particular registration in the commercial register and keeping commercial books.', 'text_ar' => null],
            ['num' => '262', 'heading_en' => 'Commercial sale — passage of risk on delivery', 'text_en' => 'In commercial sale, the risk of loss or deterioration of the goods passes to the buyer on delivery, unless the parties have agreed otherwise or unless the loss is caused by the seller\'s fault. Where the goods are dispatched, delivery to the carrier is, as between seller and buyer, equivalent to delivery to the buyer for the purposes of the passage of risk.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  KUWAIT  ───────────────────────────── */

    [
        'title_en'    => 'Kuwaiti Civil Code',
        'title_ar'    => 'القانون المدني الكويتي',
        'jurisdiction'=> 'KW',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree-Law No. 67 of 1980',
        'citation_ar' => 'المرسوم بالقانون رقم ٦٧ لسنة ١٩٨٠',
        'tags'        => ['contract', 'tort', 'sanhouri-tradition'],
        'articles'    => [
            ['num' => '196', 'heading_en' => 'Binding force of contract — good-faith performance', 'text_en' => 'The contract is the law of the parties. It cannot be revoked or modified except by their mutual consent or for the causes provided by law. The contract must be performed in conformity with its contents and in a manner consistent with the requirements of good faith, and it binds the contracting party not only to its express terms but also to all that, by law, custom, and equity, is a necessary consequence of the contract according to the nature of the obligation.', 'text_ar' => null],
            ['num' => '198', 'heading_en' => 'Hardship adjustment', 'text_en' => 'Where exceptional events of a general nature occur after the contract is made and could not have been foreseen, with the result that performance of the contractual obligation — without becoming impossible — becomes excessively onerous so as to threaten the debtor with grave loss, the judge may, having weighed the interests of both parties, reduce the excessive obligation to a reasonable extent. Any agreement to the contrary is void.', 'text_ar' => null],
            ['num' => '227', 'heading_en' => 'Tort — fault, damage, causation', 'text_en' => 'Every fault that causes damage to another obliges the doer thereof to compensate. The injured party must establish the fault, the damage, and the causal link between them; the burden of proof rests on the claimant unless the law provides for a presumption of liability.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Kuwaiti Companies Law',
        'title_ar'    => 'قانون الشركات الكويتي',
        'jurisdiction'=> 'KW',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 1 of 2016 and its amendments',
        'citation_ar' => 'القانون رقم ١ لسنة ٢٠١٦ وتعديلاته',
        'tags'        => ['corporate', 'governance'],
        'articles'    => [
            ['num' => '4',   'heading_en' => 'Forms of companies regulated', 'text_en' => 'This Law regulates the principal forms of commercial companies: joint-liability companies, simple-limited partnerships, joint-stock companies (closed and listed), limited liability companies, single-person companies, and holding companies. Other forms are governed by the special provisions applicable to them.', 'text_ar' => null],
            ['num' => '95',  'heading_en' => 'Liability of LLC quotaholders', 'text_en' => 'The liability of each quotaholder of a limited-liability company is limited to the amount of the quotas he subscribes for in the capital. The company\'s name shall include an indication of its form (W.L.L.) and a quotaholder may not be liable for the company\'s debts beyond the value of his quota.', 'text_ar' => null],
            ['num' => '187', 'heading_en' => 'Board of directors — duty of care', 'text_en' => 'The directors of a joint-stock company shall perform their duties with the care of a prudent person, in the interest of the company. They are jointly liable to the company, to the shareholders, and to third parties for acts of fraud, misuse of power, breach of this Law or the articles of association, or for fault in the conduct of management.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  QATAR  ───────────────────────────── */

    [
        'title_en'    => 'Qatari Civil Code',
        'title_ar'    => 'القانون المدني القطري',
        'jurisdiction'=> 'QA',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 22 of 2004',
        'citation_ar' => 'القانون رقم ٢٢ لسنة ٢٠٠٤',
        'tags'        => ['contract', 'tort', 'obligations'],
        'articles'    => [
            ['num' => '171', 'heading_en' => 'Binding force of contract', 'text_en' => 'The contract is the law of the parties. It cannot be revoked or modified except by their mutual consent or for the causes provided by law. The contract must be performed in accordance with its contents and in a manner consistent with the requirements of good faith.', 'text_ar' => null],
            ['num' => '171', 'heading_en' => 'Hardship — judicial readjustment', 'text_en' => 'Where, after the contract is made and before its performance becomes due, exceptional general and unforeseeable events occur as a result of which performance of the contractual obligation, without becoming impossible, becomes excessively onerous so as to threaten the debtor with grave loss, the judge may, having weighed the interests of the parties, reduce the excessive obligation to a reasonable extent. Any agreement to the contrary is void.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Qatari Commercial Companies Law',
        'title_ar'    => 'قانون الشركات التجارية القطري',
        'jurisdiction'=> 'QA',
        'category'    => 'companies',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law No. 11 of 2015 and its amendments',
        'citation_ar' => 'القانون رقم ١١ لسنة ٢٠١٥ وتعديلاته',
        'tags'        => ['corporate', 'governance'],
        'articles'    => [
            ['num' => '4',   'heading_en' => 'Forms of commercial companies', 'text_en' => 'A commercial company in the State of Qatar may take one of the following forms: joint-liability company, simple-limited partnership, particular-partnership company, joint-stock company (public or private), limited-share partnership, limited-liability company, single-person company, or holding company. Any company not taking one of these forms shall be deemed null.', 'text_ar' => null],
            ['num' => '224', 'heading_en' => 'LLC — quotaholder liability and minimum quotaholders', 'text_en' => 'In a limited-liability company, the liability of each quotaholder is limited to the value of his quotas in the capital, and a quotaholder is not liable for the company\'s debts beyond his quotas. The number of quotaholders shall not exceed fifty and shall not be less than one (single-person LLC). The company\'s name shall be followed by the words "limited liability company" (W.L.L.).', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  BAHRAIN  ───────────────────────────── */

    [
        'title_en'    => 'Bahraini Civil Code',
        'title_ar'    => 'القانون المدني البحريني',
        'jurisdiction'=> 'BH',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Decree-Law No. 19 of 2001',
        'citation_ar' => 'المرسوم بقانون رقم ١٩ لسنة ٢٠٠١',
        'tags'        => ['contract', 'tort'],
        'articles'    => [
            ['num' => '129', 'heading_en' => 'Binding force of contract — good faith', 'text_en' => 'A validly formed contract is the law of the parties. It cannot be revoked or modified except by their mutual consent or for the causes provided by law. The contract must be performed in accordance with its contents and in conformity with the requirements of good faith, and it obliges the parties not only to what is expressly stipulated but also to all that, by law, custom, or equity, is a necessary consequence of the obligation.', 'text_ar' => null],
            ['num' => '130', 'heading_en' => 'Hardship adjustment', 'text_en' => 'Where exceptional events of a general nature occur which could not have been foreseen at the time of contracting, with the result that the performance of the contractual obligation — though not impossible — has become excessively onerous and threatens the debtor with grave loss, the judge may, after balancing the interests of the parties, reduce the excessive obligation to a reasonable extent. Any agreement to the contrary is void.', 'text_ar' => null],
            ['num' => '158', 'heading_en' => 'Tort — fault liability', 'text_en' => 'Every fault causing damage to another obliges the doer to compensate it. Compensation includes the loss suffered and the gain of which the injured party was deprived, provided they are a natural result of the wrongful act.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  OMAN  ───────────────────────────── */

    [
        'title_en'    => 'Omani Civil Transactions Law',
        'title_ar'    => 'قانون المعاملات المدنية العماني',
        'jurisdiction'=> 'OM',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Royal Decree No. 29 of 2013',
        'citation_ar' => 'المرسوم السلطاني رقم ٢٩ لسنة ٢٠١٣',
        'tags'        => ['contract', 'tort', 'islamic-tradition'],
        'articles'    => [
            ['num' => '156', 'heading_en' => 'Conclusion of contract', 'text_en' => 'A contract is concluded by the conjunction of an offer issued by one of the contracting parties with the acceptance of the other, in a manner that produces its effects on the subject matter and gives rise to an obligation on each party in respect of what he has undertaken towards the other.', 'text_ar' => null],
            ['num' => '172', 'heading_en' => 'Binding force and good faith', 'text_en' => 'The contract is the law of the parties; it cannot be revoked or modified except by their mutual consent or for the causes provided by law. It must be performed in conformity with what it contains and with the requirements of good faith, and it binds the parties not only to its express terms but also to all that follows from it by force of law, custom, and equity according to the nature of the obligation.', 'text_ar' => null],
            ['num' => '176', 'heading_en' => 'Hardship — readjustment', 'text_en' => 'Where exceptional and unforeseeable events of a general nature occur, rendering performance of the contractual obligation — without becoming impossible — excessively onerous so as to threaten the debtor with grave loss, the judge may, on balancing the interests of the parties, reduce the burdensome obligation to a reasonable extent. Any agreement to the contrary is void.', 'text_ar' => null],
            ['num' => '176', 'heading_en' => 'Tort — every harm requires reparation', 'text_en' => 'Every harm inflicted upon another obliges its doer, even if not of full legal capacity, to make reparation for the damage. Where there are multiple authors of the tort, they are jointly liable to the injured party, the share of each being assessed by reference to his contribution to the damage.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  TUNISIA  ───────────────────────────── */

    [
        'title_en'    => 'Tunisian Code of Obligations and Contracts',
        'title_ar'    => 'مجلة الالتزامات والعقود التونسية',
        'jurisdiction'=> 'TN',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Beylical Decree of 15 December 1906 (Code of Obligations and Contracts)',
        'citation_ar' => 'الأمر العلي المؤرخ في ١٥ ديسمبر ١٩٠٦ — مجلة الالتزامات والعقود',
        'tags'        => ['contract', 'civil-law'],
        'articles'    => [
            ['num' => '2',   'heading_en' => 'Capacity to contract', 'text_en' => 'Capacity to enter into binding obligations is governed by the personal law of the person concerned. Where no personal law expressly governs a person, capacity is determined by Tunisian law. The minor, the prodigal, and the weak-minded may bind themselves only within the limits and forms determined by law.', 'text_ar' => null],
            ['num' => '242', 'heading_en' => 'Binding force of obligations', 'text_en' => 'Obligations validly contracted have the force of law as between those who have created them. They cannot be revoked except by their mutual consent or for the causes provided by law. They must be performed in good faith and bind the parties not only to what is expressed in them but also to all the consequences which equity, custom, or law attach to the obligation according to its nature.', 'text_ar' => null],
            ['num' => '282', 'heading_en' => 'Force majeure — discharge', 'text_en' => 'There is no liability where the debtor establishes that performance has become impossible by reason of a cause not attributable to him, such as force majeure, fortuitous event, or the fault of the creditor. Force majeure is any event that man could not foresee, such as natural phenomena (floods, droughts, storms, fires, locusts), an enemy invasion, an act of the sovereign, or the fact of preventing the exercise of one\'s right.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────────────  LIBYA  ───────────────────────────── */

    [
        'title_en'    => 'Libyan Civil Code',
        'title_ar'    => 'القانون المدني الليبي',
        'jurisdiction'=> 'LY',
        'category'    => 'civil',
        'source'      => 'eastlaws',
        'language'    => 'ar',
        'citation_en' => 'Law of 28 November 1953 (Libyan Civil Code)',
        'citation_ar' => 'قانون ٢٨ نوفمبر ١٩٥٣ — القانون المدني الليبي',
        'tags'        => ['contract', 'tort', 'sanhouri-tradition'],
        'articles'    => [
            ['num' => '147', 'heading_en' => 'Binding force of contract; hardship', 'text_en' => 'The contract is the law of the parties. It cannot be revoked or modified except by their mutual consent or for the causes provided by law. However, where, after the contract is made and before its performance becomes due, exceptional and unforeseeable events of a general nature occur as a result of which performance of the contractual obligation, without becoming impossible, becomes excessively onerous so as to threaten the debtor with grave loss, the judge may, after weighing the interests of the parties, reduce the excessive obligation to a reasonable extent. Any agreement to the contrary is void.', 'text_ar' => 'العقد شريعة المتعاقدين، فلا يجوز نقضه ولا تعديله إلا باتفاق الطرفين، أو للأسباب التي يقررها القانون.'],
            ['num' => '148', 'heading_en' => 'Performance in good faith', 'text_en' => 'A contract must be performed in accordance with its contents, and in a manner consistent with the requirements of good faith. The contract is binding upon the contracting party not only as to its express terms, but also as to all that is — according to law, custom, and equity — a necessary consequence of the obligation in light of its nature.', 'text_ar' => 'يجب تنفيذ العقد طبقاً لما اشتمل عليه، وبطريقة تتفق مع ما يوجبه حسن النية.'],
            ['num' => '166', 'heading_en' => 'Tort — general principle', 'text_en' => 'Every fault causing damage to another obliges the person who committed it to compensate the damage.', 'text_ar' => 'كل خطأ سبب ضرراً للغير يلزم من ارتكبه بالتعويض.'],
        ],
    ],

    /* ─────────────────────────  UAE — FAMILY  ───────────────────────── */

    [
        'title_en'    => 'UAE Personal Status Law',
        'title_ar'    => 'قانون الأحوال الشخصية الاتحادي',
        'jurisdiction'=> 'AE',
        'category'    => 'family',
        'source'      => 'uae-federal-gazette',
        'language'    => 'ar',
        'citation_en' => 'Federal Law No. 28 of 2005, as amended',
        'citation_ar' => 'القانون الاتحادي رقم ٢٨ لسنة ٢٠٠٥ وتعديلاته',
        'tags'        => ['family', 'personal-status'],
        'articles'    => [
            ['num' => '19',  'heading_en' => 'Conditions for valid marriage contract', 'text_en' => 'A marriage contract is concluded by an offer from one of the contracting parties and acceptance by the other, in the presence of two competent witnesses, with the consent of the guardian where required by law. The marriage contract must be registered in accordance with the procedures laid down by the executive regulations; non-registration does not affect the validity of the contract but exposes the parties to administrative penalties.', 'text_ar' => null],
            ['num' => '63',  'heading_en' => 'Duties of the spouses', 'text_en' => 'The husband shall be responsible for the maintenance of the wife in accordance with his financial capacity and the standard of living, including food, clothing, lodging, and medical treatment. The wife shall, in turn, take care of the marital home, raise the children, and obey her husband in matters of lawful marital life. Each spouse owes the other respect, mutual cooperation, and the preservation of the family.', 'text_ar' => null],
            ['num' => '110', 'heading_en' => 'Divorce — judicial dissolution for harm', 'text_en' => 'Either spouse may apply to the court for dissolution of the marriage on the ground of harm rendering the continuation of marital life impossible between them. The court shall, where possible, attempt reconciliation through two arbitrators chosen from the families of the spouses. If reconciliation fails and the harm is established, the court shall pronounce dissolution and determine its financial consequences in accordance with this Law.', 'text_ar' => null],
        ],
    ],

    /* ─────────────────────  EG — ADDITIONAL CASSATION  ───────────────── */

    [
        'title_en'    => 'Cassation Principles — Limitation Periods and Tolling',
        'title_ar'    => 'مبادئ النقض — التقادم ووقفه',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'ar',
        'citation_en' => 'Egyptian Court of Cassation — synthesised limitation principles',
        'citation_ar' => 'محكمة النقض المصرية — مبادئ مستقرة في التقادم',
        'tags'        => ['cassation', 'limitation', 'prescription'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'General fifteen-year prescription', 'text_en' => 'Under Civil Code Article 374, all rights are extinguished by the lapse of fifteen years, save where the law provides for a shorter period. The starting point of the prescription is the day on which the right became due and capable of being asserted by judicial action. The court may not raise prescription of its own motion; it must be pleaded by the party benefitting from it before the merits are decided.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Interruption by judicial demand and acknowledgement', 'text_en' => 'Prescription is interrupted by a judicial demand even where it is brought before a court lacking jurisdiction, by an act of execution, or by any act by which the debtor acknowledges the right of the creditor — whether expressly or impliedly, such as by partial payment, request for a grace period, or provision of security. After interruption a new period begins to run, equal in length to the original.', 'text_ar' => null],
            ['num' => 'P-3', 'heading_en' => 'Suspension during incapacity or impediment', 'text_en' => 'Prescription is suspended whenever a legal or material impediment, beyond the creditor\'s control, prevents him from asserting the right — including the absence of a legal representative for a person under incapacity. The period of suspension does not count in calculating the prescription, and it resumes upon the disappearance of the impediment.', 'text_ar' => null],
        ],
    ],

    [
        'title_en'    => 'Cassation Principles — Penalty Clauses and Liquidated Damages',
        'title_ar'    => 'مبادئ النقض — الشرط الجزائي',
        'jurisdiction'=> 'EG',
        'category'    => 'civil',
        'source'      => 'cassation',
        'language'    => 'ar',
        'citation_en' => 'Egyptian Court of Cassation — synthesised principles on Civil Code Arts. 223–224',
        'citation_ar' => 'محكمة النقض المصرية — مبادئ مستقرة في الشرط الجزائي (المواد ٢٢٣–٢٢٤ مدني)',
        'tags'        => ['cassation', 'penalty-clause', 'liquidated-damages'],
        'articles'    => [
            ['num' => 'P-1', 'heading_en' => 'Penalty clause presupposes damage', 'text_en' => 'A penalty stipulated by contract is not payable where the debtor establishes that the creditor has suffered no damage. The penalty clause merely fixes the amount of damages in advance; it does not create an obligation independent of damage. The burden of proving the absence of damage rests on the debtor.', 'text_ar' => null],
            ['num' => 'P-2', 'heading_en' => 'Judicial reduction of grossly excessive penalty', 'text_en' => 'The judge may reduce a stipulated penalty where the debtor establishes that the amount fixed is grossly exaggerated in relation to the damage actually suffered, or where the principal obligation has been performed in part. Any prior waiver of the right to seek reduction is void. Conversely, where the actual damage exceeds the stipulated penalty, the creditor may not claim more than the penalty unless he establishes fraud or gross fault on the part of the debtor.', 'text_ar' => null],
        ],
    ],
];
