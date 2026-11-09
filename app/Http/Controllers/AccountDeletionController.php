<?php

namespace App\Http\Controllers;

use App\Enums\AccountStatus;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AccountDeletionController extends Controller
{
    public function cancel(User $user): RedirectResponse
    {
        $requestedAt = $user->deletion_requested_at;

        abort_if($requestedAt === null, 404);

        abort_if(
            now()->greaterThan($requestedAt->copy()->addDays(30)),
            410,
            'The account recovery period has expired.'
        );

        abort_if(
            $user->status === AccountStatus::Suspended,
            403,
            'Suspended accounts cannot cancel deletion requests.'
        );

        $user->forceFill([
            'deletion_requested_at' => null,
        ])->save();

        return redirect()
            ->route('login')
            ->with('status', 'Your account deletion request has been cancelled successfully.');
    }
}
