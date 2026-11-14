<?php

namespace App\Services\Companies;

use App\Enums\ChangeRequestStatus;
use App\Models\Company;
use App\Models\CompanyChangeRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompanyChangeRequestReviewService
{
    public function approve(
        CompanyChangeRequest $changeRequest,
        User $admin
    ): CompanyChangeRequest {
        return DB::transaction(function () use ($changeRequest, $admin) {
            [$company, $lockedRequest] = $this->lockPendingRequest($changeRequest);

            if ($lockedRequest->new_name !== null) {
                $company->name = $lockedRequest->new_name;
            }

            if ($lockedRequest->new_industry !== null) {
                $company->industry = $lockedRequest->new_industry;
            }

            $company->save();

            $lockedRequest->status = ChangeRequestStatus::Approved;
            $lockedRequest->reviewed_by = $admin->id;
            $lockedRequest->reviewed_at = now();

            $lockedRequest->save();

            return $lockedRequest->refresh();
        });
    }

    public function reject(
        CompanyChangeRequest $changeRequest,
        User $admin
    ): CompanyChangeRequest {
        return DB::transaction(function () use ($changeRequest, $admin) {
            [, $lockedRequest] = $this->lockPendingRequest($changeRequest);

            $lockedRequest->status = ChangeRequestStatus::Rejected;
            $lockedRequest->reviewed_by = $admin->id;
            $lockedRequest->reviewed_at = now();

            $lockedRequest->save();

            return $lockedRequest->refresh();
        });
    }

    /**
     * Lock the company first, then its change request.
     *
     * @return array{0: Company, 1: CompanyChangeRequest}
     */
    private function lockPendingRequest(
        CompanyChangeRequest $changeRequest
    ): array {
        $company = Company::query()
            ->whereKey($changeRequest->company_id)
            ->lockForUpdate()
            ->firstOrFail();

        $lockedRequest = CompanyChangeRequest::query()
            ->whereKey($changeRequest->getKey())
            ->lockForUpdate()
            ->firstOrFail();

        if ($lockedRequest->status !== ChangeRequestStatus::Pending) {
            throw ValidationException::withMessages([
                'change_request' => 'This change request has already been reviewed.',
            ]);
        }

        return [$company, $lockedRequest];
    }
}
