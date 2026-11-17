@php
    $user = auth()->user();

    $userInitials = collect(preg_split('/\s+/', trim($user->full_name)))
        ->filter()
        ->take(2)
        ->map(fn($part) => mb_substr($part, 0, 1))
        ->implode('');
@endphp

<flux:dropdown position="bottom" align="start" class="w-full">
    <flux:sidebar.profile :name="$user->full_name" :initials="$userInitials" :avatar="$user->profile_picture_url"
        icon:trailing="chevrons-up-down" data-test="sidebar-menu-button"
        class="w-full rounded-xl px-2 py-2 transition-colors hover:!bg-indigo-50/80" />
    <flux:menu class="!border-indigo-100 shadow-lg shadow-indigo-950/10">
        <div class="flex items-center gap-2 rounded-lg bg-indigo-50/70 px-2 py-2 text-start text-sm">
            <flux:avatar :name="$user->full_name" :initials="$userInitials" :src="$user->profile_picture_url" />

            <div class="grid flex-1 text-start text-sm leading-tight">
                <flux:heading class="truncate">
                    {{ $user->full_name }}
                </flux:heading>

                <flux:text class="truncate">
                    {{ $user->email }}
                </flux:text>
            </div>
        </div>

        <flux:menu.separator />

        <flux:menu.radio.group>
            <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                {{ __('Settings') }}
            </flux:menu.item>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf

                <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer" data-test="logout-button">
                    {{ __('Log out') }}
                </flux:menu.item>
            </form>
        </flux:menu.radio.group>
    </flux:menu>
</flux:dropdown>