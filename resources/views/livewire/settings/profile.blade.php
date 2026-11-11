<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        {{ __('Profile settings') }}
    </flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Manage your personal information and profile photo')">
        @php
            $user = auth()->user();
        @endphp

        <div class="space-y-6">
            <section class="relative isolate overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-violet-800 p-6 text-white shadow-lg shadow-indigo-950/10 sm:p-8">
                <div aria-hidden="true" class="absolute -end-12 -top-20 -z-10 size-64 rounded-full bg-white/10 blur-3xl"></div>
                <div aria-hidden="true" class="absolute -bottom-28 start-1/3 -z-10 size-72 rounded-full bg-violet-300/20 blur-3xl"></div>

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center">
                    <div class="flex size-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/20 bg-white/10 shadow-inner">
                        @if ($profilePicture?->isPreviewable())
                            <img src="{{ $profilePicture->temporaryUrl() }}" alt="Profile picture preview" class="size-full object-cover">
                        @elseif ($user->profile_picture)
                            <img src="{{ $user->profile_picture_url }}" alt="Profile picture" class="size-full object-cover">
                        @else
                            <flux:icon name="user" class="size-10 text-white/90" />
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-indigo-100">{{ __('Your account') }}</p>
                        <h2 class="mt-1 truncate text-2xl font-semibold tracking-tight sm:text-3xl">{{ $user->full_name }}</h2>
                        <p class="mt-1 truncate text-sm text-indigo-100">{{ $user->email }}</p>
                        <div class="mt-4 flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1 text-xs font-medium">
                                <span class="size-1.5 rounded-full {{ $this->hasUnverifiedEmail ? 'bg-amber-300' : 'bg-emerald-300' }}"></span>
                                {{ $this->hasUnverifiedEmail ? __('Email not verified') : __('Email verified') }}
                            </span>
                        </div>
                    </div>

                    <div class="w-full rounded-2xl border border-white/15 bg-white/10 p-4 backdrop-blur-sm sm:max-w-xs">
                        <flux:input type="file" wire:model="profilePicture" accept="image/png,image/jpeg,image/webp"
                            label="Update profile photo" />
                        <p class="mt-2 text-xs text-indigo-100">{{ __('JPG, PNG or WebP. Maximum size: 2 MB.') }}</p>

                        @error('profilePicture')
                            <p class="mt-2 text-sm text-rose-100">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="profilePicture" class="mt-2 text-sm text-indigo-100">
                            {{ __('Preparing preview...') }}
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <flux:button variant="primary" class="!bg-white !text-indigo-700 hover:!bg-indigo-50"
                                wire:click="updateProfilePicture" wire:loading.attr="disabled"
                                wire:target="updateProfilePicture" :disabled="!$profilePicture">
                                {{ __('Save photo') }}
                            </flux:button>

                            @if ($user->profile_picture)
                                <flux:button variant="ghost" class="!text-white hover:!bg-white/10"
                                    wire:click="removeProfilePicture"
                                    wire:confirm="Are you sure you want to remove your profile picture?"
                                    wire:loading.attr="disabled" wire:target="removeProfilePicture">
                                    {{ __('Remove') }}
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <form wire:submit="updateProfileInformation"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-start gap-4 border-b border-slate-100 px-5 py-5 sm:px-7 dark:border-slate-800">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-300">
                        <flux:icon name="identification" class="size-5" />
                    </span>
                    <div>
                        <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">{{ __('Personal information') }}</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Keep your contact details up to date.') }}</p>
                    </div>
                </div>

                <div class="grid gap-5 p-5 sm:grid-cols-2 sm:p-7">
                    <flux:input wire:model="full_name" :label="__('Full Name')" type="text" required autofocus
                        autocomplete="name" />

                    <flux:input wire:model="phone" :label="__('Phone Number')" type="tel" required autocomplete="tel" />

                    <div class="space-y-3 sm:col-span-2">
                        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                        @if ($this->hasUnverifiedEmail)
                            <div class="flex flex-col gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 sm:flex-row sm:items-center sm:justify-between dark:border-amber-900 dark:bg-amber-950/40">
                                <p class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                    {{ __('Your email address is unverified.') }}
                                </p>
                                <flux:button variant="ghost" size="sm" class="!text-amber-800 dark:!text-amber-300"
                                    wire:click="resendVerificationNotification">
                                    {{ __('Resend verification') }}
                                </flux:button>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-3 border-t border-slate-100 bg-slate-50/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-7 dark:border-slate-800 dark:bg-slate-950/40">
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('Your information is only visible to your account.') }}</p>
                    <flux:button variant="primary" type="submit">
                        {{ __('Save changes') }}
                    </flux:button>
                </div>
            </form>
        </div>

        @if ($this->showDeleteUser)
            <livewire:settings.delete-user-form />
        @endif
    </x-settings.layout>
</section>