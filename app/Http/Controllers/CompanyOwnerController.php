<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreCompanyOwnerRequest;
use App\Models\User;
use App\Services\CompanyOwnerInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompanyOwnerController extends Controller
{
    public function __construct(private CompanyOwnerInvitationService $service) {}

    public function create(): View
    {
        Gate::authorize('create', User::class);

        return view('company-owners.create');
    }

    public function store(StoreCompanyOwnerRequest $request): RedirectResponse
    {
        $owner = $this->service->invite($request->user(), $request->validated());

        return $this->sendAndRedirect(
            $owner,
            'Company owner created and invitation sent. The link is valid for 48 hours.'
        );
    }

    public function resend(User $user): RedirectResponse
    {
        Gate::authorize('resendInvitation', $user);

        return $this->sendAndRedirect(
            $user,
            'A new invitation was sent. Any older link no longer works.'
        );
    }

    private function sendAndRedirect(User $owner, string $message): RedirectResponse
    {
        $redirect = redirect()->route('users.show', $owner);

        if ($this->service->sendInvitation($owner)) {
            return $redirect->with('status', $message);
        }

        // The account is already saved; only the email failed.
        return $redirect->withErrors([
            'account' => 'The invitation email could not be sent. Use "Resend invitation" to try again.',
        ]);
    }
}
