<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold tracking-[0.16em] text-indigo-600 uppercase dark:text-indigo-400">Welcome back</p>
            <h2 class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">Sign in to Shagalni</h2>
            <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">Enter your account details to continue to your workspace.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-5">
            @csrf

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot your password?') }}
                    </flux:link>
                @endif
            </div>

            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end pt-1">
                <flux:button variant="primary" type="submit" class="w-full !rounded-lg !bg-indigo-600 hover:!bg-indigo-700" data-test="login-button">
                    {{ __('Sign in') }}
                </flux:button>
            </div>
        </form>

    </div>
</x-layouts::auth>
