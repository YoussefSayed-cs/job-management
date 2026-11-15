<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Shagalni\Shared\Enums\UserRole;

class DashboardController extends Controller
{
    public function __invoke(Request $request, AnalyticsService $analytics): View
    {
        if ($request->user()->role === UserRole::Admin) {
            return $this->adminDashboard($analytics);
        }

        return view('account.home');
    }

    private function adminDashboard(AnalyticsService $analytics): View
    {
        $daily = $analytics->dailyApplications();
        $topCompanies = $analytics->topCompaniesByApplications();

        return view('analytics.admin', [
            'totals' => $analytics->platformTotals(),
            'activity' => $analytics->platformActivity(),
            'distribution' => $analytics->statusDistribution(),
            'dailyLabels' => array_map(
                fn (string $date) => Carbon::parse($date)->format('d M'),
                array_keys($daily),
            ),
            'dailyValues' => array_values($daily),
            'topCompanyNames' => array_column($topCompanies, 'name'),
            'topCompanyValues' => array_column($topCompanies, 'applications'),
        ]);
    }
}
