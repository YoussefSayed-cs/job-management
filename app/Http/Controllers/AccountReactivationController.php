<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\RequestReactivationLinkRequest;
use App\Services\AccountLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Shagalni\Shared\Models\User;
use Shagalni\Shared\Notifications\AccountReactivationRequested;

class AccountReactivationController extends Controller
{
    public function __construct(private AccountLifecycleService $service) {}

    public function create(): View
    {
        return view('account.request-reactivation');
    }

    public function store(RequestReactivationLinkRequest $request): RedirectResponse
    {
        $user = User::where('email', Str::lower($request->validated('email')))->first();

        if ($user && $this->service->canRequestReactivation($user)) {
            try {
                $token = $this->service->issueReactivationToken($user);
            } catch (ValidationException) {
                $token = null;
            }

            if ($token !== null) {
                $user->notify(new AccountReactivationRequested($token));
            }
        }

        // Same answer in every case, so nobody can use this form to find out which emails exist.
        return back()->with(
            'status',
            'If a deactivated account exists for this email, a reactivation link has been sent. It is valid for 24 hours.'
        );
    }

    public function confirm(Request $request, User $user): RedirectResponse
    {
        try {
            $this->service->reactivateWithToken($user, (string) $request->query('token'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('login')
                ->with('status', $e->validator->errors()->first('account'));
        }

        $user->refresh();

        $message = $user->deletion_requested_at !== null
            ? 'Your account has been reactivated, but it still has a pending deletion request. Cancel the deletion using the link in your email before signing in.'
            : 'Your account has been reactivated. You can sign in now.';

        return redirect()
            ->route('login')
            ->with('status', $message);
    }
}
