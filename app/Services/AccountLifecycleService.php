<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Shagalni\Shared\Enums\AccountStatus;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\User;

class AccountLifecycleService
{
    public const REACTIVATION_LINK_HOURS = 24;

    public const DELETION_GRACE_DAYS = 30;

    public function suspend(User $target): User
    {
        return $this->transition(
            $target,
            [AccountStatus::Active, AccountStatus::Deactivated],
            AccountStatus::Suspended,
            'Only active or deactivated accounts can be suspended.'
        );
    }

    public function deactivate(User $target): User
    {
        return $this->transition(
            $target,
            [AccountStatus::Active],
            AccountStatus::Deactivated,
            'Only active accounts can be deactivated.'
        );
    }

    public function reactivate(User $target): User
    {
        return $this->transition(
            $target,
            [AccountStatus::Suspended, AccountStatus::Deactivated],
            AccountStatus::Active,
            'Only suspended or deactivated accounts can be reactivated.'
        );
    }

    public function deactivateOwnAccount(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $locked = User::query()->with('company')->lockForUpdate()->findOrFail($user->id);

            if (
                ! $this->canManageFromBackoffice($locked)
                || $locked->status !== AccountStatus::Active
                || $locked->deletion_requested_at !== null
                || $locked->anonymized_at !== null
            ) {
                throw ValidationException::withMessages([
                    'account' => 'This account cannot be deactivated from the Backoffice.',
                ]);
            }

            $locked->forceFill(['status' => AccountStatus::Deactivated])->save();

            return $locked;
        });
    }

    public function requestDeletion(User $user): User
    {
        return DB::transaction(function () use ($user) {
            $locked = User::query()->with('company')->lockForUpdate()->findOrFail($user->id);

            if (
                ! $this->canManageFromBackoffice($locked)
                || $locked->status !== AccountStatus::Active
                || $locked->deletion_requested_at !== null
                || $locked->anonymized_at !== null
            ) {
                throw ValidationException::withMessages([
                    'account' => 'This account cannot request deletion.',
                ]);
            }

            $locked->forceFill(['deletion_requested_at' => now()])->save();

            return $locked;
        });
    }

    public function issueReactivationToken(User $user): string
    {
        return DB::transaction(function () use ($user) {
            $locked = User::query()->with('company')->lockForUpdate()->findOrFail($user->id);

            if (! $this->canRequestReactivation($locked)) {
                throw ValidationException::withMessages([
                    'account' => 'This account cannot request a reactivation link.',
                ]);
            }

            $token = Str::random(64);

            Cache::put(
                $this->tokenKey($locked),
                $token,
                now()->addHours(self::REACTIVATION_LINK_HOURS)
            );

            return $token;
        });
    }

    public function reactivateWithToken(User $user, string $token): User
    {
        return DB::transaction(function () use ($user, $token) {
            $locked = User::query()->with('company')->lockForUpdate()->findOrFail($user->id);
            $stored = Cache::get($this->tokenKey($locked));

            if (
                ! is_string($stored)
                || ! hash_equals($stored, $token)
                || ! $this->canRequestReactivation($locked)
            ) {
                throw ValidationException::withMessages([
                    'account' => 'This reactivation link is invalid or has already been used.',
                ]);
            }

            $locked->forceFill(['status' => AccountStatus::Active])->save();
            Cache::forget($this->tokenKey($locked));

            return $locked;
        });
    }

    public function canRequestReactivation(User $user): bool
    {
        return $this->canManageFromBackoffice($user)
            && $user->status === AccountStatus::Deactivated
            && $user->anonymized_at === null;
    }

    public function cancelDeletion(User $user, int $requestedAt): User
    {
        return DB::transaction(function () use ($user, $requestedAt) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);

            if (
                $locked->anonymized_at !== null
                || $locked->deletion_requested_at === null
                || $locked->deletion_requested_at->getTimestamp() !== $requestedAt
            ) {
                throw ValidationException::withMessages([
                    'account' => 'This cancellation link is invalid or has already been used.',
                ]);
            }

            $graceEndsAt = $locked->deletion_requested_at
                ->addDays(self::DELETION_GRACE_DAYS);

            if (! now()->lt($graceEndsAt)) {
                throw ValidationException::withMessages([
                    'account' => 'The account recovery period has expired.',
                ]);
            }

            $locked->forceFill(['deletion_requested_at' => null])->save();

            return $locked;
        });
    }

    public function canManageFromBackoffice(User $user): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $user->company?->registration_status === RegistrationStatus::Approved;
    }

    /**
     * @param  array<int, AccountStatus>  $from
     */
    private function transition(User $user, array $from, AccountStatus $to, string $message): User
    {
        $locked = DB::transaction(function () use ($user, $from, $to, $message) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($locked->role === UserRole::Admin || $locked->anonymized_at !== null) {
                throw ValidationException::withMessages([
                    'account' => 'This account cannot be changed.',
                ]);
            }

            if (! in_array($locked->status, $from, true)) {
                throw ValidationException::withMessages(['account' => $message]);
            }

            $locked->forceFill(['status' => $to])->save();

            return $locked;
        });

        if ($to === AccountStatus::Active) {
            Cache::forget($this->tokenKey($locked));
        }

        return $locked;
    }

    private function tokenKey(User $user): string
    {
        return 'account-reactivation:'.$user->id;
    }
}
