@php
    $user = auth()->user();

    $userInitials = collect(preg_split('/\s+/', trim($user->full_name)))
        ->filter()
        ->take(2)
        ->map(fn($part) => mb_substr($part, 0, 1))
        ->implode('');

    $roleLabel = \Illuminate\Support\Str::headline($user->role->value);
@endphp

<flux:dropdown position="bottom" align="start" class="w-full">
    <flux:sidebar.profile :name="$user->full_name" :initials="$userInitials" :avatar="$user->profile_picture_url"
        icon:trailing="chevrons-up-down" data-test="sidebar-menu-button"
        class="app-user-trigger w-full rounded-xl px-2 py-2" />

    <flux:menu class="app-menu min-w-60">
        {{-- Identity --}}
        <div class="app-menu-identity flex items-center gap-3 rounded-lg px-2 py-2.5 text-start text-sm">
            <flux:avatar :name="$user->full_name" :initials="$userInitials" :src="$user->profile_picture_url" />

            <div class="grid min-w-0 flex-1 leading-tight">
                <flux:heading class="truncate">
                    {{ $user->full_name }}
                </flux:heading>

                <flux:text class="truncate text-xs">
                    {{ $user->email }}
                </flux:text>

                @if ($user->role === \Shagalni\Shared\Enums\UserRole::Admin)
                    <span
                        class="mt-1.5 inline-flex w-fit items-center rounded-full border border-teal-200/80 bg-teal-50 px-2 py-0.5 text-[0.6875rem] font-semibold text-teal-800 dark:border-teal-800/40 dark:bg-teal-950/40 dark:text-teal-300">
                        {{ $roleLabel }}
                    </span>
                @else
                    <span
                        class="mt-1.5 inline-flex w-fit items-center rounded-full border border-slate-200 bg-slate-50 px-2 py-0.5 text-[0.6875rem] font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        {{ $roleLabel }}
                    </span>
                @endif
            </div>
        </div>

        <flux:menu.separator />

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf

            <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                class="w-full cursor-pointer" data-test="logout-button">
                {{ __('Log out') }}
            </flux:menu.item>
        </form>
    </flux:menu>
</flux:dropdown>