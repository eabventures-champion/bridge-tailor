<?php

namespace App\Http\Controllers;

use App\Diagnostics\ConsultantAssignmentService;
use App\Models\ActionItem;
use App\Models\ActionLog;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\FrameworkVersion;
use App\Models\UploadedDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ClientPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $fw = FrameworkVersion::active();

        if ($user) {
            if ($user->isConsultant()) {
                $companies = Company::with(['assessments.report', 'actionItems.logs', 'documents', 'consultationRequests'])
                    ->orderBy('name')
                    ->get();
            } else {
                $companies = Company::with(['assessments.report', 'actionItems.logs', 'documents', 'consultationRequests'])
                    ->where('owner_user_id', $user->id)
                    ->orWhereHas('users', fn ($q) => $q->where('users.id', $user->id))
                    ->orderBy('name')
                    ->get();
            }
        } else {
            // Unauthenticated visitor sees only companies created in their own session or the demo showcase account
            $sessionAssessmentUlids = array_keys($request->session()->get('assessment_access', []));
            $companies = Company::with(['assessments.report', 'actionItems.logs', 'documents', 'consultationRequests'])
                ->where('contact_email', 'kwame@voltaagro.com.gh')
                ->when(! empty($sessionAssessmentUlids), function ($q) use ($sessionAssessmentUlids) {
                    $q->orWhereHas('assessments', fn ($aq) => $aq->whereIn('ulid', $sessionAssessmentUlids));
                })
                ->orderBy('id')
                ->get();
        }

        $activeCompany = null;
        if ($request->filled('company')) {
            $activeCompany = $companies->firstWhere('ulid', $request->query('company'));
        }
        if (! $activeCompany) {
            $activeCompany = $companies->first();
        }

        return view('portal.index', [
            'fw' => $fw,
            'companies' => $companies,
            'activeCompany' => $activeCompany,
        ]);
    }

    public function storeCompany(Request $request, ConsultantAssignmentService $assignmentService)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'sector' => 'required|string|max:64',
            'stage' => 'required|string|max:32',
            'size_band' => 'nullable|string|max:32',
            'revenue_band' => 'nullable|string|max:32',
            'country' => 'nullable|string|max:64',
            'city' => 'nullable|string|max:64',
            'contact_name' => 'required|string|max:160',
            'contact_email' => 'required|email|max:180',
            'contact_phone' => 'nullable|string|max:64',
            'objectives' => 'nullable|string|max:2000',
            'challenges' => 'nullable|string|max:2000',
            'retention_policy' => 'nullable|string|in:6_months,18_months,36_months',
        ]);

        $user = Auth::user();
        $company = Company::create($data + [
            'owner_user_id' => $user?->id,
        ]);

        if ($user) {
            $company->users()->syncWithoutDetaching([$user->id => ['company_role' => 'owner']]);
        }

        $assignmentService->autoAssignCompany($company);

        AuditLog::record('company.created', $company->id, null, ['name' => $company->name]);

        return redirect()->route('portal.index', ['company' => $company->ulid])
            ->with('status', "Company '{$company->name}' added to your account.");
    }

    public function logActionProgress(Request $request, ActionItem $actionItem)
    {
        $this->authorizeCompanyAccess($request, $actionItem->company);

        $data = $request->validate([
            'delta' => 'required|integer|in:-1,0,1,2',
            'note' => 'required|string|max:1000',
            'status' => 'nullable|string|in:open,in_progress,completed,blocked',
        ]);

        ActionLog::create([
            'action_item_id' => $actionItem->id,
            'user_id' => Auth::id(),
            'delta' => (int) $data['delta'],
            'note' => $data['note'],
        ]);

        $newNet = $actionItem->logs()->sum('delta');
        $actionItem->update([
            'net_delta' => $newNet,
            'status' => $data['status'] ?? ($data['delta'] >= 2 ? 'in_progress' : $actionItem->status),
        ]);

        AuditLog::record(
            'action_item.progress_logged',
            $actionItem->company_id,
            $actionItem->assessment_id,
            ['action_item_id' => $actionItem->id, 'delta' => $data['delta'], 'status' => $actionItem->status]
        );

        return back()->with('status', "Progress logged on '{$actionItem->title}' (net movement: ".($newNet >= 0 ? "+{$newNet}" : $newNet).').');
    }

    public function purgeDocument(Request $request, UploadedDocument $document)
    {
        $this->authorizeCompanyAccess($request, $document->company);

        if ($document->storage_path && Storage::exists($document->storage_path)) {
            Storage::delete($document->storage_path);
        }

        $document->update([
            'extracted_text' => null,
            'extraction_status' => 'purged',
        ]);

        AuditLog::record(
            'document.purged_by_retention_policy',
            $document->company_id,
            $document->assessment_id,
            ['document_id' => $document->id, 'original_name' => $document->original_name]
        );

        return back()->with('status', "Document '{$document->original_name}' permanently purged from storage in accordance with retention controls.");
    }

    private function authorizeCompanyAccess(Request $request, Company $company): void
    {
        $user = Auth::user();
        if ($user && $user->isConsultant()) {
            return;
        }
        if ($user && ($company->owner_user_id === $user->id || $company->users()->where('users.id', $user->id)->exists())) {
            return;
        }
        $sessionUlids = array_keys($request->session()->get('assessment_access', []));
        if (! empty($sessionUlids) && $company->assessments()->whereIn('ulid', $sessionUlids)->exists()) {
            return;
        }
        if ($company->contact_email === 'kwame@voltaagro.com.gh') {
            return;
        }
        abort(403, 'Strict tenant isolation: you do not have permission to modify another company\'s records.');
    }
}
