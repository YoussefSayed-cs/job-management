<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\User;
use App\Services\CompanyOwnerInvitationService;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function view(User $user, User $target): bool
    {
        return $user->role === UserRole::Admin && $target->role !== UserRole::Admin;
    }

    public function suspend(User $user, User $target): bool
    {
        return $this->canManageAccount($user, $target);
    }

    public function deactivate(User $user, User $target): bool
    {
        return $this->canManageAccount($user, $target);
    }

    public function reactivate(User $user, User $target): bool
    {
        return $this->canManageAccount($user, $target);
    }

    /**
     * Admin can manage Job Seeker / Company Owner accounts only while
     * anonymized_at is null. Admin accounts and tombstones are untouchable.
     */
    private function canManageAccount(User $user, User $target): bool
    {
        return $user->role === UserRole::Admin
            && $target->role !== UserRole::Admin
            && $target->anonymized_at === null;
    }

    public function __construct(private CompanyOwnerInvitationService $invitations) {}

    public function create(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function resendInvitation(User $user, User $target): bool
    {
        return $user->role === UserRole::Admin
            && $this->invitations->isInvitationPending($target);
    }
}
