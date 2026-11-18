<?php

namespace App\Diagnostics;

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\PlatformSetting;
use App\Models\User;

/**
 * Handles both Manual Admin Assignment and Automated Consultant Routing
 * (Workload-Balanced or Round-Robin) when an Admin is unavailable.
 */
class ConsultantAssignmentService
{
    public function isAutoAssignEnabled(): bool
    {
        return PlatformSetting::visible('system.auto_assign_consultants', true);
    }

    public function strategy(): string
    {
        return PlatformSetting::text('system.auto_assign_strategy', 'least_loaded') ?: 'least_loaded';
    }

    /**
     * Automatically assigns a consultant to a company if unassigned and auto-assignment is enabled
     * (or when forced by the "Auto-Assign Unassigned Companies" button).
     */
    public function autoAssignCompany(Company $company, bool $force = false): ?User
    {
        if ($company->assigned_consultant_id) {
            return $company->consultant;
        }

        if (! $force && ! $this->isAutoAssignEnabled()) {
            return null;
        }

        $consultant = $this->pickNextConsultant();
        if (! $consultant) {
            return null;
        }

        $company->update([
            'assigned_consultant_id' => $consultant->id,
        ]);

        // Also sync any unassigned consultation requests for this company
        $company->consultationRequests()
            ->whereNull('assigned_consultant_id')
            ->update(['assigned_consultant_id' => $consultant->id]);

        AuditLog::record(
            'company.consultant_auto_assigned',
            $company->id,
            null,
            [
                'consultant_id' => $consultant->id,
                'consultant_name' => $consultant->name,
                'strategy' => $this->strategy(),
            ],
            'Automated Assignment Engine'
        );

        return $consultant;
    }

    /**
     * Batch-assigns all currently unassigned companies.
     */
    public function autoAssignAllUnassigned(): int
    {
        $unassigned = Company::whereNull('assigned_consultant_id')->orderBy('id')->get();
        $count = 0;

        foreach ($unassigned as $company) {
            if ($this->autoAssignCompany($company, true)) {
                $count++;
            }
        }

        return $count;
    }

    public function pickNextConsultant(): ?User
    {
        $consultants = User::where('role', 'consultant')
            ->withCount('assignedCompanies')
            ->orderBy('id')
            ->get();

        // Fall back to admin if no dedicated consultant user exists
        if ($consultants->isEmpty()) {
            $consultants = User::where('role', 'admin')
                ->withCount('assignedCompanies')
                ->orderBy('id')
                ->get();
        }

        if ($consultants->isEmpty()) {
            return null;
        }

        $strategy = $this->strategy();

        if ($strategy === 'round_robin') {
            $lastAssignedId = (int) Company::whereNotNull('assigned_consultant_id')
                ->latest('updated_at')
                ->value('assigned_consultant_id');

            if ($lastAssignedId) {
                $next = $consultants->first(fn (User $u) => $u->id > $lastAssignedId);

                return $next ?? $consultants->first();
            }

            return $consultants->first();
        }

        // Default: 'least_loaded' (Workload-Balanced — fewest assigned companies, tie-break by ID)
        return $consultants->sortBy([
            ['assigned_companies_count', 'asc'],
            ['id', 'asc'],
        ])->first();
    }
}
