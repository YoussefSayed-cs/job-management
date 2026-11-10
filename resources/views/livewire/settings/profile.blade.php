<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        {{ __('Profile settings') }}
    </flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your personal information')">
        {{-- Profile Picture --}}
        <div class="my-6 space-y-4">
            <flux:heading size="lg">Profile Picture</flux:heading>

            <div class="flex items-center gap-5">
                @if ($profilePicture)
                    <img src="{{ $profilePicture->temporaryUrl() }}" alt="Profile picture preview"
                        class="size-24 rounded-full object-cover">
                @elseif (auth()->user()->profile_picture)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url(auth()->user()->profile_picture) }}"
                        alt="Profile picture" class="size-24 rounded-full object-cover">
                @else
                    <div class="flex size-24 items-center justify-center rounded-full bg-slate-200 text-slate-500">
                        <flux:icon name="user" size="2xl" />
                    </div>
                @endif

                <div class="space-y-2">
                    <flux:input type="file" wire:model="profilePicture" accept="image/png,image/jpeg,image/webp"
                        label="Choose a profile picture" />

                    <flux:text size="xs" class="text-zinc-500">
                        JPG, PNG or WebP. Maximum size: 2 MB.
                    </flux:text>

                    @error('profilePicture')
                        <flux:text color="danger" size="sm">
                            {{ $message }}
                        </flux:text>
                    @enderror
                </div>
            </div>

            <div wire:loading wire:target="profilePicture">
                <flux:text size="sm">Uploading preview...</flux:text>
            </div>

            <div class="flex items-center gap-3">
                <flux:button variant="primary" wire:click="updateProfilePicture" wire:loading.attr="disabled"
                    wire:target="updateProfilePicture" :disabled="!$profilePicture">
                    Save Picture
                </flux:button>

                @if (auth()->user()->profile_picture)
                    <flux:button variant="ghost" wire:click="removeProfilePicture"
                        wire:confirm="Are you sure you want to remove your profile picture?" wire:loading.attr="disabled"
                        wire:target="removeProfilePicture">
                        Remove Picture
                    </flux:button>
                @endif
            </div>
        </div>

        {{-- Profile Information --}}
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="full_name" :label="__('Full Name')" type="text" required autofocus
                autocomplete="name" />

            <flux:input wire:model="phone" :label="__('Phone Number')" type="tel" required autocomplete="tel" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">
                    {{ __('Save') }}
                </flux:button>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>