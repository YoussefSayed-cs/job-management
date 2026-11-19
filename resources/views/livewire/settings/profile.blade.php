<section class="w-full">
    <x-settings.layout :heading="__('Profile')" :subheading="__('Manage your personal information and profile photo')">
        @php
            $user = auth()->user();
        @endphp

        <div class="space-y-6">
            <x-settings.card x-data>
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                    <input
                        x-ref="profilePicture"
                        type="file"
                        wire:model="profilePicture"
                        accept="image/png,image/jpeg,image/webp"
                        class="hidden"
                    >

                    <div class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-100 text-sm font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        @if ($profilePicture?->isPreviewable())
                            <img src="{{ $profilePicture->temporaryUrl() }}" alt="Profile picture preview" class="size-full object-cover">
                        @elseif ($user->profile_picture_url)
                            <img src="{{ $user->profile_picture_url }}" alt="Profile picture" class="size-full object-cover">
                        @else
                            {{ collect(explode(' ', $user->full_name))->filter()->take(2)->map(fn ($part) => mb_substr($part, 0, 1))->implode('') }}
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div class="min-w-0">
                                <h2 class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $user->full_name }}</h2>
                                <p class="truncate text-sm text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                            </div>

                            <span @class([
                                'inline-flex w-fit shrink-0 items-center rounded-full border px-2.5 py-1 text-xs font-medium',
                                'border-slate-200 text-emerald-700 dark:border-slate-700 dark:text-emerald-300' => ! $this->hasUnverifiedEmail,
                                'border-slate-200 text-amber-700 dark:border-slate-700 dark:text-amber-300' => $this->hasUnverifiedEmail,
                            ])>
                                {{ $this->hasUnverifiedEmail ? __('Email not verified') : __('Email verified') }}
                            </span>
                        </div>

                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if ($profilePicture)
                                <flux:button variant="primary" wire:click="updateProfilePicture" wire:loading.attr="disabled" wire:target="updateProfilePicture">
                                    {{ __('Save photo') }}
                                </flux:button>
                                <flux:button variant="ghost" type="button" x-on:click="$wire.profilePicture = null; $refs.profilePicture.value = ''">
                                    {{ __('Cancel') }}
                                </flux:button>
                            @else
                                <flux:button variant="outline" type="button" x-on:click="$refs.profilePicture.click()">
                                    {{ __('Change photo') }}
                                </flux:button>

                                @if ($user->profile_picture)
                                    <flux:button
                                        variant="ghost"
                                        wire:click="removeProfilePicture"
                                        wire:confirm="Are you sure you want to remove your profile picture?"
                                        wire:loading.attr="disabled"
                                        wire:target="removeProfilePicture"
                                    >
                                        {{ __('Remove') }}
                                    </flux:button>
                                @endif
                            @endif
                        </div>

                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">{{ __('JPG, PNG or WebP, max 2 MB') }}</p>

                        @error('profilePicture')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <p wire:loading wire:target="profilePicture" class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                            {{ __('Preparing preview...') }}
                        </p>
                    </div>
                </div>
            </x-settings.card>

            <form wire:submit="updateProfileInformation">
                <x-settings.card :title="__('Personal information')" :description="__('Keep your contact details up to date.')">
                    <div class="grid gap-5 sm:grid-cols-2">
                        <flux:input wire:model="full_name" :label="__('Full Name')" type="text" required autofocus autocomplete="name" />
                        <flux:input wire:model="phone" :label="__('Phone Number')" type="tel" required autocomplete="tel" />

                        <div class="space-y-3 sm:col-span-2">
                            <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                            @if ($this->hasUnverifiedEmail)
                                <div class="flex flex-col gap-3 rounded-xl border border-amber-200 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-amber-900">
                                    <p class="text-sm font-medium text-amber-800 dark:text-amber-300">
                                        {{ __('Your email address is unverified.') }}
                                    </p>
                                    <flux:button variant="ghost" size="sm" class="!text-amber-800 dark:!text-amber-300" wire:click="resendVerificationNotification">
                                        {{ __('Resend verification') }}
                                    </flux:button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <x-slot:footer>
                        <flux:button variant="primary" type="submit">{{ __('Save changes') }}</flux:button>
                    </x-slot:footer>
                </x-settings.card>
            </form>
        </div>

        @if ($this->showDeleteUser)
            <x-settings.card :title="__('Danger zone')" title-class="text-red-600 dark:text-red-400">
                <livewire:settings.deactivate-account-form />
                <div class="my-5 border-t border-slate-200 dark:border-slate-800"></div>
                <livewire:settings.delete-user-form />
            </x-settings.card>
        @endif
    </x-settings.layout>
</section>