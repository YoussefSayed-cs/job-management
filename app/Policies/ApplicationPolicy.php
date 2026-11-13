<?php

namespace App\Policies;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    /**
     * Only approved Company Owners can access applications.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $user->company?->registration_status === RegistrationStatus::Approved;
    }

    /**
     * Company Owners can view applications attributed to their company.
     * This includes historical applications whose jobs were purged.
     */
    public function view(User $user, Application $application): bool
    {
        $company = $user->company;

        return $user->role === UserRole::CompanyOwner
            && $company !== null
            && $company->registration_status === RegistrationStatus::Approved
            && $application->company_id === $company->id;
    }

    /**
     * Status changes require an existing Job belonging to the owner.
     * Historical applications for purged jobs are read-only.
     */
    public function updateStatus(User $user, Application $application): bool
    {
        if (! $this->view($user, $application)) {
            return false;
        }

        return $application->job_vacancy_id !== null
            && $application->jobVacancy !== null
            && $application->jobVacancy->company_id === $user->company->id;
    }
}
