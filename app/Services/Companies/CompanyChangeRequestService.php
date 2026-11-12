<?php

namespace App\Services\Companies;

use App\Enums\ChangeRequestStatus;
use App\Enums\RegistrationStatus;
use App\Models\Company;
use App\Models\CompanyChangeRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
                throw ValidationException::withMessages([
                    'new_name' => 'The new company name must be different from the current name.',
                ]);
            }

            if ($newIndustry !== null && mb_strtolower(trim($newIndustry)) === mb_strtolower(trim($lockedCompany->industry))) {
                throw ValidationException::withMessages([
                    'new_industry' => 'The new industry must be different from the current industry.',
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
