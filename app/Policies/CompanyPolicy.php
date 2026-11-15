<?php

namespace App\Policies;

use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, Company $company): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function review(User $user, Company $company): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function viewOwnProfile(User $user, Company $company): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $company->owner_id === $user->id
            && $company->registration_status === RegistrationStatus::Approved;
    }

    public function updateProfile(User $user, Company $company): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $company->owner_id === $user->id
            && $company->registration_status === RegistrationStatus::Approved;
    }

    public function viewChangeRequests(User $user, Company $company): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $company->owner_id === $user->id
            && $company->registration_status === RegistrationStatus::Approved;
    }

    public function createChangeRequest(User $user, Company $company): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $company->owner_id === $user->id
            && $company->registration_status === RegistrationStatus::Approved;
    }
}
