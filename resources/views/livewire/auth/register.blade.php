<x-layouts::auth :title="__('Register')">
    <div class="flex flex-col gap-6">
        <div class="flex flex-col gap-2">
            <p class="text-xs font-semibold tracking-[0.16em] text-indigo-600 uppercase dark:text-indigo-400">Get started</p>
            <h2 class="text-3xl font-semibold tracking-tight text-slate-900 dark:text-white">Create your account</h2>
            <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">A few details and you’ll be ready to join Shagalni.</p>
        </div>

        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-4">
            @csrf

            <flux:input
                name="full_name"
                :label="__('Full name')"
                :value="old('full_name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Your full name"
            />

            <flux:input
                name="phone"
                :label="__('Phone number')"
                :value="old('phone')"
                type="tel"
                required
                autocomplete="tel"
                placeholder="+1 555 000 0000"
            />

            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <flux:input
                name="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">
                New accounts are created with Job Seeker access. Verify your email to continue.
            </p>

            <div class="flex items-center justify-end pt-1">
                <flux:button type="submit" variant="primary" class="w-full !rounded-lg !bg-indigo-600 hover:!bg-indigo-700" data-test="register-user-button">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="border-t border-slate-100 pt-5 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link class="font-semibold text-indigo-700 dark:text-indigo-400" :href="route('login')" wire:navigate>{{ __('Sign in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
