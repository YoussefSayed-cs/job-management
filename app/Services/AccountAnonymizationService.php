<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LogicException;
use Throwable;

class AccountAnonymizationService
{
    public const GRACE_PERIOD_DAYS = 30;

    public function anonymize(User $user): void
    {
        $filesToDelete = [];

        DB::transaction(function () use ($user, &$filesToDelete) {
            // lock the row so two workers can't anonymize the same user together
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();

            $this->ensureCanBeAnonymized($locked);

            $oldEmail = $locked->email;

            // 1) Company Owner: delete the company (DB cascades jobs + change requests,
            //    and sets applications.job_vacancy_id / company_id to null)
            $company = $locked->company;
            if ($company) {
                if ($company->logo) {
                    $filesToDelete[] = ['public', $company->logo];
                }
                $company->delete();
            }

            // 2) Current resume (snapshots are NOT touched)
            $resume = $locked->resume;
            if ($resume) {
                $filesToDelete[] = ['local', $resume->file_path];
                $resume->delete();
            }

            // 3) Saved jobs + personal notifications
            $locked->savedJobs()->delete();
            $locked->notifications()->delete();

            // 4) Profile picture
            if ($locked->profile_picture) {
                $filesToDelete[] = ['public', $locked->profile_picture];
            }

            // 5) Replace identity, keep id + role
            $locked->forceFill([
                'full_name'         => 'Deleted User',
                'email'             => "anonymized-{$locked->id}@anonymized.invalid",
                'phone'             => '',
                'profile_picture'   => null,
                'email_verified_at' => null,
                'password'          => Hash::make(Str::random(64)),
                'status'            => AccountStatus::Deactivated,
                'anonymized_at'     => now(),
            ])->save();

            // 6) Any pending password reset for the old email
            DB::table('password_reset_tokens')->where('email', $oldEmail)->delete();
        });

        // Files are removed only after the transaction committed successfully
        $this->deleteFiles($filesToDelete);
    }

    private function ensureCanBeAnonymized(User $user): void
    {
        if ($user->role === UserRole::Admin) {
            throw new LogicException('The admin account cannot be anonymized.');
        }

        if ($user->anonymized_at !== null) {
            throw new LogicException('This account is already anonymized.');
        }

        if ($user->deletion_requested_at === null) {
            throw new LogicException('This account has no deletion request.');
        }

        if ($user->deletion_requested_at->gt(now()->subDays(self::GRACE_PERIOD_DAYS))) {
            throw new LogicException('The 30-day grace period has not finished yet.');
        }
    }

    private function deleteFiles(array $files): void
    {
        foreach ($files as [$disk, $path]) {
            try {
                Storage::disk($disk)->delete($path);
            } catch (Throwable $e) {
                // a leftover file must never undo a finished anonymization
                Log::warning('Could not delete file during anonymization', [
                    'disk'  => $disk,
                    'path'  => $path,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}