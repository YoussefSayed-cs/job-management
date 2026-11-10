<?php

namespace App\Policies;

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
}