<?php

namespace App\Http\Controllers;

use App\Diagnostics\ConsultantAssignmentService;
use App\Models\ActionItem;
use App\Models\Assessment;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\ConsultationRequest;
use App\Models\FrameworkVersion;
use App\Models\NavigationItem;
use App\Models\PlatformSetting;
use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DeveloperController extends Controller
{
    public function index(ConsultantAssignmentService $assignmentService)
    {
        $defaults = PlatformSetting::defaults();
        $currentMap = PlatformSetting::allMap();

        $textSettings = [];
        $visibilitySettings = [];

        foreach ($defaults as $key => $meta) {
            $item = $meta + ['key' => $key, 'current' => $currentMap[$key] ?? $meta['value']];
            if ($meta['group'] === 'text') {
                $textSettings[$key] = $item;
            } elseif ($meta['group'] === 'visibility') {
                $visibilitySettings[$key] = $item;
            }
        }

        $isDeveloper = Auth::user()?->isDeveloper() ?? false;

        $usersQuery = User::with(['ownedCompanies', 'companies', 'assignedCompanies'])
            ->orderByRaw("FIELD(role, 'developer', 'admin', 'consultant', 'client')")
            ->orderBy('id');

        // Admin Console must never see or preview Developer accounts
        if (! $isDeveloper) {
            $usersQuery->where('role', '!=', 'developer');
        }

        $users = $usersQuery->get();

        $companies = Company::with(['owner', 'consultant'])
            ->withCount(['assessments', 'documents', 'actionItems', 'consultationRequests'])
            ->orderBy('name')
            ->get();

        $assessments = Assessment::with(['company', 'report.reviewer'])
            ->latest('id')
            ->get();

        $auditLogs = AuditLog::with(['user', 'company'])
            ->latest('id')
            ->limit(40)
            ->get();

        $navigationItems = NavigationItem::orderBy('audience')->orderBy('sort_order')->orderBy('id')->get();
        $routePrefix = request()->is('admin*') ? 'admin' : ($isDeveloper ? 'developer' : 'admin');

        $stats = [
            'users_total' => $users->count(),
            'clients_count' => $users->where('role', 'client')->count(),
            'consultants_count' => $users->where('role', 'consultant')->count(),
            'admins_count' => $isDeveloper
                ? $users->whereIn('role', ['admin', 'developer'])->count()
                : $users->where('role', 'admin')->count(),
            'companies_total' => $companies->count(),
            'assessments_total' => $assessments->count(),
            'documents_total' => UploadedDocument::count(),
            'actions_total' => ActionItem::count(),
            'consultations_total' => ConsultationRequest::count(),
            'visible_components' => collect($visibilitySettings)->filter(fn ($v) => in_array((string) $v['current'], ['1', 'true'], true))->count(),
            'total_components' => count($visibilitySettings),
            'menu_items_total' => $navigationItems->count(),
            'menu_items_active' => $navigationItems->where('is_active', true)->where('is_visible', true)->count(),
        ];

        $availableRoles = $isDeveloper
            ? ['client' => 'Client', 'consultant' => 'Consultant', 'admin' => 'Admin', 'developer' => 'Developer']
            : ['client' => 'Client', 'consultant' => 'Consultant', 'admin' => 'Admin'];

        $assignableConsultants = User::whereIn('role', ['consultant', 'admin'])
            ->withCount('assignedCompanies')
            ->orderByRaw("FIELD(role, 'consultant', 'admin')")
            ->orderBy('name')
            ->get();

        $unassignedCompaniesCount = $companies->whereNull('assigned_consultant_id')->count();

        return view('developer.console', [
            'fw' => FrameworkVersion::active(),
            'isDeveloper' => $isDeveloper,
            'routePrefix' => $routePrefix,
            'availableRoles' => $availableRoles,
            'assignableConsultants' => $assignableConsultants,
            'autoAssignEnabled' => $assignmentService->isAutoAssignEnabled(),
            'autoAssignStrategy' => $assignmentService->strategy(),
            'unassignedCompaniesCount' => $unassignedCompaniesCount,
            'textSettings' => $textSettings,
            'visibilitySettings' => $visibilitySettings,
            'navigationItems' => $navigationItems,
            'users' => $users,
            'companies' => $companies,
            'assessments' => $assessments,
            'auditLogs' => $auditLogs,
            'stats' => $stats,
        ]);
    }

    public function updateText(Request $request)
    {
        $inputs = $request->input('texts', []);
        $defaults = PlatformSetting::defaults();

        foreach ($inputs as $key => $val) {
            if (! isset($defaults[$key]) || $defaults[$key]['group'] !== 'text') {
                continue;
            }
            PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => 'text',
                    'type' => 'string',
                    'label' => $defaults[$key]['label'],
                    'value' => (string) $val,
                    'updated_by' => Auth::id(),
                ]
            );
        }

        PlatformSetting::flushCache();
        AuditLog::record('developer.text_settings_updated', null, null, ['updated_keys' => count($inputs)]);

        return back()->with('status', 'Dynamic platform text updated across all pages.');
    }

    public function updateVisibility(Request $request)
    {
        $checked = $request->input('vis', []);
        $defaults = PlatformSetting::defaults();

        foreach ($defaults as $key => $meta) {
            if ($meta['group'] !== 'visibility') {
                continue;
            }
            $isOn = isset($checked[$key]) && in_array((string) $checked[$key], ['1', 'on', 'true'], true);
            PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => 'visibility',
                    'type' => 'boolean',
                    'label' => $meta['label'],
                    'value' => $isOn ? '1' : '0',
                    'updated_by' => Auth::id(),
                ]
            );
        }

        if (isset($checked['vis.nav_overview'])) {
            $isOverviewOn = in_array((string) $checked['vis.nav_overview'], ['1', 'on', 'true'], true);
            NavigationItem::where('key', 'like', '%overview%')->update(['is_visible' => $isOverviewOn]);
        } else {
            NavigationItem::where('key', 'like', '%overview%')->update(['is_visible' => false]);
        }

        PlatformSetting::flushCache();
        AuditLog::record('developer.visibility_toggles_updated', null, null, ['enabled_count' => count($checked)]);

        return back()->with('status', 'Component & card visibility toggles updated immediately across the platform.');
    }

    public function resetDefaults()
    {
        foreach (PlatformSetting::defaults() as $key => $meta) {
            PlatformSetting::updateOrCreate(
                ['key' => $key],
                [
                    'group' => $meta['group'],
                    'type' => $meta['type'],
                    'label' => $meta['label'],
                    'value' => $meta['value'],
                    'updated_by' => Auth::id(),
                ]
            );
        }

        PlatformSetting::flushCache();
        AuditLog::record('developer.settings_reset_to_defaults');

        return back()->with('status', 'All dynamic text overrides and component visibility toggles restored to Bridge-Tailor defaults.');
    }

    public function storeUser(Request $request)
    {
        $allowedRoles = Auth::user()?->isDeveloper()
            ? 'client,consultant,admin,developer'
            : 'client,consultant,admin';

        $data = $request->validate([
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:180|unique:users,email',
            'role' => 'required|string|in:'.$allowedRoles,
            'phone' => 'nullable|string|max:64',
            'job_title' => 'nullable|string|max:120',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? ucfirst($data['role']),
            'password' => Hash::make($data['password']),
        ]);

        AuditLog::record('developer.user_created', null, null, ['user_id' => $user->id, 'email' => $user->email, 'role' => $user->role]);

        return back()->with('status', "Created {$user->role} account for {$user->name} ({$user->email}).");
    }

    public function updateUser(Request $request, User $user)
    {
        $isDeveloper = Auth::user()?->isDeveloper() ?? false;
        if (! $isDeveloper && $user->role === 'developer') {
            abort(403, 'Administrators cannot inspect or modify Developer accounts.');
        }

        $allowedRoles = $isDeveloper
            ? 'client,consultant,admin,developer'
            : 'client,consultant,admin';

        $data = $request->validate([
            'name' => 'required|string|max:160',
            'email' => 'required|email|max:180|unique:users,email,'.$user->id,
            'role' => 'required|string|in:'.$allowedRoles,
            'phone' => 'nullable|string|max:64',
            'job_title' => 'nullable|string|max:120',
            'new_password' => 'nullable|string|min:6',
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'phone' => $data['phone'] ?? null,
            'job_title' => $data['job_title'] ?? null,
        ];

        if (! empty($data['new_password'])) {
            $payload['password'] = Hash::make($data['new_password']);
        }

        $user->update($payload);

        AuditLog::record('developer.user_updated', null, null, ['user_id' => $user->id, 'role' => $user->role]);

        return back()->with('status', "Updated account details for {$user->name} ({$user->role}).");
    }

    public function updateAutoAssignmentSettings(Request $request, ConsultantAssignmentService $assignmentService)
    {
        $enabled = $request->boolean('auto_assign_enabled');
        $strategy = $request->input('auto_assign_strategy', 'least_loaded');
        if (! in_array($strategy, ['least_loaded', 'round_robin'], true)) {
            $strategy = 'least_loaded';
        }

        PlatformSetting::updateOrCreate(
            ['key' => 'system.auto_assign_consultants'],
            [
                'group' => 'system',
                'type' => 'boolean',
                'label' => 'Automated Consultant Assignment when Admin is Unavailable',
                'value' => $enabled ? '1' : '0',
                'updated_by' => Auth::id(),
            ]
        );

        PlatformSetting::updateOrCreate(
            ['key' => 'system.auto_assign_strategy'],
            [
                'group' => 'system',
                'type' => 'string',
                'label' => 'Automated Assignment Strategy (least_loaded | round_robin)',
                'value' => $strategy,
                'updated_by' => Auth::id(),
            ]
        );

        PlatformSetting::flushCache();

        $assignedNow = 0;
        if ($enabled && $request->boolean('assign_existing_unassigned')) {
            $assignedNow = $assignmentService->autoAssignAllUnassigned();
        }

        AuditLog::record('admin.auto_assignment_configured', null, null, [
            'enabled' => $enabled,
            'strategy' => $strategy,
            'assigned_existing' => $assignedNow,
        ]);

        $msg = 'Automated Consultant Assignment is now '.($enabled ? "ENABLED ({$strategy})" : 'set to MANUAL ONLY').'.';
        if ($assignedNow > 0) {
            $msg .= " Automatically assigned {$assignedNow} previously unassigned business(es).";
        }

        return back()->with('status', $msg);
    }

    public function assignCompanyConsultant(Request $request, Company $company)
    {
        $data = $request->validate([
            'assigned_consultant_id' => 'nullable|exists:users,id',
        ]);

        $consultantId = $data['assigned_consultant_id'] ?: null;
        $consultant = $consultantId ? User::find($consultantId) : null;

        $company->update([
            'assigned_consultant_id' => $consultant?->id,
        ]);

        if ($consultant) {
            $company->consultationRequests()
                ->whereNull('assigned_consultant_id')
                ->update(['assigned_consultant_id' => $consultant->id]);
        }

        AuditLog::record('admin.company_consultant_assigned', $company->id, null, [
            'consultant_id' => $consultant?->id,
            'consultant_name' => $consultant?->name ?? 'Unassigned',
        ]);

        return back()->with('status', $consultant
            ? "Assigned {$company->name} to {$consultant->name}."
            : "Cleared consultant assignment for {$company->name}.");
    }

    public function runBatchAutoAssign(ConsultantAssignmentService $assignmentService)
    {
        $count = $assignmentService->autoAssignAllUnassigned();

        return back()->with('status', $count > 0
            ? "Automated routing engine assigned {$count} unassigned business(es) across active consultants."
            : 'All businesses already have an assigned consultant.');
    }

    public function storeMenuItem(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:160',
            'url' => 'required|string|max:255',
            'route_name' => 'nullable|string|max:120',
            'target' => 'required|in:_self,_blank',
            'audience' => 'required|in:guest,all,auth,client,consultant,admin,developer',
            'is_active' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'is_cta' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $item = NavigationItem::create([
            'key' => 'custom_'.time().'_'.substr(md5($data['label']), 0, 6),
            'label' => $data['label'],
            'url' => $data['url'],
            'route_name' => $data['route_name'] ?? null,
            'target' => $data['target'] ?? '_self',
            'audience' => $data['audience'] ?? 'all',
            'is_active' => $request->boolean('is_active', true),
            'is_visible' => $request->boolean('is_visible', true),
            'is_cta' => $request->boolean('is_cta', false),
            'is_system' => false,
            'sort_order' => (int) ($data['sort_order'] ?? 50),
        ]);

        AuditLog::record('menu.created', null, null, [
            'item_id' => $item->id,
            'label' => $item->label,
            'url' => $item->url,
            'audience' => $item->audience,
        ]);

        return back()->with('status', "Dynamic navigation menu item '{$item->label}' created successfully.");
    }

    public function updateMenuItem(Request $request, NavigationItem $item)
    {
        $data = $request->validate([
            'label' => 'required|string|max:160',
            'url' => 'required|string|max:255',
            'route_name' => 'nullable|string|max:120',
            'target' => 'required|in:_self,_blank',
            'audience' => 'required|in:guest,all,auth,client,consultant,admin,developer',
            'is_active' => 'nullable|boolean',
            'is_visible' => 'nullable|boolean',
            'is_cta' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $item->update([
            'label' => $data['label'],
            'url' => $data['url'],
            'route_name' => $data['route_name'] ?? null,
            'target' => $data['target'] ?? '_self',
            'audience' => $data['audience'],
            'is_active' => $request->boolean('is_active'),
            'is_visible' => $request->boolean('is_visible'),
            'is_cta' => $request->boolean('is_cta'),
            'sort_order' => (int) ($data['sort_order'] ?? $item->sort_order),
        ]);

        AuditLog::record('menu.updated', null, null, [
            'item_id' => $item->id,
            'label' => $item->label,
        ]);

        return back()->with('status', "Navigation menu item '{$item->label}' updated successfully.");
    }

    public function toggleMenuVisibility(NavigationItem $item)
    {
        $item->update(['is_visible' => ! $item->is_visible]);

        $state = $item->is_visible ? 'VISIBLE (shown on header)' : 'HIDDEN (hidden from header)';
        AuditLog::record('menu.visibility_toggled', null, null, [
            'item_id' => $item->id,
            'label' => $item->label,
            'is_visible' => $item->is_visible,
        ]);

        return back()->with('status', "Navigation menu item '{$item->label}' is now {$state}.");
    }

    public function toggleMenuActive(NavigationItem $item)
    {
        $item->update(['is_active' => ! $item->is_active]);

        $state = $item->is_active ? 'ACTIVE (enabled)' : 'INACTIVE (disabled)';
        AuditLog::record('menu.active_toggled', null, null, [
            'item_id' => $item->id,
            'label' => $item->label,
            'is_active' => $item->is_active,
        ]);

        return back()->with('status', "Navigation menu item '{$item->label}' is now {$state}.");
    }

    public function destroyMenuItem(NavigationItem $item)
    {
        $label = $item->label;
        $item->delete();

        AuditLog::record('menu.totally_removed', null, null, [
            'item_label' => $label,
        ]);

        return back()->with('status', "Menu item '{$label}' has been totally removed from the platform.");
    }

    public function resetMenuDefaults()
    {
        NavigationItem::resetToDefaults();

        AuditLog::record('menu.reset_defaults', null, null, []);

        return back()->with('status', 'All navigation menu items have been restored to platform system defaults.');
    }
}
