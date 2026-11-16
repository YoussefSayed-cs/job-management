<?php

namespace App\Services;

use Carbon\CarbonPeriod;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Shagalni\Shared\Enums\ApplicationStatus;
use Shagalni\Shared\Enums\JobStatus;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\ApplicationStatusHistory;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\JobVacancy;
use Shagalni\Shared\Models\User;

class AnalyticsService
{
    private const ACTIVITY_DAYS = 30;

    public function platformTotals(): array
    {
        $distribution = $this->statusDistribution();

        return [
            'users' => User::whereNull('anonymized_at')->count(),
            'companies' => Company::count(),
            'jobs' => JobVacancy::count(),
            'applications' => array_sum($distribution),
            ...$this->rates($distribution),
        ];
    }

    public function platformActivity(): array
    {
        $since = now()->subDays(self::ACTIVITY_DAYS);

        return [
            'new_users' => User::where('created_at', '>=', $since)->count(),
            'company_registrations' => Company::where('created_at', '>=', $since)->count(),
            'jobs_published' => JobVacancy::where('created_at', '>=', $since)->count(),
            'applications_submitted' => Application::where('submitted_at', '>=', $since)->count(),
            'status_changes' => ApplicationStatusHistory::whereNotNull('from_status')
                ->where('created_at', '>=', $since)
                ->count(),
        ];
    }

    public function companyOverview(Company $company): array
    {
        $distribution = $this->statusDistribution($company);

        return [
            'jobs' => $company->jobVacancies()->count(),
            'applications' => array_sum($distribution),
            'applicants' => Application::where('company_id', $company->id)
                ->distinct()
                ->count('user_id'),
            'status_distribution' => $distribution,
            ...$this->rates($distribution),
        ];
    }

    public function companyDashboard(Company $company): array
    {
        $today = now()->toDateString();
        $jobs = $company->jobVacancies();
        $statusDistribution = $this->statusDistribution($company);
        $summary = (clone $jobs)
            ->selectRaw(
                'COUNT(*) as total,
                SUM(CASE WHEN status = ? AND (application_deadline IS NULL OR application_deadline >= ?) THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = ? AND application_deadline < ? THEN 1 ELSE 0 END) as expired,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as archived',
                [
                    JobStatus::Active->value,
                    $today,
                    JobStatus::Active->value,
                    $today,
                    JobStatus::Closed->value,
                    JobStatus::Archived->value,
                ],
            )
            ->first();

        return [
            'company' => $company,
            'applicationStats' => [
                'total' => array_sum($statusDistribution),
                'applicants' => Application::where('company_id', $company->id)
                    ->distinct()
                    ->count('user_id'),
            ],
            'statusDistribution' => $statusDistribution,
            'jobStats' => [
                'total' => (int) $summary->total,
                'active' => (int) $summary->active,
                'expired' => (int) $summary->expired,
                'closed' => (int) $summary->closed,
                'archived' => (int) $summary->archived,
            ],
            'recentJobs' => $company->jobVacancies()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(['id', 'title', 'status', 'created_at']),
        ];
    }

    public function statusDistribution(?Company $company = null): array
    {
        $counts = Application::query()
            ->when($company, fn ($query) => $query->where('company_id', $company->id))
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $distribution = [];

        foreach (ApplicationStatus::cases() as $status) {
            $distribution[$status->value] = (int) ($counts[$status->value] ?? 0);
        }

        return $distribution;
    }

    public function applicationsPerJob(Company $company, int $limit = 10): array
    {
        return collect($this->applicationsPerJobPaginated($company, $limit)->items())
            ->map(fn (JobVacancy $job) => [
                'title' => $job->title,
                'applications' => (int) $job->applications_count,
            ])
            ->all();
    }

    public function applicationsPerJobPaginated(Company $company, int $perPage = 10): LengthAwarePaginator
    {
        return JobVacancy::query()
            ->where('company_id', $company->id)
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->orderBy('title')
            ->paginate($perPage);
    }

    public function dailyApplications(?Company $company = null): array
    {
        $start = now()->subDays(self::ACTIVITY_DAYS - 1)->startOfDay();

        $counts = Application::query()
            ->when($company, fn ($query) => $query->where('company_id', $company->id))
            ->where('submitted_at', '>=', $start)
            ->selectRaw('DATE(submitted_at) as day, COUNT(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $series = [];

        foreach (CarbonPeriod::create($start, now()->startOfDay()) as $date) {
            $key = $date->toDateString();
            $series[$key] = (int) ($counts[$key] ?? 0);
        }

        return $series;
    }

    public function topCompaniesByApplications(int $limit = 5): array
    {
        return Application::query()
            ->join('companies', 'companies.id', '=', 'applications.company_id')
            ->selectRaw('companies.name as name, COUNT(applications.id) as total')
            ->groupBy('companies.id', 'companies.name')
            ->orderByDesc('total')
            ->limit($limit)
            ->toBase()
            ->get()
            ->map(fn ($row) => [
                'name' => $row->name,
                'applications' => (int) $row->total,
            ])
            ->all();
    }

    private function rates(array $distribution): array
    {
        $accepted = $distribution[ApplicationStatus::Accepted->value] ?? 0;
        $rejected = $distribution[ApplicationStatus::Rejected->value] ?? 0;
        $decided = $accepted + $rejected;

        return [
            'acceptance_rate' => $decided > 0 ? round($accepted / $decided * 100, 1) : null,
            'rejection_rate' => $decided > 0 ? round($rejected / $decided * 100, 1) : null,
        ];
    }
}
