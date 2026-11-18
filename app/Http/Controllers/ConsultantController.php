<?php

namespace App\Http\Controllers;

use App\Diagnostics\AiAnalysisService;
use App\Diagnostics\Framework;
use App\Models\ActionItem;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Benchmark;
use App\Models\Company;
use App\Models\ConsultationRequest;
use App\Models\FrameworkVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConsultantController extends Controller
{
    public function dashboard(Request $request)
    {
        $fw = FrameworkVersion::active();
        $assessments = Assessment::with(['company', 'report.reviewer', 'documents'])
            ->latest('id')
            ->get();

        $consultations = ConsultationRequest::with(['company', 'assessment', 'consultant'])
            ->latest('id')
            ->get();

        $companies = Company::withCount('assessments')->orderBy('name')->get();
        $auditLogs = AuditLog::with(['user', 'company'])->latest('id')->limit(25)->get();

        $stats = [
            'total_assessments' => $assessments->count(),
            'avg_health_index' => (int) round($assessments->whereNotNull('health_index')->avg('health_index') ?? 0),
            'pending_reviews' => $assessments->filter(fn ($a) => in_array($a->report?->review_status, ['ai_draft', 'under_review'], true))->count(),
            'active_leads' => $consultations->whereIn('status', ['new', 'contacted', 'discovery_booked', 'proposal_sent'])->count(),
        ];

        return view('consultant.dashboard', [
            'fw' => $fw,
            'assessments' => $assessments,
            'consultations' => $consultations,
            'companies' => $companies,
            'auditLogs' => $auditLogs,
            'stats' => $stats,
        ]);
    }

    public function reviewShow(string $ulid)
    {
        $assessment = Assessment::with(['company', 'report.reviewer', 'documents', 'teamInvites', 'actionItems.logs'])
            ->where('ulid', $ulid)
            ->firstOrFail();

        $fw = $assessment->framework();
        $scored = $assessment->score_snapshot ?? [];
        $report = $assessment->report;
        $analysis = $report ? $report->effectiveAnalysis() : [];
        $benchmarks = Benchmark::forProfile($assessment->company->sector ?? 'general', $assessment->company->stage ?? 'growth');
        $auditLogs = AuditLog::where('assessment_id', $assessment->id)->latest('id')->get();

        return view('consultant.review', [
            'assessment' => $assessment,
            'company' => $assessment->company,
            'report' => $report,
            'fw' => $fw,
            'scored' => $scored,
            'analysis' => $analysis,
            'benchmarks' => $benchmarks,
            'auditLogs' => $auditLogs,
        ]);
    }

    public function reviewUpdate(Request $request, string $ulid)
    {
        $assessment = Assessment::with(['company', 'report'])->where('ulid', $ulid)->firstOrFail();
        $report = $assessment->report;

        $data = $request->validate([
            'executive_summary' => 'required|string|max:6000',
            'consultant_notes' => 'nullable|string|max:6000',
            'review_status' => 'required|string|in:ai_draft,under_review,approved,published',
            'recommendations' => 'nullable|array',
            'recommendations.*.title' => 'required_with:recommendations|string|max:240',
            'recommendations.*.module_key' => 'required_with:recommendations|string|max:16',
            'recommendations.*.programme_key' => 'required_with:recommendations|string|max:32',
            'recommendations.*.priority' => 'required_with:recommendations|string|in:Critical,High,Medium',
            'recommendations.*.intervention' => 'required_with:recommendations|string|max:2000',
            'recommendations.*.expected_outcome' => 'nullable|string|max:1000',
            'recommendations.*.target_kpi' => 'nullable|string|max:500',
            'consulting_priorities' => 'nullable|array',
            'consulting_priorities.*.rank' => 'required_with:consulting_priorities|integer',
            'consulting_priorities.*.business_line' => 'required_with:consulting_priorities|string|max:160',
            'consulting_priorities.*.entry_programme' => 'required_with:consulting_priorities|string|max:200',
            'consulting_priorities.*.expansion_programme' => 'nullable|string|max:200',
            'consulting_priorities.*.delivery_mode' => 'nullable|string|max:160',
            'consulting_priorities.*.duration' => 'nullable|string|max:120',
            'consulting_priorities.*.rationale' => 'nullable|string|max:1000',
        ]);

        $amendments = $report->consultant_amendments ?? [];
        $amendments['executive_summary'] = $data['executive_summary'];
        if (! empty($data['recommendations'])) {
            $amendments['recommendations'] = array_values($data['recommendations']);
        }
        if (! empty($data['consulting_priorities'])) {
            $amendments['consulting_priorities'] = array_values($data['consulting_priorities']);
        }

        $isPublished = in_array($data['review_status'], ['approved', 'published'], true);

        $report->update([
            'consultant_amendments' => $amendments,
            'executive_summary' => $data['executive_summary'],
            'consultant_notes' => $data['consultant_notes'] ?? null,
            'review_status' => $data['review_status'],
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'published_at' => $isPublished ? ($report->published_at ?? now()) : $report->published_at,
        ]);

        $assessment->update([
            'status' => $data['review_status'] === 'published' ? 'published' : 'reviewed',
        ]);

        AuditLog::record(
            "report.consultant_{$data['review_status']}",
            $assessment->company_id,
            $assessment->id,
            [
                'review_status' => $data['review_status'],
                'amended_recommendations_count' => count($data['recommendations'] ?? []),
            ]
        );

        return back()->with('status', "Consultant Review saved. Report status is now '{$data['review_status']}'.");
    }

    public function regenerateAi(string $ulid, AiAnalysisService $aiService)
    {
        $assessment = Assessment::with(['company', 'report'])->where('ulid', $ulid)->firstOrFail();
        $fw = $assessment->framework();
        $scored = $assessment->score_snapshot ?? [];

        $aiResult = $aiService->analyzeAssessment($assessment, $scored, $fw);
        $assessment->report?->update([
            'ai_provider' => $aiResult['provider'],
            'ai_model' => $aiResult['model'],
            'ai_analysis' => $aiResult['analysis'],
        ]);

        AuditLog::record(
            'report.ai_regenerated',
            $assessment->company_id,
            $assessment->id,
            ['provider' => $aiResult['provider'], 'model' => $aiResult['model']]
        );

        return back()->with('status', "AI Diagnostic synthesis regenerated ({$aiResult['provider']} · {$aiResult['model']}).");
    }

    public function addActionItem(Request $request, string $ulid)
    {
        $assessment = Assessment::with('company')->where('ulid', $ulid)->firstOrFail();
        $data = $request->validate([
            'module_key' => 'required|string|in:SG,FH,OE,MG,HC',
            'programme_key' => 'nullable|string|max:32',
            'title' => 'required|string|max:240',
            'intervention' => 'nullable|string|max:2000',
            'priority' => 'required|string|in:Critical,High,Medium,Low',
            'horizon' => 'required|string|in:0-30 days,31-90 days,90-180 days',
            'owner_name' => 'nullable|string|max:128',
            'target_metric' => 'nullable|string|max:255',
        ]);

        $item = ActionItem::create($data + [
            'company_id' => $assessment->company_id,
            'assessment_id' => $assessment->id,
            'status' => 'open',
        ]);

        AuditLog::record('action_item.added_by_consultant', $assessment->company_id, $assessment->id, ['title' => $item->title]);

        return back()->with('status', "Action item '{$item->title}' added to the client's implementation tracker.");
    }

    public function updateConsultationStatus(Request $request, ConsultationRequest $consultation)
    {
        $data = $request->validate([
            'status' => 'required|string|in:new,contacted,discovery_booked,proposal_sent,active_engagement,closed',
            'internal_notes' => 'nullable|string|max:2000',
        ]);

        $consultation->update([
            'status' => $data['status'],
            'internal_notes' => $data['internal_notes'] ?? $consultation->internal_notes,
            'assigned_consultant_id' => $consultation->assigned_consultant_id ?? Auth::id(),
        ]);

        AuditLog::record(
            'consultation.status_updated',
            $consultation->company_id,
            $consultation->assessment_id,
            ['consultation_id' => $consultation->id, 'status' => $data['status']]
        );

        return back()->with('status', "Engagement lead for {$consultation->company->name} updated to '{$data['status']}'.");
    }

    public function frameworkEditor(Request $request)
    {
        $activeRecord = FrameworkVersion::where('is_active', true)->latest('id')->first()
            ?? FrameworkVersion::latest('id')->first();

        $inspectedRecord = $activeRecord;
        if ($request->filled('inspect')) {
            $inspectedRecord = FrameworkVersion::find($request->query('inspect')) ?? $activeRecord;
        }

        $versions = FrameworkVersion::with('publisher')->withCount('assessments')->latest('id')->get();
        $fw = $inspectedRecord ? $inspectedRecord->toFramework() : FrameworkVersion::active();
        $activeFw = $activeRecord ? $activeRecord->toFramework() : $fw;

        $auditHistory = AuditLog::whereIn('event', [
            'framework.version_published',
            'framework.minor_text_edited',
            'framework.version_activated',
        ])->latest('id')->limit(20)->get();

        // Suggest next semantic version bump (e.g. 1.0.0 -> 1.1.0)
        $parts = explode('.', $activeFw->version());
        $nextSuggestedVersion = count($parts) === 3
            ? "{$parts[0]}.".((int) $parts[1] + 1).'.0'
            : $activeFw->version().'.1';

        return view('consultant.framework-editor', [
            'fw' => $fw,
            'activeFw' => $activeFw,
            'activeRecord' => $activeRecord,
            'inspectedRecord' => $inspectedRecord,
            'versions' => $versions,
            'auditHistory' => $auditHistory,
            'nextSuggestedVersion' => $nextSuggestedVersion,
        ]);
    }

    public function frameworkPublish(Request $request)
    {
        $data = $request->validate([
            'save_mode' => 'required|string|in:minor_edit,new_version',
            'version' => 'required|string|max:32',
            'name' => 'required|string|max:200',
            'change_notes' => 'nullable|string|max:1000',
            'definition_json' => 'required|string',
        ]);

        $decoded = json_decode($data['definition_json'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['definition_json' => 'Invalid JSON syntax. Please check brackets and commas.'])->withInput();
        }

        $activeRecord = FrameworkVersion::where('is_active', true)->latest('id')->first()
            ?? FrameworkVersion::latest('id')->first();

        if ($data['save_mode'] === 'minor_edit' && $activeRecord) {
            // Mere edit of text/wording in-place on the active version
            $decoded['version'] = $activeRecord->version;
            $decoded['name'] = $data['name'];

            $errors = Framework::validate($decoded);
            if (! empty($errors)) {
                return back()->withErrors(['definition_json' => implode(' | ', $errors)])->withInput();
            }

            $activeRecord->update([
                'name' => $data['name'],
                'definition' => $decoded,
                'change_type' => 'minor_edit',
                'change_notes' => $data['change_notes'] ?: 'Minor text / wording edit applied in-place.',
                'published_by' => Auth::id(),
                'published_at' => now(),
            ]);

            AuditLog::record('framework.minor_text_edited', null, null, [
                'version' => $activeRecord->version,
                'notes' => $activeRecord->change_notes,
            ]);

            return redirect()->route('consultant.framework')
                ->with('status', "Minor text edit saved in-place on active Framework v{$activeRecord->version}.");
        }

        // New Version mode: preserve previous version in database history so it can be referenced anytime
        $targetVersion = trim($data['version']);
        if ($activeRecord && $targetVersion === $activeRecord->version) {
            $parts = explode('.', $activeRecord->version);
            $targetVersion = count($parts) === 3
                ? "{$parts[0]}.".((int) $parts[1] + 1).'.0'
                : $activeRecord->version.'.1';
        }

        // Ensure uniqueness if version string already exists in history
        if (FrameworkVersion::where('version', $targetVersion)->exists()) {
            $targetVersion .= '-rev'.(FrameworkVersion::count() + 1);
        }

        $decoded['version'] = $targetVersion;
        $decoded['name'] = $data['name'];

        $errors = Framework::validate($decoded);
        if (! empty($errors)) {
            return back()->withErrors(['definition_json' => implode(' | ', $errors)])->withInput();
        }

        $previousVersion = $activeRecord?->version ?? 'none';
        FrameworkVersion::query()->update(['is_active' => false]);

        $record = FrameworkVersion::create([
            'version' => $targetVersion,
            'name' => $data['name'],
            'definition' => $decoded,
            'is_active' => true,
            'change_type' => 'new_version',
            'change_notes' => $data['change_notes'] ?: "Published new version v{$targetVersion} (archived previous v{$previousVersion}).",
            'published_by' => Auth::id(),
            'published_at' => now(),
        ]);

        AuditLog::record('framework.version_published', null, null, [
            'new_version' => $record->version,
            'previous_archived_version' => $previousVersion,
            'notes' => $record->change_notes,
        ]);

        return redirect()->route('consultant.framework')
            ->with('status', "Published new Framework v{$record->version} as ACTIVE. Previous version v{$previousVersion} is archived and logged for historical reference.");
    }

    public function frameworkActivate(FrameworkVersion $version)
    {
        $prev = FrameworkVersion::where('is_active', true)->first();
        FrameworkVersion::query()->update(['is_active' => false]);
        $version->update(['is_active' => true]);

        AuditLog::record('framework.version_activated', null, null, [
            'activated_version' => $version->version,
            'previous_active' => $prev?->version,
        ]);

        return redirect()->route('consultant.framework', ['inspect' => $version->id])
            ->with('status', "Restored and activated Framework v{$version->version} ({$version->name}).");
    }
}
