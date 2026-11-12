<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CompanyChangeRequest;
use App\Models\User;

class CompanyChangeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function review(User $user, CompanyChangeRequest $changeRequest): bool
    {
        return $user->role === UserRole::Admin;
    }
}
