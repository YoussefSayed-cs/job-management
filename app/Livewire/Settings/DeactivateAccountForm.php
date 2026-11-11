<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Livewire\Actions\Logout;
use App\Services\AccountLifecycleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DeactivateAccountForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Deactivate the signed-in account (FR-25).
     */
    public function deactivate(Logout $logout, AccountLifecycleService $service): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $user = Auth::user();

        if ($user->role === UserRole::Admin) {
            $this->addError('password', 'The administrator account cannot be deactivated.');

            return;
        }

        try {
            $service->deactivateOwnAccount($user);
        } catch (ValidationException $e) {
            $this->addError('password', $e->validator->errors()->first('account'));

            return;
        }

        $this->reset('password');

        $logout();

        session()->flash('status', 'Your account has been deactivated. You can reactivate it from the sign-in page.');

        $this->redirect(route('login'), navigate: true);
    }
}