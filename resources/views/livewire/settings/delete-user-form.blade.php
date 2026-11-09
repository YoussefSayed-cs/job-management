<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Request Account Deletion') }}</flux:heading>

        <flux:subheading>
            {{ __('Request the permanent deletion of your account.') }}
        </flux:subheading>
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