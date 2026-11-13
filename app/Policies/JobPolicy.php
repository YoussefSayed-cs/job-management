<?php

namespace App\Policies;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Enums\JobStatus;
use App\Models\JobVacancy;
use App\Models\User;

class JobPolicy
{
    /**
     * Admin can view all jobs.
     * Company Owner can view jobs belonging to their approved company.
     */
    public function viewAny(User $user): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $this->hasApprovedCompany($user);
    }

    /**
     * Admin can view any job.
     * Company Owner can only view their own company's jobs.
     */
    public function view(User $user, JobVacancy $job): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $this->ownsJob($user, $job);
    }

    /**
     * Only Company Owners with an approved company can create jobs.
     * Admin cannot create jobs on behalf of companies.
     */
    public function create(User $user): bool
    {
        return $this->hasApprovedCompany($user);
    }

    /**
     * Admin can update any job.
     * Company Owner can update only their own company's jobs.
     */
    public function update(User $user, JobVacancy $job): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $this->ownsJob($user, $job);
    }

    public function close(User $user, JobVacancy $job): bool
    {
        return $this->canManageJob($user, $job);
    }

    public function reopen(User $user, JobVacancy $job): bool
    {
        return $this->canManageJob($user, $job);
    }

    public function archive(User $user, JobVacancy $job): bool
    {
        return $this->canManageJob($user, $job);
    }

    public function restore(User $user, JobVacancy $job): bool
    {
        return $this->canManageJob($user, $job);
    }

    /**
     * Permanent deletion is allowed only for archived jobs.
     */
    public function delete(User $user, JobVacancy $job): bool
    {
        return $job->status === JobStatus::Archived && $this->canManageJob($user, $job);
           
    }

    /**
     * Shared authorization for job lifecycle operations.
     */
    private function canManageJob(User $user, JobVacancy $job): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $this->ownsJob($user, $job);
    }

    /**
     * Check whether the job belongs to the authenticated owner's company.
     */
    private function ownsJob(User $user, JobVacancy $job): bool
    {
        if ($user->role !== UserRole::CompanyOwner) {
            return false;
        }

        $company = $user->company;

        return $company !== null
            && $company->registration_status === RegistrationStatus::Approved
            && $job->company_id === $company->id;
    }

    /**
     * Check whether the user owns an approved company.
     */
    private function hasApprovedCompany(User $user): bool
    {
        if ($user->role !== UserRole::CompanyOwner) {
            return false;
        }

        $company = $user->company;

        return $company !== null && $company->registration_status === RegistrationStatus::Approved;
           
    }
}
