<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\AccountLifecycleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AccountDeletionController extends Controller
{
    public function __construct(private AccountLifecycleService $service) {}

    public function cancel(Request $request, User $user): RedirectResponse
    {
        try {
            $user = $this->service->cancelDeletion($user, (int) $request->query('requested'));
        } catch (ValidationException $e) {
            return redirect()
                ->route('login')
                ->with('status', $e->validator->errors()->first('account'));
        }

        return redirect()
            ->route('login')
            ->with('status', $this->cancelledMessage($user));
    }

    private function cancelledMessage(User $user): string
    {
        return match ($user->status) {
            AccountStatus::Active => 'Your account deletion request has been cancelled. You can sign in now.',
            AccountStatus::Deactivated => 'Your deletion request has been cancelled. Your account is still deactivated, you can request a reactivation link.',
            AccountStatus::Suspended => 'Your deletion request has been cancelled. Your account is still suspended and only an administrator can restore access.',
        };
    }
}
