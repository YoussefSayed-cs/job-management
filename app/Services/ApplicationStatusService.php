<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\ApplicationStatusHistory;
use Shagalni\Shared\Models\User;

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

            if (! $currentStatus->canTransitionTo($newStatus)) {
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

        return $application;
    }
}
