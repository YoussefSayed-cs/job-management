<?php

namespace App\Policies;

use Shagalni\Shared\Enums\AiAnalysisStatus;
use Shagalni\Shared\Enums\RegistrationStatus;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\AiAnalysis;
use Shagalni\Shared\Models\User;

class AiAnalysisPolicy
{
    public function viewFailed(User $user): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function retry(User $user, AiAnalysis $analysis): bool
    {
        if ($analysis->status !== AiAnalysisStatus::Failed) {
            return false;
        }

        if ($user->role === UserRole::Admin) {
            return true;
        }

        $company = $user->company;

        return $user->role === UserRole::CompanyOwner
            && $company?->registration_status === RegistrationStatus::Approved
            && $analysis->application?->company_id === $company->id;
    }
}