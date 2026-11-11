<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>{{ __('Deactivate Account') }}</flux:heading>

        <flux:subheading>
            {{ __('Temporarily deactivate your account. You can reactivate it later with a link sent to your email.') }}
        </flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-account-deactivation">
        <flux:button variant="filled" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-account-deactivation')">
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
</section>