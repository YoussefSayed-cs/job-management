<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\JobCategory;
use App\Models\User;

class JobCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function update(User $user, JobCategory $category): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function delete(User $user, JobCategory $category): bool
    {
        return $user->role === UserRole::Admin;
    }
}