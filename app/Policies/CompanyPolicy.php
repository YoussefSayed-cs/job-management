<?php

namespace App\Policies;

use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;

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
}
