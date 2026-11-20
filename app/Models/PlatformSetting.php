<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class PlatformSetting extends Model
{
    protected $fillable = [
        'key',
        'group',
        'type',
        'label',
        'value',
        'updated_by',
    ];

    private static ?array $cache = null;

    public static function defaults(): array
    {
        return [
            // Dynamic Text Overrides
            'text.top_bar_office' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Top Utility Bar — Office & Location Text',
                'value' => 'East Legon, Accra, Ghana',
            ],
            'text.brand_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Header — Brand Title',
                'value' => 'Bridge-Tailor',
            ],
            'text.brand_subtitle' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Header — Platform Subtitle',
                'value' => 'AI Business Diagnostic Platform',
            ],
            'text.hero_eyebrow' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero — Eyebrow Tagline',
                'value' => 'DIAGNOSTICS.BRIDGE-TAILOR.COM · DIGITAL MANAGEMENT CONSULTANT',
            ],
            'text.hero_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero — Main Headline',
                'value' => 'Bridge Tailor — AI-Driven Business Diagnostic & Advisory Engine',
            ],
            'text.hero_subtitle' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero — Lead Paragraph',
                'value' => 'A structured diagnostic platform purpose-built for African SMEs. Instead of generic chatbot advice, this engine evaluates your enterprise against Bridge Tailor’s consulting methodology—blending how operational strain feels from the inside with hard evidence across five business functions, calculating deterministic health scores, exposing blind spots, and sequencing the exact consulting intervention required.',
            ],
            'text.hero_cta_primary' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero — Primary CTA Button Text',
                'value' => 'Launch Business Health Diagnostic →',
            ],
            'text.hero_slide_1_image' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 1 Image (URL or Local Path)',
                'placeholder' => 'e.g. images/slider/slide-1.jpg or https://images.unsplash.com/...',
                'value' => 'images/slider/slide-1.jpg',
            ],
            'text.login_welcome_image' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Login & Register — Welcome Visual Image (URL or Local Path)',
                'placeholder' => 'e.g. images/slider/slide-1.jpg or https://images.unsplash.com/...',
                'value' => 'images/slider/slide-1.jpg',
            ],
            'text.hero_slide_2_image' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 2 Image (URL or Local Path)',
                'placeholder' => 'e.g. images/slider/slide-2.jpg or https://images.unsplash.com/...',
                'value' => 'images/slider/slide-2.jpg',
            ],
            'text.hero_slide_2_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 2 Title',
                'value' => 'Corporate Strategy & Board Governance',
            ],
            'text.hero_slide_2_desc' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Landing Hero Slider — Slide 2 Description',
                'value' => 'Align board oversight, capital readiness, and management execution to unlock institutional investment and enterprise growth across African markets.',
            ],
            'text.hero_slide_3_image' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 3 Image (URL or Local Path)',
                'placeholder' => 'e.g. images/slider/slide-3.jpg or https://images.unsplash.com/...',
                'value' => 'images/slider/slide-3.jpg',
            ],
            'text.hero_slide_3_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 3 Title',
                'value' => 'Financial Health & Capital Access',
            ],
            'text.hero_slide_3_desc' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Landing Hero Slider — Slide 3 Description',
                'value' => 'Deterministic financial gateway analysis that ensures bookkeeping integrity, financial controls, and compliance before approaching investors.',
            ],
            'text.hero_slide_4_image' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 4 Image (URL or Local Path)',
                'placeholder' => 'e.g. images/slider/slide-4.jpg or https://images.unsplash.com/...',
                'value' => 'images/slider/slide-4.jpg',
            ],
            'text.hero_slide_4_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 4 Title',
                'value' => 'Operational Excellence & Market Expansion',
            ],
            'text.hero_slide_4_desc' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Landing Hero Slider — Slide 4 Description',
                'value' => 'Expose operational blind spots, streamline local supply chains, and build scalable systems tailored for rapid regional growth.',
            ],
            'text.hero_slide_5_image' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 5 Image (URL or Local Path)',
                'placeholder' => 'e.g. images/slider/slide-5.jpg or https://images.unsplash.com/...',
                'value' => 'images/slider/slide-5.jpg',
            ],
            'text.hero_slide_5_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Hero Slider — Slide 5 Title',
                'value' => 'Actionable Diagnostic Intervention Roadmap',
            ],
            'text.hero_slide_5_desc' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Landing Hero Slider — Slide 5 Description',
                'value' => 'Convert diagnostic evidence directly into prioritized 30, 90, and 180-day consulting roadmaps approved by expert advisory partners.',
            ],
            'text.capabilities_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Section 2 — Six Capabilities Heading',
                'value' => 'Six Core Capabilities & Client Diagnostic Workflow',
            ],
            'text.modules_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Section 3 — Five Assessment Modules Heading',
                'value' => 'Aligned with Bridge Tailor’s Consulting Practice Lines',
            ],
            'text.wp_kit_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Landing Section 4 — WordPress Integration Heading',
                'value' => 'WordPress Integration Kit for www.bridge-tailor.com',
            ],
            'text.wizard_step1_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Assessment Wizard — Step 01 Heading',
                'value' => 'Business Profile & Diagnostic Scope',
            ],
            'text.wizard_step2_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Assessment Wizard — Step 02 Intake Heading',
                'value' => 'What\'s Going On in the Business?',
            ],
            'text.wizard_step3_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Assessment Wizard — Step 03 Questionnaire Heading',
                'value' => 'Adaptive Functional Diagnostic Questionnaire',
            ],
            'text.report_consult_heading' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Report Conversion Band — Consultation Heading',
                'value' => 'Request a Bridge Tailor Strategy & Diagnostic Review Session',
            ],
            'text.footer_col1_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Footer Col 1 — Company Brand & Location Heading',
                'value' => 'BRIDGE-TAILOR CONSULTING · ACCRA, GHANA',
            ],
            'text.footer_tagline' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Footer Col 1 — Consulting Tagline & Description',
                'value' => 'Our drive is to make every business thrive. Boutique management consultancy supporting African SMEs through Corporate Strategy, Performance Management, Finance Access, and Market Access.',
            ],
            'text.footer_engine_note' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Footer Col 1 — Diagnostic Engine & API Security Note',
                'value' => 'Deterministic Scoring Engine v1.0.0 · OpenAI Responses API Structured Output Synthesis · Strict Epistemic Tagging',
            ],
            'text.footer_col2_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Footer Col 2 — Diagnostic Modules Heading',
                'value' => 'Diagnostic Modules',
            ],
            'text.footer_col2_items' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Footer Col 2 — Diagnostic Modules List (One per line)',
                'value' => "01 · Strategy & Governance\n02 · Financial Health & Capital\n03 · Operational Excellence\n04 · Market Access & Growth\n05 · Human Capital & Capacity",
            ],
            'text.footer_col3_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Footer Col 3 — Practice Lines Heading',
                'value' => 'Practice Lines',
            ],
            'text.footer_col3_items' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Footer Col 3 — Practice Lines List (One per line)',
                'value' => "Scale Readiness Review\nPerformance System Setup\nFinance Desk Retainer\nCapital Preparation Programme\nMarket Intelligence Report",
            ],
            'text.footer_col4_title' => [
                'group' => 'text', 'type' => 'string',
                'label' => 'Footer Col 4 — Security & Governance Heading',
                'value' => 'Security & Governance',
            ],
            'text.footer_col4_items' => [
                'group' => 'text', 'type' => 'textarea',
                'label' => 'Footer Col 4 — Security & Governance List (One per line)',
                'value' => "■ Server-side API key isolation\n■ Role-based access & tenant isolation\n■ SHA-256 document verification\n■ Consultant Review Mode gate\n■ Immutable audit trail",
            ],

            // Dynamic Component & Card Visibility Toggles ('1' = visible, '0' = hidden)
            'vis.nav_overview' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Navigation — "Overview" Header Link (Highlighted in Navigation)',
                'value' => '1',
            ],
            'vis.top_utility_bar' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Global — Top Utility Bar (Accra Office & User Status)',
                'value' => '1',
            ],
            'vis.landing_architecture_box' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Landing — Right-Hand Controlled Diagnostic Architecture Card',
                'value' => '1',
            ],
            'vis.landing_capabilities_section' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Landing — Section 2: Six Core Capabilities & Workflow Cards',
                'value' => '1',
            ],
            'vis.landing_modules_table' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Landing — Section 3: Five Assessment Modules Table',
                'value' => '1',
            ],
            'vis.landing_wp_integration_kit' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Landing — Section 4: WordPress Integration Kit Section',
                'value' => '1',
            ],
            'vis.wizard_demo_prefill_button' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Wizard — "Prefill Sample African SME Profile" Shortcut Button',
                'value' => '1',
            ],
            'vis.report_kpi_strip' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — 4-Column Headline KPI Strip',
                'value' => '1',
            ],
            'vis.report_perception_vs_evidence' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — "How It Feels vs. What the Evidence Shows" & Cross-Area Insights',
                'value' => '1',
            ],
            'vis.report_swot_risk_cards' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — Strengths, Weaknesses, Risk & Opportunity Cards',
                'value' => '1',
            ],
            'vis.report_roadmap_and_priorities' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — Recommendations, 30/90/180-Day Roadmap & Consulting Priorities',
                'value' => '1',
            ],
            'vis.report_epistemic_ledger' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — Fact vs. Assumption Epistemic Ledger Cards',
                'value' => '1',
            ],
            'vis.report_document_vault_card' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — Supporting Document Upload Card',
                'value' => '1',
            ],
            'vis.report_team_invite_card' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — Multi-Employee Team Invite Card',
                'value' => '1',
            ],
            'vis.report_history_card' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — Historical Diagnostic Trajectory Card',
                'value' => '1',
            ],
            'vis.report_consultation_booking' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Report — Bottom Strategy Session Booking Section',
                'value' => '1',
            ],
            'vis.footer_section' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Global — Footer Section',
                'value' => '1',
            ],
            'vis.footer_practice_lines' => [
                'group' => 'visibility', 'type' => 'boolean',
                'label' => 'Footer — Column 3: "Practice Lines" Section',
                'value' => '0',
            ],

            // Automated Consultant Routing Engine Settings
            'system.auto_assign_consultants' => [
                'group' => 'system', 'type' => 'boolean',
                'label' => 'Automated Consultant Assignment when Admin is Unavailable',
                'value' => '1',
            ],
            'system.auto_assign_strategy' => [
                'group' => 'system', 'type' => 'string',
                'label' => 'Automated Assignment Strategy (least_loaded | round_robin)',
                'value' => 'least_loaded',
            ],
        ];
    }

    public static function allMap(): array
    {
        if (static::$cache !== null) {
            return static::$cache;
        }

        $defaults = collect(static::defaults())->mapWithKeys(fn ($item, $key) => [$key => $item['value']])->all();

        try {
            if (Schema::hasTable('platform_settings')) {
                $dbRows = static::query()->pluck('value', 'key')->all();
                static::$cache = array_replace($defaults, $dbRows);

                return static::$cache;
            }
        } catch (\Throwable $e) {
            // Fallback to defaults if migration hasn't run yet
        }

        static::$cache = $defaults;

        return static::$cache;
    }

    public static function text(string $key, ?string $default = null): string
    {
        $map = static::allMap();

        return (string) ($map[$key] ?? $default ?? (static::defaults()[$key]['value'] ?? ''));
    }

    public static function visible(string $key, bool $default = true): bool
    {
        $map = static::allMap();
        if (! array_key_exists($key, $map)) {
            return $default;
        }

        return in_array((string) $map[$key], ['1', 'true', 'yes', 'on'], true);
    }

    public static function flushCache(): void
    {
        static::$cache = null;
    }
}
