<?php

namespace App\Livewire\Settings;

use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Profile settings')]
class Profile extends Component
{
    use ProfileValidationRules, WithFileUploads;

    public string $full_name = '';

    public string $email = '';

    public string $phone = '';

    public $profilePicture = null;

    public function mount(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $this->full_name = $user->full_name;
        $this->email = $user->email;
        $this->phone = $user->phone;
    }

    public function updateProfileInformation(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $validated = $this->validate(
            $this->profileRules($user->id)
        );

        $user->fill($validated);

        $emailChanged = $user->isDirty('email');

        if ($emailChanged) {

            $user->email_verified_at = null;
        }

        $user->save();

        if ($emailChanged) {
            $user->sendEmailVerificationNotification();
        }

        Flux::toast(
            variant: 'success',
            text: __('Profile updated.')
        );
    }

    public function updateProfilePicture(): void
    {
        $this->validate([
            'profilePicture' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();

        $oldPicture = $user->profile_picture;

        $newPicture = $this->profilePicture->store(
            'profile-pictures',
            'public'
        );

        $user->fill([
            'profile_picture' => $newPicture,
        ]);
        $user->save();

        if ($oldPicture) {
            Storage::disk('public')->delete($oldPicture);
        }

        $this->reset('profilePicture');

        Flux::toast(
            variant: 'success',
            text: __('Profile picture updated.')
        );
    }

    public function removeProfilePicture(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $oldPicture = $user->profile_picture;

        if (!$oldPicture) {
            return;
        }

        $user->forceFill([
            'profile_picture' => null,
        ])->save();

        Storage::disk('public')->delete($oldPicture);

        Flux::toast(
            variant: 'success',
            text: __('Profile picture removed.')
        );
    }

    public function resendVerificationNotification(): void
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(
                default: route('dashboard', absolute: false)
            );

            return;
        }

        $user->sendEmailVerificationNotification();

        Flux::toast(
            text: __('A new verification link has been sent to your email address.')
        );
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user instanceof MustVerifyEmail
            && !$user->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        
        if ($user->role === UserRole::Admin) {
            return false;
        }

        return !$user instanceof MustVerifyEmail || $user->hasVerifiedEmail();
    }
}
