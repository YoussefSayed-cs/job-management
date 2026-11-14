<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\User;
use App\Notifications\ApplicationStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ApplicationStatusService
{
    public function updateStatus(
        Application $application,
        ApplicationStatus $newStatus,
        User $changedBy,
        ?string $message = null
    ): Application {
        $application = DB::transaction(function () use (
            $application,
            $newStatus,
            $changedBy,
            $message
        ) {
            $application = Application::query()
                ->lockForUpdate()
                ->findOrFail($application->id);

            if ($application->job_vacancy_id === null) {
                throw ValidationException::withMessages([
                    'status' => 'This application is linked to a deleted job.',
                ]);
            }

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

            $application->update([
                'status' => $newStatus,
            ]);

            ApplicationStatusHistory::create([
                'application_id' => $application->id,
                'from_status' => $currentStatus,
                'to_status' => $newStatus,
                'message' => $message,
                'changed_by' => $changedBy->id,
            ]);

            return $application->fresh([
                'statusHistories.changedBy',
            ]);
        });

        // Notify only after the transaction has been committed.
        $this->notifyJobSeeker($application, $message);

        return $application;
    }

    /**
     * Delivery is suppressed (not queued) while the Job Seeker account is
     * Suspended, Deactivated or pending deletion. The status and history
     * remain the source of truth. A delivery failure never rolls back the
     * status change.
     */
    private function notifyJobSeeker(Application $application, ?string $message): void
    {
        $jobSeeker = $application->user;

        if ($jobSeeker === null || ! $jobSeeker->canReceiveApplicationNotifications()) {
            return;
        }

        try {
            $jobSeeker->notify(new ApplicationStatusChanged($application, $message));
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function isAllowedTransition(
        ApplicationStatus $current,
        ApplicationStatus $next
    ): bool {
        if ($next === ApplicationStatus::Rejected) {
            return true;
        }

        $allowedTransitions = [
            ApplicationStatus::Pending->value => ApplicationStatus::UnderReview,

            ApplicationStatus::UnderReview->value => ApplicationStatus::Shortlisted,

            ApplicationStatus::Shortlisted->value => ApplicationStatus::Interview,

            ApplicationStatus::Interview->value => ApplicationStatus::Accepted,
        ];

        return ($allowedTransitions[$current->value] ?? null) === $next;
    }
}
