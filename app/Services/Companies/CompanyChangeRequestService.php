<?php

namespace App\Services\Companies;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Shagalni\Shared\Enums\ChangeRequestStatus;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\CompanyChangeRequest;

class CompanyChangeRequestService
{
    public function submit(Company $company, array $data): CompanyChangeRequest
    {
        return DB::transaction(function () use ($company, $data) {
            $lockedCompany = Company::query()
                ->whereKey($company->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedCompany->registration_status !== RegistrationStatus::Approved) {
                throw ValidationException::withMessages([
                    'company' => 'Only approved companies can submit change requests.',
                ]);
            }

            $newName = $data['new_name'] ?? null;
            $newIndustry = $data['new_industry'] ?? null;

            if ($newName !== null && mb_strtolower(trim($newName)) === mb_strtolower(trim($lockedCompany->name))) {
                $newName = null;
            }

            if ($newIndustry !== null && mb_strtolower(trim($newIndustry)) === mb_strtolower(trim($lockedCompany->industry))) {
                $newIndustry = null;
            }

            if ($newName === null && $newIndustry === null) {
                throw ValidationException::withMessages([
                    'change_request' => 'Nothing to change: the submitted values match your current company information.',
                ]);
            }

            $hasPendingRequest = $lockedCompany->changeRequests()
                ->where('status', ChangeRequestStatus::Pending)
                ->exists();

            if ($hasPendingRequest) {
                throw ValidationException::withMessages([
                    'change_request' => 'Your company already has a pending change request.',
                ]);
            }

            return $lockedCompany->changeRequests()->create([
                'new_name' => $newName,
                'new_industry' => $newIndustry,
                'status' => ChangeRequestStatus::Pending,
            ]);
        });
    }
}
