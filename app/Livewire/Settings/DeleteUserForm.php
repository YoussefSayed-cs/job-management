<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Enums\UserRole;
use App\Livewire\Actions\Logout;
use App\Notifications\AccountDeletionRequested;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Submit an account deletion request.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $user = Auth::user();

        if ($user->role === UserRole::Admin) {
            $this->addError('password', 'The administrator account cannot be deleted.');

            return;
        }

        if ($user->deletion_requested_at !== null) {
            $this->addError('password', 'An account deletion request already exists.');

            return;
        }

        $user->forceFill([
            'deletion_requested_at' => now(),
        ])->save();

        $user->notify(new AccountDeletionRequested);

        $this->reset('password');

        $logout();

        $this->redirect('/', navigate: true);
    }
}
