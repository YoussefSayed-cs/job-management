<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Http\Requests\User\IndexUsersRequest;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(IndexUsersRequest $request): View
    {
        $filters = $request->validated();

        $users = User::query()->whereNull('anonymized_at')->whereIn('role', [
            UserRole::CompanyOwner->value,
            UserRole::JobSeeker->value,
        ])->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        })
            ->when($filters['role'] ?? null, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })->latest()
            ->paginate(10)
            ->withQueryString();

        return view('users.index', compact('users', 'filters'));
    }

    public function show(User $user): View
    {
        Gate::authorize('view', $user);

        $user->load('company');

        return view('users.show', compact('user'));
    }
}
