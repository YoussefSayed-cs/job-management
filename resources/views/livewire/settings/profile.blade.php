<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">
        {{ __('Profile settings') }}
    </flux:heading>

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your personal information')">
        <div class="flex flex-col gap-5">
            <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Profile picture</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Add a photo so your account is easy to recognize.</p>
                </div>

                <div class="flex flex-col gap-5 p-5 sm:flex-row sm:items-center">
                    @if ($profilePicture?->isPreviewable())
                        <img src="{{ $profilePicture->temporaryUrl() }}" alt="Profile picture preview"
                            class="size-20 shrink-0 rounded-full object-cover ring-4 ring-indigo-50 dark:ring-indigo-950">
                    @elseif (auth()->user()->profile_picture)
                        <img src="{{ auth()->user()->profile_picture_url }}" alt="Profile picture"
                            class="size-20 shrink-0 rounded-full object-cover ring-4 ring-indigo-50 dark:ring-indigo-950">
                    @else
                        <div class="flex size-20 shrink-0 items-center justify-center rounded-full bg-indigo-50 text-indigo-600 ring-4 ring-indigo-50 dark:bg-indigo-950 dark:text-indigo-300 dark:ring-indigo-950">
                            <flux:icon name="user" size="2xl" />
                        </div>
                    @endif

                    <div class="min-w-0 flex-1 space-y-3">
                        <flux:input type="file" wire:model="profilePicture" accept="image/png,image/jpeg,image/webp"
                            label="Choose a profile picture" />

                        <p class="text-xs text-slate-500 dark:text-slate-400">JPG, PNG or WebP. Maximum size: 2 MB.</p>

                        @error('profilePicture')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror

                        <div wire:loading wire:target="profilePicture" class="text-sm text-slate-500 dark:text-slate-400">
                            Uploading preview...
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <flux:button variant="primary" wire:click="updateProfilePicture" wire:loading.attr="disabled"
                                wire:target="updateProfilePicture" :disabled="!$profilePicture">
                                Save picture
                            </flux:button>

                            @if (auth()->user()->profile_picture)
                                <flux:button variant="ghost" wire:click="removeProfilePicture"
                                    wire:confirm="Are you sure you want to remove your profile picture?"
                                    wire:loading.attr="disabled" wire:target="removeProfilePicture">
                                    Remove picture
                                </flux:button>
                            @endif
                        </div>
                    </div>
                </div>
            </section>

            <form wire:submit="updateProfileInformation"
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                    <h3 class="text-base font-semibold text-slate-900 dark:text-slate-100">Personal information</h3>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Keep your contact details up to date.</p>
                </div>

                <div class="space-y-5 p-5">
                    <flux:input wire:model="full_name" :label="__('Full Name')" type="text" required autofocus
                        autocomplete="name" />

                    <flux:input wire:model="phone" :label="__('Phone Number')" type="tel" required autocomplete="tel" />

                    <div class="space-y-3">
                        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                        @if ($this->hasUnverifiedEmail)
                            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 dark:border-amber-900 dark:bg-amber-950/40">
                                <p class="text-sm font-medium text-amber-900 dark:text-amber-200">
                                    {{ __('Your email address is unverified.') }}
                                </p>
                                <flux:link class="mt-1 inline-block text-sm text-amber-800 dark:text-amber-300"
                                    wire:click.prevent="resendVerificationNotification">
                                    {{ __('Click here to re-send the verification email.') }}
                                </flux:link>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-5 py-4 dark:border-slate-800 dark:bg-slate-950/40">
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