<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Notifications\CompanyOwnerInvitation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompanyOwnerInvitationService
{
    public const INVITATION_LINK_HOURS = 48;

    private const INVALID_MESSAGE = 'This invitation link is invalid or has expired. Ask an administrator to send you a new one.';

    /**
     * FR-03: create the Company Owner and exactly one Approved Company together.
     * Nothing is emailed here, so a mail failure can never undo the account.
     */
    public function invite(User $admin, array $data): User
    {
        return DB::transaction(function () use ($admin, $data) {
            $owner = new User([
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                // Random value nobody knows: the owner picks a real password from the invitation link.
                'password' => Str::random(64),
            ]);

            // role and status are not mass assignable on purpose, so forceFill is used.
            $owner->forceFill([
                'role' => UserRole::CompanyOwner,
                'status' => AccountStatus::Active,
            ])->save();

            Company::create([
                'owner_id' => $owner->id,
                'name' => $data['company_name'],
                'industry' => $data['industry'],
                'location' => $data['location'],
                'description' => $data['description'] ?? null,
                'website' => $data['website'] ?? null,
                'registration_status' => RegistrationStatus::Approved,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $owner;
        });
    }

    /**
     * Issues a fresh token (which replaces any older one) and emails it.
     * Returns false when the email could not be sent, so the caller can tell the Admin.
     */
    public function sendInvitation(User $owner): bool
    {
        try {
            $token = $this->issueToken($owner);

            $owner->notify(new CompanyOwnerInvitation($token));

            return true;
        } catch (Throwable $e) {
            report($e);

            return false;
        }
    }

    public function issueToken(User $owner): string
    {
        $token = Str::random(64);

        Cache::put(
            $this->tokenKey($owner),
            $token,
            now()->addHours(self::INVITATION_LINK_HOURS)
        );

        return $token;
    }

    public function hasValidToken(User $owner, string $token): bool
    {
        $stored = Cache::get($this->tokenKey($owner));

        return is_string($stored) && hash_equals($stored, $token);
    }

    /**
     * An invitation is pending while the owner has an Approved Company but
     * has not verified the email yet (accepting the invitation verifies it).
     */
    public function isInvitationPending(User $user): bool
    {
        return $user->role === UserRole::CompanyOwner
            && $user->anonymized_at === null
            && $user->email_verified_at === null
            && $user->company?->registration_status === RegistrationStatus::Approved;
    }

    /**
     * Completing the invitation sets the first password and verifies the email.
     */
    public function accept(User $user, string $token, string $password): User
    {
        if (! $this->hasValidToken($user, $token)) {
            throw ValidationException::withMessages(['account' => self::INVALID_MESSAGE]);
        }

        $locked = DB::transaction(function () use ($user, $password) {
            $locked = User::query()->lockForUpdate()->findOrFail($user->id);

            // After the first success the email is verified, so a second request is refused here.
            if (! $this->isInvitationPending($locked)) {
                throw ValidationException::withMessages(['account' => self::INVALID_MESSAGE]);
            }

            // password is hashed by the model cast.
            $locked->forceFill([
                'password' => $password,
                'email_verified_at' => now(),
            ])->save();

            return $locked;
        });

        // Single-use: the token is useless from now on.
        Cache::forget($this->tokenKey($locked));

        return $locked;
    }

    private function tokenKey(User $owner): string
    {
        return 'company-owner-invitation:'.$owner->id;
    }
}
