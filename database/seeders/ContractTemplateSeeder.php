<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContractTemplate;
use Illuminate\Database\Seeder;

class ContractTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach (self::templates() as $row) {
            ContractTemplate::updateOrCreate(
                ['slug' => $row['slug']],
                $row + ['user_id' => null, 'is_system' => true]
            );
        }
    }

    /** @return array<int, array<string, mixed>> */
    private static function templates(): array
    {
        return [
            // ───── Generic English templates ─────
            [
                'slug' => 'mutual-nda-system',
                'name' => 'Mutual Non-Disclosure Agreement',
                'category' => 'nda',
                'jurisdiction' => null,
                'language' => 'en',
                'description' => 'A balanced mutual confidentiality agreement for two business parties.',
                'required_fields' => ['party_a' => 'string', 'party_b' => 'string', 'effective_date' => 'date', 'governing_law' => 'string', 'term_years' => 'number'],
                'body' => self::ndaBody(),
            ],
            [
                'slug' => 'employment-agreement-system',
                'name' => 'Employment Agreement (At-Will)',
                'category' => 'employment',
                'jurisdiction' => null,
                'language' => 'en',
                'description' => 'A general at-will employment agreement template. Replace placeholders and adapt to local labour law.',
                'required_fields' => ['employer' => 'string', 'employee' => 'string', 'job_title' => 'string', 'start_date' => 'date', 'salary' => 'string', 'governing_law' => 'string'],
                'body' => self::employmentBody(),
            ],
            [
                'slug' => 'services-agreement-system',
                'name' => 'Independent Services Agreement',
                'category' => 'services',
                'jurisdiction' => null,
                'language' => 'en',
                'description' => 'Master services agreement for independent contractor / consulting engagements.',
                'required_fields' => ['client' => 'string', 'provider' => 'string', 'effective_date' => 'date', 'scope_summary' => 'string', 'fees' => 'string', 'governing_law' => 'string'],
                'body' => self::servicesBody(),
            ],

            // ───── Egyptian Arabic templates ─────
            [
                'slug' => 'eg-land-sale',
                'name' => 'عقد بيع قطعة أرض (مصري)',
                'category' => 'real-estate',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد بيع قطعة أرض وفقاً للقانون المدني المصري وقانون الشهر العقاري.',
                'required_fields' => ['seller' => 'string', 'buyer' => 'string', 'land_location' => 'string', 'land_area_m2' => 'number', 'price_egp' => 'number', 'effective_date' => 'date'],
                'body' => self::egLandSaleBody(),
            ],
            [
                'slug' => 'eg-residential-lease',
                'name' => 'عقد إيجار سكني (مصري)',
                'category' => 'lease',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد إيجار شقة سكنية وفقاً للقانون المدني المصري والقانون رقم 4 لسنة 1996 وتعديلاته.',
                'required_fields' => ['landlord' => 'string', 'tenant' => 'string', 'unit_address' => 'string', 'monthly_rent_egp' => 'number', 'term_months' => 'number', 'start_date' => 'date'],
                'body' => self::egResidentialLeaseBody(),
            ],
            [
                'slug' => 'eg-employment',
                'name' => 'عقد عمل فردي (مصري)',
                'category' => 'employment',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد عمل فردي وفقاً لقانون العمل المصري رقم 12 لسنة 2003.',
                'required_fields' => ['employer' => 'string', 'employee' => 'string', 'job_title' => 'string', 'start_date' => 'date', 'monthly_salary_egp' => 'number', 'probation_months' => 'number'],
                'body' => self::egEmploymentBody(),
            ],
            [
                'slug' => 'eg-services',
                'name' => 'عقد تقديم خدمات استشارية (مصري)',
                'category' => 'services',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد تقديم خدمات استشارية / مقاول مستقل وفقاً للقانون المدني المصري.',
                'required_fields' => ['client' => 'string', 'provider' => 'string', 'scope_summary' => 'string', 'fees_egp' => 'number', 'effective_date' => 'date'],
                'body' => self::egServicesBody(),
            ],
            [
                'slug' => 'eg-poa',
                'name' => 'توكيل خاص (مصري)',
                'category' => 'power-of-attorney',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'توكيل خاص لمصلحة محددة وفقاً للقانون المدني المصري وقانون التوثيق.',
                'required_fields' => ['principal' => 'string', 'agent' => 'string', 'scope' => 'string', 'effective_date' => 'date'],
                'body' => self::egPoaBody(),
            ],
            [
                'slug' => 'eg-nda',
                'name' => 'اتفاقية عدم إفصاح متبادلة (مصري)',
                'category' => 'nda',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'اتفاقية عدم إفصاح متبادلة وفقاً للقانون المدني المصري.',
                'required_fields' => ['party_a' => 'string', 'party_b' => 'string', 'effective_date' => 'date', 'term_years' => 'number'],
                'body' => self::egNdaBody(),
            ],

            // ───── Saudi Arabia ─────
            [
                'slug' => 'sa-employment',
                'name' => 'عقد عمل (سعودي)',
                'category' => 'employment',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'عقد عمل وفقاً لنظام العمل السعودي رقم م/51 وتعديلاته.',
                'required_fields' => ['employer' => 'string', 'employee' => 'string', 'job_title' => 'string', 'start_date' => 'date', 'monthly_salary_sar' => 'number'],
                'body' => self::saEmploymentBody(),
            ],
            [
                'slug' => 'sa-services',
                'name' => 'عقد تقديم خدمات (سعودي)',
                'category' => 'services',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'عقد تقديم خدمات وفقاً لنظام المعاملات المدنية السعودي م/191.',
                'required_fields' => ['client' => 'string', 'provider' => 'string', 'scope_summary' => 'string', 'fees_sar' => 'number', 'effective_date' => 'date'],
                'body' => self::saServicesBody(),
            ],

            // ───── United Arab Emirates ─────
            [
                'slug' => 'ae-employment',
                'name' => 'عقد عمل (إماراتي)',
                'category' => 'employment',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'عقد عمل محدد المدة وفقاً للمرسوم بقانون اتحادي رقم 33 لسنة 2021.',
                'required_fields' => ['employer' => 'string', 'employee' => 'string', 'job_title' => 'string', 'start_date' => 'date', 'monthly_salary_aed' => 'number', 'term_years' => 'number'],
                'body' => self::aeEmploymentBody(),
            ],
            [
                'slug' => 'ae-services',
                'name' => 'عقد تقديم خدمات (إماراتي ثنائي اللغة)',
                'category' => 'services',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'عقد تقديم خدمات وفقاً لقانون المعاملات المدنية الإماراتي. ثنائي اللغة عربي/إنجليزي.',
                'required_fields' => ['client' => 'string', 'provider' => 'string', 'scope_summary' => 'string', 'fees_aed' => 'number', 'effective_date' => 'date'],
                'body' => self::aeServicesBody(),
            ],

            // ───── Kuwait ─────
            [
                'slug' => 'kw-employment',
                'name' => 'عقد عمل (كويتي)',
                'category' => 'employment',
                'jurisdiction' => 'KW',
                'language' => 'ar',
                'description' => 'عقد عمل وفقاً لقانون العمل في القطاع الأهلي الكويتي رقم 6 لسنة 2010.',
                'required_fields' => ['employer' => 'string', 'employee' => 'string', 'job_title' => 'string', 'start_date' => 'date', 'monthly_salary_kwd' => 'number'],
                'body' => self::kwEmploymentBody(),
            ],

            // ───── Qatar ─────
            [
                'slug' => 'qa-employment',
                'name' => 'عقد عمل (قطري)',
                'category' => 'employment',
                'jurisdiction' => 'QA',
                'language' => 'ar',
                'description' => 'عقد عمل وفقاً لقانون العمل القطري رقم 14 لسنة 2004.',
                'required_fields' => ['employer' => 'string', 'employee' => 'string', 'job_title' => 'string', 'start_date' => 'date', 'monthly_salary_qar' => 'number'],
                'body' => self::qaEmploymentBody(),
            ],

            // ───── Corporate-law templates (high value for companies-focused practice) ─────
            [
                'slug' => 'eg-shareholders-agreement',
                'name' => 'عقد مساهمين / شركاء (مصري)',
                'category' => 'companies',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد مساهمين / اتفاق شركاء بين مؤسسي شركة وفقاً للقانون التجاري المصري وقانون الشركات.',
                'required_fields' => [
                    'company_name' => 'string',
                    'company_form' => 'string',
                    'capital_egp' => 'number',
                    'partner_a' => 'string',
                    'partner_a_share_pct' => 'number',
                    'partner_b' => 'string',
                    'partner_b_share_pct' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egShareholdersBody(),
            ],
            [
                'slug' => 'eg-share-purchase-agreement',
                'name' => 'عقد بيع حصص / أسهم (مصري)',
                'category' => 'companies',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد بيع حصص أو أسهم في شركة قائمة (M&A) وفقاً للقانون التجاري المصري.',
                'required_fields' => [
                    'seller' => 'string',
                    'buyer' => 'string',
                    'target_company' => 'string',
                    'shares_or_units_purchased' => 'string',
                    'purchase_price_egp' => 'number',
                    'closing_date' => 'date',
                ],
                'body' => self::egSharePurchaseBody(),
            ],
            [
                'slug' => 'eg-founders-agreement',
                'name' => 'اتفاق مؤسسين / مشروع مشترك (مصري)',
                'category' => 'companies',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'اتفاق مؤسسين أو مشروع مشترك لتأسيس شركة جديدة، يحدد المساهمات والإدارة والاستحقاق.',
                'required_fields' => [
                    'founder_a' => 'string',
                    'founder_b' => 'string',
                    'project_name' => 'string',
                    'contribution_a' => 'string',
                    'contribution_b' => 'string',
                    'equity_split_a_pct' => 'number',
                    'equity_split_b_pct' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egFoundersBody(),
            ],
            [
                'slug' => 'eg-commercial-agency',
                'name' => 'عقد وكالة تجارية / توزيع (مصري)',
                'category' => 'commercial',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد وكالة تجارية بين أصيل وموزع/وكيل وفقاً للقانون التجاري المصري.',
                'required_fields' => [
                    'principal' => 'string',
                    'agent' => 'string',
                    'territory' => 'string',
                    'products' => 'string',
                    'term_years' => 'number',
                    'commission_rate_pct' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egAgencyBody(),
            ],
            [
                'slug' => 'sa-shareholders-agreement',
                'name' => 'عقد شركاء (سعودي)',
                'category' => 'companies',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'عقد شركاء في شركة ذات مسؤولية محدودة وفقاً لنظام الشركات السعودي رقم م/132 لسنة 1443 هـ.',
                'required_fields' => [
                    'company_name' => 'string',
                    'capital_sar' => 'number',
                    'partner_a' => 'string',
                    'partner_a_share_pct' => 'number',
                    'partner_b' => 'string',
                    'partner_b_share_pct' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::saShareholdersBody(),
            ],
            [
                'slug' => 'ae-shareholders-agreement',
                'name' => 'عقد مساهمين (إماراتي - ثنائي اللغة)',
                'category' => 'companies',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'عقد مساهمين في شركة ذات مسؤولية محدودة وفقاً للمرسوم بقانون اتحادي رقم 32 لسنة 2021. ثنائي اللغة عربي/إنجليزي.',
                'required_fields' => [
                    'company_name' => 'string',
                    'capital_aed' => 'number',
                    'shareholder_a' => 'string',
                    'shareholder_a_share_pct' => 'number',
                    'shareholder_b' => 'string',
                    'shareholder_b_share_pct' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::aeShareholdersBody(),
            ],

            // ───── Stage-0 deal docs ─────
            [
                'slug' => 'eg-mou-loi',
                'name' => 'مذكرة تفاهم / خطاب نوايا (مصري)',
                'category' => 'companies',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'مذكرة تفاهم غير ملزمة (Term Sheet / LOI) للمراحل الأولى من المفاوضات.',
                'required_fields' => [
                    'party_a' => 'string',
                    'party_b' => 'string',
                    'transaction_summary' => 'string',
                    'exclusivity_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egMouLoiBody(),
            ],
            [
                'slug' => 'eg-board-resolution',
                'name' => 'محضر / قرار مجلس إدارة (مصري)',
                'category' => 'companies',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'محضر اجتماع مجلس إدارة / قرار شركاء بالموافقة على إجراء جوهري (تعيين مدير، توقيع عقد، رفع رأس مال).',
                'required_fields' => [
                    'company_name' => 'string',
                    'meeting_date' => 'date',
                    'resolution_subject' => 'string',
                    'authorized_signatory' => 'string',
                ],
                'body' => self::egBoardResolutionBody(),
            ],

            // ───── Tech / IP ─────
            [
                'slug' => 'eg-saas-subscription',
                'name' => 'عقد اشتراك / ترخيص برمجيات SaaS (مصري)',
                'category' => 'commercial',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد اشتراك في خدمة برمجية / SaaS مع شروط الاستخدام، البيانات، ومستويات الخدمة (SLA).',
                'required_fields' => [
                    'provider' => 'string',
                    'customer' => 'string',
                    'service_name' => 'string',
                    'subscription_fee_egp' => 'number',
                    'term_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egSaasBody(),
            ],
            [
                'slug' => 'eg-ip-assignment',
                'name' => 'عقد تنازل عن حقوق الملكية الفكرية (مصري)',
                'category' => 'commercial',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد تنازل / نقل ملكية لحقوق الملكية الفكرية (كود، علامات تجارية، تصاميم) من المؤلف إلى الشركة.',
                'required_fields' => [
                    'assignor' => 'string',
                    'assignee' => 'string',
                    'work_description' => 'string',
                    'consideration_egp' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egIpAssignmentBody(),
            ],

            // ───── Financing ─────
            [
                'slug' => 'eg-loan-agreement',
                'name' => 'عقد قرض / تسهيل (مصري)',
                'category' => 'banking',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد قرض بسيط بين أطراف خاصة أو قرض مساهم (shareholder loan) للشركة.',
                'required_fields' => [
                    'lender' => 'string',
                    'borrower' => 'string',
                    'principal_egp' => 'number',
                    'interest_rate_pct' => 'number',
                    'term_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egLoanBody(),
            ],

            // ───── Disputes ─────
            [
                'slug' => 'eg-settlement',
                'name' => 'اتفاق تصالح / تسوية (مصري)',
                'category' => 'civil-general',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'اتفاق تصالح ينهي نزاعاً قائماً مقابل مبلغ متفق عليه مع إقرار بإبراء الذمة.',
                'required_fields' => [
                    'party_a' => 'string',
                    'party_b' => 'string',
                    'dispute_summary' => 'string',
                    'settlement_amount_egp' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::egSettlementBody(),
            ],

            // ───── Compliance / Data ─────
            [
                'slug' => 'eg-dpa',
                'name' => 'اتفاقية معالجة بيانات شخصية (DPA) — مصري',
                'category' => 'compliance',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'ملحق معالجة بيانات شخصية وفقاً لقانون حماية البيانات الشخصية المصري رقم 151 لسنة 2020.',
                'required_fields' => [
                    'controller' => 'string',
                    'processor' => 'string',
                    'processing_purpose' => 'string',
                    'data_categories' => 'string',
                    'effective_date' => 'date',
                ],
                'body' => self::egDpaBody(),
            ],

            // ───── Commercial lease ─────
            [
                'slug' => 'eg-commercial-lease',
                'name' => 'عقد إيجار محل تجاري / إداري (مصري)',
                'category' => 'real-estate',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'عقد إيجار وحدة لاستعمال تجاري أو إداري وفقاً للقانون رقم 6 لسنة 2022.',
                'required_fields' => [
                    'landlord' => 'string',
                    'tenant' => 'string',
                    'unit_address' => 'string',
                    'use_purpose' => 'string',
                    'monthly_rent_egp' => 'number',
                    'term_years' => 'number',
                    'start_date' => 'date',
                ],
                'body' => self::egCommercialLeaseBody(),
            ],

            // ───── SA corporate templates ─────
            [
                'slug' => 'sa-mou-loi',
                'name' => 'مذكرة تفاهم / خطاب نوايا (سعودي)',
                'category' => 'companies',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'مذكرة تفاهم غير ملزمة (LOI / Term Sheet) للمراحل الأولى من المفاوضات وفقاً لنظام المعاملات المدنية السعودي.',
                'required_fields' => [
                    'party_a' => 'string',
                    'party_b' => 'string',
                    'transaction_summary' => 'string',
                    'exclusivity_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::saMouLoiBody(),
            ],
            [
                'slug' => 'sa-board-resolution',
                'name' => 'محضر / قرار مجلس إدارة (سعودي)',
                'category' => 'companies',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'محضر اجتماع مجلس إدارة / قرار شركاء وفقاً لنظام الشركات السعودي م/132 لسنة 1443 هـ.',
                'required_fields' => [
                    'company_name' => 'string',
                    'meeting_date' => 'date',
                    'resolution_subject' => 'string',
                    'authorized_signatory' => 'string',
                ],
                'body' => self::saBoardResolutionBody(),
            ],
            [
                'slug' => 'sa-saas-subscription',
                'name' => 'عقد اشتراك SaaS (سعودي)',
                'category' => 'commercial',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'عقد اشتراك في خدمة برمجية متوافق مع نظام حماية البيانات الشخصية السعودي (PDPL) ولوائح هيئة الاتصالات وتقنية المعلومات.',
                'required_fields' => [
                    'provider' => 'string',
                    'customer' => 'string',
                    'service_name' => 'string',
                    'subscription_fee_sar' => 'number',
                    'term_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::saSaasBody(),
            ],
            [
                'slug' => 'sa-ip-assignment',
                'name' => 'عقد تنازل عن حقوق الملكية الفكرية (سعودي)',
                'category' => 'commercial',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'عقد تنازل / نقل ملكية حقوق ملكية فكرية وفقاً لنظام حماية حقوق المؤلف ونظام براءات الاختراع السعودي.',
                'required_fields' => [
                    'assignor' => 'string',
                    'assignee' => 'string',
                    'work_description' => 'string',
                    'consideration_sar' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::saIpAssignmentBody(),
            ],
            [
                'slug' => 'sa-dpa',
                'name' => 'اتفاقية معالجة بيانات شخصية (سعودي - PDPL)',
                'category' => 'compliance',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'ملحق معالجة بيانات شخصية وفقاً لنظام حماية البيانات الشخصية السعودي م/19 لسنة 1443 هـ ولائحته التنفيذية.',
                'required_fields' => [
                    'controller' => 'string',
                    'processor' => 'string',
                    'processing_purpose' => 'string',
                    'data_categories' => 'string',
                    'effective_date' => 'date',
                ],
                'body' => self::saDpaBody(),
            ],
            [
                'slug' => 'sa-settlement',
                'name' => 'اتفاق تصالح وتسوية (سعودي)',
                'category' => 'civil-general',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'اتفاق تصالح ينهي نزاعاً قائماً وفقاً لأحكام الصلح في نظام المعاملات المدنية السعودي.',
                'required_fields' => [
                    'party_a' => 'string',
                    'party_b' => 'string',
                    'dispute_summary' => 'string',
                    'settlement_amount_sar' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::saSettlementBody(),
            ],

            // ───── AE corporate templates ─────
            [
                'slug' => 'ae-mou-loi',
                'name' => 'مذكرة تفاهم / LOI (إماراتي - ثنائي اللغة)',
                'category' => 'companies',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'مذكرة تفاهم غير ملزمة وفقاً لقانون المعاملات المدنية الإماراتي. ثنائي اللغة عربي/إنجليزي.',
                'required_fields' => [
                    'party_a' => 'string',
                    'party_b' => 'string',
                    'transaction_summary' => 'string',
                    'exclusivity_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::aeMouLoiBody(),
            ],
            [
                'slug' => 'ae-board-resolution',
                'name' => 'محضر / قرار مجلس إدارة (إماراتي)',
                'category' => 'companies',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'محضر اجتماع مجلس إدارة / قرار شركاء وفقاً للمرسوم بقانون اتحادي رقم 32 لسنة 2021 بشأن الشركات التجارية.',
                'required_fields' => [
                    'company_name' => 'string',
                    'meeting_date' => 'date',
                    'resolution_subject' => 'string',
                    'authorized_signatory' => 'string',
                ],
                'body' => self::aeBoardResolutionBody(),
            ],
            [
                'slug' => 'ae-saas-subscription',
                'name' => 'عقد اشتراك SaaS (إماراتي - ثنائي اللغة)',
                'category' => 'commercial',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'عقد اشتراك SaaS متوافق مع المرسوم بقانون اتحادي رقم 45 لسنة 2021 بشأن حماية البيانات الشخصية.',
                'required_fields' => [
                    'provider' => 'string',
                    'customer' => 'string',
                    'service_name' => 'string',
                    'subscription_fee_aed' => 'number',
                    'term_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::aeSaasBody(),
            ],
            [
                'slug' => 'ae-ip-assignment',
                'name' => 'عقد تنازل عن حقوق الملكية الفكرية (إماراتي)',
                'category' => 'commercial',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'عقد تنازل عن حقوق ملكية فكرية وفقاً للمرسوم بقانون اتحادي رقم 38 لسنة 2021 بشأن حقوق المؤلف.',
                'required_fields' => [
                    'assignor' => 'string',
                    'assignee' => 'string',
                    'work_description' => 'string',
                    'consideration_aed' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::aeIpAssignmentBody(),
            ],
            [
                'slug' => 'ae-dpa',
                'name' => 'اتفاقية معالجة بيانات شخصية (إماراتي - PDPL)',
                'category' => 'compliance',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'ملحق معالجة بيانات شخصية وفقاً للمرسوم بقانون اتحادي رقم 45 لسنة 2021 ولائحته التنفيذية.',
                'required_fields' => [
                    'controller' => 'string',
                    'processor' => 'string',
                    'processing_purpose' => 'string',
                    'data_categories' => 'string',
                    'effective_date' => 'date',
                ],
                'body' => self::aeDpaBody(),
            ],
            [
                'slug' => 'ae-settlement',
                'name' => 'اتفاق تصالح وتسوية (إماراتي)',
                'category' => 'civil-general',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'اتفاق تصالح ينهي نزاعاً قائماً وفقاً لأحكام الصلح في قانون المعاملات المدنية الإماراتي (المواد 760+).',
                'required_fields' => [
                    'party_a' => 'string',
                    'party_b' => 'string',
                    'dispute_summary' => 'string',
                    'settlement_amount_aed' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::aeSettlementBody(),
            ],

            // ───── Labour ─────
            [
                'slug' => 'eg-employment-termination',
                'name' => 'اتفاق إنهاء عمل ودي + إقرار إبراء (مصري)',
                'category' => 'labour',
                'jurisdiction' => 'EG',
                'language' => 'ar',
                'description' => 'اتفاق إنهاء علاقة العمل بالتراضي مع تسوية المستحقات وإقرار من العامل بإبراء الذمة.',
                'required_fields' => [
                    'employer' => 'string',
                    'employee' => 'string',
                    'last_working_day' => 'date',
                    'settlement_amount_egp' => 'number',
                ],
                'body' => self::egTerminationMutualBody(),
            ],

            // ───── SA: financing, lease, and termination ─────
            [
                'slug' => 'sa-loan-agreement',
                'name' => 'عقد قرض / تسهيل (سعودي)',
                'category' => 'banking',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'عقد قرض بين أطراف خاصة أو قرض مساهم وفقاً لنظام المعاملات المدنية السعودي ونظام البنك المركزي السعودي (ساما).',
                'required_fields' => [
                    'lender' => 'string',
                    'borrower' => 'string',
                    'principal_sar' => 'number',
                    'profit_rate_pct' => 'number',
                    'term_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::saLoanBody(),
            ],
            [
                'slug' => 'sa-commercial-lease',
                'name' => 'عقد إيجار محل تجاري / إداري (سعودي)',
                'category' => 'real-estate',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'عقد إيجار وحدة لاستعمال تجاري أو إداري وفقاً لأحكام الإجارة في نظام المعاملات المدنية السعودي.',
                'required_fields' => [
                    'landlord' => 'string',
                    'tenant' => 'string',
                    'unit_address' => 'string',
                    'use_purpose' => 'string',
                    'monthly_rent_sar' => 'number',
                    'term_years' => 'number',
                    'start_date' => 'date',
                ],
                'body' => self::saCommercialLeaseBody(),
            ],
            [
                'slug' => 'sa-employment-termination',
                'name' => 'اتفاق إنهاء عمل ودي + إقرار إبراء (سعودي)',
                'category' => 'labour',
                'jurisdiction' => 'SA',
                'language' => 'ar',
                'description' => 'اتفاق إنهاء علاقة العمل بالتراضي مع تسوية مكافأة نهاية الخدمة وفقاً لنظام العمل السعودي م/51 وتعديلاته.',
                'required_fields' => [
                    'employer' => 'string',
                    'employee' => 'string',
                    'last_working_day' => 'date',
                    'settlement_amount_sar' => 'number',
                ],
                'body' => self::saTerminationMutualBody(),
            ],

            // ───── AE: financing, lease, and termination ─────
            [
                'slug' => 'ae-loan-agreement',
                'name' => 'عقد قرض / تسهيل (إماراتي)',
                'category' => 'banking',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'عقد قرض وفقاً لقانون المعاملات المدنية الإماراتي وقانون البنك المركزي والمرسوم بقانون اتحادي رقم 14 لسنة 2018.',
                'required_fields' => [
                    'lender' => 'string',
                    'borrower' => 'string',
                    'principal_aed' => 'number',
                    'profit_rate_pct' => 'number',
                    'term_months' => 'number',
                    'effective_date' => 'date',
                ],
                'body' => self::aeLoanBody(),
            ],
            [
                'slug' => 'ae-commercial-lease',
                'name' => 'عقد إيجار محل تجاري / إداري (إماراتي)',
                'category' => 'real-estate',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'عقد إيجار وحدة لاستعمال تجاري أو إداري وفقاً لقانون المعاملات المدنية الإماراتي وقانون الإيجار الإماراتي (دبي قانون 26/2007 / أبوظبي قانون 20/2006).',
                'required_fields' => [
                    'landlord' => 'string',
                    'tenant' => 'string',
                    'unit_address' => 'string',
                    'use_purpose' => 'string',
                    'monthly_rent_aed' => 'number',
                    'term_years' => 'number',
                    'start_date' => 'date',
                ],
                'body' => self::aeCommercialLeaseBody(),
            ],
            [
                'slug' => 'ae-employment-termination',
                'name' => 'اتفاق إنهاء عمل ودي + إقرار إبراء (إماراتي)',
                'category' => 'labour',
                'jurisdiction' => 'AE',
                'language' => 'ar',
                'description' => 'اتفاق إنهاء علاقة العمل بالتراضي مع تسوية مكافأة نهاية الخدمة وفقاً للمرسوم بقانون اتحادي رقم 33 لسنة 2021 وتعديلاته.',
                'required_fields' => [
                    'employer' => 'string',
                    'employee' => 'string',
                    'last_working_day' => 'date',
                    'settlement_amount_aed' => 'number',
                ],
                'body' => self::aeTerminationMutualBody(),
            ],
        ];
    }

    private static function ndaBody(): string
    {
        return <<<'TXT'
MUTUAL NON-DISCLOSURE AGREEMENT

This Mutual Non-Disclosure Agreement (the "Agreement") is entered into as of {{effective_date}} by and between {{party_a}} and {{party_b}} (each a "Party" and together the "Parties").

1. PURPOSE. The Parties wish to explore a potential business relationship and may disclose Confidential Information.

2. CONFIDENTIAL INFORMATION. "Confidential Information" means non-public information disclosed by one Party to the other in any form that is identified as confidential or that would reasonably be understood to be confidential.

3. OBLIGATIONS. Each Party shall (a) use Confidential Information solely for the Purpose; (b) protect it with at least the same care it uses to protect its own confidential information, but no less than reasonable care; and (c) not disclose it to any third party without the discloser's prior written consent.

4. EXCLUSIONS. Confidential Information does not include information that is or becomes publicly available through no breach of this Agreement, was known prior to disclosure, was independently developed, or is rightfully received from a third party without confidentiality obligations.

5. TERM. This Agreement remains in effect for {{term_years}} years from the Effective Date.

6. RETURN OR DESTRUCTION. Upon written request, each Party shall return or destroy all Confidential Information of the other Party.

7. NO LICENSE. No license or other rights in the Confidential Information are granted except as expressly set forth herein.

8. REMEDIES. The Parties acknowledge that monetary damages may be inadequate and that injunctive relief may be appropriate.

9. GOVERNING LAW. This Agreement is governed by the laws of {{governing_law}}.

IN WITNESS WHEREOF, the Parties have executed this Agreement as of the Effective Date.

{{party_a}}                                {{party_b}}
By: __________________________            By: __________________________
Name:                                     Name:
Title:                                    Title:
TXT;
    }

    private static function employmentBody(): string
    {
        return <<<'TXT'
EMPLOYMENT AGREEMENT

This Employment Agreement (the "Agreement") is entered into between {{employer}} ("Employer") and {{employee}} ("Employee") effective {{start_date}}.

1. POSITION. Employee is hired as {{job_title}} reporting to such persons as the Employer may designate.

2. DUTIES. Employee shall perform duties customarily associated with the position and any other duties reasonably assigned by Employer.

3. COMPENSATION. Employer shall pay Employee a salary of {{salary}}, less applicable withholdings, in accordance with Employer's standard payroll practices.

4. AT-WILL EMPLOYMENT. Employment is at-will; either Party may terminate the relationship at any time, with or without cause and with or without notice, subject to applicable law.

5. CONFIDENTIALITY. Employee shall not disclose any confidential information of Employer during or after employment.

6. INTELLECTUAL PROPERTY. All work product created within the scope of employment is the sole property of Employer, and Employee assigns all rights therein to Employer.

7. GOVERNING LAW. This Agreement is governed by the laws of {{governing_law}}.

8. ENTIRE AGREEMENT. This Agreement constitutes the entire agreement between the Parties regarding the subject matter and supersedes prior discussions.

EMPLOYER: {{employer}}                     EMPLOYEE: {{employee}}
By: __________________________            By: __________________________
Name:                                     Date:
Title:
TXT;
    }

    private static function egLandSaleBody(): string
    {
        return <<<'TXT'
عقد بيع قطعة أرض

إنه في يوم {{effective_date}} تم الاتفاق بين كلٍ من:
الطرف الأول (البائع): {{seller}}
الطرف الثاني (المشتري): {{buyer}}

تمهيد: لما كان البائع يمتلك قطعة الأرض الموصوفة أدناه ملكية تامة خالية من جميع الحقوق العينية والشخصية، ورغب في بيعها للمشتري الذي يرغب في شرائها، فقد اتفق الطرفان على ما يلي:

البند الأول — محل العقد:
يبيع الطرف الأول للطرف الثاني — الذي يقبل الشراء — قطعة الأرض الكائنة بـ{{land_location}}، ومساحتها {{land_area_m2}} متر مربع.

البند الثاني — الثمن:
يبلغ الثمن المتفق عليه مبلغ {{price_egp}} جنيه مصري (.....)، ويُسدَّد على دفعتين: 50% عند توقيع هذا العقد، و50% عند التسجيل بالشهر العقاري.

البند الثالث — التسليم:
يلتزم البائع بتسليم قطعة الأرض للمشتري خالية من الشواغل وبكامل ملحقاتها فور التسجيل.

البند الرابع — الضمانات:
يضمن البائع للمشتري عدم التعرض الشخصي والتعرض الصادر من الغير، طبقاً لأحكام المادة 439 وما بعدها من القانون المدني المصري.

البند الخامس — التسجيل:
يتعهد الطرفان بتسجيل البيع لدى مصلحة الشهر العقاري وفقاً لأحكام القانون رقم 114 لسنة 1946 وتعديلاته. ويتحمل المشتري رسوم التسجيل ما لم يُتفق على غير ذلك.

البند السادس — فسخ العقد:
في حالة إخلال أحد الطرفين بالتزاماته يحق للطرف الآخر طلب الفسخ مع التعويض، طبقاً لأحكام المواد 157 و158 من القانون المدني.

البند السابع — الاختصاص القضائي:
تختص محاكم {{land_location}} بالفصل في أي نزاع ينشأ عن هذا العقد.

البند الثامن — النسخ:
حُرر هذا العقد من نسختين، بيد كلِّ طرف نسخة للعمل بموجبها.

التوقيعات:
البائع: ______________________        المشتري: ______________________
الاسم: {{seller}}                       الاسم: {{buyer}}
التاريخ: ______________               التاريخ: ______________
TXT;
    }

    private static function egResidentialLeaseBody(): string
    {
        return <<<'TXT'
عقد إيجار وحدة سكنية

إنه في يوم {{start_date}} تم الاتفاق بين:
الطرف الأول (المؤجر): {{landlord}}
الطرف الثاني (المستأجر): {{tenant}}

البند الأول — محل العقد:
أجر المؤجر للمستأجر الوحدة السكنية الكائنة في {{unit_address}}، وذلك للسكن الخاص به وأسرته دون سواه.

البند الثاني — مدة الإيجار:
مدة هذا العقد {{term_months}} شهراً، تبدأ من {{start_date}}، قابلة للتجديد باتفاق الطرفين كتابة.

البند الثالث — الأجرة:
الأجرة الشهرية مبلغ {{monthly_rent_egp}} جنيه مصري (.....)، تُدفع مقدماً خلال الأسبوع الأول من كل شهر.

البند الرابع — التزامات المستأجر:
يلتزم المستأجر بـ:
أ) سداد الأجرة في مواعيدها.
ب) المحافظة على العين المؤجرة وعدم إجراء تعديلات بدون إذن كتابي.
ج) سداد فواتير الكهرباء والمياه والغاز والإنترنت.
د) عدم استعمال العين في أي غرض غير السكن.

البند الخامس — التزامات المؤجر:
يلتزم المؤجر بتسليم العين صالحة للانتفاع، وإجراء أي إصلاحات جوهرية ضرورية للسكن، طبقاً لأحكام المادة 568 من القانون المدني.

البند السادس — التأمين:
يدفع المستأجر تأميناً قدره أجرة شهرين يُرد عند انتهاء العقد بعد التحقق من سلامة العين.

البند السابع — الفسخ والإنهاء:
يخضع هذا العقد لأحكام القانون المدني المصري والقانون رقم 4 لسنة 1996 وتعديلاته.

البند الثامن — الاختصاص:
تختص محاكم القاهرة بأي نزاع ينشأ عن هذا العقد.

التوقيعات:
المؤجر: ______________________      المستأجر: ______________________
TXT;
    }

    private static function egEmploymentBody(): string
    {
        return <<<'TXT'
عقد عمل فردي

إنه في يوم {{start_date}} تم الاتفاق بين:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

البند الأول — موضوع العقد:
يعمل الطرف الثاني لدى الطرف الأول بوظيفة {{job_title}} ابتداءً من {{start_date}}.

البند الثاني — مدة الاختبار:
يخضع العامل لفترة اختبار مدتها {{probation_months}} أشهر، يحق خلالها لأي من الطرفين إنهاء العقد دون إخطار سابق، طبقاً للمادة 33 من قانون العمل رقم 12 لسنة 2003.

البند الثالث — الأجر:
يبلغ الأجر الشهري {{monthly_salary_egp}} جنيه مصري (.....)، يُصرف في نهاية كل شهر ميلادي بعد خصم الاشتراكات التأمينية والضرائب المستحقة.

البند الرابع — ساعات العمل:
8 ساعات يومياً، بمعدل 48 ساعة أسبوعياً، طبقاً للمادة 80 من قانون العمل، مع راحة أسبوعية يومي الجمعة والسبت.

البند الخامس — الإجازات:
يستحق العامل إجازة سنوية مدفوعة الأجر مدتها 21 يوماً للسنة الأولى، تزداد بحسب أحكام المواد 47 و48 من قانون العمل.

البند السادس — التأمينات الاجتماعية:
يُؤمَّن العامل لدى الهيئة القومية للتأمين الاجتماعي طبقاً لقانون التأمينات الاجتماعية والمعاشات رقم 148 لسنة 2019.

البند السابع — السرية:
يلتزم العامل بالحفاظ على أسرار العمل أثناء وبعد انتهاء الخدمة، طبقاً للمادة 58 من قانون العمل.

البند الثامن — إنهاء العقد:
يخضع إنهاء العقد لأحكام المواد من 110 إلى 122 من قانون العمل المصري.

البند التاسع — الاختصاص:
تختص محاكم العمل بأي نزاع ينشأ عن هذا العقد.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
TXT;
    }

    private static function egServicesBody(): string
    {
        return <<<'TXT'
عقد تقديم خدمات استشارية

إنه في يوم {{effective_date}} تم الاتفاق بين:
الطرف الأول (العميل): {{client}}
الطرف الثاني (مقدم الخدمة): {{provider}}

البند الأول — نطاق الخدمات:
يقدم مقدم الخدمة للعميل الخدمات التالية: {{scope_summary}}.

البند الثاني — الأتعاب:
يلتزم العميل بسداد مبلغ {{fees_egp}} جنيه مصري (.....) مقابل الخدمات، يُسدد طبقاً لجدول الدفعات المتفق عليه كتابة.

البند الثالث — صفة مقدم الخدمة:
مقدم الخدمة مقاول مستقل وليس موظفاً لدى العميل، ويتحمل بمفرده الضرائب والاشتراكات الاجتماعية المتعلقة بنشاطه.

البند الرابع — الملكية الفكرية:
تنتقل ملكية أي مخرجات تم إعدادها خصيصاً للعميل بموجب هذا العقد إلى العميل عند سداد الأتعاب كاملةً، مع احتفاظ مقدم الخدمة بحقوقه السابقة على المواد المستخدمة كأدوات عمل.

البند الخامس — السرية:
يلتزم كل طرف بسرية المعلومات التي يحصل عليها من الطرف الآخر، وعدم استخدامها في غير غرض هذا العقد.

البند السادس — المسؤولية:
لا يكون أيٌّ من الطرفين مسؤولاً عن الأضرار غير المباشرة، ويقتصر سقف المسؤولية على إجمالي الأتعاب المدفوعة في الاثني عشر شهراً السابقة.

البند السابع — الاختصاص:
يخضع هذا العقد للقانون المدني المصري، وتختص محاكم القاهرة بأي نزاع.

التوقيعات:
العميل: ______________________      مقدم الخدمة: ______________________
TXT;
    }

    private static function egPoaBody(): string
    {
        return <<<'TXT'
توكيل خاص

في يوم {{effective_date}} أنا الموقع أدناه:
{{principal}} (الموكل)

أوكل بموجب هذا التوكيل السيد/السيدة:
{{agent}} (الوكيل)

في أن ينوب عني فيما يلي:
{{scope}}

ويشمل هذا التوكيل ما يستلزمه إنفاذه من إجراءات أمام جميع الجهات الرسمية، بما في ذلك مصلحة الشهر العقاري ومصلحة الضرائب والمحاكم على اختلاف درجاتها، واستلام وتسليم المستندات الموقعة.

كما يحق للوكيل التوقيع على ما يلزم من إقرارات ومحاضر، وتلقي المبالغ المالية المتعلقة بنطاق التوكيل، وتقديم الطعون والاستئنافات وفقاً لما تستلزمه المصلحة.

هذا التوكيل ساري المفعول من تاريخه ولحين إلغائه كتابةً، طبقاً لأحكام المواد 699 إلى 717 من القانون المدني المصري وقانون التوثيق رقم 68 لسنة 1947.

التوقيع: ______________________
الاسم: {{principal}}
التاريخ: {{effective_date}}

(يُوثَّق هذا التوكيل لدى مكتب الشهر العقاري المختص)
TXT;
    }

    private static function egNdaBody(): string
    {
        return <<<'TXT'
اتفاقية عدم إفصاح متبادلة

إنه في يوم {{effective_date}} تم الاتفاق بين:
الطرف الأول: {{party_a}}
الطرف الثاني: {{party_b}}

ولما كان كل طرف من الطرفين قد يفصح للطرف الآخر عن معلومات سرية بمناسبة الدراسة المشتركة لعلاقة عمل محتملة، فقد اتفقا على ما يلي:

البند الأول — الغرض:
استكشاف فرص تعاون مشترك يستلزم تبادل معلومات سرية.

البند الثاني — تعريف المعلومات السرية:
كل معلومة غير معلنة يفصح بها أحد الطرفين للآخر، سواء كانت كتابية أو شفهية أو إلكترونية، تتعلق بأنشطة الطرف المُفصِح أو عملائه أو أسراره التجارية أو خططه أو بياناته المالية أو قواعد بياناته.

البند الثالث — الالتزامات:
يلتزم كل طرف بـ:
أ) عدم إفشاء المعلومات السرية لأي طرف ثالث دون إذن كتابي مسبق.
ب) استخدام المعلومات في الغرض المتفق عليه فقط.
ج) حماية المعلومات بنفس درجة العناية التي يوليها لمعلوماته السرية، على ألا تقل عن العناية المعقولة.

البند الرابع — الاستثناءات:
لا تشمل المعلومات السرية ما هو معلن أو معروف للعموم بدون خطأ من الطرف المتلقي، أو ما تم تطويره مستقلاً، أو ما يُكشف بأمر من جهة قضائية مختصة.

البند الخامس — المدة:
تظل التزامات السرية سارية لمدة {{term_years}} سنوات من تاريخ هذا العقد.

البند السادس — الإعادة والإتلاف:
عند انتهاء العقد، يلتزم كل طرف بإعادة أو إتلاف المعلومات السرية الخاصة بالطرف الآخر.

البند السابع — التعويض:
يحق للطرف المتضرر المطالبة بالتعويض عن أي خرق، طبقاً للقواعد العامة في القانون المدني المصري (المواد 163 وما بعدها).

البند الثامن — الاختصاص:
يخضع هذا العقد للقانون المصري، وتختص محاكم القاهرة بأي نزاع.

التوقيعات:
{{party_a}} ______________________   {{party_b}} ______________________
TXT;
    }

    private static function saEmploymentBody(): string
    {
        return <<<'TXT'
عقد عمل

إنه في يوم {{start_date}}، تم إبرام هذا العقد بين كلٍ من:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

البند الأول — موضوع العقد:
يلتحق العامل بالعمل لدى صاحب العمل بوظيفة {{job_title}} اعتباراً من {{start_date}}.

البند الثاني — مدة الاختبار:
يخضع العامل لفترة اختبار مدتها 90 يوماً، طبقاً للمادة 53 من نظام العمل السعودي. يجوز تمديد فترة الاختبار لمدة 90 يوماً إضافية بموافقة الطرفين كتابة.

البند الثالث — الأجر:
يبلغ الأجر الشهري الإجمالي {{monthly_salary_sar}} ريال سعودي، يُصرف في موعد أقصاه نهاية كل شهر ميلادي عبر التحويل البنكي طبقاً لنظام حماية الأجور.

البند الرابع — ساعات العمل والإجازات:
8 ساعات يومياً، بمعدل 48 ساعة أسبوعياً، طبقاً للمواد 98 وما بعدها من النظام. يستحق العامل إجازة سنوية مدفوعة الأجر مدتها 21 يوماً، تزداد إلى 30 يوماً بعد خمس سنوات من الخدمة.

البند الخامس — مكافأة نهاية الخدمة:
تُحسب طبقاً للمادة 84 من نظام العمل: نصف أجر شهر عن كل سنة من السنوات الخمس الأولى، وأجر شهر كامل عن كل سنة بعد ذلك، عن المدة الفعلية للخدمة.

البند السادس — السرية وعدم المنافسة:
يلتزم العامل بالحفاظ على أسرار العمل أثناء وبعد انتهاء الخدمة، وبعدم العمل لدى منافس مباشر داخل المملكة العربية السعودية لمدة سنتين بعد انتهاء العقد، طبقاً للمادة 83 من النظام.

البند السابع — التأمينات الاجتماعية:
يُسجَّل العامل لدى المؤسسة العامة للتأمينات الاجتماعية وفقاً لنظام التأمينات الاجتماعية.

البند الثامن — إنهاء العقد:
يخضع إنهاء هذا العقد لأحكام المواد 74 إلى 77 من نظام العمل، مع مراعاة فترة الإشعار المحددة بـ60 يوماً للعقود غير محددة المدة.

البند التاسع — الاختصاص:
تختص المحاكم العمالية المختصة بنظر أي نزاع ينشأ عن تنفيذ هذا العقد، بعد استنفاد إجراءات التسوية الودية أمام مكتب العمل.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
TXT;
    }

    private static function saServicesBody(): string
    {
        return <<<'TXT'
عقد تقديم خدمات

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين كلٍ من:
الطرف الأول (العميل): {{client}}
الطرف الثاني (مقدم الخدمة): {{provider}}

البند الأول — نطاق الخدمات:
يقدم مقدم الخدمة للعميل الخدمات التالية: {{scope_summary}}.

البند الثاني — الأتعاب:
يلتزم العميل بسداد مبلغ {{fees_sar}} ريال سعودي مقابل الخدمات، يُسدد طبقاً لجدول الدفعات المتفق عليه كتابة.

البند الثالث — الالتزامات:
يلتزم مقدم الخدمة ببذل العناية الواجبة في تقديم الخدمات وفق المعايير المهنية المعتمدة، طبقاً لأحكام المواد 304 وما بعدها من نظام المعاملات المدنية السعودي.

البند الرابع — الملكية الفكرية:
تنتقل ملكية المخرجات المُعدَّة خصيصاً للعميل بموجب هذا العقد عند سداد كامل الأتعاب، مع احتفاظ مقدم الخدمة بحقوقه على أدوات العمل العامة.

البند الخامس — السرية:
يلتزم كل طرف بالحفاظ على سرية المعلومات التي يحصل عليها بمناسبة تنفيذ هذا العقد ولمدة خمس سنوات بعد انتهائه.

البند السادس — القانون والاختصاص:
يخضع هذا العقد لأنظمة المملكة العربية السعودية، وتختص المحاكم التجارية بالرياض دون غيرها بالفصل في أي نزاع.

التوقيعات:
العميل: ______________________     مقدم الخدمة: ______________________
TXT;
    }

    private static function aeEmploymentBody(): string
    {
        return <<<'TXT'
عقد عمل محدد المدة

إنه في يوم {{start_date}}، أُبرم هذا العقد في دولة الإمارات العربية المتحدة بين كلٍ من:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

البند الأول — موضوع العقد:
يلتحق العامل بالعمل لدى صاحب العمل بوظيفة {{job_title}} اعتباراً من {{start_date}}.

البند الثاني — مدة العقد:
هذا العقد محدد المدة لفترة {{term_years}} سنة، قابل للتجديد باتفاق الطرفين، طبقاً للمادة 8 من المرسوم بقانون اتحادي رقم 33 لسنة 2021.

البند الثالث — مدة الاختبار:
ستة أشهر من تاريخ بدء العمل، طبقاً للمادة 9 من القانون.

البند الرابع — الأجر:
يبلغ الأجر الشهري الإجمالي {{monthly_salary_aed}} درهم إماراتي، يُصرف عبر نظام حماية الأجور (WPS) في موعد أقصاه 15 يوماً من تاريخ الاستحقاق.

البند الخامس — ساعات العمل والإجازات:
8 ساعات يومياً، 48 ساعة أسبوعياً. إجازة سنوية مدفوعة الأجر 30 يوماً عن كل سنة عمل كاملة، طبقاً للمادة 29.

البند السادس — مكافأة نهاية الخدمة:
تُحسب طبقاً للمادة 30 من القانون: ثلث أجر شهر عن كل سنة من السنوات الخمس الأولى، وأجر شهر كامل عن كل سنة بعد ذلك، بحد أقصى أجر سنتين.

البند السابع — السرية وعدم المنافسة:
يلتزم العامل بسرية المعلومات وبعدم العمل لدى منافس مباشر لمدة سنتين بعد انتهاء العقد ضمن نطاق جغرافي محدد، طبقاً للمواد 10 و11.

البند الثامن — إنهاء العقد:
يخضع إنهاء هذا العقد للمواد 42 إلى 46، مع فترة إشعار من 30 إلى 90 يوماً بحسب مدة الخدمة.

البند التاسع — الاختصاص:
تختص محاكم {{employer}} الاتحادية / المحلية بنظر أي نزاع، بعد استنفاد إجراءات التسوية أمام وزارة الموارد البشرية والتوطين.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
TXT;
    }

    private static function aeServicesBody(): string
    {
        return <<<'TXT'
عقد تقديم خدمات

إنه في يوم {{effective_date}}، أُبرم هذا العقد في دولة الإمارات العربية المتحدة بين كلٍ من:
الطرف الأول (العميل): {{client}}
الطرف الثاني (مقدم الخدمة): {{provider}}

البند الأول — نطاق الخدمات:
يقدم مقدم الخدمة للعميل الخدمات التالية: {{scope_summary}}.

البند الثاني — الأتعاب:
{{fees_aed}} درهم إماراتي، يُسدد طبقاً لجدول الدفعات المتفق عليه. تشمل الأتعاب جميع الضرائب المفروضة بما فيها ضريبة القيمة المضافة (VAT) ما لم يُنص خلاف ذلك.

البند الثالث — صفة مقدم الخدمة:
مقدم الخدمة مقاول مستقل، وليس موظفاً لدى العميل، ويتحمل بمفرده أي التزامات ضريبية أو تأمينية متعلقة بنشاطه.

البند الرابع — الملكية الفكرية:
تنتقل ملكية المخرجات المُعدَّة خصيصاً للعميل عند سداد كامل الأتعاب، طبقاً لأحكام المواد 491 وما بعدها من قانون المعاملات المدنية الاتحادي.

البند الخامس — السرية:
يلتزم كل طرف بسرية المعلومات لمدة خمس سنوات بعد انتهاء العقد.

البند السادس — القانون والاختصاص:
يخضع هذا العقد لقانون دولة الإمارات العربية المتحدة. تختص محاكم دبي / المحاكم الاتحادية دون غيرها بالفصل في أي نزاع. (يمكن للأطراف اختيار محاكم مركز دبي المالي العالمي DIFC إذا رغبوا في فصل قضائي بالقانون الإنجليزي.)

البند السابع — اللغة:
حُرر هذا العقد باللغتين العربية والإنجليزية، وفي حالة وجود أي تعارض تكون الأولوية للنص العربي.

التوقيعات:
العميل: ______________________     مقدم الخدمة: ______________________
TXT;
    }

    private static function kwEmploymentBody(): string
    {
        return <<<'TXT'
عقد عمل

إنه في يوم {{start_date}}، تم إبرام هذا العقد في مدينة الكويت بين كلٍ من:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

البند الأول — موضوع العقد:
يلتحق العامل بالعمل بوظيفة {{job_title}} اعتباراً من {{start_date}}.

البند الثاني — مدة الاختبار:
100 يوم، طبقاً للمادة 28 من قانون العمل في القطاع الأهلي رقم 6 لسنة 2010.

البند الثالث — الأجر:
{{monthly_salary_kwd}} دينار كويتي شهرياً، يُصرف خلال الأسبوع الأول من الشهر التالي.

البند الرابع — مكافأة نهاية الخدمة:
طبقاً للمادة 51: 15 يوماً عن كل سنة من السنوات الخمس الأولى، وشهر كامل عن كل سنة بعد ذلك.

البند الخامس — الإجازات:
30 يوماً سنوياً مدفوعة الأجر بعد إكمال 9 أشهر من الخدمة.

البند السادس — إنهاء العقد:
طبقاً للمواد 41 إلى 53 من القانون، مع فترة إشعار 3 أشهر للعقود غير محددة المدة.

البند السابع — الاختصاص:
تختص الدائرة العمالية بمحكمة الكويت الكلية بنظر أي نزاع.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
TXT;
    }

    private static function qaEmploymentBody(): string
    {
        return <<<'TXT'
عقد عمل

إنه في يوم {{start_date}}، أُبرم هذا العقد في مدينة الدوحة بين كلٍ من:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

البند الأول — موضوع العقد:
يلتحق العامل بالعمل بوظيفة {{job_title}} اعتباراً من {{start_date}}.

البند الثاني — مدة الاختبار:
ستة أشهر، طبقاً للمادة 39 من قانون العمل القطري رقم 14 لسنة 2004.

البند الثالث — الأجر:
{{monthly_salary_qar}} ريال قطري شهرياً، يُصرف عبر نظام حماية الأجور خلال 7 أيام من نهاية الشهر.

البند الرابع — مكافأة نهاية الخدمة:
ثلاثة أسابيع عن كل سنة عمل، طبقاً للمادة 54 من القانون.

البند الخامس — الإجازات:
3 أسابيع سنوياً مدفوعة الأجر للمدة الأقل من خمس سنوات، 4 أسابيع بعد ذلك.

البند السادس — السرية:
يلتزم العامل بأحكام السرية أثناء وبعد انتهاء الخدمة.

البند السابع — إنهاء العقد:
طبقاً للمواد 49 إلى 53 من القانون.

البند الثامن — الاختصاص:
تختص المحكمة الابتدائية بالدوحة (الدائرة العمالية) بنظر أي نزاع.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
TXT;
    }

    private static function egShareholdersBody(): string
    {
        return <<<'TXT'
عقد مساهمين / اتفاق شركاء

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين كلٍ من:
الطرف الأول: {{partner_a}}، يملك حصة قدرها {{partner_a_share_pct}}% من رأس المال.
الطرف الثاني: {{partner_b}}، يملك حصة قدرها {{partner_b_share_pct}}% من رأس المال.

تمهيد:
لما كان الطرفان قد اتفقا على تأسيس / تشغيل شركة {{company_name}}، وهي شركة من نوع {{company_form}}، برأس مال قدره {{capital_egp}} جنيه مصري، فقد اتفقا على تنظيم العلاقة فيما بينهما بموجب هذا العقد على النحو التالي:

البند الأول — الأطراف ورأس المال:
يتم توزيع رأس مال الشركة على الشركاء بالنسب المذكورة أعلاه. ويلتزم كل طرف بسداد قيمة حصته كاملةً وفقاً لعقد التأسيس وطبقاً لأحكام قانون الشركات المصري.

البند الثاني — الإدارة والحوكمة:
1) يتولى إدارة الشركة مدير معيّن بموافقة الشركاء.
2) القرارات الجوهرية (تعديل عقد التأسيس، زيادة أو تخفيض رأس المال، الاندماج، التصفية، التصرف في أصل جوهري) تتطلب موافقة الشركاء بنسبة لا تقل عن 75% من رأس المال.
3) القرارات العادية تتخذ بالأغلبية البسيطة لرأس المال.

البند الثالث — التنازل عن الحصص (حق الشفعة):
لا يحق لأي شريك التنازل عن حصته أو رهنها للغير إلا بعد تقديمها أولاً للشريك الآخر بنفس الشروط والثمن. ويكون للشريك المستهدف أجل خمسة عشر يوماً للقبول أو الرفض كتابةً، طبقاً لأحكام قانون الشركات.

البند الرابع — توزيع الأرباح والخسائر:
توزع الأرباح والخسائر بنسبة الحصص في رأس المال، بعد اقتطاع الاحتياطي القانوني المنصوص عليه قانوناً.

البند الخامس — عدم المنافسة وعدم الاستقطاب:
يلتزم كل شريك بعدم ممارسة أي نشاط منافس مباشرة أو غير مباشرة، وعدم استقطاب موظفي أو عملاء الشركة، وذلك طوال مدة شراكته ولمدة سنتين بعد خروجه.

البند السادس — السرية:
يلتزم الشركاء بسرية المعلومات التجارية والمالية للشركة، أثناء الشراكة وبعد انتهائها لمدة خمس سنوات.

البند السابع — فض النزاعات:
كل نزاع ينشأ عن هذا العقد يُحلّ ودياً أولاً، فإن تعذر، يُحال إلى التحكيم وفقاً لقانون التحكيم في المواد المدنية والتجارية رقم 27 لسنة 1994.

البند الثامن — الاختصاص:
يخضع هذا العقد للقانون المصري، وتختص محاكم القاهرة الاقتصادية بأي نزاع غير قابل للتحكيم.

التوقيعات:
{{partner_a}} ______________________   {{partner_b}} ______________________
TXT;
    }

    private static function egSharePurchaseBody(): string
    {
        return <<<'TXT'
عقد بيع حصص / أسهم

إنه في يوم {{closing_date}}، تم إبرام هذا العقد بين كلٍ من:
الطرف الأول (البائع): {{seller}}
الطرف الثاني (المشتري): {{buyer}}

تمهيد:
لما كان البائع يمتلك حصة / أسهماً في شركة {{target_company}} (المُشار إليها بـ"الشركة المستهدفة")، ورغب في بيع الحصص / الأسهم الموصوفة أدناه للمشتري، الذي يرغب بدوره في شرائها، فقد اتفق الطرفان على ما يلي:

البند الأول — محل البيع:
يبيع البائع للمشتري {{shares_or_units_purchased}} في الشركة المستهدفة، خاليةً من أي حقوق عينية أو امتيازات أو رهون لصالح الغير.

البند الثاني — الثمن:
يبلغ الثمن الإجمالي مبلغ {{purchase_price_egp}} جنيه مصري، يُسدد على دفعتين:
أ) الدفعة الأولى عند توقيع هذا العقد ويُقر البائع باستلامها بموجب هذا العقد.
ب) الدفعة الثانية عند إتمام إجراءات نقل الملكية لدى الجهات المختصة.

البند الثالث — إقرارات وضمانات البائع:
يضمن البائع للمشتري:
1) ملكيته الكاملة للحصص محل البيع وعدم وجود أي حقوق للغير عليها.
2) عدم وجود نزاعات قضائية أو تنفيذية على الشركة المستهدفة قد تؤثر جوهرياً على قيمتها.
3) صحة القوائم المالية المسلمة للمشتري عن آخر سنتين ماليتين.
4) سداد كافة الالتزامات الضريبية والاجتماعية المستحقة على الشركة حتى تاريخ الإقفال.

البند الرابع — التعويض:
يتعهد البائع بتعويض المشتري عن أي خسائر أو أضرار تنشأ عن مخالفة الإقرارات والضمانات الواردة في هذا العقد، طبقاً لأحكام المواد 163 وما بعدها من القانون المدني المصري.

البند الخامس — شرط عدم المنافسة:
يلتزم البائع بعدم ممارسة أي نشاط منافس للشركة المستهدفة لمدة سنتين من تاريخ هذا العقد، داخل جمهورية مصر العربية.

البند السادس — السرية:
يلتزم الطرفان بسرية المفاوضات والمعلومات المتبادلة طوال مدة العقد ولمدة خمس سنوات بعد انتهائه.

البند السابع — الاختصاص:
يخضع هذا العقد للقانون المصري، وتختص محاكم القاهرة الاقتصادية بالفصل في أي نزاع.

التوقيعات:
البائع: ______________________   المشتري: ______________________
TXT;
    }

    private static function egFoundersBody(): string
    {
        return <<<'TXT'
اتفاق مؤسسين / مشروع مشترك

إنه في يوم {{effective_date}}، تم الاتفاق بين كلٍ من:
الطرف الأول (المؤسس الأول): {{founder_a}}
الطرف الثاني (المؤسس الثاني): {{founder_b}}

تمهيد:
لما كان الطرفان قد اتفقا على تأسيس مشروع مشترك تحت اسم "{{project_name}}"، فقد اتفقا على تنظيم علاقتهما بموجب هذا الاتفاق:

البند الأول — موضوع المشروع:
يهدف المشروع إلى ........... ويزاول نشاطه في الأراضي المصرية ودولياً بحسب ما يقرره المؤسسان مجتمعَين.

البند الثاني — مساهمات المؤسسين:
يساهم الطرف الأول بـ: {{contribution_a}}.
يساهم الطرف الثاني بـ: {{contribution_b}}.

البند الثالث — حصص الملكية:
يمتلك الطرف الأول {{equity_split_a_pct}}% من المشروع.
يمتلك الطرف الثاني {{equity_split_b_pct}}% من المشروع.

البند الرابع — الإدارة وصنع القرار:
1) القرارات اليومية تُتخذ بالأغلبية البسيطة لحصص الملكية.
2) القرارات الجوهرية (تعديل النشاط، رفع رأس المال، قبول شريك جديد، التصرف في أصل جوهري) تتطلب موافقة الطرفين معاً.

البند الخامس — استحقاق حصص المؤسسين (Vesting):
تستحق حصص كل مؤسس على مدى أربع سنوات من تاريخ هذا الاتفاق، بنسبة 25% بعد السنة الأولى، ثم بنسبة شهرية متساوية على مدار الثلاث سنوات التالية. في حال خروج أحد المؤسسين قبل اكتمال الاستحقاق، تعود الحصص غير المستحقة للمشروع.

البند السادس — السرية وعدم المنافسة:
يلتزم كل مؤسس بسرية تفاصيل المشروع، وعدم منافسته بشكل مباشر أو غير مباشر طوال مدة شراكته ولمدة سنتين بعد خروجه.

البند السابع — الملكية الفكرية:
كل ما يُبتكر أو يُطوّر في إطار المشروع، أياً كان الطرف المُساهم في إعداده، يُعدّ ملكاً للمشروع وتنتقل حقوق الملكية الفكرية كاملةً إليه.

البند الثامن — التحكيم:
يُحل أي نزاع ودياً، فإن تعذر، يُحال إلى التحكيم وفقاً لقانون التحكيم المصري رقم 27 لسنة 1994.

التوقيعات:
{{founder_a}} ______________________   {{founder_b}} ______________________
TXT;
    }

    private static function egAgencyBody(): string
    {
        return <<<'TXT'
عقد وكالة تجارية / توزيع

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول (الأصيل / الشركة المُنتجة): {{principal}}
الطرف الثاني (الوكيل / الموزع): {{agent}}

البند الأول — موضوع العقد:
يفوّض الأصيل الوكيلَ ليكون موزعه في {{territory}} لمنتجات / خدمات: {{products}}.

البند الثاني — مدة العقد:
تكون مدة هذا العقد {{term_years}} سنوات تبدأ من {{effective_date}}، قابلة للتجديد باتفاق كتابي.

البند الثالث — العمولة:
يستحق الوكيل عمولة قدرها {{commission_rate_pct}}% من صافي قيمة المبيعات المتحققة في الإقليم المتفق عليه، تُسدد شهرياً بعد تقديم تقرير معتمد من محاسب قانوني.

البند الرابع — التزامات الوكيل:
1) بذل العناية الواجبة في تسويق المنتجات والترويج لها.
2) عدم تمثيل أي منتج منافس داخل الإقليم.
3) الالتزام بالأسعار والشروط التجارية التي يحددها الأصيل.
4) تقديم تقارير شهرية عن المبيعات، المخزون، وملاحظات السوق.

البند الخامس — التزامات الأصيل:
1) تزويد الوكيل بالمنتجات في المواعيد المتفق عليها.
2) تقديم الدعم التسويقي والتقني اللازم.
3) عدم تعيين وكيل آخر داخل نفس الإقليم خلال مدة هذا العقد (حصرية).

البند السادس — الملكية الفكرية:
يبقى للأصيل كامل حقوق الملكية الفكرية للعلامات التجارية والمنتجات، ويُمنح الوكيل ترخيصاً غير حصري وغير قابل للتنازل لاستخدامها في الترويج فقط.

البند السابع — السرية:
يلتزم الطرفان بسرية المعلومات التجارية، طوال مدة العقد ولمدة ثلاث سنوات بعد انتهائه.

البند الثامن — إنهاء العقد:
يجوز إنهاء هذا العقد قبل مدته في حال إخلال جوهري لم يُصلَح خلال 30 يوماً من الإنذار الكتابي. ويتم تسوية أي عمولات مستحقة قبل تاريخ الإنهاء.

البند التاسع — الاختصاص:
يخضع هذا العقد للقانون التجاري المصري وقانون الوكالة التجارية، وتختص محاكم القاهرة الاقتصادية بالفصل في أي نزاع.

التوقيعات:
الأصيل: ______________________   الوكيل: ______________________
TXT;
    }

    private static function saShareholdersBody(): string
    {
        return <<<'TXT'
عقد شركاء

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول: {{partner_a}}، يمتلك حصة قدرها {{partner_a_share_pct}}% من رأس المال.
الطرف الثاني: {{partner_b}}، يمتلك حصة قدرها {{partner_b_share_pct}}% من رأس المال.

تمهيد:
لما كان الطرفان شركاء في شركة {{company_name}} (شركة ذات مسؤولية محدودة)، برأس مال قدره {{capital_sar}} ريال سعودي، فقد اتفقا على تنظيم علاقتهما طبقاً لأحكام نظام الشركات السعودي الصادر بالمرسوم الملكي رقم م/132 وتاريخ 1/1/1444 هـ.

البند الأول — رأس المال والحصص:
رأس المال {{capital_sar}} ريال سعودي، موزع على الشركاء بالنسب المذكورة أعلاه. ويلتزم كل شريك بسداد قيمة حصته كاملةً، طبقاً للمادة 152 من نظام الشركات.

البند الثاني — الإدارة:
1) يدير الشركة مدير يُعيّن بموافقة الشركاء.
2) صلاحيات المدير محدودة وفقاً لما تنص عليه عقد التأسيس واللائحة الداخلية.
3) القرارات الجوهرية (تعديل النظام، زيادة / تخفيض رأس المال، الاندماج، التحول، التصفية) تتطلب موافقة شركاء يمثلون 75% من رأس المال على الأقل، طبقاً للمادة 163 من نظام الشركات.

البند الثالث — التنازل عن الحصص (حق الأولوية):
لا يحق لأي شريك التنازل عن حصته للغير إلا بعد تقديمها للشركاء الآخرين بنفس الشروط والثمن. ولهم أجل ثلاثين يوماً للقبول، طبقاً للمادة 168 من النظام.

البند الرابع — الجمعية العامة:
تنعقد الجمعية العامة سنوياً للنظر في القوائم المالية وتقرير المدير، وتتخذ قراراتها بالأغلبية المطلقة لرأس المال إلا فيما اشترط له النظام أغلبية أعلى.

البند الخامس — توزيع الأرباح:
توزع الأرباح بنسبة الحصص بعد اقتطاع 10% احتياطياً نظامياً، ولا يجوز توزيع أرباح في غياب أرباح حقيقية محققة، طبقاً للمادة 173.

البند السادس — عدم المنافسة:
يلتزم الشركاء بعدم ممارسة أي نشاط منافس داخل المملكة العربية السعودية طوال مدة شراكتهم ولمدة سنتين بعد خروجهم.

البند السابع — السرية:
يلتزم الشركاء بسرية المعلومات التجارية والمالية للشركة طوال الشراكة ولمدة خمس سنوات بعدها.

البند الثامن — التحكيم:
يُحل كل نزاع ودياً أولاً، فإن تعذر، يُحال إلى التحكيم وفقاً لنظام التحكيم السعودي الصادر بالمرسوم الملكي رقم م/34 لسنة 1433 هـ، مقعده مدينة الرياض.

البند التاسع — الاختصاص:
يخضع هذا العقد لأنظمة المملكة العربية السعودية، وتختص المحاكم التجارية بالرياض بأي نزاع غير قابل للتحكيم.

التوقيعات:
{{partner_a}} ______________________   {{partner_b}} ______________________
TXT;
    }

    private static function aeShareholdersBody(): string
    {
        return <<<'TXT'
عقد مساهمين / SHAREHOLDERS' AGREEMENT

إنه في يوم {{effective_date}}، أُبرم هذا العقد في دولة الإمارات العربية المتحدة بين:
الطرف الأول (المساهم الأول / Shareholder A): {{shareholder_a}}، يمتلك {{shareholder_a_share_pct}}% من رأس المال.
الطرف الثاني (المساهم الثاني / Shareholder B): {{shareholder_b}}، يمتلك {{shareholder_b_share_pct}}% من رأس المال.

تمهيد:
لما كان الطرفان مساهمَين في شركة {{company_name}} (شركة ذات مسؤولية محدودة)، برأس مال قدره {{capital_aed}} درهم إماراتي، فقد اتفقا على تنظيم علاقتهما طبقاً لأحكام المرسوم بقانون اتحادي رقم 32 لسنة 2021 بشأن الشركات التجارية.

البند الأول — رأس المال والحصص:
رأس المال {{capital_aed}} درهم إماراتي، موزع وفقاً للنسب المذكورة أعلاه. يلتزم كل مساهم بسداد قيمة حصته بالكامل عند تسجيل الشركة.

البند الثاني — الإدارة:
1) يدير الشركة مدير معيّن بقرار من الجمعية العامة.
2) القرارات الجوهرية (تعديل النظام الأساسي، زيادة أو تخفيض رأس المال، الاندماج، التصفية) تتطلب موافقة 75% من رأس المال.
3) القرارات العادية تُتخذ بالأغلبية البسيطة.

البند الثالث — حق الأولوية والشفعة:
لا يحق لأي مساهم التنازل عن حصته للغير إلا بعد تقديمها للمساهم الآخر بنفس الشروط والثمن، ولمهلة لا تقل عن 30 يوماً.

البند الرابع — توزيع الأرباح:
توزع الأرباح الصافية بنسبة الحصص بعد اقتطاع الاحتياطي النظامي.

البند الخامس — عدم المنافسة:
يلتزم المساهمون بعدم ممارسة أي نشاط منافس داخل الإمارات العربية المتحدة طوال مدة شراكتهم ولمدة سنتين بعد خروجهم.

البند السادس — السرية:
سرية المعلومات التجارية والمالية ملزمة طوال الشراكة ولمدة خمس سنوات بعدها.

البند السابع — التحكيم:
يُحل كل نزاع بالتحكيم وفقاً لقواعد مركز دبي للتحكيم الدولي (DIAC) — أو يجوز للأطراف اختيار محاكم مركز دبي المالي العالمي (DIFC Courts) للفصل بالقانون الإنجليزي إذا اتفقا كتابةً.

البند الثامن — اللغة:
حُرر هذا العقد باللغتين العربية والإنجليزية. وفي حال أي تعارض، تكون الأولوية للنص العربي.

البند التاسع — الاختصاص:
يخضع هذا العقد لقانون دولة الإمارات العربية المتحدة، مع مراعاة شرط التحكيم أعلاه.

التوقيعات:
{{shareholder_a}} ______________________   {{shareholder_b}} ______________________
TXT;
    }

    private static function saLoanBody(): string
    {
        return <<<'TXT'
عقد قرض

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول (المُقرض): {{lender}}
الطرف الثاني (المُقترض): {{borrower}}

تمهيد:
رغب المُقترض في الحصول على قرض من المُقرض، ووافق المُقرض على تقديمه وفقاً للشروط الواردة بهذا العقد، طبقاً لأحكام نظام المعاملات المدنية السعودي الصادر بالمرسوم الملكي رقم م/191 لسنة 1444 هـ والأنظمة ذات الصلة الصادرة من البنك المركزي السعودي (ساما).

البند الأول — مبلغ القرض:
يقدم المُقرض للمُقترض مبلغاً قدره {{principal_sar}} ريال سعودي، يُسلَّم بموجب تحويل بنكي إلى الحساب المُحدَّد من المُقترض. يُعدّ هذا العقد إيصالاً باستلام المبلغ بمجرد إتمام التحويل.

البند الثاني — أرباح / تكلفة التمويل:
يلتزم المُقترض بسداد ربح / تكلفة تمويل سنوية بنسبة {{profit_rate_pct}}% على رصيد القرض القائم. (ملاحظة: يلتزم الطرفان بالأنظمة السعودية وتعليمات البنك المركزي السعودي بشأن سعر الفائدة المعقول، ولا يجوز تجاوز السقف المُعلَن، ولا تطبق هذه النسبة في حال كان أحد الطرفين منشأة مالية مرخصة من ساما إلا وفقاً لتعليماتها.)

البند الثالث — مدة القرض والسداد:
يُسدَّد القرض خلال {{term_months}} شهراً من تاريخه على أقساط شهرية متساوية، تُقتطع تلقائياً من حساب المُقترض في اليوم الأول من كل شهر.

البند الرابع — السداد المُبكِّر:
يحق للمُقترض السداد المُبكِّر كلياً أو جزئياً في أي وقت، مع احتساب الربح على المبلغ المُسدَّد فعلاً للفترة المُنقضية فقط. لا تُفرض غرامة سداد مبكر للمبالغ التي تقل عن سقف ساما المُعلَن.

البند الخامس — الإخلال والاستحقاق المُعجَّل:
في حالة تأخر المُقترض عن سداد قسطين متتاليين، يحق للمُقرض اعتبار باقي القرض مُستحقاً فوراً والمطالبة به دون الحاجة لإنذار، طبقاً لأحكام نظام المعاملات المدنية ونظام التنفيذ.

البند السادس — الضمانات:
(اختياري — يُحذف إن لم تكن هناك ضمانات) يقدم المُقترض كضمان لسداد القرض: ........................

البند السابع — التحويل:
لا يجوز للمُقترض التنازل عن التزاماته بموجب هذا العقد، ويحق للمُقرض التنازل عن حقوقه بإخطار كتابي للمُقترض.

البند الثامن — السند التنفيذي:
يُعد هذا العقد سنداً تنفيذياً بعد توثيقه أمام كاتب العدل، طبقاً للمادة 9 من نظام التنفيذ الصادر بالمرسوم الملكي رقم م/53 لسنة 1433 هـ.

البند التاسع — النظام والاختصاص:
يخضع هذا العقد لأنظمة المملكة العربية السعودية، وتختص المحاكم التجارية بالرياض بأي نزاع.

التوقيعات:
المُقرض: ______________________   المُقترض: ______________________
الشاهد الأول: ______________________   الشاهد الثاني: ______________________
TXT;
    }

    private static function saCommercialLeaseBody(): string
    {
        return <<<'TXT'
عقد إيجار محل تجاري / إداري

إنه في يوم {{start_date}}، تم الاتفاق بين:
الطرف الأول (المؤجر): {{landlord}}
الطرف الثاني (المستأجر): {{tenant}}

تمهيد:
يخضع هذا العقد لأحكام الإجارة المنصوص عليها في نظام المعاملات المدنية السعودي الصادر بالمرسوم الملكي رقم م/191 لسنة 1444 هـ وللوائح الصادرة من وزارة الشؤون البلدية والقروية والإسكان، مع تسجيله في منصة "إيجار" التابعة لوزارة الإسكان طبقاً للمتطلبات النظامية.

البند الأول — العين المؤجرة:
أجر المؤجر للمستأجر الوحدة الكائنة في {{unit_address}}، بحالتها المعاينة، لاستعمالها في غرض: {{use_purpose}}، دون تغييره دون موافقة كتابية من المؤجر.

البند الثاني — مدة الإيجار:
تكون مدة هذا العقد {{term_years}} سنوات، تبدأ من {{start_date}}، قابلة للتجديد باتفاق مكتوب بين الطرفين قبل انتهاء المدة بـ60 يوماً على الأقل.

البند الثالث — الأجرة:
الأجرة الشهرية {{monthly_rent_sar}} ريال سعودي، شاملةً ضريبة القيمة المضافة، تُسدَّد مقدماً عبر التحويل البنكي خلال الأسبوع الأول من كل شهر ميلادي. تزيد الأجرة بنسبة 5% سنوياً اعتباراً من السنة الثانية، ما لم يتفق على غير ذلك كتابة.

البند الرابع — التأمين:
يدفع المستأجر تأميناً قدره أجرة شهرين يُرَدّ عند انتهاء العقد بعد التحقق من سلامة العين وسداد كامل الالتزامات.

البند الخامس — التزامات المستأجر:
1) سداد الأجرة في مواعيدها وسداد فواتير المرافق (كهرباء، مياه، خدمات).
2) المحافظة على العين وإجراء الإصلاحات البسيطة على نفقته.
3) عدم إجراء تعديلات إنشائية دون موافقة كتابية مسبقة.
4) الحصول على جميع التراخيص اللازمة لمزاولة النشاط من البلدية ووزارة التجارة على نفقته.

البند السادس — التزامات المؤجر:
1) تسليم العين صالحة للاستعمال في الغرض المتفق عليه.
2) إجراء الإصلاحات الجوهرية الضرورية لاستمرار صلاحية العين.
3) عدم التعرض الشخصي أو من الغير للمستأجر طوال مدة الإيجار، طبقاً لمواد الضمان في نظام المعاملات المدنية.

البند السابع — التنازل والتأجير من الباطن:
لا يحق للمستأجر التنازل عن العقد أو تأجير العين من الباطن دون موافقة كتابية مسبقة من المؤجر.

البند الثامن — الإنهاء:
يحق لأي طرف إنهاء العقد قبل مدته بإخطار كتابي مدته 90 يوماً، أو فوراً في حالة إخلال جوهري لم يُصلَح خلال 30 يوماً من الإنذار.

البند التاسع — الترميمات وإعادة الحالة:
عند انتهاء العقد، يلتزم المستأجر بإعادة العين إلى حالتها الأصلية، باستثناء الاستهلاك العادي، وإلا خُصمت تكلفة الترميم من التأمين.

البند العاشر — التسجيل:
يلتزم الطرفان بتسجيل هذا العقد في منصة "إيجار" خلال مدة لا تتجاوز ستين يوماً من تاريخ التوقيع، طبقاً للائحة التنفيذية ذات الصلة.

البند الحادي عشر — النظام والاختصاص:
يخضع هذا العقد لأنظمة المملكة العربية السعودية، وتختص المحاكم التجارية بمدينة العقار بأي نزاع.

التوقيعات:
المؤجر: ______________________   المستأجر: ______________________
TXT;
    }

    private static function saTerminationMutualBody(): string
    {
        return <<<'TXT'
اتفاق إنهاء علاقة عمل ودياً وإقرار إبراء ذمة

إنه تم الاتفاق بين:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

تمهيد:
لما كانت علاقة العمل قائمة بين الطرفين، ورغب الطرفان في إنهائها بالتراضي وفقاً للمادة 74/2 من نظام العمل الصادر بالمرسوم الملكي رقم م/51 لسنة 1426 هـ وتعديلاته، فقد اتفقا على ما يلي:

البند الأول — تاريخ انتهاء العمل:
تنتهي علاقة العمل بين الطرفين بتاريخ {{last_working_day}}، ويُعدّ هذا التاريخ آخر يوم خدمة فعلي للعامل.

البند الثاني — تسوية المستحقات:
يلتزم صاحب العمل بسداد للعامل مبلغاً إجمالياً قدره {{settlement_amount_sar}} ريال سعودي، يشمل:
أ) الأجر المستحق عن أيام العمل المُنجَزة.
ب) رصيد الإجازات السنوية غير المُستعمَلة.
ج) مكافأة نهاية الخدمة المحتسبة طبقاً للمادة 84 من نظام العمل: نصف أجر شهر عن كل سنة من السنوات الخمس الأولى، وأجر شهر كامل عن كل سنة بعد ذلك، عن المدة الفعلية للخدمة.
د) أي بدلات أو حوافز مستحقة حتى تاريخ الانتهاء.
يُسدَّد المبلغ بتحويل بنكي عبر نظام حماية الأجور خلال سبعة أيام من توقيع هذا الاتفاق.

البند الثالث — إقرار العامل بالإبراء:
يُقر العامل بأن المبلغ المُشار إليه يمثل تسويةً نهائيةً وكاملةً لجميع مستحقاته النظامية والتعاقدية لدى صاحب العمل، ويُبرئ ذمته إبراءً تاماً غير قابل للرجوع فيه من جميع المطالبات الحالية والمستقبلية الناشئة عن أو المتعلقة بعلاقة العمل أو إنهائها، طبقاً لأحكام النظام.

البند الرابع — التزامات لاحقة للعامل:
1) السرية: يلتزم العامل بسرية المعلومات التجارية والفنية لصاحب العمل أثناء وبعد انتهاء الخدمة، طبقاً للمادة 83 من نظام العمل.
2) عدم المنافسة: يلتزم العامل بعدم العمل لدى منافس مباشر داخل المملكة العربية السعودية لمدة سنتين بعد انتهاء العقد، طبقاً للمادة 83.
3) رد العهد: يلتزم العامل بإعادة جميع العهد والمستندات والأجهزة الخاصة بصاحب العمل في موعد أقصاه آخر يوم عمل.
4) عدم استقطاب: يلتزم العامل بعدم استقطاب موظفي صاحب العمل أو عملائه لمدة سنة من تاريخ هذا الاتفاق.

البند الخامس — تسليم الشهادة وإنهاء الاشتراك:
يلتزم صاحب العمل بتسليم شهادة الخبرة وإنهاء الاشتراك في المؤسسة العامة للتأمينات الاجتماعية ونقل ملف العامل في منصة قوى ومدد خلال 14 يوماً من تاريخ الانتهاء.

البند السادس — السرية المتبادلة:
يلتزم الطرفان بسرية شروط هذا الاتفاق ومبلغ التسوية، إلا فيما يستلزمه القانون.

البند السابع — عدم الاعتراف بالمسؤولية:
لا يُعتبر هذا الاتفاق إقراراً من صاحب العمل بأي إخلال، وهو إنهاء بالتراضي وفقاً للمادة 74/2.

البند الثامن — النظام والاختصاص:
يخضع هذا الاتفاق لأنظمة المملكة العربية السعودية، وتختص المحاكم العمالية المختصة بأي نزاع.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
الشاهد الأول: ______________________   الشاهد الثاني: ______________________
TXT;
    }

    private static function aeLoanBody(): string
    {
        return <<<'TXT'
عقد قرض

إنه في يوم {{effective_date}}، أُبرم هذا العقد في دولة الإمارات العربية المتحدة بين:
الطرف الأول (المُقرض): {{lender}}
الطرف الثاني (المُقترض): {{borrower}}

تمهيد:
رغب المُقترض في الحصول على قرض من المُقرض، ووافق المُقرض على تقديمه وفقاً للشروط الواردة بهذا العقد، طبقاً لأحكام عقد القرض في قانون المعاملات المدنية الاتحادي (القانون الاتحادي رقم 5 لسنة 1985 وتعديلاته) ومراعاة المرسوم بقانون اتحادي رقم 14 لسنة 2018 بشأن المصرف المركزي وأحكام تنظيم الائتمان حيث ينطبق.

البند الأول — مبلغ القرض:
يقدم المُقرض للمُقترض مبلغاً قدره {{principal_aed}} درهم إماراتي، يُسلَّم بموجب تحويل بنكي إلى الحساب المُحدَّد من المُقترض. يُعدّ هذا العقد إيصالاً باستلام المبلغ بمجرد إتمام التحويل.

البند الثاني — التكلفة / الفائدة:
يلتزم المُقترض بسداد فائدة سنوية بنسبة {{profit_rate_pct}}% على رصيد القرض القائم، تُحتسب يومياً وتُسدَّد مع الأقساط الشهرية. (ملاحظة: تخضع نسبة الفائدة لتعليمات المصرف المركزي الإماراتي بشأن السقوف الجائزة، ولا يجوز فرضها بين أطراف غير تجارية بشكل مخالف للقانون.)

البند الثالث — مدة القرض والسداد:
يُسدَّد القرض خلال {{term_months}} شهراً من تاريخه على أقساط شهرية متساوية تشمل أصل المبلغ والفائدة، وذلك في اليوم الأول من كل شهر.

البند الرابع — السداد المُبكِّر:
يحق للمُقترض السداد المُبكِّر كلياً أو جزئياً في أي وقت، مع احتساب الفائدة على المبلغ المُسدَّد فعلاً للفترة المُنقضية فقط.

البند الخامس — الإخلال والاستحقاق المُعجَّل:
في حالة تأخر المُقترض عن سداد قسطين متتاليين، يحق للمُقرض اعتبار باقي القرض مُستحقاً فوراً والمطالبة به دون الحاجة لإنذار.

البند السادس — الضمانات:
(اختياري — يُحذف إن لم تكن هناك ضمانات) يقدم المُقترض كضمان لسداد القرض: ........................

البند السابع — التحويل:
لا يجوز للمُقترض التنازل عن التزاماته بموجب هذا العقد، ويحق للمُقرض التنازل عن حقوقه بإخطار كتابي للمُقترض.

البند الثامن — القانون والاختصاص:
يخضع هذا العقد لقانون دولة الإمارات العربية المتحدة، وتختص محاكم دبي / المحاكم الاتحادية بأي نزاع، ويجوز للأطراف اختيار محاكم مركز دبي المالي العالمي (DIFC Courts) أو محاكم سوق أبوظبي العالمي (ADGM Courts) كتابةً.

التوقيعات:
المُقرض: ______________________   المُقترض: ______________________
الشاهد الأول: ______________________   الشاهد الثاني: ______________________
TXT;
    }

    private static function aeCommercialLeaseBody(): string
    {
        return <<<'TXT'
عقد إيجار محل تجاري / إداري

إنه في يوم {{start_date}}، أُبرم هذا العقد في دولة الإمارات العربية المتحدة بين:
الطرف الأول (المؤجر): {{landlord}}
الطرف الثاني (المستأجر): {{tenant}}

تمهيد:
يخضع هذا العقد للأحكام الخاصة بالإيجار في قانون المعاملات المدنية الاتحادي (القانون الاتحادي رقم 5 لسنة 1985 وتعديلاته)، وتسري عليه القوانين الخاصة بالإمارة التي تقع فيها العين المؤجرة، بما في ذلك القانون رقم 26 لسنة 2007 بشأن تنظيم العلاقة بين المؤجرين والمستأجرين في إمارة دبي وتعديلاته (القانون رقم 33 لسنة 2008)، أو القانون رقم 20 لسنة 2006 لإمارة أبوظبي بشأن تنظيم قطاع الإيجارات وتعديلاته، حسب الإمارة.

البند الأول — العين المؤجرة:
أجر المؤجر للمستأجر الوحدة الكائنة في {{unit_address}}، بحالتها المعاينة، لاستعمالها في غرض: {{use_purpose}}، دون تغييره دون موافقة كتابية من المؤجر.

البند الثاني — مدة الإيجار:
تكون مدة هذا العقد {{term_years}} سنوات، تبدأ من {{start_date}}، قابلة للتجديد باتفاق مكتوب بين الطرفين قبل انتهاء المدة بـ90 يوماً على الأقل (مراعاةً لإخطار عدم التجديد المنصوص عليه في القانون 33/2008 لإمارة دبي).

البند الثالث — الأجرة:
الأجرة السنوية تعادل {{monthly_rent_aed}} درهم إماراتي شهرياً، شاملةً ضريبة القيمة المضافة (5%)، تُسدَّد عبر شيكات سنوية / ربع سنوية حسب الاتفاق. تخضع الزيادة في الأجرة عند التجديد لمؤشر الإيجارات الصادر عن دائرة الأراضي والأملاك (RERA) في دبي أو الجهة المماثلة في كل إمارة.

البند الرابع — التأمين:
يدفع المستأجر تأميناً قدره أجرة شهرين يُرَدّ عند انتهاء العقد بعد التحقق من سلامة العين وسداد كامل الالتزامات.

البند الخامس — التزامات المستأجر:
1) سداد الأجرة في مواعيدها وسداد فواتير المرافق (كهرباء، مياه، شيلر، خدمات).
2) المحافظة على العين وإجراء الإصلاحات البسيطة على نفقته.
3) عدم إجراء تعديلات إنشائية دون موافقة كتابية مسبقة.
4) الحصول على جميع التراخيص اللازمة لمزاولة النشاط من دائرة التنمية الاقتصادية والبلدية على نفقته.

البند السادس — التزامات المؤجر:
1) تسليم العين صالحة للاستعمال في الغرض المتفق عليه.
2) إجراء الإصلاحات الجوهرية الضرورية لاستمرار صلاحية العين.
3) عدم التعرض الشخصي أو من الغير للمستأجر طوال مدة الإيجار.

البند السابع — التنازل والتأجير من الباطن:
لا يحق للمستأجر التنازل عن العقد أو تأجير العين من الباطن دون موافقة كتابية مسبقة من المؤجر، طبقاً للمادة 24 من القانون 26/2007 لإمارة دبي والأحكام المماثلة.

البند الثامن — الإنهاء:
يخضع إنهاء هذا العقد للأسباب المنصوص عليها في القانون المحلي للإمارة (المادة 25 من قانون 26/2007 لدبي وما يماثلها)، مع التزام الطرف الراغب في الإنهاء بإخطار كتابي مدته 90 يوماً.

البند التاسع — التسجيل:
يلتزم الطرفان بتسجيل هذا العقد لدى الجهة المختصة (Ejari في دبي / Tawtheeq في أبوظبي) خلال 60 يوماً من تاريخ التوقيع.

البند العاشر — الترميمات وإعادة الحالة:
عند انتهاء العقد، يلتزم المستأجر بإعادة العين إلى حالتها الأصلية، باستثناء الاستهلاك العادي.

البند الحادي عشر — القانون والاختصاص:
يخضع هذا العقد لقانون دولة الإمارات وقوانين الإمارة المختصة، وتختص لجنة فض المنازعات الإيجارية بالإمارة المعنية بالفصل في أي نزاع، ثم محاكم الإمارة على درجة الاستئناف.

التوقيعات:
المؤجر: ______________________   المستأجر: ______________________
TXT;
    }

    private static function aeTerminationMutualBody(): string
    {
        return <<<'TXT'
اتفاق إنهاء علاقة عمل ودياً وإقرار إبراء ذمة

إنه أُبرم هذا الاتفاق في دولة الإمارات العربية المتحدة بين:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

تمهيد:
لما كانت علاقة العمل قائمة بين الطرفين، ورغب الطرفان في إنهائها بالتراضي وفقاً للمادة 42/1 من المرسوم بقانون اتحادي رقم 33 لسنة 2021 بشأن تنظيم علاقات العمل وتعديلاته (بما فيها القانون رقم 9 لسنة 2024)، فقد اتفقا على ما يلي:

البند الأول — تاريخ انتهاء العمل:
تنتهي علاقة العمل بين الطرفين بتاريخ {{last_working_day}}، ويُعدّ هذا التاريخ آخر يوم خدمة فعلي للعامل.

البند الثاني — تسوية المستحقات:
يلتزم صاحب العمل بسداد للعامل مبلغاً إجمالياً قدره {{settlement_amount_aed}} درهم إماراتي، يشمل:
أ) الأجر المستحق عن أيام العمل المُنجَزة.
ب) رصيد الإجازات السنوية غير المُستعمَلة.
ج) مكافأة نهاية الخدمة المحتسبة طبقاً للمادة 30 من المرسوم بقانون 33/2021: ثلث أجر شهر عن كل سنة من السنوات الخمس الأولى، وأجر شهر كامل عن كل سنة بعد ذلك، بحد أقصى أجر سنتين.
د) أي بدلات أو حوافز مستحقة حتى تاريخ الانتهاء.
هـ) تذكرة العودة إلى الوطن إذا كان العامل من خارج الدولة، طبقاً للمادة 13 من القانون.
يُسدَّد المبلغ عبر نظام حماية الأجور (WPS) خلال 14 يوماً من تاريخ الانتهاء، طبقاً للمادة 53 من القانون.

البند الثالث — إقرار العامل بالإبراء:
يُقر العامل بأن المبلغ المُشار إليه يمثل تسويةً نهائيةً وكاملةً لجميع مستحقاته القانونية والتعاقدية لدى صاحب العمل، ويُبرئ ذمته إبراءً تاماً غير قابل للرجوع فيه من جميع المطالبات.

البند الرابع — التزامات لاحقة للعامل:
1) السرية: يلتزم العامل بسرية المعلومات أثناء وبعد انتهاء الخدمة، طبقاً للمادة 10 من القانون.
2) عدم المنافسة: يلتزم العامل بعدم العمل لدى منافس مباشر داخل الدولة لمدة لا تتجاوز سنتين بعد انتهاء العقد ضمن نطاق جغرافي محدد، طبقاً للمادة 10 من القانون والقرار الوزاري رقم 47/2022.
3) رد العهد: يلتزم العامل بإعادة جميع العهد والمستندات والأجهزة في موعد أقصاه آخر يوم عمل.
4) عدم استقطاب: يلتزم العامل بعدم استقطاب موظفي صاحب العمل أو عملائه لمدة سنة من تاريخ هذا الاتفاق.

البند الخامس — تسليم الشهادة وإلغاء الإقامة:
يلتزم صاحب العمل بتسليم شهادة الخبرة وإلغاء تأشيرة العمل والإقامة وفقاً للإجراءات المعتمدة لدى وزارة الموارد البشرية والتوطين والهيئة الاتحادية للهوية والجنسية والجمارك وأمن المنافذ، خلال 14 يوماً من تاريخ الانتهاء.

البند السادس — السرية المتبادلة:
يلتزم الطرفان بسرية شروط هذا الاتفاق ومبلغ التسوية، إلا فيما يستلزمه القانون.

البند السابع — عدم الاعتراف بالمسؤولية:
لا يُعتبر هذا الاتفاق إقراراً من صاحب العمل بأي إخلال، وهو إنهاء بالتراضي وفقاً للمادة 42/1.

البند الثامن — القانون والاختصاص:
يخضع هذا الاتفاق لقانون دولة الإمارات العربية المتحدة، وتختص وزارة الموارد البشرية والتوطين بالنظر في أي نزاع كأول درجة، ثم المحاكم العمالية الاتحادية / المحلية حسب الإمارة.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
الشاهد الأول: ______________________   الشاهد الثاني: ______________________
TXT;
    }

    private static function saMouLoiBody(): string
    {
        return <<<'TXT'
مذكرة تفاهم / خطاب نوايا

إنه في يوم {{effective_date}}، تم الاتفاق بين كلٍ من:
الطرف الأول: {{party_a}}
الطرف الثاني: {{party_b}}

تمهيد:
يرغب الطرفان في الدخول في مفاوضات بشأن: {{transaction_summary}}، ويرغبان في توثيق المبادئ الأساسية للمحادثات الجارية بينهما، على أن يتم التفاوض على عقد ملزم نهائي لاحقاً.

البند الأول — طبيعة هذه المذكرة:
هذه المذكرة تعبير عن نية حسنة فقط، وليست عقداً ملزماً عدا فيما يتعلق بالبنود الواردة في الفقرات (السرية، الحصرية، النفقات، النظام والاختصاص) أدناه. لا تنشأ بموجبها أي التزامات نظامية بإتمام الصفقة.

البند الثاني — التفاوض بحسن نية:
يلتزم الطرفان بالتفاوض بحسن نية بشأن الشروط التجارية والنظامية للصفقة، بما في ذلك الثمن، الضمانات، الإقرارات، والشروط السابقة للإقفال، وفقاً لمبدأ حسن النية المنصوص عليه في نظام المعاملات المدنية السعودي الصادر بالمرسوم الملكي رقم م/191 وتاريخ 29/11/1444 هـ.

البند الثالث — السرية (ملزم):
يلتزم كل طرف بالحفاظ على سرية المعلومات المتبادلة، وعدم إفشائها لأي طرف ثالث دون موافقة كتابية.

البند الرابع — الحصرية (ملزم):
يلتزم الطرفان بالتفاوض الحصري فيما بينهما لمدة {{exclusivity_months}} أشهر من تاريخ هذه المذكرة، وعدم الدخول في مفاوضات موازية مع الغير بشأن نفس الصفقة.

البند الخامس — المصاريف (ملزم):
يتحمل كل طرف مصاريفه الخاصة (مستشاريه القانونيين والماليين) سواء أُتمت الصفقة أم لا.

البند السادس — مدة سريان المذكرة:
تظل هذه المذكرة سارية حتى أبكر من: (أ) توقيع العقد النهائي، (ب) انقضاء فترة الحصرية، (ج) إخطار كتابي من أي طرف بعدم رغبته في الاستمرار.

البند السابع — النظام والاختصاص (ملزم):
يخضع تفسير هذه المذكرة لأنظمة المملكة العربية السعودية، وتختص المحاكم التجارية بالرياض بأي نزاع.

التوقيعات:
{{party_a}} ______________________   {{party_b}} ______________________
TXT;
    }

    private static function saBoardResolutionBody(): string
    {
        return <<<'TXT'
محضر اجتماع مجلس إدارة / قرار شركاء

الشركة: {{company_name}}
تاريخ الاجتماع: {{meeting_date}}

اجتمع مجلس الإدارة (أو الجمعية العامة للشركاء) بمقر الشركة بتاريخ المذكور أعلاه، وحضر الاجتماع نصاب صحيح من الأعضاء طبقاً للمادة 67 من نظام الشركات السعودي الصادر بالمرسوم الملكي رقم م/132 وتاريخ 1/1/1444 هـ، وبعد التداول، صدر القرار التالي:

موضوع القرار:
{{resolution_subject}}

مسوّغ القرار:
بعد الاطلاع على المستندات والتقارير ذات الصلة، ورأي المستشار النظامي للشركة، تبيّن أن إصدار هذا القرار يقع ضمن صلاحيات مجلس الإدارة / الشركاء وفقاً لعقد التأسيس واللائحة الداخلية للشركة، ولا يتعارض مع أحكام نظام الشركات ولائحته التنفيذية ولا أي نظام آخر معمول به في المملكة العربية السعودية.

نص القرار:
1) الموافقة بالأغلبية المنصوص عليها في عقد التأسيس على ما ورد في موضوع القرار أعلاه، طبقاً للمادتين 71 و163 من نظام الشركات.
2) تفويض السيد/السيدة {{authorized_signatory}} في التوقيع نيابةً عن الشركة على جميع المستندات اللازمة لتنفيذ هذا القرار، بما في ذلك العقود والإقرارات والتفويضات أمام كافة الجهات الحكومية والمصرفية ومركز الإيداع وهيئة السوق المالية.
3) تفويض المدير في تسجيل القرار لدى وزارة التجارة (السجل التجاري) إذا تطلّب النظام ذلك.
4) سريان هذا القرار من تاريخ صدوره.

أُقفل المحضر في تمام الساعة .....

التوقيعات:
أعضاء المجلس / الشركاء الحاضرون:
1. ______________________   2. ______________________   3. ______________________

أمين السر: ______________________
TXT;
    }

    private static function saSaasBody(): string
    {
        return <<<'TXT'
عقد اشتراك في خدمة برمجية (SaaS)

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول (مقدم الخدمة): {{provider}}
الطرف الثاني (العميل): {{customer}}

تمهيد:
يقدم مقدم الخدمة منصة برمجية باسم "{{service_name}}" تعمل عبر الإنترنت، ويرغب العميل في الاشتراك فيها وفقاً للشروط التالية:

البند الأول — حق الاستخدام:
يمنح مقدم الخدمة العميل ترخيصاً غير حصري وغير قابل للتنازل لاستخدام الخدمة طوال مدة هذا العقد، لأغراض العمل الداخلي للعميل فقط.

البند الثاني — رسوم الاشتراك:
يلتزم العميل بسداد {{subscription_fee_sar}} ريال سعودي شهرياً / سنوياً، شاملاً ضريبة القيمة المضافة. يتم السداد خلال 15 يوماً من تاريخ الفاتورة عبر التحويل البنكي أو نظام الفوترة الإلكترونية المعتمد من هيئة الزكاة والضريبة والجمارك.

البند الثالث — مدة العقد:
تكون مدة الاشتراك {{term_months}} شهراً ابتداءً من {{effective_date}}، وتُجدَّد تلقائياً ما لم يخطر أي طرف الآخر كتابةً قبل 30 يوماً من نهاية المدة.

البند الرابع — مستوى الخدمة (SLA):
1) يلتزم مقدم الخدمة بتوافر لا يقل عن 99% شهرياً، باستثناء فترات الصيانة المُعلَنة مسبقاً.
2) في حالة انخفاض التوافر عن الحد المتفق عليه، يستحق العميل خصماً متناسباً من رسوم الشهر التالي.
3) يقدم مقدم الخدمة دعماً فنياً عبر البريد الإلكتروني خلال ساعات العمل الرسمية بتوقيت المملكة العربية السعودية.

البند الخامس — البيانات الشخصية والخصوصية:
1) تظل بيانات العميل وعملائه ملكاً للعميل، ويعمل مقدم الخدمة كـ"معالج" للبيانات بالمعنى الوارد في نظام حماية البيانات الشخصية السعودي الصادر بالمرسوم الملكي رقم م/19 لسنة 1443 هـ ولائحته التنفيذية.
2) يلتزم مقدم الخدمة بمعالجة البيانات فقط لأغراض تشغيل الخدمة، وعدم نقلها خارج المملكة دون استيفاء شروط النقل المنصوص عليها في النظام والحصول على الموافقات اللازمة من الهيئة السعودية للبيانات والذكاء الاصطناعي (سدايا).
3) يُبرم الطرفان ملحق معالجة بيانات (DPA) يصبح جزءاً لا يتجزأ من هذا العقد.
4) عند انتهاء العقد، يمنح مقدم الخدمة العميل مدة 30 يوماً لاستخراج بياناته، ثم تُحذف بشكل نهائي.

البند السادس — الملكية الفكرية:
تظل كافة حقوق الملكية الفكرية للخدمة وكودها وواجهتها مملوكة لمقدم الخدمة، طبقاً لنظام حماية حقوق المؤلف. لا يجوز للعميل عكس الهندسة (reverse engineering) أو إعادة بيع الخدمة.

البند السابع — حدود المسؤولية:
لا تتجاوز المسؤولية الكلية لأي طرف في أي حال إجمالي الرسوم المدفوعة في الاثني عشر شهراً السابقة، ولا يكون أيٌّ من الطرفين مسؤولاً عن الأضرار غير المباشرة، طبقاً للمادة 137 من نظام المعاملات المدنية.

البند الثامن — السرية:
يلتزم كل طرف بسرية المعلومات الفنية والتجارية المتبادلة طوال مدة العقد ولمدة ثلاث سنوات بعد انتهائه.

البند التاسع — الإنهاء:
يجوز لأي طرف إنهاء العقد بإنذار كتابي مدته 30 يوماً، أو فوراً في حالة إخلال جوهري لم يُصلَح خلال 15 يوماً من الإخطار.

البند العاشر — النظام والاختصاص:
يخضع هذا العقد لأنظمة المملكة العربية السعودية، وتختص المحاكم التجارية بالرياض بالفصل في أي نزاع.

التوقيعات:
مقدم الخدمة: ______________________   العميل: ______________________
TXT;
    }

    private static function saIpAssignmentBody(): string
    {
        return <<<'TXT'
عقد تنازل عن حقوق الملكية الفكرية

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول (المتنازِل / المؤلف): {{assignor}}
الطرف الثاني (المتنازَل إليه): {{assignee}}

تمهيد:
لما كان المتنازِل قد أبدع / طوّر العمل الموصوف أدناه، ورغب في نقل ملكيته بالكامل إلى المتنازَل إليه مقابل المقابل المُحدَّد، فقد اتفق الطرفان على ما يلي وفقاً لأحكام نظام حماية حقوق المؤلف الصادر بالمرسوم الملكي رقم م/41 لسنة 1424 هـ ولائحته التنفيذية:

البند الأول — وصف العمل:
{{work_description}}

البند الثاني — التنازل:
يتنازل المتنازِل بموجب هذا العقد تنازلاً مطلقاً نهائياً للمتنازَل إليه عن جميع حقوقه المالية في العمل المذكور أعلاه، بما في ذلك:
1) حق النشر والتوزيع والاستغلال التجاري في جميع الوسائط الحالية والمستقبلية.
2) حق التعديل والترجمة وإنتاج أعمال مشتقة.
3) جميع التطبيقات والاكتشافات والتحسينات المتعلقة بالعمل.
4) حق تسجيل الملكية الفكرية (براءات اختراع، علامات تجارية، تصاميم) باسم المتنازَل إليه أمام الهيئة السعودية للملكية الفكرية.

البند الثالث — المقابل:
يتعهد المتنازَل إليه بسداد مبلغ {{consideration_sar}} ريال سعودي للمتنازِل عند توقيع هذا العقد، يُقر المتنازِل باستلامه ومخالصته من جميع الحقوق المالية المتعلقة بالعمل.

البند الرابع — إقرارات وضمانات المتنازِل:
يقر المتنازِل ويضمن:
1) أنه المؤلف الأصلي للعمل وأنه أنجزه بنفسه.
2) أن العمل لا ينتهك أي حق ملكية فكرية للغير.
3) أنه لم يسبق له التنازل عن العمل أو منح ترخيص لأي طرف ثالث.
4) أن العمل خالٍ من أي رهون أو حقوق عينية.

البند الخامس — الحقوق الأدبية:
تظل الحقوق الأدبية للمتنازِل (الحق في نسبة العمل إليه وفي الاعتراض على تشويهه) محفوظةً وغير قابلة للتنازل، طبقاً للمادة 8 من نظام حماية حقوق المؤلف. ويلتزم المتنازَل إليه باحترام هذه الحقوق.

البند السادس — التزامات لاحقة:
يلتزم المتنازِل بتوقيع أي مستندات إضافية يستلزمها تسجيل التنازل أمام الجهات المختصة، بما في ذلك الهيئة السعودية للملكية الفكرية (SAIP).

البند السابع — السرية:
يلتزم المتنازِل بسرية تفاصيل العمل واستخداماته التجارية لمدة خمس سنوات.

البند الثامن — النظام والاختصاص:
يخضع هذا العقد لأنظمة المملكة العربية السعودية، وتختص المحكمة التجارية المختصة بأي نزاع، مع مراعاة الاختصاص الخاص للجان الفصل في منازعات الملكية الفكرية.

التوقيعات:
المتنازِل: ______________________   المتنازَل إليه: ______________________
TXT;
    }

    private static function saDpaBody(): string
    {
        return <<<'TXT'
ملحق معالجة بيانات شخصية (DPA)

إنه في يوم {{effective_date}}، أُبرم هذا الملحق بين:
الطرف الأول (المُتحكم / Data Controller): {{controller}}
الطرف الثاني (المُعالج / Data Processor): {{processor}}

تمهيد:
ينظم هذا الملحق معالجة البيانات الشخصية بين المُتحكم والمُعالج وفقاً لنظام حماية البيانات الشخصية السعودي الصادر بالمرسوم الملكي رقم م/19 وتاريخ 9/2/1443 هـ ولائحته التنفيذية الصادرة من الهيئة السعودية للبيانات والذكاء الاصطناعي (سدايا).

البند الأول — الغرض من المعالجة:
{{processing_purpose}}

البند الثاني — فئات البيانات:
يقتصر نطاق هذا الملحق على البيانات الشخصية التالية: {{data_categories}}.

البند الثالث — التزامات المُعالج:
يلتزم المُعالج بـ:
1) معالجة البيانات فقط بناءً على التعليمات الموثقة من المُتحكم وللغرض المذكور.
2) ضمان أن جميع الأشخاص المُخوَّلين بالوصول إلى البيانات ملتزمون بالسرية.
3) تطبيق التدابير التقنية والتنظيمية المناسبة لحماية البيانات (تشفير، تحكم بالوصول، نسخ احتياطي، سجلات تدقيق) طبقاً للمادة 19 من النظام.
4) عدم إشراك معالج فرعي إلا بإذن كتابي مسبق من المُتحكم، مع نقل التزامات هذا الملحق إلى المعالج الفرعي.
5) معاونة المُتحكم في الوفاء بالتزاماته تجاه أصحاب البيانات (حق الوصول، التصحيح، المحو، نقل البيانات).
6) إخطار المُتحكم خلال 24 ساعة من العلم بأي حادث متعلق بالبيانات الشخصية، طبقاً للمادة 20 من النظام.

البند الرابع — نقل البيانات خارج المملكة:
لا يجوز للمُعالج نقل البيانات الشخصية خارج المملكة العربية السعودية إلا في الحالات المنصوص عليها في الفصل السابع من النظام واللائحة التنفيذية، وبشرط الحصول على موافقة كتابية من المُتحكم وعلى الضمانات اللازمة من المركز الوطني للأمن السيبراني والهيئة السعودية للبيانات والذكاء الاصطناعي.

البند الخامس — مدة الاحتفاظ والحذف:
يلتزم المُعالج بحذف البيانات أو إعادتها للمُتحكم عند انتهاء غرض المعالجة أو إنهاء العقد الرئيسي، خلال 30 يوماً من تاريخ الإنهاء، وتقديم شهادة حذف موقعة، ما لم يلزم النظام بالاحتفاظ بها لمدة محددة.

البند السادس — التدقيق:
يحق للمُتحكم — مرة سنوياً على الأقل أو عند وقوع حادث — إجراء تدقيق على إجراءات المُعالج أو تكليف طرف مستقل لذلك، مع إخطار مسبق لا يقل عن 14 يوماً.

البند السابع — المسؤولية والتعويض:
يلتزم المُعالج بتعويض المُتحكم عن أي عقوبات إدارية أو غرامات أو أضرار ناشئة عن إخلاله بالتزاماته في هذا الملحق، طبقاً لأحكام النظام الذي يقرر عقوبات تصل إلى 5,000,000 ريال سعودي عن المخالفات الجسيمة.

البند الثامن — الأولوية:
في حال أي تعارض بين هذا الملحق والعقد الرئيسي، تكون الأولوية لهذا الملحق فيما يخص معالجة البيانات الشخصية.

التوقيعات:
المُتحكم: ______________________   المُعالج: ______________________
TXT;
    }

    private static function saSettlementBody(): string
    {
        return <<<'TXT'
اتفاق تصالح وتسوية نهائية

إنه في يوم {{effective_date}}، تم إبرام هذا الاتفاق بين:
الطرف الأول: {{party_a}}
الطرف الثاني: {{party_b}}

تمهيد:
نشأ بين الطرفين نزاع موضوعه: {{dispute_summary}}، ورغبةً منهما في إنهاء هذا النزاع ودياً وتفادي التقاضي، فقد اتفقا على ما يلي طبقاً لأحكام عقد الصلح في نظام المعاملات المدنية السعودي الصادر بالمرسوم الملكي رقم م/191 لسنة 1444 هـ:

البند الأول — موضوع التصالح:
ينهي الطرفان بموجب هذا الاتفاق جميع المنازعات والمطالبات الحالية بينهما المتعلقة بالموضوع المُشار إليه في التمهيد.

البند الثاني — مبلغ التسوية:
يلتزم {{party_a}} بسداد مبلغ {{settlement_amount_sar}} ريال سعودي لـ {{party_b}} خلال 15 يوماً من توقيع هذا الاتفاق، وذلك تسويةً نهائيةً وكاملةً للنزاع.

البند الثالث — إقرار إبراء الذمة المتبادل:
يُقر كل طرف بإبراء ذمة الطرف الآخر إبراءً نهائياً وغير قابل للرجوع فيه من جميع الحقوق والمطالبات المتعلقة بالنزاع موضوع هذا الاتفاق، سواء كانت قائمة أو مستقبلية، معروفة أو غير معروفة.

البند الرابع — التنازل عن الدعاوى:
يتنازل الطرفان عن أي دعاوى قضائية أو تنفيذية متبادلة قائمة بشأن النزاع، ويقدمان طلباً لشطبها وانتهاء الخصومة أمام المحاكم المختصة.

البند الخامس — السرية:
يلتزم الطرفان بسرية تفاصيل التسوية والمبلغ المدفوع، وعدم الإفصاح عنها لأي طرف ثالث إلا بأمر قضائي أو موافقة كتابية.

البند السادس — عدم الاعتراف بالمسؤولية:
لا يُعتبر هذا الاتفاق إقراراً من أيٍّ من الطرفين بصحة مزاعم الطرف الآخر، ولا يُستخدم كدليل في أي إجراء آخر.

البند السابع — تخلف عن السداد:
في حالة تخلف {{party_a}} عن السداد في الأجل المحدد، يسقط أثر التصالح ويعود {{party_b}} إلى وضعه النظامي الأصلي مع حق المطالبة بالفوائد والتعويضات.

البند الثامن — قوة هذا الاتفاق:
يُعد هذا الاتفاق سنداً تنفيذياً بعد توثيقه أمام كاتب العدل أو تصديقه من المحكمة المختصة، طبقاً للمادة 9 من نظام التنفيذ.

البند التاسع — النظام والاختصاص:
يخضع هذا الاتفاق لأنظمة المملكة العربية السعودية، وتختص المحاكم بالرياض بالفصل في أي نزاع ينشأ عن تنفيذه.

التوقيعات:
{{party_a}} ______________________   {{party_b}} ______________________
TXT;
    }

    private static function aeMouLoiBody(): string
    {
        return <<<'TXT'
مذكرة تفاهم / MEMORANDUM OF UNDERSTANDING

إنه في يوم {{effective_date}}، أُبرمت هذه المذكرة في دولة الإمارات العربية المتحدة بين كلٍ من:
الطرف الأول: {{party_a}}
الطرف الثاني: {{party_b}}

تمهيد:
يرغب الطرفان في الدخول في مفاوضات بشأن: {{transaction_summary}}، ويرغبان في توثيق المبادئ الأساسية للمحادثات الجارية بينهما، على أن يتم التفاوض على عقد ملزم نهائي لاحقاً.

البند الأول — طبيعة هذه المذكرة:
هذه المذكرة تعبير عن نية حسنة فقط، وليست عقداً ملزماً قانوناً عدا فيما يتعلق بالبنود الواردة في الفقرات (السرية، الحصرية، النفقات، القانون والاختصاص) أدناه. لا تنشأ بموجبها أي التزامات قانونية بإتمام الصفقة.

البند الثاني — التفاوض بحسن نية:
يلتزم الطرفان بالتفاوض بحسن نية بشأن الشروط التجارية والقانونية للصفقة، وفقاً للمادتين 246 و247 من قانون المعاملات المدنية الإماراتي (القانون الاتحادي رقم 5 لسنة 1985 وتعديلاته).

البند الثالث — السرية (ملزم):
يلتزم كل طرف بالحفاظ على سرية المعلومات المتبادلة، وعدم إفشائها لأي طرف ثالث دون موافقة كتابية.

البند الرابع — الحصرية (ملزم):
يلتزم الطرفان بالتفاوض الحصري فيما بينهما لمدة {{exclusivity_months}} أشهر من تاريخ هذه المذكرة.

البند الخامس — المصاريف (ملزم):
يتحمل كل طرف مصاريفه الخاصة سواء أُتمت الصفقة أم لا.

البند السادس — مدة سريان المذكرة:
تظل هذه المذكرة سارية حتى أبكر من: (أ) توقيع العقد النهائي، (ب) انقضاء فترة الحصرية، (ج) إخطار كتابي من أي طرف بعدم رغبته في الاستمرار.

البند السابع — اللغة:
حُرر هذا العقد باللغتين العربية والإنجليزية. وفي حال تعارض، تكون الأولوية للنص العربي.

البند الثامن — القانون والاختصاص (ملزم):
يخضع تفسير هذه المذكرة لقانون دولة الإمارات العربية المتحدة. تختص محاكم دبي / المحاكم الاتحادية بأي نزاع، أو يجوز للأطراف الاتفاق كتابةً على اختصاص محاكم مركز دبي المالي العالمي (DIFC Courts) أو محاكم سوق أبوظبي العالمي (ADGM Courts).

التوقيعات:
{{party_a}} ______________________   {{party_b}} ______________________
TXT;
    }

    private static function aeBoardResolutionBody(): string
    {
        return <<<'TXT'
محضر اجتماع مجلس إدارة / قرار مساهمين

الشركة: {{company_name}}
تاريخ الاجتماع: {{meeting_date}}

اجتمع مجلس الإدارة (أو الجمعية العمومية للمساهمين) بمقر الشركة بتاريخ المذكور أعلاه، وحضر الاجتماع نصاب صحيح طبقاً للنظام الأساسي للشركة وأحكام المرسوم بقانون اتحادي رقم 32 لسنة 2021 بشأن الشركات التجارية، وبعد التداول، صدر القرار التالي:

موضوع القرار:
{{resolution_subject}}

مسوّغ القرار:
بعد الاطلاع على المستندات والتقارير ذات الصلة، ورأي المستشار القانوني للشركة، تبيّن أن إصدار هذا القرار يقع ضمن صلاحيات مجلس الإدارة / المساهمين وفقاً للنظام الأساسي للشركة، ولا يتعارض مع أحكام المرسوم بقانون اتحادي رقم 32 لسنة 2021 ولا أي قانون آخر معمول به في دولة الإمارات.

نص القرار:
1) الموافقة بالأغلبية المنصوص عليها في النظام الأساسي على ما ورد في موضوع القرار أعلاه.
2) تفويض السيد/السيدة {{authorized_signatory}} في التوقيع نيابةً عن الشركة على جميع المستندات اللازمة لتنفيذ هذا القرار، بما في ذلك العقود والإقرارات والتفويضات أمام كافة الجهات الحكومية والمصرفية ودائرة التنمية الاقتصادية وهيئة الأوراق المالية والسلع.
3) تفويض المدير في تسجيل القرار لدى السجل التجاري وأي جهات تنظيمية مختصة.
4) سريان هذا القرار من تاريخ صدوره.

أُقفل المحضر في تمام الساعة .....

التوقيعات:
أعضاء المجلس / المساهمون الحاضرون:
1. ______________________   2. ______________________   3. ______________________

أمين السر: ______________________
TXT;
    }

    private static function aeSaasBody(): string
    {
        return <<<'TXT'
عقد اشتراك في خدمة برمجية / SaaS Subscription Agreement

إنه في يوم {{effective_date}}، أُبرم هذا العقد في دولة الإمارات العربية المتحدة بين:
الطرف الأول (مقدم الخدمة): {{provider}}
الطرف الثاني (العميل): {{customer}}

تمهيد:
يقدم مقدم الخدمة منصة برمجية باسم "{{service_name}}" تعمل عبر الإنترنت، ويرغب العميل في الاشتراك فيها وفقاً للشروط التالية:

البند الأول — حق الاستخدام:
يمنح مقدم الخدمة العميل ترخيصاً غير حصري وغير قابل للتنازل لاستخدام الخدمة طوال مدة هذا العقد، لأغراض العمل الداخلي للعميل فقط.

البند الثاني — رسوم الاشتراك:
يلتزم العميل بسداد {{subscription_fee_aed}} درهم إماراتي شهرياً / سنوياً، شاملاً ضريبة القيمة المضافة (VAT) بنسبة 5% طبقاً للمرسوم بقانون اتحادي رقم 8 لسنة 2017. يتم السداد خلال 15 يوماً من تاريخ الفاتورة.

البند الثالث — مدة العقد:
تكون مدة الاشتراك {{term_months}} شهراً ابتداءً من {{effective_date}}، وتُجدَّد تلقائياً ما لم يخطر أي طرف الآخر كتابةً قبل 30 يوماً من نهاية المدة.

البند الرابع — مستوى الخدمة (SLA):
1) يلتزم مقدم الخدمة بتوافر لا يقل عن 99% شهرياً، باستثناء فترات الصيانة المُعلَنة مسبقاً.
2) في حالة انخفاض التوافر عن الحد المتفق عليه، يستحق العميل خصماً متناسباً من رسوم الشهر التالي.
3) يقدم مقدم الخدمة دعماً فنياً عبر البريد الإلكتروني خلال ساعات العمل الرسمية بتوقيت دولة الإمارات.

البند الخامس — البيانات الشخصية والخصوصية:
1) تظل بيانات العميل وعملائه ملكاً للعميل، ويعمل مقدم الخدمة كـ"معالج" للبيانات بالمعنى الوارد في المرسوم بقانون اتحادي رقم 45 لسنة 2021 بشأن حماية البيانات الشخصية.
2) يلتزم مقدم الخدمة بمعالجة البيانات فقط لأغراض تشغيل الخدمة، وعدم نقلها خارج الدولة دون استيفاء شروط النقل المنصوص عليها في القانون والحصول على الموافقات اللازمة من مكتب الإمارات للبيانات.
3) يُبرم الطرفان ملحق معالجة بيانات (DPA) ملحقاً بهذا العقد.
4) عند انتهاء العقد، يمنح مقدم الخدمة العميل مدة 30 يوماً لاستخراج بياناته، ثم تُحذف بشكل نهائي.

البند السادس — الملكية الفكرية:
تظل كافة حقوق الملكية الفكرية للخدمة وكودها وواجهتها مملوكة لمقدم الخدمة، طبقاً للمرسوم بقانون اتحادي رقم 38 لسنة 2021 بشأن حقوق المؤلف. لا يجوز للعميل عكس الهندسة (reverse engineering) أو إعادة بيع الخدمة.

البند السابع — حدود المسؤولية:
لا تتجاوز المسؤولية الكلية لأي طرف في أي حال إجمالي الرسوم المدفوعة في الاثني عشر شهراً السابقة، ولا يكون أيٌّ من الطرفين مسؤولاً عن الأضرار غير المباشرة.

البند الثامن — السرية:
يلتزم كل طرف بسرية المعلومات الفنية والتجارية المتبادلة طوال مدة العقد ولمدة ثلاث سنوات بعد انتهائه.

البند التاسع — الإنهاء:
يجوز لأي طرف إنهاء العقد بإنذار كتابي مدته 30 يوماً، أو فوراً في حالة إخلال جوهري لم يُصلَح خلال 15 يوماً من الإخطار.

البند العاشر — اللغة والقانون والاختصاص:
حُرر هذا العقد باللغتين العربية والإنجليزية. وفي حال تعارض، تكون الأولوية للنص العربي. يخضع هذا العقد لقانون دولة الإمارات العربية المتحدة، وتختص محاكم دبي / المحاكم الاتحادية بالفصل في أي نزاع، أو يجوز للأطراف اختيار محاكم مركز دبي المالي العالمي (DIFC Courts) كتابةً.

التوقيعات:
مقدم الخدمة: ______________________   العميل: ______________________
TXT;
    }

    private static function aeIpAssignmentBody(): string
    {
        return <<<'TXT'
عقد تنازل عن حقوق الملكية الفكرية

إنه في يوم {{effective_date}}، أُبرم هذا العقد في دولة الإمارات العربية المتحدة بين:
الطرف الأول (المتنازِل / المؤلف): {{assignor}}
الطرف الثاني (المتنازَل إليه): {{assignee}}

تمهيد:
لما كان المتنازِل قد أبدع / طوّر العمل الموصوف أدناه، ورغب في نقل ملكيته بالكامل إلى المتنازَل إليه مقابل المقابل المُحدَّد، فقد اتفق الطرفان على ما يلي وفقاً للمرسوم بقانون اتحادي رقم 38 لسنة 2021 بشأن حقوق المؤلف والحقوق المجاورة:

البند الأول — وصف العمل:
{{work_description}}

البند الثاني — التنازل:
يتنازل المتنازِل بموجب هذا العقد تنازلاً مطلقاً نهائياً للمتنازَل إليه عن جميع حقوقه المالية في العمل المذكور أعلاه، بما في ذلك:
1) حق النشر والتوزيع والاستغلال التجاري في جميع الوسائط الحالية والمستقبلية.
2) حق التعديل والترجمة وإنتاج أعمال مشتقة.
3) جميع التطبيقات والاكتشافات والتحسينات المتعلقة بالعمل.
4) حق تسجيل الملكية الفكرية (براءات اختراع، علامات تجارية، تصاميم) باسم المتنازَل إليه أمام وزارة الاقتصاد ودائرة الملكية الفكرية.

البند الثالث — المقابل:
يتعهد المتنازَل إليه بسداد مبلغ {{consideration_aed}} درهم إماراتي للمتنازِل عند توقيع هذا العقد، يُقر المتنازِل باستلامه ومخالصته من جميع الحقوق المالية المتعلقة بالعمل.

البند الرابع — إقرارات وضمانات المتنازِل:
يقر المتنازِل ويضمن:
1) أنه المؤلف الأصلي للعمل وأنه أنجزه بنفسه.
2) أن العمل لا ينتهك أي حق ملكية فكرية للغير.
3) أنه لم يسبق له التنازل عن العمل أو منح ترخيص لأي طرف ثالث.

البند الخامس — الحقوق الأدبية:
تظل الحقوق الأدبية للمتنازِل (الحق في نسبة العمل إليه وفي الاعتراض على تشويهه) محفوظةً وغير قابلة للتنازل، طبقاً للمادة 5 من المرسوم بقانون اتحادي رقم 38 لسنة 2021. ويلتزم المتنازَل إليه باحترام هذه الحقوق.

البند السادس — التزامات لاحقة:
يلتزم المتنازِل بتوقيع أي مستندات إضافية يستلزمها تسجيل التنازل لدى وزارة الاقتصاد.

البند السابع — السرية:
يلتزم المتنازِل بسرية تفاصيل العمل واستخداماته التجارية لمدة خمس سنوات.

البند الثامن — القانون والاختصاص:
يخضع هذا العقد لقانون دولة الإمارات العربية المتحدة، وتختص محاكم دبي / المحاكم الاتحادية بأي نزاع، مع مراعاة الاختصاص الخاص للجان الفصل في منازعات الملكية الفكرية.

التوقيعات:
المتنازِل: ______________________   المتنازَل إليه: ______________________
TXT;
    }

    private static function aeDpaBody(): string
    {
        return <<<'TXT'
ملحق معالجة بيانات شخصية (DPA)

إنه في يوم {{effective_date}}، أُبرم هذا الملحق بين:
الطرف الأول (المُتحكم / Data Controller): {{controller}}
الطرف الثاني (المُعالج / Data Processor): {{processor}}

تمهيد:
ينظم هذا الملحق معالجة البيانات الشخصية بين المُتحكم والمُعالج وفقاً للمرسوم بقانون اتحادي رقم 45 لسنة 2021 بشأن حماية البيانات الشخصية ولائحته التنفيذية الصادرة من مكتب الإمارات للبيانات (UAE Data Office).

البند الأول — الغرض من المعالجة:
{{processing_purpose}}

البند الثاني — فئات البيانات:
يقتصر نطاق هذا الملحق على البيانات الشخصية التالية: {{data_categories}}.

البند الثالث — التزامات المُعالج:
يلتزم المُعالج بـ:
1) معالجة البيانات فقط بناءً على التعليمات الموثقة من المُتحكم وللغرض المذكور.
2) ضمان أن جميع الأشخاص المُخوَّلين بالوصول إلى البيانات ملتزمون بالسرية.
3) تطبيق التدابير التقنية والتنظيمية المناسبة لحماية البيانات (تشفير، تحكم بالوصول، نسخ احتياطي، سجلات تدقيق) طبقاً للمادة 22 من القانون.
4) عدم إشراك معالج فرعي إلا بإذن كتابي مسبق من المُتحكم، مع نقل التزامات هذا الملحق إلى المعالج الفرعي.
5) معاونة المُتحكم في الوفاء بالتزاماته تجاه أصحاب البيانات (حق الوصول، التصحيح، المحو، نقل البيانات، الاعتراض على المعالجة الآلية).
6) إخطار المُتحكم خلال 72 ساعة من العلم بأي خرق للبيانات الشخصية، طبقاً للمادة 9 من القانون.

البند الرابع — نقل البيانات خارج الدولة:
لا يجوز للمُعالج نقل البيانات الشخصية خارج دولة الإمارات إلا في الحالات المنصوص عليها في الفصل الخامس من القانون، وبشرط الحصول على موافقة كتابية من المُتحكم وعلى الضمانات اللازمة من مكتب الإمارات للبيانات.

البند الخامس — مدة الاحتفاظ والحذف:
يلتزم المُعالج بحذف البيانات أو إعادتها للمُتحكم عند انتهاء غرض المعالجة أو إنهاء العقد الرئيسي، خلال 30 يوماً من تاريخ الإنهاء، وتقديم شهادة حذف موقعة.

البند السادس — التدقيق:
يحق للمُتحكم — مرة سنوياً على الأقل أو عند وقوع حادث — إجراء تدقيق على إجراءات المُعالج أو تكليف طرف مستقل لذلك، مع إخطار مسبق لا يقل عن 14 يوماً.

البند السابع — المسؤولية والتعويض:
يلتزم المُعالج بتعويض المُتحكم عن أي عقوبات إدارية أو غرامات أو أضرار ناشئة عن إخلاله بالتزاماته في هذا الملحق، طبقاً للقانون.

البند الثامن — الأولوية:
في حال أي تعارض بين هذا الملحق والعقد الرئيسي، تكون الأولوية لهذا الملحق فيما يخص معالجة البيانات الشخصية.

التوقيعات:
المُتحكم: ______________________   المُعالج: ______________________
TXT;
    }

    private static function aeSettlementBody(): string
    {
        return <<<'TXT'
اتفاق تصالح وتسوية نهائية

إنه في يوم {{effective_date}}، أُبرم هذا الاتفاق في دولة الإمارات العربية المتحدة بين:
الطرف الأول: {{party_a}}
الطرف الثاني: {{party_b}}

تمهيد:
نشأ بين الطرفين نزاع موضوعه: {{dispute_summary}}، ورغبةً منهما في إنهاء هذا النزاع ودياً وتفادي التقاضي، فقد اتفقا على ما يلي طبقاً لأحكام عقد الصلح المنصوص عليها في المواد 760 وما بعدها من قانون المعاملات المدنية الإماراتي (القانون الاتحادي رقم 5 لسنة 1985 وتعديلاته):

البند الأول — موضوع التصالح:
ينهي الطرفان بموجب هذا الاتفاق جميع المنازعات والمطالبات الحالية بينهما المتعلقة بالموضوع المُشار إليه في التمهيد.

البند الثاني — مبلغ التسوية:
يلتزم {{party_a}} بسداد مبلغ {{settlement_amount_aed}} درهم إماراتي لـ {{party_b}} خلال 15 يوماً من توقيع هذا الاتفاق، وذلك تسويةً نهائيةً وكاملةً للنزاع.

البند الثالث — إقرار إبراء الذمة المتبادل:
يُقر كل طرف بإبراء ذمة الطرف الآخر إبراءً نهائياً وغير قابل للرجوع فيه من جميع الحقوق والمطالبات المتعلقة بالنزاع موضوع هذا الاتفاق، سواء كانت قائمة أو مستقبلية.

البند الرابع — التنازل عن الدعاوى:
يتنازل الطرفان عن أي دعاوى قضائية أو تنفيذية متبادلة قائمة بشأن النزاع، ويقدمان طلباً لشطبها وانتهاء الخصومة أمام المحاكم المختصة.

البند الخامس — السرية:
يلتزم الطرفان بسرية تفاصيل التسوية والمبلغ المدفوع، وعدم الإفصاح عنها لأي طرف ثالث إلا بأمر قضائي أو موافقة كتابية.

البند السادس — عدم الاعتراف بالمسؤولية:
لا يُعتبر هذا الاتفاق إقراراً من أيٍّ من الطرفين بصحة مزاعم الطرف الآخر، ولا يُستخدم كدليل في أي إجراء آخر.

البند السابع — تخلف عن السداد:
في حالة تخلف {{party_a}} عن السداد في الأجل المحدد، يسقط أثر التصالح ويعود {{party_b}} إلى وضعه القانوني الأصلي مع حق المطالبة بالفوائد التأخيرية.

البند الثامن — قوة هذا الاتفاق:
يُعد هذا الاتفاق سنداً تنفيذياً بعد توثيقه أمام كاتب العدل أو تصديقه من المحكمة المختصة.

البند التاسع — القانون والاختصاص:
يخضع هذا الاتفاق لقانون دولة الإمارات العربية المتحدة، وتختص محاكم دبي / المحاكم الاتحادية بالفصل في أي نزاع ينشأ عن تنفيذه.

التوقيعات:
{{party_a}} ______________________   {{party_b}} ______________________
TXT;
    }

    private static function egMouLoiBody(): string
    {
        return <<<'TXT'
مذكرة تفاهم / خطاب نوايا

إنه في يوم {{effective_date}}، تم الاتفاق بين كلٍ من:
الطرف الأول: {{party_a}}
الطرف الثاني: {{party_b}}

تمهيد:
يرغب الطرفان في الدخول في مفاوضات بشأن: {{transaction_summary}}، ويرغبان في توثيق المبادئ الأساسية للمحادثات الجارية بينهما، على أن يتم التفاوض على عقد ملزم نهائي لاحقاً.

البند الأول — طبيعة هذه المذكرة:
هذه المذكرة تعبير عن نية حسنة فقط، وليست عقداً ملزماً قانوناً عدا فيما يتعلق بالبنود الواردة في الفقرات (السرية، الحصرية، النفقات، القانون والاختصاص) أدناه. لا تنشأ بموجبها أي التزامات قانونية بإتمام الصفقة.

البند الثاني — الشروط الرئيسية المُتفاوض عليها:
يلتزم الطرفان بالتفاوض بحسن نية بشأن الشروط التجارية والقانونية للصفقة، بما في ذلك الثمن، الضمانات، الإقرارات، والشروط السابقة للإقفال.

البند الثالث — السرية (ملزم):
يلتزم كل طرف بالحفاظ على سرية المعلومات المتبادلة، وعدم إفشائها لأي طرف ثالث دون موافقة كتابية، طبقاً للقواعد العامة في القانون المدني المصري.

البند الرابع — الحصرية (ملزم):
يلتزم الطرفان بالتفاوض الحصري فيما بينهما لمدة {{exclusivity_months}} أشهر من تاريخ هذه المذكرة، وعدم الدخول في مفاوضات موازية مع الغير بشأن نفس الصفقة.

البند الخامس — المصاريف (ملزم):
يتحمل كل طرف مصاريفه الخاصة (مستشاريه القانونيين والماليين) سواء أُتمت الصفقة أم لا.

البند السادس — مدة سريان المذكرة:
تظل هذه المذكرة سارية حتى أبكر من: (أ) توقيع العقد النهائي، (ب) انقضاء فترة الحصرية، (ج) إخطار كتابي من أي طرف بعدم رغبته في الاستمرار.

البند السابع — القانون والاختصاص (ملزم):
يخضع تفسير هذه المذكرة للقانون المصري، وتختص محاكم القاهرة بأي نزاع.

التوقيعات:
{{party_a}} ______________________   {{party_b}} ______________________
TXT;
    }

    private static function egBoardResolutionBody(): string
    {
        return <<<'TXT'
محضر اجتماع مجلس إدارة / قرار شركاء

الشركة: {{company_name}}
تاريخ الاجتماع: {{meeting_date}}

اجتمع مجلس الإدارة (أو هيئة الشركاء) بمقر الشركة بتاريخ المذكور أعلاه، وحضر الاجتماع نصاب صحيح من الأعضاء، وبعد التداول، صدر القرار التالي:

موضوع القرار:
{{resolution_subject}}

مسوّغ القرار:
بعد الاطلاع على المستندات والتقارير ذات الصلة، ورأي المستشار القانوني للشركة، تبيّن أن إصدار هذا القرار يقع ضمن صلاحيات مجلس الإدارة / الشركاء وفقاً لعقد التأسيس واللائحة الداخلية للشركة، ولا يتعارض مع أحكام قانون الشركات المصري ولا أي تشريع آخر معمول به.

نص القرار:
1) الموافقة بالإجماع / بالأغلبية المنصوص عليها في عقد التأسيس على ما ورد في موضوع القرار أعلاه.
2) تفويض السيد/السيدة {{authorized_signatory}} في التوقيع نيابةً عن الشركة على جميع المستندات اللازمة لتنفيذ هذا القرار، بما في ذلك العقود والإقرارات والتفويضات أمام كافة الجهات الحكومية والمصرفية.
3) تفويض المدير في تسجيل القرار لدى الجهات المختصة (الهيئة العامة للاستثمار / السجل التجاري) إذا تطلّب القانون ذلك.
4) سريان هذا القرار من تاريخ صدوره.

أُقفل المحضر في تمام الساعة .....

التوقيعات:
أعضاء المجلس / الشركاء الحاضرون:
1. ______________________   2. ______________________   3. ______________________

أمين السر: ______________________
TXT;
    }

    private static function egSaasBody(): string
    {
        return <<<'TXT'
عقد اشتراك في خدمة برمجية (SaaS)

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول (مقدم الخدمة): {{provider}}
الطرف الثاني (العميل): {{customer}}

تمهيد:
يقدم مقدم الخدمة منصة برمجية باسم "{{service_name}}" تعمل عبر الإنترنت في بيئة مستضافة، ويرغب العميل في الاشتراك فيها وفقاً للشروط التالية:

البند الأول — حق الاستخدام:
يمنح مقدم الخدمة العميل ترخيصاً غير حصري وغير قابل للتنازل لاستخدام الخدمة طوال مدة هذا العقد، لأغراض العمل الداخلي للعميل فقط.

البند الثاني — رسوم الاشتراك:
يلتزم العميل بسداد {{subscription_fee_egp}} جنيه مصري شهرياً / سنوياً، تُحرَّر فاتورة بها مقدماً، ويتم السداد خلال 15 يوماً من تاريخ الفاتورة.

البند الثالث — مدة العقد:
تكون مدة الاشتراك {{term_months}} شهراً ابتداءً من {{effective_date}}، وتُجدَّد تلقائياً ما لم يخطر أي طرف الآخر كتابةً قبل 30 يوماً من نهاية المدة.

البند الرابع — مستوى الخدمة (SLA):
1) يلتزم مقدم الخدمة بتوافر لا يقل عن 99% شهرياً، باستثناء فترات الصيانة المُعلَنة مسبقاً.
2) في حالة انخفاض التوافر عن الحد المتفق عليه، يستحق العميل خصماً متناسباً من رسوم الشهر التالي.
3) يقدم مقدم الخدمة دعماً فنياً عبر البريد الإلكتروني خلال ساعات العمل الرسمية.

البند الخامس — البيانات والخصوصية:
1) تظل بيانات العميل ملكاً للعميل، ويمتلك مقدم الخدمة ترخيصاً محدوداً لمعالجتها فقط لأغراض تشغيل الخدمة.
2) يلتزم مقدم الخدمة بمعايير حماية البيانات الشخصية وفقاً لقانون حماية البيانات الشخصية المصري رقم 151 لسنة 2020، ويُبرم الطرفان ملحق معالجة بيانات (DPA) ملحقاً بهذا العقد عند الطلب.
3) عند انتهاء العقد، يمنح مقدم الخدمة العميل مدة 30 يوماً لاستخراج بياناته، ثم تُحذف بشكل نهائي.

البند السادس — الملكية الفكرية:
تظل كافة حقوق الملكية الفكرية للخدمة وكودها وواجهتها مملوكة لمقدم الخدمة. لا يجوز للعميل عكس الهندسة (reverse engineering) أو إعادة بيع الخدمة.

البند السابع — حدود المسؤولية:
لا تتجاوز المسؤولية الكلية لأي طرف في أي حال إجمالي الرسوم المدفوعة في الاثني عشر شهراً السابقة، ولا يكون أيٌّ من الطرفين مسؤولاً عن الأضرار غير المباشرة أو الفرص الفائتة.

البند الثامن — السرية:
يلتزم كل طرف بسرية المعلومات الفنية والتجارية المتبادلة طوال مدة العقد ولمدة ثلاث سنوات بعد انتهائه.

البند التاسع — الإنهاء:
يجوز لأي طرف إنهاء العقد بإنذار كتابي مدته 30 يوماً، أو فوراً في حالة إخلال جوهري لم يُصلَح خلال 15 يوماً من الإخطار.

البند العاشر — القانون والاختصاص:
يخضع هذا العقد للقانون المصري، وتختص محاكم القاهرة الاقتصادية بالفصل في أي نزاع.

التوقيعات:
مقدم الخدمة: ______________________   العميل: ______________________
TXT;
    }

    private static function egIpAssignmentBody(): string
    {
        return <<<'TXT'
عقد تنازل عن حقوق الملكية الفكرية

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول (المتنازل / المؤلف): {{assignor}}
الطرف الثاني (المتنازَل إليه): {{assignee}}

تمهيد:
لما كان المتنازِل قد أبدع / طوّر العمل الموصوف أدناه، ورغب في نقل ملكيته بالكامل إلى المتنازَل إليه مقابل المقابل المُحدَّد، فقد اتفق الطرفان على ما يلي:

البند الأول — وصف العمل:
{{work_description}}

البند الثاني — التنازل:
يتنازل المتنازِل بموجب هذا العقد تنازلاً مطلقاً نهائياً للمتنازَل إليه عن جميع حقوقه المالية والمعنوية في العمل المذكور أعلاه، بما في ذلك:
1) حق النشر والتوزيع والاستغلال التجاري.
2) حق التعديل والترجمة وإنتاج أعمال مشتقة.
3) جميع التطبيقات والاكتشافات والتحسينات المتعلقة بالعمل.
4) حق تسجيل الملكية الفكرية باسم المتنازَل إليه أمام الجهات المختصة.

البند الثالث — المقابل:
يتعهد المتنازَل إليه بسداد مبلغ {{consideration_egp}} جنيه مصري للمتنازِل عند توقيع هذا العقد، يُقر المتنازِل باستلامه ومخالصته من جميع الحقوق المالية المتعلقة بالعمل.

البند الرابع — إقرارات وضمانات المتنازِل:
يقر المتنازِل ويضمن:
1) أنه المؤلف الأصلي للعمل وأنه أنجزه بنفسه.
2) أن العمل لا ينتهك أي حق ملكية فكرية للغير.
3) أنه لم يسبق له التنازل عن العمل أو منح ترخيص لأي طرف ثالث.
4) أن العمل خالٍ من أي رهون أو حقوق عينية.

البند الخامس — الحقوق الأدبية:
لا يجوز للمتنازَل إليه نسبة العمل لشخص آخر بصورة تُلحق ضرراً جسيماً بسمعة المتنازِل، طبقاً للحقوق الأدبية غير القابلة للتنازل بموجب قانون حماية الملكية الفكرية المصري رقم 82 لسنة 2002.

البند السادس — التزامات لاحقة:
يلتزم المتنازِل بتوقيع أي مستندات إضافية يستلزمها تسجيل التنازل أمام الجهات المختصة (مكتب براءات الاختراع، جهاز حماية حقوق المؤلف).

البند السابع — السرية:
يلتزم المتنازِل بسرية تفاصيل العمل واستخداماته التجارية لمدة خمس سنوات.

البند الثامن — القانون والاختصاص:
يخضع هذا العقد للقانون المصري، وتختص محاكم القاهرة بأي نزاع.

التوقيعات:
المتنازِل: ______________________   المتنازَل إليه: ______________________
TXT;
    }

    private static function egLoanBody(): string
    {
        return <<<'TXT'
عقد قرض

إنه في يوم {{effective_date}}، تم إبرام هذا العقد بين:
الطرف الأول (المُقرض): {{lender}}
الطرف الثاني (المُقترض): {{borrower}}

تمهيد:
رغب المُقترض في الحصول على قرض من المُقرض، ووافق المُقرض على تقديمه وفقاً للشروط الواردة بهذا العقد، وذلك طبقاً لأحكام المواد 538 وما بعدها من القانون المدني المصري الخاصة بعقد القرض.

البند الأول — مبلغ القرض:
يقدم المُقرض للمُقترض مبلغاً قدره {{principal_egp}} جنيه مصري، يُسلَّم بموجب تحويل بنكي إلى الحساب المُحدَّد من المُقترض. يُعدّ هذا العقد إيصالاً باستلام المبلغ بمجرد إتمام التحويل.

البند الثاني — الفائدة:
يلتزم المُقترض بسداد فائدة سنوية بنسبة {{interest_rate_pct}}% على رصيد القرض القائم، تُحتسب يومياً وتُسدَّد مع الأقساط الشهرية. (ملاحظة: يجب ألا تتجاوز نسبة الفائدة السقف القانوني المقرر للمعاملات المدنية وفقاً للمادة 227 من القانون المدني.)

البند الثالث — مدة القرض والسداد:
يُسدَّد القرض خلال {{term_months}} شهراً من تاريخه على أقساط شهرية متساوية تشمل أصل المبلغ والفائدة، وذلك في اليوم الأول من كل شهر.

البند الرابع — السداد المُبكِّر:
يحق للمُقترض السداد المُبكِّر كلياً أو جزئياً في أي وقت دون غرامة، مع احتساب الفائدة على المبلغ المُسدَّد فعلاً للفترة المُنقضية فقط.

البند الخامس — الإخلال والاستحقاق المُعجَّل:
في حالة تأخر المُقترض عن سداد قسطين متتالين، يحق للمُقرض اعتبار باقي القرض مُستحقاً فوراً والمطالبة به دون الحاجة لإنذار، طبقاً للمادة 274 من القانون المدني.

البند السادس — الضمانات:
(اختياري — يُحذف إن لم تكن هناك ضمانات) يقدم المُقترض كضمان لسداد القرض: ........................

البند السابع — التحويل:
لا يجوز للمُقترض التنازل عن التزاماته بموجب هذا العقد، ويحق للمُقرض التنازل عن حقوقه بإخطار كتابي للمُقترض.

البند الثامن — القانون والاختصاص:
يخضع هذا العقد للقانون المصري، وتختص محاكم القاهرة بأي نزاع. ويُعد هذا العقد سنداً تنفيذياً بعد توثيقه أمام الشهر العقاري إن رغب الطرفان.

التوقيعات:
المُقرض: ______________________   المُقترض: ______________________
الشاهد الأول: ______________________   الشاهد الثاني: ______________________
TXT;
    }

    private static function egSettlementBody(): string
    {
        return <<<'TXT'
اتفاق تصالح وتسوية نهائية

إنه في يوم {{effective_date}}، تم إبرام هذا الاتفاق بين:
الطرف الأول: {{party_a}}
الطرف الثاني: {{party_b}}

تمهيد:
نشأ بين الطرفين نزاع موضوعه: {{dispute_summary}}، ورغبةً منهما في إنهاء هذا النزاع ودياً وتفادي التقاضي، فقد اتفقا على ما يلي طبقاً لأحكام المواد 549 وما بعدها من القانون المدني المصري الخاصة بعقد الصلح:

البند الأول — موضوع التصالح:
ينهي الطرفان بموجب هذا الاتفاق جميع المنازعات والمطالبات الحالية بينهما المتعلقة بالموضوع المُشار إليه في التمهيد.

البند الثاني — مبلغ التسوية:
يلتزم {{party_a}} بسداد مبلغ {{settlement_amount_egp}} جنيه مصري لـ {{party_b}} خلال 15 يوماً من توقيع هذا الاتفاق، وذلك تسويةً نهائيةً وكاملةً للنزاع.

البند الثالث — إقرار إبراء الذمة المتبادل:
يُقر كل طرف بإبراء ذمة الطرف الآخر إبراءً نهائياً وغير قابل للرجوع فيه من جميع الحقوق والمطالبات المتعلقة بالنزاع موضوع هذا الاتفاق، سواء كانت قائمة أو مستقبلية، معروفة أو غير معروفة.

البند الرابع — التنازل عن الدعاوى:
يتنازل الطرفان عن أي دعاوى قضائية أو تنفيذية متبادلة قائمة بشأن النزاع، ويقدمان طلباً لشطبها من الجدول وانتهاء الخصومة.

البند الخامس — السرية:
يلتزم الطرفان بسرية تفاصيل التسوية والمبلغ المدفوع، وعدم الإفصاح عنها لأي طرف ثالث إلا بأمر قضائي أو موافقة كتابية.

البند السادس — عدم الاعتراف بالمسؤولية:
لا يُعتبر هذا الاتفاق إقراراً من أيٍّ من الطرفين بصحة مزاعم الطرف الآخر، ولا يُستخدم كدليل في أي إجراء آخر.

البند السابع — تخلف عن السداد:
في حالة تخلف {{party_a}} عن السداد في الأجل المحدد، يسقط أثر التصالح ويعود {{party_b}} إلى وضعه القانوني الأصلي.

البند الثامن — القانون والاختصاص:
يخضع هذا الاتفاق للقانون المصري، وتختص محاكم القاهرة بالفصل في أي نزاع ينشأ عن تنفيذه.

التوقيعات:
{{party_a}} ______________________   {{party_b}} ______________________
TXT;
    }

    private static function egDpaBody(): string
    {
        return <<<'TXT'
ملحق معالجة بيانات شخصية (DPA)

إنه في يوم {{effective_date}}، أُبرم هذا الملحق بين:
الطرف الأول (المتحكم — Data Controller): {{controller}}
الطرف الثاني (المعالج — Data Processor): {{processor}}

تمهيد:
ينظم هذا الملحق معالجة البيانات الشخصية بين المتحكم والمعالج وفقاً لقانون حماية البيانات الشخصية المصري رقم 151 لسنة 2020 ولائحته التنفيذية.

البند الأول — الغرض من المعالجة:
{{processing_purpose}}

البند الثاني — فئات البيانات:
يقتصر نطاق هذا الملحق على البيانات الشخصية التالية: {{data_categories}}.

البند الثالث — التزامات المعالج:
يلتزم المعالج بـ:
1) معالجة البيانات فقط بناءً على التعليمات الموثقة من المتحكم وللغرض المذكور.
2) ضمان أن جميع الأشخاص المُخوَّلين بالوصول إلى البيانات ملتزمون بالسرية.
3) تطبيق التدابير التقنية والتنظيمية المناسبة لحماية البيانات (تشفير، تحكم بالوصول، نسخ احتياطي).
4) عدم إشراك معالج فرعي إلا بإذن كتابي مسبق من المتحكم، مع نقل التزامات هذا الملحق إلى المعالج الفرعي.
5) معاونة المتحكم في الوفاء بالتزاماته تجاه أصحاب البيانات (حق الوصول، التصحيح، المحو).
6) إخطار المتحكم خلال 24 ساعة من العلم بأي خرق للبيانات الشخصية.

البند الرابع — نقل البيانات خارج جمهورية مصر العربية:
لا يجوز للمعالج نقل البيانات الشخصية خارج البلاد دون ترخيص من المركز القومي لحماية البيانات الشخصية ودون موافقة كتابية من المتحكم، طبقاً للمادة 14 من القانون.

البند الخامس — مدة الاحتفاظ والحذف:
يلتزم المعالج بحذف البيانات أو إعادتها للمتحكم عند انتهاء غرض المعالجة أو إنهاء العقد الرئيسي، خلال 30 يوماً من تاريخ الإنهاء، وتقديم شهادة حذف موقعة.

البند السادس — التدقيق:
يحق للمتحكم — مرة سنوياً على الأقل أو عند وقوع حادث — إجراء تدقيق على إجراءات المعالج أو تكليف طرف مستقل لذلك، مع إخطار مسبق لا يقل عن 14 يوماً.

البند السابع — المسؤولية والتعويض:
يلتزم المعالج بتعويض المتحكم عن أي غرامات أو أضرار ناشئة عن إخلاله بالتزاماته في هذا الملحق، طبقاً للمواد 38 وما بعدها من القانون 151/2020.

البند الثامن — الأولوية:
في حال أي تعارض بين هذا الملحق والعقد الرئيسي، تكون الأولوية لهذا الملحق فيما يخص معالجة البيانات الشخصية.

التوقيعات:
المتحكم: ______________________   المعالج: ______________________
TXT;
    }

    private static function egCommercialLeaseBody(): string
    {
        return <<<'TXT'
عقد إيجار محل تجاري / إداري

إنه في يوم {{start_date}}، تم الاتفاق بين:
الطرف الأول (المؤجر): {{landlord}}
الطرف الثاني (المستأجر): {{tenant}}

تمهيد:
يخضع هذا العقد للقانون رقم 6 لسنة 2022 بإصدار قانون تنظيم العلاقة الإيجارية بين المالك والمستأجر للأماكن غير السكنية، وللقواعد العامة في القانون المدني المصري.

البند الأول — العين المؤجرة:
أجر المؤجر للمستأجر الوحدة الكائنة في {{unit_address}}، بحالتها المعاينة، لاستعمالها في غرض: {{use_purpose}}، دون تغييره دون موافقة كتابية من المؤجر.

البند الثاني — مدة الإيجار:
تكون مدة هذا العقد {{term_years}} سنوات، تبدأ من {{start_date}}، قابلة للتجديد باتفاق مكتوب بين الطرفين قبل انتهاء المدة بـ60 يوماً على الأقل.

البند الثالث — الأجرة:
الأجرة الشهرية {{monthly_rent_egp}} جنيه مصري، تُسدَّد مقدماً في الأسبوع الأول من كل شهر ميلادي. تزيد الأجرة بنسبة 7% سنوياً اعتباراً من السنة الثانية، ما لم يتفق على غير ذلك كتابة.

البند الرابع — التأمين:
يدفع المستأجر تأميناً قدره أجرة ثلاثة أشهر يُرَدّ عند انتهاء العقد بعد التحقق من سلامة العين وسداد كامل الالتزامات.

البند الخامس — التزامات المستأجر:
1) سداد الأجرة في مواعيدها وسداد فواتير المرافق (كهرباء، مياه، غاز، إنترنت، نظافة).
2) المحافظة على العين وإجراء الإصلاحات البسيطة على نفقته.
3) عدم إجراء تعديلات إنشائية دون موافقة كتابية مسبقة.
4) الحصول على جميع التراخيص اللازمة لمزاولة النشاط على نفقته الخاصة.

البند السادس — التزامات المؤجر:
1) تسليم العين صالحة للاستعمال في الغرض المتفق عليه.
2) إجراء الإصلاحات الجوهرية الضرورية لاستمرار صلاحية العين.
3) عدم التعرض الشخصي أو من الغير للمستأجر طوال مدة الإيجار.

البند السابع — التنازل والتأجير من الباطن:
لا يحق للمستأجر التنازل عن العقد أو تأجير العين من الباطن، كلياً أو جزئياً، إلا بموافقة كتابية مسبقة من المؤجر، طبقاً للمادة 593 من القانون المدني.

البند الثامن — الإنهاء:
يحق لأي طرف إنهاء العقد قبل مدته بإخطار كتابي مدته 90 يوماً، أو فوراً في حالة إخلال جوهري لم يُصلَح خلال 30 يوماً من الإنذار.

البند التاسع — الترميمات وإعادة الحالة:
عند انتهاء العقد، يلتزم المستأجر بإعادة العين إلى حالتها الأصلية، باستثناء الاستهلاك العادي، وإلا خُصمت تكلفة الترميم من التأمين.

البند العاشر — الاختصاص:
يخضع هذا العقد للقانون المصري، وتختص محاكم {{unit_address}} بالفصل في أي نزاع.

التوقيعات:
المؤجر: ______________________   المستأجر: ______________________
TXT;
    }

    private static function egTerminationMutualBody(): string
    {
        return <<<'TXT'
اتفاق إنهاء علاقة عمل ودياً وإقرار إبراء ذمة

إنه تم الاتفاق بين:
الطرف الأول (صاحب العمل): {{employer}}
الطرف الثاني (العامل): {{employee}}

تمهيد:
لما كانت علاقة العمل قائمة بين الطرفين، ورغب الطرفان في إنهائها بالتراضي وفقاً للمادة 119/4 من قانون العمل المصري رقم 12 لسنة 2003، فقد اتفقا على ما يلي:

البند الأول — تاريخ انتهاء العمل:
ينتهي عقد العمل بين الطرفين بتاريخ {{last_working_day}}، ويُعدّ هذا التاريخ آخر يوم خدمة فعلي للعامل.

البند الثاني — تسوية المستحقات:
يلتزم صاحب العمل بسداد للعامل مبلغاً إجمالياً قدره {{settlement_amount_egp}} جنيه مصري، يشمل:
أ) الأجر المستحق عن أيام العمل المُنجَزة.
ب) رصيد الإجازات الاعتيادية غير المُستعمَلة.
ج) مكافأة نهاية الخدمة المستحقة طبقاً للقانون.
د) أي بدلات أو حوافز مستحقة حتى تاريخ الانتهاء.
يُسدَّد المبلغ بشيك / تحويل بنكي خلال سبعة أيام من توقيع هذا الاتفاق.

البند الثالث — إقرار العامل بالإبراء:
يُقر العامل بأن المبلغ المُشار إليه يمثل تسويةً نهائيةً وكاملةً لجميع مستحقاته القانونية والتعاقدية لدى صاحب العمل، ويُبرئ ذمته إبراءً تاماً غير قابل للرجوع فيه من جميع المطالبات الحالية والمستقبلية الناشئة عن أو المتعلقة بعلاقة العمل أو إنهائها.

البند الرابع — التزامات لاحقة للعامل:
1) السرية: يلتزم العامل بسرية المعلومات التجارية والفنية لصاحب العمل أثناء وبعد انتهاء الخدمة، دون تحديد مدة.
2) رد العهد: يلتزم العامل بإعادة جميع العهد والمستندات والأجهزة الخاصة بصاحب العمل في موعد أقصاه آخر يوم عمل.
3) عدم استقطاب: يلتزم العامل بعدم استقطاب موظفي صاحب العمل أو عملائه لمدة سنة من تاريخ هذا الاتفاق.

البند الخامس — تسليم الشهادة وملفات التأمينات:
يلتزم صاحب العمل بتسليم شهادة الخبرة وإنهاء الاشتراك التأميني خلال 14 يوماً من تاريخ الانتهاء.

البند السادس — السرية المتبادلة:
يلتزم الطرفان بسرية شروط هذا الاتفاق ومبلغ التسوية.

البند السابع — عدم الاعتراف بالمسؤولية:
لا يُعتبر هذا الاتفاق إقراراً من صاحب العمل بأي إخلال، وهو إنهاء بالتراضي.

البند الثامن — القانون والاختصاص:
يخضع هذا الاتفاق للقانون المصري، وتختص المحاكم العمالية بأي نزاع.

التوقيعات:
صاحب العمل: ______________________   العامل: ______________________
الشاهد الأول: ______________________   الشاهد الثاني: ______________________
TXT;
    }

    private static function servicesBody(): string
    {
        return <<<'TXT'
INDEPENDENT SERVICES AGREEMENT

This Independent Services Agreement (the "Agreement") is entered into as of {{effective_date}} between {{client}} ("Client") and {{provider}} ("Provider").

1. SERVICES. Provider shall provide the following services: {{scope_summary}}, as further described in any Statement of Work executed by the Parties.

2. FEES. Client shall pay Provider {{fees}} in accordance with the payment terms set forth in the applicable Statement of Work.

3. INDEPENDENT CONTRACTOR. Provider is an independent contractor and not an employee, agent, or partner of Client. Provider is responsible for its own taxes and benefits.

4. INTELLECTUAL PROPERTY. Unless otherwise agreed in writing, all deliverables created specifically for Client become Client's property upon full payment, excluding any pre-existing materials of Provider, which are licensed for use in the deliverables.

5. CONFIDENTIALITY. Each Party shall protect the other Party's confidential information and use it only as needed to perform the Services.

6. WARRANTY. Provider warrants that Services will be performed in a professional and workmanlike manner.

7. LIMITATION OF LIABILITY. To the maximum extent permitted by applicable law, neither Party shall be liable for indirect or consequential damages, and each Party's aggregate liability shall not exceed the fees paid in the prior twelve (12) months.

8. TERM AND TERMINATION. This Agreement remains in effect until terminated by either Party with 30 days' written notice or for material breach not cured within 15 days.

9. GOVERNING LAW. This Agreement is governed by the laws of {{governing_law}}.

CLIENT: {{client}}                         PROVIDER: {{provider}}
By: __________________________            By: __________________________
Name:                                     Name:
Title:                                    Title:
TXT;
    }
}
