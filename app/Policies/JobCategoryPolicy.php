<?php

namespace App\Policies;

use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\User;

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
