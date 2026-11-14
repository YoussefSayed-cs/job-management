<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\StoreCompanyChangeRequest;
use App\Services\Companies\CompanyChangeRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyChangeRequestController extends Controller
{
    public function index(Request $request): View
    {
        $company = $request->user()->company;

        abort_unless($company, 404);

        Gate::authorize('viewChangeRequests', $company);

        $changeRequests = $company->changeRequests()
            ->with('reviewer')
            ->latest()
            ->paginate(10);

        return view('companies.change-requests.index', compact('company', 'changeRequests'));
    }

    public function store(
        StoreCompanyChangeRequest $request,
        CompanyChangeRequestService $service
    ): RedirectResponse {
        $company = $request->user()->company;

        abort_unless($company, 404);

        $service->submit($company, $request->validated());

        return redirect()
            ->route('my-company.change-requests.index')
            ->with('success', 'Your company change request has been submitted for Admin review.');
    }
}
