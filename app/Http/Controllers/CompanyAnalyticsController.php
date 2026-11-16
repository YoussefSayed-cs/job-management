<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Shagalni\Shared\Models\Company;

class CompanyAnalyticsController extends Controller
{
    public function forCompany(Company $company, AnalyticsService $analytics): View
    {
        Gate::authorize('viewAnalytics', $company);

        return $this->show($company, $analytics);
    }

    protected function show(Company $company, AnalyticsService $analytics): View
    {
        return view('analytics.company', [
            'company' => $company,
            'overview' => $analytics->companyOverview($company),
            'distribution' => $analytics->statusDistribution($company),
            'jobBreakdown' => $analytics->applicationsPerJobPaginated($company),
        ]);
    }
}
