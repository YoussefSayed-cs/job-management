<?php

namespace App\Services;

use App\Enums\JobStatus;
use App\Models\JobVacancy;
use Carbon\CarbonInterface;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class JobLifecycleService
{
    public function close(JobVacancy $job): JobVacancy
    {
        return $this->withLockedJob($job, function (JobVacancy $lockedJob) {
            if ($lockedJob->status !== JobStatus::Active) {
                throw ValidationException::withMessages([
                    'status' => 'Only active jobs can be closed.',
                ]);
            }

            $lockedJob->update([
                'status' => JobStatus::Closed,
            ]);

            return $lockedJob;
        });
    }

    public function reopen(JobVacancy $job): JobVacancy
    {
        return $this->withLockedJob($job, function (JobVacancy $lockedJob) {
            if ($lockedJob->status !== JobStatus::Closed) {
                throw ValidationException::withMessages([
                    'status' => 'Only closed jobs can be reopened.',
                ]);
            }

            $lockedJob->update([
                'status' => JobStatus::Active,
            ]);

            return $lockedJob;
        });
    }

    public function archive(JobVacancy $job): JobVacancy
    {
        return $this->withLockedJob($job, function (JobVacancy $lockedJob) {
            if (! in_array($lockedJob->status, [
                JobStatus::Active,
                JobStatus::Closed,
            ], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Only active or closed jobs can be archived.',
                ]);
            }

            $lockedJob->update([
                'status' => JobStatus::Archived,
                'archived_at' => now(),
            ]);

            return $lockedJob;
        });
    }

    public function restore(JobVacancy $job): JobVacancy
    {
        return $this->withLockedJob($job, function (JobVacancy $lockedJob) {
            if ($lockedJob->status !== JobStatus::Archived) {
                throw ValidationException::withMessages([
                    'status' => 'Only archived jobs can be restored.',
                ]);
            }

            if (
                $lockedJob->archived_at === null ||
                $lockedJob->archived_at->lte(now()->subDays(30))
            ) {
                throw ValidationException::withMessages([
                    'status' => 'This job can no longer be restored because the 30-day period has expired.',
                ]);
            }

            $lockedJob->update([
                'status' => JobStatus::Active,
                'archived_at' => null,
            ]);

            return $lockedJob;
        });
    }

    public function permanentlyDelete(JobVacancy $job): void
    {
        $this->withLockedJob($job, function (JobVacancy $lockedJob) {
            if ($lockedJob->status !== JobStatus::Archived) {
                throw ValidationException::withMessages([
                    'status' => 'Only archived jobs can be permanently deleted.',
                ]);
            }

            $lockedJob->delete();
        });
    }

    /**
     * Permanently remove archived jobs whose 30-day grace period has expired.
     */
    public function purgeExpiredArchivedJobs(): int
    {
        $cutoff = now()->subDays(30);
        $purged = 0;

        JobVacancy::query()
            ->where('status', JobStatus::Archived->value)
            ->whereNotNull('archived_at')
            ->where('archived_at', '<=', $cutoff)
            ->chunkById(100, function ($jobs) use ($cutoff, &$purged) {
                foreach ($jobs as $job) {
                    $deleted = DB::transaction(function () use ($job, $cutoff) {
                        $lockedJob = JobVacancy::query()
                            ->whereKey($job->id)
                            ->lockForUpdate()
                            ->first();

                        if (! $lockedJob) {
                            return false;
                        }

                        if (
                            $lockedJob->status !== JobStatus::Archived ||
                            $lockedJob->archived_at === null ||
                            $lockedJob->archived_at->gt($cutoff)
                        ) {
                            return false;
                        }

                        $lockedJob->delete();

                        return true;
                    });

                    if ($deleted) {
                        $purged++;
                    }
                }
            }, 'id');

        return $purged;
    }

    private function withLockedJob(
        JobVacancy $job,
        Closure $callback
    ): mixed {
        return DB::transaction(function () use ($job, $callback) {
            $lockedJob = JobVacancy::query()
                ->whereKey($job->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            return $callback($lockedJob);
        });
    }
}