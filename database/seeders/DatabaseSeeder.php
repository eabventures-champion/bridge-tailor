<?php

namespace Database\Seeders;

use App\Diagnostics\AiAnalysisService;
use App\Diagnostics\ScoringEngine;
use App\Models\ActionItem;
use App\Models\ActionLog;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Benchmark;
use App\Models\Company;
use App\Models\ConsultationRequest;
use App\Models\DiagnosticReport;
use App\Models\FrameworkVersion;
use App\Models\PlatformSetting;
use App\Models\TeamInvite;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Super-User Developer Account (Full Platform Control, Dynamic Text & Component Visibility, User Directory)
        $developer = User::updateOrCreate(
            ['email' => 'dev@bridge-tailor.com'],
            [
                'name' => 'Lead Platform Developer',
                'role' => 'developer',
                'phone' => '+233 20 000 0001',
                'job_title' => 'Systems Architect & Super-Admin',
                'password' => Hash::make('Developer2026!'),
            ]
        );

        // Seed default dynamic text & component visibility settings
        foreach (PlatformSetting::defaults() as $sKey => $meta) {
            PlatformSetting::firstOrCreate(
                ['key' => $sKey],
                [
                    'group' => $meta['group'],
                    'type' => $meta['type'],
                    'label' => $meta['label'],
                    'value' => $meta['value'],
                    'updated_by' => $developer->id,
                ]
            );
        }
        PlatformSetting::flushCache();

        // Seed initial platform clearance code
        \App\Models\PlatformSecurityCode::firstOrCreate(
            ['access_code' => 'BT-SEC-DEMO26'],
            [
                'email' => 'client@bridge-tailor.com',
                'name' => 'Demo Enterprise Client',
                'organization' => 'Volta Agro-Processing Ltd',
                'role_requested' => 'client',
                'request_message' => 'Initial platform security code generated for demo and onboarding review.',
                'delivered_to' => 'eabventures@gmail.com',
                'status' => 'active',
                'times_used' => 0,
                'max_uses' => 100,
                'approved_by' => $developer->id,
                'expires_at' => now()->addMonths(6),
            ]
        );

        // 1. Users (Admin, Senior Consultant, Multi-Company Client Account)
        $admin = User::updateOrCreate(
            ['email' => 'admin@bridge-tailor.com'],
            [
                'name' => 'Samuel Bridge-Tailor',
                'role' => 'admin',
                'phone' => '+233 30 255 0190',
                'job_title' => 'Managing Partner',
                'password' => Hash::make('BridgeTailor2026!'),
            ]
        );

        $consultant = User::updateOrCreate(
            ['email' => 'consult@bridge-tailor.com'],
            [
                'name' => 'Abena Mensah',
                'role' => 'consultant',
                'phone' => '+233 24 411 2233',
                'job_title' => 'Principal Consultant · Strategy & Finance',
                'password' => Hash::make('BridgeTailor2026!'),
            ]
        );

        $client = User::updateOrCreate(
            ['email' => 'kwame@voltaagro.com.gh'],
            [
                'name' => 'Kwame Osei-Tutu',
                'role' => 'client',
                'phone' => '+233 20 889 4411',
                'job_title' => 'Founder & Group CEO',
                'password' => Hash::make('ClientDemo2026!'),
            ]
        );

        // 2. Active Framework Version
        $definition = require base_path('resources/framework/bridge-tailor-v1.php');
        $fwRecord = FrameworkVersion::updateOrCreate(
            ['version' => $definition['version']],
            [
                'name' => $definition['name'],
                'definition' => $definition,
                'is_active' => true,
                'published_by' => $admin->id,
                'published_at' => now(),
            ]
        );
        $fw = $fwRecord->toFramework();

        // 3. African SME Industry Benchmarks across sectors & modules
        $benchmarkData = [
            ['general', 'growth', 'SG', 38, 54, 68, 84, 'Bridge Tailor African SME Reference Cohort (n=140)'],
            ['general', 'growth', 'FH', 34, 49, 65, 82, 'Bridge Tailor African SME Reference Cohort (n=140)'],
            ['general', 'growth', 'OE', 40, 55, 70, 85, 'Bridge Tailor African SME Reference Cohort (n=140)'],
            ['general', 'growth', 'MG', 44, 58, 72, 86, 'Bridge Tailor African SME Reference Cohort (n=140)'],
            ['general', 'growth', 'HC', 36, 51, 66, 81, 'Bridge Tailor African SME Reference Cohort (n=140)'],
            ['agritech', 'growth', 'SG', 35, 50, 65, 82, 'West Africa Agribusiness & Processing Cohort'],
            ['agritech', 'growth', 'FH', 30, 46, 62, 80, 'West Africa Agribusiness & Processing Cohort'],
            ['agritech', 'growth', 'OE', 38, 53, 68, 84, 'West Africa Agribusiness & Processing Cohort'],
            ['agritech', 'growth', 'MG', 42, 56, 71, 85, 'West Africa Agribusiness & Processing Cohort'],
            ['agritech', 'growth', 'HC', 34, 49, 64, 79, 'West Africa Agribusiness & Processing Cohort'],
            ['fintech', 'growth', 'SG', 48, 63, 76, 89, 'African Financial Services & Fintech Cohort'],
            ['fintech', 'growth', 'FH', 50, 65, 78, 90, 'African Financial Services & Fintech Cohort'],
            ['fintech', 'growth', 'OE', 46, 61, 75, 88, 'African Financial Services & Fintech Cohort'],
            ['fintech', 'growth', 'MG', 45, 60, 74, 87, 'African Financial Services & Fintech Cohort'],
            ['fintech', 'growth', 'HC', 44, 59, 73, 86, 'African Financial Services & Fintech Cohort'],
            ['oil_gas', 'growth', 'SG', 44, 59, 73, 87, 'Ghana Energy & Industrial Supply Chain Cohort'],
            ['oil_gas', 'growth', 'FH', 42, 57, 71, 86, 'Ghana Energy & Industrial Supply Chain Cohort'],
            ['oil_gas', 'growth', 'OE', 45, 60, 74, 88, 'Ghana Energy & Industrial Supply Chain Cohort'],
            ['oil_gas', 'growth', 'MG', 40, 55, 70, 84, 'Ghana Energy & Industrial Supply Chain Cohort'],
            ['oil_gas', 'growth', 'HC', 41, 56, 70, 85, 'Ghana Energy & Industrial Supply Chain Cohort'],
        ];

        foreach ($benchmarkData as [$sec, $stg, $mod, $p25, $med, $p75, $top, $note]) {
            Benchmark::updateOrCreate(
                ['sector' => $sec, 'stage' => $stg, 'module_key' => $mod],
                [
                    'p25_score' => $p25,
                    'median_score' => $med,
                    'p75_score' => $p75,
                    'top_decile_score' => $top,
                    'sample_note' => $note,
                ]
            );
        }

        // 4. Multi-Company Client Portfolio (2 companies under Kwame's account)
        $company1 = Company::updateOrCreate(
            ['name' => 'Volta Agro-Processing Ltd'],
            [
                'sector' => 'agritech',
                'stage' => 'growth',
                'size_band' => '21-50',
                'revenue_band' => '3to6',
                'country' => 'Ghana',
                'city' => 'Accra (East Legon)',
                'contact_name' => 'Kwame Osei-Tutu',
                'contact_email' => 'kwame@voltaagro.com.gh',
                'contact_phone' => '+233 20 889 4411',
                'objectives' => 'Raise GHS 4.5M in growth capital for cold-chain expansion and enter Côte d\'Ivoire wholesale distribution.',
                'challenges' => 'Monthly management accounts arrive 6 weeks late, cash feels tight despite strong orders, and every operational decision escalates to the founder.',
                'owner_user_id' => $client->id,
                'assigned_consultant_id' => $consultant->id,
                'retention_policy' => '18_months',
            ]
        );
        $company1->users()->syncWithoutDetaching([$client->id => ['company_role' => 'owner']]);

        $company2 = Company::updateOrCreate(
            ['name' => 'Meridian Corridor Logistics Ltd'],
            [
                'sector' => 'logistics',
                'stage' => 'mature',
                'size_band' => '51-200',
                'revenue_band' => '10to20',
                'country' => 'Ghana',
                'city' => 'Tema',
                'contact_name' => 'Kwame Osei-Tutu',
                'contact_email' => 'kwame@voltaagro.com.gh',
                'contact_phone' => '+233 20 889 4411',
                'objectives' => 'Formalise board succession and tighten fleet utilisation & fuel cost controls across the Tema–Ouagadougou corridor.',
                'challenges' => 'Family-founded business with no written succession plan and manual spreadsheet reconciliation.',
                'owner_user_id' => $client->id,
                'assigned_consultant_id' => $consultant->id,
                'retention_policy' => '18_months',
            ]
        );
        $company2->users()->syncWithoutDetaching([$client->id => ['company_role' => 'owner']]);

        // 5. Historical Assessment + Current Assessment for Volta Agro-Processing Ltd
        if ($company1->assessments()->count() === 0) {
            $scorer = new ScoringEngine($fw);
            $aiService = new AiAnalysisService();

            $histSymptoms = ['s2' => 5, 's5' => 5, 's6' => 4, 's8' => 5, 's9' => 4, 's11' => 5, 's14' => 4];
            $histAnswers = [
                'sg_1' => 2, 'sg_2' => 2, 'fd4' => 2, 'fd2' => 2, 'sg_3' => 2, 'sg_4' => 1, 'fd1' => 2, 'sg_f1' => 2, 'fd3' => 2, 'sg_5' => 2, 'sg_6' => 1,
                'fc1' => 2, 'fh_1' => 2, 'fc2' => 1, 'fc4' => 2, 'fh_2' => 2, 'fh_f1' => 2, 'fc3' => 2, 'fh_3' => 1, 'ir2' => 1, 'ir_f1' => 2, 'ir4' => 1, 'ir1' => 2, 'ir3' => 2,
                'oe_1' => 2, 'oe_2' => 1, 'oe_3' => 2, 'sec_mf' => 1, 'oe_4' => 1, 'oe_5' => 1, 'oe_6' => 1, 'oe_7' => 1, 'oe_f1' => 1,
                'mg_1' => 1, 'mg_2' => 2, 'mg_3' => 1, 'rm4' => 1, 'mg_4' => 1, 'rm3' => 1, 'rm1' => 1, 'rm2' => 2, 'sec_ag' => 2,
                'hc_1' => 1, 'hc_2' => 2, 'hc_3' => 2, 'hc_4' => 2, 'hc_5' => 1, 'hc_6' => 1, 'hc_f1' => 1, 'hc_7' => 2,
            ];
            $modulesAll = ['SG', 'FH', 'OE', 'MG', 'HC'];
            $histScored = $scorer->score(['stage' => 'growth', 'sector' => 'agritech'], $modulesAll, $histSymptoms, $histAnswers);

            $histAssessment = Assessment::create([
                'company_id' => $company1->id,
                'framework_version_id' => $fwRecord->id,
                'created_by' => $client->id,
                'mode' => 'comprehensive',
                'modules_in_scope' => $modulesAll,
                'symptoms' => $histSymptoms,
                'answers' => $histAnswers,
                'custom_issue' => 'Initial baseline before installing monthly close checklist.',
                'score_snapshot' => $histScored,
                'health_index' => $histScored['health_index'],
                'health_band' => $histScored['band']['label'] ?? null,
                'ai_consent' => true,
                'ai_consented_at' => now()->subMonths(4),
                'status' => 'published',
                'completed_at' => now()->subMonths(4),
            ]);
            $histAssessment->timestamps = false;
            $histAssessment->created_at = now()->subMonths(4);
            $histAssessment->updated_at = now()->subMonths(4);
            $histAssessment->save();

            $histAi = $aiService->analyzeAssessment($histAssessment, $histScored, $fw);
            DiagnosticReport::create([
                'assessment_id' => $histAssessment->id,
                'ai_provider' => $histAi['provider'],
                'ai_model' => $histAi['model'],
                'ai_analysis' => $histAi['analysis'],
                'executive_summary' => $histAi['analysis']['executive_summary'],
                'consultant_notes' => 'Baseline diagnostic completed prior to Performance System Setup.',
                'review_status' => 'published',
                'reviewed_by' => $consultant->id,
                'reviewed_at' => now()->subMonths(4),
                'published_at' => now()->subMonths(4),
            ]);

            $currSymptoms = ['s2' => 4, 's5' => 3, 's8' => 4, 's10' => 4, 's11' => 4, 's14' => 3, 's19' => 3];
            $currAnswers = [
                'sg_1' => 1, 'sg_2' => 1, 'fd4' => 1, 'fd2' => 1, 'sg_3' => 1, 'sg_4' => 0, 'fd1' => 2, 'sg_f1' => 1, 'fd3' => 2, 'sg_5' => 1, 'sg_6' => 0,
                'fc1' => 1, 'fh_1' => 1, 'fc2' => 0, 'fc4' => 2, 'fh_2' => 2, 'fh_f1' => 1, 'fc3' => 1, 'fh_3' => 0, 'ir2' => 1, 'ir_f1' => 1, 'ir4' => 1, 'ir1' => 1, 'ir3' => 2,
                'oe_1' => 1, 'oe_2' => 1, 'oe_3' => 1, 'sec_mf' => 1, 'oe_4' => 1, 'oe_5' => 0, 'oe_6' => 0, 'oe_7' => 1, 'oe_f1' => 1,
                'mg_1' => 0, 'mg_2' => 1, 'mg_3' => 1, 'rm4' => 1, 'mg_4' => 1, 'rm3' => 1, 'rm1' => 1, 'rm2' => 1, 'sec_ag' => 2,
                'hc_1' => 1, 'hc_2' => 1, 'hc_3' => 1, 'hc_4' => 1, 'hc_5' => 0, 'hc_6' => 1, 'hc_f1' => 1, 'hc_7' => 1,
            ];
            $currScored = $scorer->score(['stage' => 'growth', 'sector' => 'agritech'], $modulesAll, $currSymptoms, $currAnswers);

            $currAssessment = Assessment::create([
                'company_id' => $company1->id,
                'framework_version_id' => $fwRecord->id,
                'created_by' => $client->id,
                'mode' => 'comprehensive',
                'modules_in_scope' => $modulesAll,
                'symptoms' => $currSymptoms,
                'answers' => $currAnswers,
                'custom_issue' => 'We have cleaned up tax filings and bank reconciliations, but still lack a 13-week cash forecast, an investment data room, and traceability mapping for export buyers.',
                'score_snapshot' => $currScored,
                'health_index' => $currScored['health_index'],
                'health_band' => $currScored['band']['label'] ?? null,
                'ai_consent' => true,
                'ai_consented_at' => now()->subDays(2),
                'status' => 'reviewed',
                'completed_at' => now()->subDays(2),
            ]);

            $currAi = $aiService->analyzeAssessment($currAssessment, $currScored, $fw);
            DiagnosticReport::create([
                'assessment_id' => $currAssessment->id,
                'ai_provider' => $currAi['provider'],
                'ai_model' => $currAi['model'],
                'ai_analysis' => $currAi['analysis'],
                'executive_summary' => $currAi['analysis']['executive_summary'],
                'consultant_notes' => 'Reviewed by Abena Mensah (Principal Consultant). Noticeable +17 pt lift in internal controls and statutory compliance since the baseline run. Priority now shifts to 13-week cash forecasting and Capital Preparation Programme before approaching agribusiness debt/equity funds.',
                'review_status' => 'approved',
                'reviewed_by' => $consultant->id,
                'reviewed_at' => now()->subDay(),
                'published_at' => now()->subDay(),
            ]);

            TeamInvite::create([
                'assessment_id' => $currAssessment->id,
                'respondent_name' => 'Efua Darko',
                'respondent_email' => 'efua.darko@voltaagro.com.gh',
                'respondent_role' => 'Head of Finance & Administration',
                'symptoms' => ['s5' => 3, 's8' => 5, 's11' => 4],
                'answers' => ['fc1' => 1, 'fh_1' => 1, 'fc4' => 2, 'fh_2' => 2, 'ir1' => 1, 'ir3' => 2],
                'notes' => 'Receivables from two supermarket chains stretch past 65 days, creating cash crunches before raw material harvests.',
                'status' => 'submitted',
                'submitted_at' => now()->subDays(2),
            ]);

            TeamInvite::create([
                'assessment_id' => $currAssessment->id,
                'respondent_name' => 'Kojo Baffour',
                'respondent_email' => 'kojo.baffour@voltaagro.com.gh',
                'respondent_role' => 'Operations & Plant Manager',
                'status' => 'pending',
            ]);

            foreach (array_slice($currAi['analysis']['recommendations'], 0, 4) as $idx => $rec) {
                $item = ActionItem::create([
                    'company_id' => $company1->id,
                    'assessment_id' => $currAssessment->id,
                    'module_key' => $rec['module_key'],
                    'programme_key' => $rec['programme_key'],
                    'title' => $rec['title'],
                    'intervention' => $rec['intervention'],
                    'priority' => $rec['priority'],
                    'horizon' => $idx < 2 ? '0-30 days' : '31-90 days',
                    'owner_name' => $idx === 0 ? 'Efua Darko (Finance)' : 'Kwame Osei-Tutu (CEO)',
                    'target_metric' => $rec['target_kpi'],
                    'status' => $idx === 0 ? 'in_progress' : 'open',
                    'net_delta' => $idx === 0 ? 2 : 0,
                    'due_date' => now()->addDays(($idx + 1) * 21),
                ]);

                if ($idx === 0) {
                    ActionLog::create([
                        'action_item_id' => $item->id,
                        'user_id' => $consultant->id,
                        'delta' => 2,
                        'note' => 'Installed 13-week rolling cash-flow template and reconciled top 15 customer debtor ageing balances.',
                    ]);
                }
            }

            $firstPriority = $currAi['analysis']['consulting_priorities'][0] ?? null;
            ConsultationRequest::create([
                'company_id' => $company1->id,
                'assessment_id' => $currAssessment->id,
                'contact_name' => 'Kwame Osei-Tutu',
                'contact_email' => 'kwame@voltaagro.com.gh',
                'contact_phone' => '+233 20 889 4411',
                'recommended_programme' => $firstPriority['entry_programme'] ?? 'Performance System Setup',
                'business_line' => $firstPriority['business_line'] ?? 'Business Performance',
                'estimated_value_ghs' => 'GHS 15,000 – 25,000',
                'preferred_channel' => 'in_person_accra',
                'preferred_window' => 'Thursday 10:00 GMT · East Legon Office',
                'client_message' => 'We want Bridge Tailor to review our diagnostic results and scope the Performance System Setup ahead of our Q3 capital raise.',
                'status' => 'discovery_booked',
                'assigned_consultant_id' => $consultant->id,
                'internal_notes' => 'Discovery session confirmed at East Legon office. Prepare Finance Desk Growth-tier retainer option as follow-on.',
            ]);

            AuditLog::create([
                'user_id' => $consultant->id,
                'company_id' => $company1->id,
                'assessment_id' => $currAssessment->id,
                'event' => 'report.approved_and_published',
                'actor_label' => 'Abena Mensah (consultant)',
                'ip_address' => '127.0.0.1',
                'metadata' => ['health_index' => $currScored['health_index'], 'provider' => $currAi['provider']],
            ]);
        }
    }
}
