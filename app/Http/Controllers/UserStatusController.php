<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AccountLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class UserStatusController extends Controller
{
    public function __construct(private AccountLifecycleService $service) {}

    public function suspend(User $user): RedirectResponse
    {
        Gate::authorize('suspend', $user);

        $this->service->suspend($user);

        return $this->done($user, 'Account suspended. The user can no longer sign in.');
    }

    public function deactivate(User $user): RedirectResponse
    {
        Gate::authorize('deactivate', $user);

        $this->service->deactivate($user);

        return $this->done($user, 'Account deactivated.');
    }

    public function reactivate(User $user): RedirectResponse
    {
        Gate::authorize('reactivate', $user);

        $this->service->reactivate($user);

        return $this->done($user, 'Account reactivated.');
    }

    private function done(User $user, string $message): RedirectResponse
    {
        return redirect()
            ->route('users.show', $user)
            ->with('status', $message);
    }
}
