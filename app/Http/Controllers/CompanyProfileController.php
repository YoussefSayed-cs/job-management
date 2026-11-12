<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\UpdateCompanyProfileRequest;
use App\Models\Company;
use App\Services\Companies\UpdateCompanyProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function show(Request $request): View
    {
        $company = $request->user()->company;

        abort_unless($company, 404);

        Gate::authorize('viewOwnProfile', $company);

        return view('companies.profile', compact('company'));
    }

    public function update(
        UpdateCompanyProfileRequest $request,
        UpdateCompanyProfileService $service
    ): RedirectResponse {
        $company = $request->user()->company;

        abort_unless($company, 404);

        $company = $service->update(
            $company,
            $request->validated()
        );

        return redirect() ->route('my-company.show')->with('success', 'Company profile updated successfully.');
    }
}
