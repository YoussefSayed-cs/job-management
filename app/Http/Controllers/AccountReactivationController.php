<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\RequestReactivationLinkRequest;
use App\Models\User;
use App\Notifications\AccountReactivationRequested;
use App\Services\AccountLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class AccountReactivationController extends Controller
{
    public function __construct(private AccountLifecycleService $service)
    {
    }

    public function create(): View
    {
        return view('account.request-reactivation');
    }

    public function store(RequestReactivationLinkRequest $request): RedirectResponse
    {
        $user = User::where('email', $request->validated('email'))->first();

        if ($user && $this->service->canRequestReactivation($user)) {
            try {
                $token = $this->service->issueReactivationToken($user);

                $user->notify(new AccountReactivationRequested($token));
            } catch (Throwable $e) {
                report($e);
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

        return redirect()
            ->route('login')
            ->with('status', 'Your account has been reactivated. You can sign in now.');
    }
}