<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Shagalni\Shared\Models\Company;

class CompanyAnalyticsController extends Controller
{
    public function myCompany(Request $request, AnalyticsService $analytics): View
    {
        $company = $request->user()->company;

        abort_unless($company !== null, 403);
        Gate::authorize('viewAnalytics', $company);

        return $this->show($company, $analytics);
    }

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
