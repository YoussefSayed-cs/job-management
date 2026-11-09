<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
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

        if ($user->deletion_requested_at !== null) {
            $this->addError('password', 'An account deletion request already exists.');

            return;
        }

        $user->forceFill([
            'deletion_requested_at' => now(),
        ])->save();

        $this->reset('password');

        $logout();

        $this->redirect('/', navigate: true);
    }
}