<?php

/*
|--------------------------------------------------------------------------
| Jurisdiction Coverage Status
|--------------------------------------------------------------------------
|
| Drives the "Preview" / "Beta" / "Live" badges across marketing pages.
| As we get a jurisdiction reviewed by a licensed local lawyer, lift its
| status here.
|
| Levels:
|   live    — Lawyer-reviewed corpus, citation accuracy ≥ 95%, ready for
|             paying customers. Hero claims fully apply.
|   beta    — Corpus is comprehensive but not yet locally-reviewed.
|             Marketing copy carries a "beta" advisory.
|   preview — Corpus is structured and growing. Marketing copy carries a
|             "preview" advisory and steers users to live jurisdictions
|             for actual drafting.
*/

return [

    'levels' => [
        'live' => [
            'label_en' => 'Live',
            'label_ar' => 'متاحة',
            'short_en' => 'LIVE',
            'short_ar' => 'متاحة',
            'tone' => 'green',
            'note_en' => 'Lawyer-reviewed corpus. Citation accuracy verified. Ready for production use.',
            'note_ar' => 'مصادر قانونية مراجعة من محامين مرخصين. دقة الاستشهادات موثقة. جاهزة للاستخدام الفعلي.',
        ],
        'beta' => [
            'label_en' => 'Beta',
            'label_ar' => 'تجريبية',
            'short_en' => 'BETA',
            'short_ar' => 'تجريبية',
            'tone' => 'amber',
            'note_en' => 'Corpus complete but pending local-counsel review. Verify every output before relying on it.',
            'note_ar' => 'المصادر القانونية مكتملة وقيد المراجعة من محامين محليين. يلزم مراجعة كل مخرج قبل الاعتماد عليه.',
        ],
        'preview' => [
            'label_en' => 'Preview',
            'label_ar' => 'معاينة',
            'short_en' => 'PREVIEW',
            'short_ar' => 'معاينة',
            'tone' => 'gray',
            'note_en' => 'Coverage in active development. We recommend using a live jurisdiction for actual drafting.',
            'note_ar' => 'التغطية قيد التطوير. نوصي باستخدام ولاية قضائية متاحة للصياغة الفعلية.',
        ],
    ],

    /*
    | Per-jurisdiction status. Egypt is the launch jurisdiction. As lawyers
    | are onboarded for SA / AE / etc, lift their levels accordingly.
    */
    'jurisdictions' => [
        'EG' => 'live',
        'SA' => 'beta',     // Sanhouri tradition + new 1444H Civil Transactions Law — most familiar
        'AE' => 'beta',     // Federal Law 5/1985 well-documented, but DIFC/ADGM common-law islands need separate review
        'KW' => 'preview',
        'QA' => 'preview',
        'BH' => 'preview',
        'OM' => 'preview',
        'JO' => 'preview',
        'LB' => 'preview',
        'TN' => 'preview',
        'LY' => 'preview',
    ],

    /*
    | The launch jurisdiction. Hero copy and primary calls-to-action
    | center on this jurisdiction. The product still serves all
    | jurisdictions but we set a clear "we're best at X" expectation.
    */
    'primary_jurisdiction' => 'EG',
];
