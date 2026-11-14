<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\AcceptCompanyOwnerInvitationRequest;
use App\Models\User;
use App\Services\CompanyOwnerInvitationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CompanyOwnerInvitationController extends Controller
{
    public function __construct(private CompanyOwnerInvitationService $service) {}

    public function show(Request $request, User $user): View|RedirectResponse
    {
        $validToken = $this->service->hasValidToken($user, (string) $request->query('token'));

        if (! $validToken || ! $this->service->isInvitationPending($user)) {
            return redirect()
                ->route('login')
                ->with('status', 'This invitation link is invalid or has expired. Ask an administrator to send you a new one.');
        }

        return view('account.accept-invitation', compact('user'));
    }

    public function store(AcceptCompanyOwnerInvitationRequest $request, User $user): RedirectResponse
    {
        try {
            $this->service->accept(
                $user,
                (string) $request->query('token'),
                $request->validated('password')
            );
        } catch (ValidationException $e) {
            return redirect()
                ->route('login')
                ->with('status', $e->validator->errors()->first('account'));
        }

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been set. You can sign in now.');
    }
}
