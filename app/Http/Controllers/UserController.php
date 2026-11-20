<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\IndexUsersRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Shagalni\Shared\Enums\UserRole;
use Shagalni\Shared\Models\User;

class UserController extends Controller
{
    public function index(IndexUsersRequest $request): View
    {
        $filters = $request->validated();
        $actor = $request->user();
        $isCompanyOwner = $actor->role === UserRole::CompanyOwner;

        $query = User::query()->whereNull('anonymized_at')->whereIn('role', [
            UserRole::CompanyOwner->value,
            UserRole::JobSeeker->value,
        ]);

        if ($isCompanyOwner) {
            $company = $actor->company;
            abort_unless($company !== null, 403);

            $query->where('role', UserRole::JobSeeker->value)
                ->whereHas('applications', fn ($applications) => $applications->where('company_id', $company->id))
                ->withCount([
                    'applications as company_applications_count' => fn ($applications) => $applications
                        ->where('company_id', $company->id),
                ]);
        }

        $users = $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->whereEscapedLike('full_name', $search)
                    ->whereEscapedLike('email', $search, 'or');
            });
        })->when($isCompanyOwner ? null : ($filters['role'] ?? null), function ($query, $role) {
            $query->where('role', $role);
        })->when($filters['status'] ?? null, function ($query, $status) {
            $query->where('status', $status);
        })->latest()->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'filters', 'isCompanyOwner'));
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        $user->load('company');

        return view('users.show', compact('user'));
    }
}
