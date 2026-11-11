<x-layouts::auth :title="__('Reactivate account')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Reactivate your account')" :description="__('Enter your email and we will send you a reactivation link')" />

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('account-reactivation.store') }}" class="flex flex-col gap-6">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="reactivation-link-button">
                {{ __('Email reactivation link') }}
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>{{ __('Or, return to') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>