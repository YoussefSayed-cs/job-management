@props(['title' => null])

@php
    $user = auth()->user();

    $userInitials = collect(preg_split('/\s+/', trim($user->full_name)))
        ->filter()
        ->take(2)
        ->map(fn($part) => mb_substr($part, 0, 1))
        ->implode('');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-[#EEF2FF] text-slate-900 antialiased">
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="auth-drift absolute -right-24 -top-32 size-[28rem] rounded-full bg-indigo-300/25 blur-3xl"></div>
        <div class="auth-drift-rev absolute -bottom-40 left-1/3 size-[26rem] rounded-full bg-sky-300/20 blur-3xl"></div>
    </div>

    <flux:sidebar sticky collapsible="mobile"
        class="border-e border-indigo-100/80 bg-white/95 shadow-xl shadow-indigo-950/[0.04] backdrop-blur-xl">
        <flux:sidebar.header class="border-b border-indigo-50 px-5 py-5">
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />
            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        <flux:sidebar.nav class="px-3 py-5">
            <flux:sidebar.group :heading="__('Workspace')" class="grid">
                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </flux:sidebar.group>

            @can('viewAny', \Shagalni\Shared\Models\Company::class)
                <flux:sidebar.group :heading="__('Management')" class="grid">
                    <flux:sidebar.item icon="building-office-2" :href="route('companies.index')"
                        :current="request()->routeIs('companies.*')" wire:navigate>
                        {{ __('Companies') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item icon="users" :href="route('users.index')"
                        :current="request()->routeIs('users.*')" wire:navigate>
                        {{ __('Users') }}
                    </flux:sidebar.item>
                </flux:sidebar.group>
            @endcan

            <flux:sidebar.group :heading="__('Account')" class="grid">
                <flux:sidebar.item icon="user-circle" :href="route('profile.edit')"
                    :current="request()->routeIs('profile.edit')" wire:navigate>
                    {{ __('Profile') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="shield-check" :href="route('security.edit')"
                    :current="request()->routeIs('security.edit')" wire:navigate>
                    {{ __('Security') }}
                </flux:sidebar.item>
                <flux:sidebar.item icon="adjustments-horizontal" :href="route('appearance.edit')"
                    :current="request()->routeIs('appearance.edit')" wire:navigate>
                    {{ __('Appearance') }}
                </flux:sidebar.item>
            </flux:sidebar.group>
        </flux:sidebar.nav>

        <flux:spacer />

        <x-desktop-user-menu class="hidden lg:block" />
    </flux:sidebar>

    <div class="relative min-h-screen">
        <flux:header class="sticky top-0 z-20 border-b border-indigo-100/80 bg-white/80 px-4 py-3 backdrop-blur-xl sm:px-6 lg:px-10">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

            <x-app-logo class="sm:hidden" href="{{ route('dashboard') }}" wire:navigate />

            <div class="hidden min-w-0 sm:block">
                <p class="truncate text-sm font-semibold text-slate-900">{{ $title ?? __('Workspace') }}</p>
                <p class="mt-0.5 text-xs text-slate-500">{{ __('Shagalni management workspace') }}</p>
            </div>

            <flux:spacer />

            <flux:dropdown position="bottom" align="end" class="lg:hidden">
                <flux:profile :initials="$userInitials" :avatar="$user->profile_picture_url" icon-trailing="chevron-down" />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <flux:avatar :name="$user->full_name" :initials="$userInitials"
                                    :src="$user->profile_picture_url" />

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <flux:heading class="truncate">
                                        {{ $user->full_name }}
                                    </flux:heading>

                                    <flux:text class="truncate">
                                        {{ $user->email }}
                                    </flux:text>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>
                            {{ __('Settings') }}
                        </flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf

                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer" data-test="logout-button">
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}
    </div>

    @persist('toast')
    <flux:toast.group>
        <flux:toast />
    </flux:toast.group>
    @endpersist

    @fluxScripts
</body>

</html>