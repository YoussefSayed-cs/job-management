<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div class="space-y-1">
        <h3 class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ __('Request Account Deletion') }}</h3>
        <p class="text-sm text-slate-500 dark:text-slate-400">
            {{ __('Request the permanent deletion of your account.') }}
        </p>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">
            {{ __('Delete account') }}
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ __('Are you sure you want to request account deletion?') }}
                </flux:heading>

                <flux:subheading>
                    {{ __('Your deletion request will be recorded. Your account data will be retained during the 30-day grace period before permanent deletion.') }}
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
                    {{ __('Confirm deletion request') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>