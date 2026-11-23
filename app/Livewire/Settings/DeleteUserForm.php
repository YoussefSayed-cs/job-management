<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Livewire\Actions\Logout;
use App\Services\AccountLifecycleService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Shagalni\Shared\Notifications\AccountDeletionRequested;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Submit an account deletion request.
     */
    public function deleteUser(Logout $logout, AccountLifecycleService $service): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $user = Auth::user();

        try {
            $user = $service->requestDeletion($user);
        } catch (ValidationException $exception) {
            $this->addError('password', $exception->validator->errors()->first('account'));

            return;
        }

        $user->notify(new AccountDeletionRequested);

        $this->reset('password');

        $logout();

        $this->redirect('/', navigate: true);
    }
}
