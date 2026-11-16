<?php

namespace App\Policies;

use App\Services\CompanyOwnerInvitationService;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Admin || $this->isApprovedCompanyOwner($user);
    }

    public function view(User $user, User $target): bool
    {
        if ($user->role === UserRole::Admin) {
            return $target->role !== UserRole::Admin;
        }

        return $this->isApprovedCompanyOwner($user)
            && $target->role === UserRole::JobSeeker
            && $target->anonymized_at === null
            && $target->applications()
                ->where('company_id', $user->company->id)
                ->exists();
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

    private function isApprovedCompanyOwner(User $user): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $user->company?->registration_status === RegistrationStatus::Approved;
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
