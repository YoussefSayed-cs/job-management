@props(['title' => null])

@php
    $user = auth()->user();

    $userInitials = collect(preg_split('/\s+/', trim($user->full_name)))
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_substr($part, 0, 1))
        ->implode('');

    $pageTitle = $title ?: __('Workspace');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-[#EEF2FF] text-slate-900 antialiased">

    {{-- Background decoration --}}
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 overflow-hidden">
        <div class="absolute -right-24 -top-32 size-[28rem] rounded-full bg-indigo-300/25 blur-3xl"></div>
        <div class="absolute -bottom-40 left-1/3 size-[26rem] rounded-full bg-violet-300/20 blur-3xl"></div>
        <div class="absolute left-0 top-1/3 size-[20rem] rounded-full bg-sky-300/10 blur-3xl"></div>
    </div>

    {{-- Desktop / Mobile Sidebar --}}
    <flux:sidebar
        sticky
        collapsible="mobile"
        class="border-e border-indigo-100/80 bg-white/95 shadow-xl shadow-indigo-950/[0.04] backdrop-blur-xl"
    >
        {{-- Sidebar Branding --}}
        <flux:sidebar.header class="border-b border-indigo-50 px-5 py-5">
            <x-app-logo
                :sidebar="true"
                href="{{ route('dashboard') }}"
                wire:navigate
            />

            <flux:sidebar.collapse class="lg:hidden" />
        </flux:sidebar.header>

        {{-- Navigation --}}
        <flux:sidebar.nav class="px-3 py-5">

            {{-- Workspace --}}
            <flux:sidebar.group :heading="__('Workspace')" class="grid">

                <flux:sidebar.item
                    icon="home"
                    :href="route('dashboard')"
                    :current="request()->routeIs('dashboard')"
                    wire:navigate
                >
                    {{ __('Dashboard') }}
                </flux:sidebar.item>

            </flux:sidebar.group>

            {{-- Existing Management Navigation --}}
            @can('viewAny', \Shagalni\Shared\Models\Company::class)
                <flux:sidebar.group :heading="__('Management')" class="grid">

                    <flux:sidebar.item
                        icon="building-office-2"
                        :href="route('companies.index')"
                        :current="request()->routeIs('companies.*')"
                        wire:navigate
                    >
                        {{ __('Companies') }}
                    </flux:sidebar.item>

                    <flux:sidebar.item
                        icon="users"
                        :href="route('users.index')"
                        :current="request()->routeIs('users.*')"
                        wire:navigate
                    >
                        {{ __('Users') }}
                    </flux:sidebar.item>

                </flux:sidebar.group>
            @endcan

            {{-- Account Navigation --}}
            <flux:sidebar.group :heading="__('Account')" class="grid">

                <flux:sidebar.item
                    icon="user-circle"
                    :href="route('profile.edit')"
                    :current="request()->routeIs('profile.edit')"
                    wire:navigate
                >
                    {{ __('Profile') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="shield-check"
                    :href="route('security.edit')"
                    :current="request()->routeIs('security.edit')"
                    wire:navigate
                >
                    {{ __('Security') }}
                </flux:sidebar.item>

                <flux:sidebar.item
                    icon="adjustments-horizontal"
                    :href="route('appearance.edit')"
                    :current="request()->routeIs('appearance.edit')"
                    wire:navigate
                >
                    {{ __('Appearance') }}
                </flux:sidebar.item>

            </flux:sidebar.group>

        </flux:sidebar.nav>

        <flux:spacer />

        {{-- Desktop User Menu --}}
        <div class="hidden border-t border-indigo-50 p-3 lg:block">
            <x-desktop-user-menu />
        </div>

    </flux:sidebar>

    {{-- Main Application Area --}}
    <div class="relative min-h-screen">

        {{-- Topbar --}}
        <flux:header class="sticky top-0 z-20 border-b border-indigo-100/80 bg-white/80 px-4 py-3 backdrop-blur-xl sm:px-6 lg:px-10">

            {{-- Mobile Sidebar Toggle --}}
            <flux:sidebar.toggle
                class="lg:hidden"
                icon="bars-2"
            />

            {{-- Mobile Logo --}}
            <x-app-logo
                class="sm:hidden"
                href="{{ route('dashboard') }}"
                wire:navigate
            />

            {{-- Page Heading --}}
            <div class="hidden min-w-0 sm:block">
                <flux:heading size="lg" class="truncate font-semibold text-slate-900">
                    {{ $pageTitle }}
                </flux:heading>

                <flux:text class="mt-0.5 text-xs text-slate-500">
                    {{ __('Shagalni management workspace') }}
                </flux:text>
            </div>

            <flux:spacer />

            {{-- Mobile User Menu --}}
            <flux:dropdown
                position="bottom"
                align="end"
                class="lg:hidden"
            >
                <flux:profile
                    :initials="$userInitials"
                    :avatar="$user->profile_picture_url"
                    icon-trailing="chevron-down"
                />

                <flux:menu>

                    {{-- User Information --}}
                    <div class="p-2">
                        <div class="flex items-center gap-2 text-start text-sm">

                            <flux:avatar
                                :name="$user->full_name"
                                :initials="$userInitials"
                                :src="$user->profile_picture_url"
                            />

                            <div class="grid min-w-0 flex-1 text-start text-sm leading-tight">
                                <flux:heading class="truncate">
                                    {{ $user->full_name }}
                                </flux:heading>

                                <flux:text class="truncate text-xs">
                                    {{ $user->email }}
                                </flux:text>
                            </div>

                        </div>
                    </div>

                    <flux:menu.separator />

                    {{-- Account Settings --}}
                    <flux:menu.item
                        :href="route('profile.edit')"
                        icon="cog"
                        wire:navigate
                    >
                        {{ __('Settings') }}
                    </flux:menu.item>

                    <flux:menu.separator />

                    {{-- Logout --}}
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="w-full"
                    >
                        @csrf

                        <flux:menu.item
                            as="button"
                            type="submit"
                            icon="arrow-right-start-on-rectangle"
                            class="w-full cursor-pointer"
                            data-test="logout-button"
                        >
                            {{ __('Log out') }}
                        </flux:menu.item>
                    </form>

                </flux:menu>
            </flux:dropdown>

        </flux:header>

        {{-- Page Content --}}
        {{ $slot }}

    </div>

    {{-- Toast Notifications --}}
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    {{-- Flux Scripts --}}
    @fluxScripts

</body>
</html>