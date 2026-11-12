<x-layouts::auth :title="__('Set your password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Set your password')"
            :description="__('Choose a password for :email to activate your account.', ['email' => $user->email])" />

        {{-- The form posts back to the same signed link, so the signature and token stay valid. --}}
        <form method="POST" action="{{ request()->fullUrl() }}" class="flex flex-col gap-6">
            @csrf

            <flux:input name="password" :label="__('Password')" type="password" required
                autocomplete="new-password" :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable />

            <flux:input name="password_confirmation" :label="__('Confirm password')" type="password" required
                autocomplete="new-password" :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable />

            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Activate my account') }}
            </flux:button>
        </form>
    </div>
</x-layouts::auth>