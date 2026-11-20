<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\IndexCompaniesRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Shagalni\Shared\Models\Company;

class CompanyController extends Controller
{
    public function index(IndexCompaniesRequest $request): View
    {
        $filters = $request->validated();

        $companies = Company::query()->with('owner')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->whereEscapedLike('name', $search)
                        ->orWhereHas('owner', function ($query) use ($search) {
                            $query->whereEscapedLike('full_name', $search)
                                ->whereEscapedLike('email', $search, 'or');
                        });
                });
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('registration_status', $status);
            })->latest()->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('companies.index', compact('companies', 'filters'));
    }

    public function show(Company $company): View
    {
        Gate::authorize('view', $company);

        $company->load(['owner', 'reviewer']);

        $jobs = $company->jobVacancies()->latest()->limit(10)->get();

        return view('companies.show', compact('company', 'jobs'));
    }
}
