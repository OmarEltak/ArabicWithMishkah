<?php

/*
|--------------------------------------------------------------------------
| Legal-data source registry
|--------------------------------------------------------------------------
|
| Each entry maps an internal `source` slug stored in legal_documents to:
|   - label_en / label_ar : the user-visible name (never the vendor brand)
|   - kind                : how docs from this source are treated
|                           (`api` | `bulk` | `user`)
|   - jurisdictions       : ISO-2 codes covered by the source
|   - is_official         : whether this is a primary statutory source
|                           (drives "verified" UI badges)
|
| The internal `source` value remains a stable slug so existing snapshots,
| jobs, and tests do not break. The label layer is what users see.
|
| To add a new data source:
|   1) Add an entry here with a unique slug
|   2) Run snapshots writing source = '<slug>' into legal_documents
|   3) Optional: add an Ingestion service for the new source under
|      App\Services\Ingestion (the existing EastlawsIngestService is one
|      example; future sources slot in alongside it).
*/

return [

    'sources' => [

        // Primary statutory feed (Sanhouri-tradition civil codes + companies + labour).
        // The vendor identity is hidden from users by design.
        'eastlaws' => [
            'label_en' => 'Official Legal Database',
            'label_ar' => 'قاعدة بيانات قانونية رسمية',
            'short_en' => 'Statutory feed',
            'short_ar' => 'مصدر قانوني',
            'kind' => 'api',
            'is_official' => true,
            'jurisdictions' => ['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'LB', 'TN', 'LY'],
        ],

        // Saudi-specific bulletin (slot for an expanded Saudi feed).
        'sa-gazette' => [
            'label_en' => 'Official Gazette (Umm al-Qura)',
            'label_ar' => 'الجريدة الرسمية (أم القرى)',
            'short_en' => 'Gazette',
            'short_ar' => 'جريدة رسمية',
            'kind' => 'bulk',
            'is_official' => true,
            'jurisdictions' => ['SA'],
        ],

        // UAE-specific feed (DIFC / ADGM common-law island courts and
        // the Federal Gazette for federal laws).
        'uae-federal-gazette' => [
            'label_en' => 'UAE Federal Gazette',
            'label_ar' => 'الجريدة الرسمية الاتحادية',
            'short_en' => 'Federal Gazette',
            'short_ar' => 'الجريدة الاتحادية',
            'kind' => 'bulk',
            'is_official' => true,
            'jurisdictions' => ['AE'],
        ],

        // Egypt-specific bulletin slot (Al-Waqa'i' al-Misriyya).
        'eg-waqa' => [
            'label_en' => 'Egyptian Official Gazette',
            'label_ar' => 'الوقائع المصرية',
            'short_en' => 'Gazette',
            'short_ar' => 'الوقائع',
            'kind' => 'bulk',
            'is_official' => true,
            'jurisdictions' => ['EG'],
        ],

        // Court-decisions feed slot (cassation rulings + administrative
        // court precedents that interpret the codes).
        'cassation' => [
            'label_en' => 'Cassation & Cassation-Equivalent Rulings',
            'label_ar' => 'أحكام محاكم النقض والمحاكم العليا',
            'short_en' => 'Court rulings',
            'short_ar' => 'أحكام قضائية',
            'kind' => 'api',
            'is_official' => true,
            'jurisdictions' => ['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'LB', 'TN', 'LY'],
        ],

        // Regulatory feeds — capital-market authorities, central banks,
        // sectoral regulators (SAMA, CMA, FSRA, DFSA, CBE, etc.).
        'regulator' => [
            'label_en' => 'Regulatory & Sectoral Authorities',
            'label_ar' => 'الهيئات التنظيمية والقطاعية',
            'short_en' => 'Regulator',
            'short_ar' => 'جهة تنظيمية',
            'kind' => 'bulk',
            'is_official' => true,
            'jurisdictions' => ['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'LB', 'TN', 'LY'],
        ],

        // Practical clauses bank — reusable boilerplate the AI can
        // retrieve and adapt instead of inventing wording. Not an
        // official statutory source, but DOES count toward the RAG
        // corpus the drafter searches.
        'clauses-bank' => [
            'label_en' => 'Practical Clauses Bank',
            'label_ar' => 'مكتبة البنود العملية',
            'short_en' => 'Clauses',
            'short_ar' => 'بنود',
            'kind' => 'curated',
            'is_official' => false,
            'jurisdictions' => ['EG', 'SA', 'AE', 'KW', 'QA', 'BH', 'OM', 'JO', 'LB', 'TN', 'LY'],
        ],

        // User-uploaded — kept distinct so we never conflate user files
        // with statutory authority.
        'upload' => [
            'label_en' => 'Uploaded by you',
            'label_ar' => 'مرفوع منك',
            'short_en' => 'Upload',
            'short_ar' => 'مرفوع',
            'kind' => 'user',
            'is_official' => false,
        ],
        'url' => [
            'label_en' => 'Imported from URL',
            'label_ar' => 'مستورد من رابط',
            'short_en' => 'URL',
            'short_ar' => 'رابط',
            'kind' => 'user',
            'is_official' => false,
        ],
        'paste' => [
            'label_en' => 'Pasted text',
            'label_ar' => 'نص ملصق',
            'short_en' => 'Pasted',
            'short_ar' => 'ملصق',
            'kind' => 'user',
            'is_official' => false,
        ],
    ],

    /*
    | Slugs that the marketing pages and counters treat as "official
    | statutory sources" — the count we surface as "Documents indexed"
    | aggregates across all of these. Keep in sync with `is_official`.
    */
    'official_slugs' => [
        'eastlaws',
        'sa-gazette',
        'uae-federal-gazette',
        'eg-waqa',
        'cassation',
        'regulator',
    ],

    /*
    | Default source to use when ingesting new documents in places that
    | haven't been migrated to multi-source yet. Currently the primary
    | statutory feed.
    */
    'default_official' => 'eastlaws',
];
