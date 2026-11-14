<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        return $this->deactivate($user);
    }

    /**
     * Single-use reactivation link: the token lives in the cache for 24 hours
     * and is removed the first time it is used. Asking for a new link replaces
     * the old one.
     */
    public function issueReactivationToken(User $user): string
    {
        $token = Str::random(64);

        Cache::put(
            $this->tokenKey($user),
            $token,
            now()->addHours(self::REACTIVATION_LINK_HOURS)
        );

        return $token;
    }

    public function reactivateWithToken(User $user, string $token): User
    {
        // pull() reads and deletes in one step, which makes the link single-use.
        $stored = Cache::pull($this->tokenKey($user));

        if (! is_string($stored) || ! hash_equals($stored, $token)) {
            throw ValidationException::withMessages([
                'account' => 'This reactivation link is invalid or has already been used.',
            ]);
        }

        // Suspended accounts need an Admin decision, so only Deactivated is allowed here.
        return $this->transition(
            $user,
            [AccountStatus::Deactivated],
            AccountStatus::Active,
            'This account cannot be reactivated with an email link.'
        );
    }

    public function canRequestReactivation(User $user): bool
    {
        return $user->role !== UserRole::Admin
            && $user->status === AccountStatus::Deactivated
            && $user->anonymized_at === null;
    }

    public function cancelDeletion(User $user, int $requestedAt): User
    {
        return DB::transaction(function () use ($user, $requestedAt) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);

            // One answer for: no request, already cancelled, or a link from an older request.
            if (
                $locked->anonymized_at !== null
                || $locked->deletion_requested_at === null
                || $locked->deletion_requested_at->getTimestamp() !== $requestedAt
            ) {
                throw ValidationException::withMessages([
                    'account' => 'This cancellation link is invalid or has already been used.',
                ]);
            }

            $graceEndsAt = $locked->deletion_requested_at->copy()->addDays(self::DELETION_GRACE_DAYS);

            if (now()->greaterThan($graceEndsAt)) {
                throw ValidationException::withMessages([
                    'account' => 'The account recovery period has expired.',
                ]);
            }

            $locked->forceFill(['deletion_requested_at' => null])->save();

            return $locked;
        });
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

            // status is not mass assignable on purpose, so forceFill is used.
            $locked->forceFill(['status' => $to])->save();

            return $locked;
        });

        if ($to === AccountStatus::Active) {
            // Any link that was still waiting is useless now.
            Cache::forget($this->tokenKey($locked));
        }

        return $locked;
    }

    private function tokenKey(User $user): string
    {
        return 'account-reactivation:'.$user->id;
    }
}
