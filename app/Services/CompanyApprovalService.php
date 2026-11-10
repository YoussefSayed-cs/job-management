<?php

namespace App\Services;

use App\Enums\RegistrationStatus;
use App\Models\Company;
use App\Models\User;
use App\Notifications\CompanyRegistrationApproved;
use App\Notifications\CompanyRegistrationRejected;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompanyApprovalService
{
    public function approve(Company $company, User $admin): Company
    {
        $company = DB::transaction(function () use ($company, $admin) {
            $company = $this->lockReviewableCompany($company);

            $company->update([
                'registration_status' => RegistrationStatus::Approved,
                'rejection_reason' => null,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $company;
        });

        $this->notifyOwner($company, new CompanyRegistrationApproved($company));

        return $company;
    }

    public function reject(Company $company, User $admin, string $reason): Company
    {
        $company = DB::transaction(function () use ($company, $admin, $reason) {
            $company = $this->lockReviewableCompany($company);

            $company->update([
                'registration_status' => RegistrationStatus::Rejected,
                'rejection_reason' => $reason,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
            ]);

            return $company;
        });

        $this->notifyOwner($company, new CompanyRegistrationRejected($company));

        return $company;
    }

   
    private function lockReviewableCompany(Company $company): Company
    {
        $company = Company::query()
            ->with('owner')
            ->lockForUpdate()
            ->findOrFail($company->id);

        if ($company->registration_status !== RegistrationStatus::PendingApproval) {
            throw ValidationException::withMessages([
                'company' => 'Only registrations that are pending approval can be reviewed.',
            ]);
        }

        if (! $company->owner->hasVerifiedEmail()) {
            throw ValidationException::withMessages([
                'company' => 'The owner must verify their email address before this registration can be reviewed.',
            ]);
        }

        return $company;
    }

        private function notifyOwner(Company $company, Notification $notification): void
    {
        try {
            $company->owner->notify($notification);
        } catch (Throwable $e) {
            report($e);
        }
    }
}