<?php

namespace App\Policies;

use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\CompanyChangeRequest;
use Shagalni\Shared\Models\User;

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
