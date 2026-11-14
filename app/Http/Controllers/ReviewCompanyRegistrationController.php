<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\RejectCompanyRegistrationRequest;
use App\Models\Company;
use App\Services\CompanyApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReviewCompanyRegistrationController extends Controller
{
    public function __construct(private CompanyApprovalService $service) {}

    public function approve(Request $request, Company $company): RedirectResponse
    {
        Gate::authorize('review', $company);

        $this->service->approve($company, $request->user());

        return redirect()
            ->route('companies.show', $company)
            ->with('status', 'Company registration approved. The owner has been notified.');
    }

    public function reject(RejectCompanyRegistrationRequest $request, Company $company): RedirectResponse
    {
        $this->service->reject($company, $request->user(), $request->validated('rejection_reason'));

        return redirect()
            ->route('companies.show', $company)
            ->with('status', 'Company registration rejected. The owner has been notified.');
    }
}
