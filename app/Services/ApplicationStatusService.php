<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ApplicationStatusService
{
    /**
     * Update an Application status and preserve its history.
     */
    public function updateStatus(
        Application $application,
        ApplicationStatus $newStatus,
        User $changedBy,
        ?string $message = null
    ): Application {
        return DB::transaction(function () use (
            $application,
            $newStatus,
            $changedBy,
            $message
        ) {
            $application = Application::query()->lockForUpdate()->findOrFail($application->id);
               
                

            $currentStatus = $application->status;

            if ($currentStatus->isTerminal()) {
                throw ValidationException::withMessages([
                    'status' => 'This application has already reached a final status.',
                ]);
            }

            if (! $this->isAllowedTransition($currentStatus, $newStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'The requested status transition is not allowed.',
                ]);
            }

            $application->update([ 'status' => $newStatus,]);
           

               
            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'message' => $message,
                'changed_by' => $changedBy->id,
            ]);

            return $application->fresh(['statusHistories.changedBy',]);
            
             
        });
    }

    /**
     * Only the next stage or Rejected is allowed.
     */
    private function isAllowedTransition(
        ApplicationStatus $current,
        ApplicationStatus $next
    ): bool {
        if ($next === ApplicationStatus::Rejected) {
            return true;
        }

        $allowedNextStatuses = 
        [
            ApplicationStatus::Pending->value =>
                ApplicationStatus::UnderReview,

            ApplicationStatus::UnderReview->value =>
                ApplicationStatus::Shortlisted,

            ApplicationStatus::Shortlisted->value =>
                ApplicationStatus::Interview,

            ApplicationStatus::Interview->value =>
                ApplicationStatus::Accepted,
        ];

        $expectedNext = $allowedNextStatuses[$current->value] ?? null;

        return $expectedNext === $next;
    }
}
