<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\Company;
use App\Models\JobVacancy;
use App\Models\User;
use Carbon\CarbonPeriod;

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
        return JobVacancy::query()
            ->where('company_id', $company->id)
            ->select(['id', 'title'])
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->orderBy('title')
            ->limit($limit)
            ->get()
            ->map(fn (JobVacancy $job) => [
                'title' => $job->title,
                'applications' => (int) $job->applications_count,
            ])
            ->all();
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