<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="space-y-1">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Deactivate Account') }}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ __('Temporarily deactivate your account. You can reactivate it later with a link sent to your email.') }}
        </p>
    </div>

    <flux:modal.trigger name="confirm-account-deactivation">
        <flux:button variant="outline" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-account-deactivation')">
            {{ __('Deactivate account') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-account-deactivation" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deactivate" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ __('Are you sure you want to deactivate your account?') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('You will be signed out and will not be able to sign in until you reactivate your account from the sign-in page.') }}
                </flux:subheading>
            </div>

            <flux:input wire:model="password" :label="__('Password')" type="password" viewable required />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">
                    {{ __('Confirm deactivation') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>