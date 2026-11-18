@props(['title' => null])

@php
    $user = auth()->user();
    $company = $user->company;

    $canUsers = \Illuminate\Support\Facades\Gate::allows('viewAny', \Shagalni\Shared\Models\User::class);
    $canCompanies = \Illuminate\Support\Facades\Gate::allows('viewAny', \Shagalni\Shared\Models\Company::class);
    $canChangeRequests = \Illuminate\Support\Facades\Gate::allows('viewAny', \Shagalni\Shared\Models\CompanyChangeRequest::class);
    $canJobs = \Illuminate\Support\Facades\Gate::allows('viewAny', \Shagalni\Shared\Models\JobVacancy::class);
    $canCategories = \Illuminate\Support\Facades\Gate::allows('viewAny', \Shagalni\Shared\Models\JobCategory::class);
    $canApplications = \Illuminate\Support\Facades\Gate::allows('viewAny', \Shagalni\Shared\Models\Application::class);
    $canMyCompany = $company !== null
        && \Illuminate\Support\Facades\Gate::allows('viewOwnProfile', $company);

    $showManagement = $canUsers || $canCompanies || $canChangeRequests;
    $showRecruitment = $canJobs || $canCategories || $canApplications;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
    @livewireStyles
</head>

<body class="app-body min-h-screen antialiased">

    {{-- Background decoration (same drifting glows as the Login page) --}}
    <div aria-hidden="true" class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="app-orb app-orb-1"></div>
        <div class="app-orb app-orb-2"></div>
        <div class="app-orb app-orb-3"></div>
    </div>

    {{-- ================= Sidebar ================= --}}
    <flux:sidebar sticky collapsible class="app-sidebar">

        {{-- Branding + collapse / close button --}}
        <flux:sidebar.header class="px-1">
            <x-app-logo :sidebar="true" href="{{ route('dashboard') }}" wire:navigate />

            <flux:sidebar.collapse
                class="in-data-flux-sidebar-on-desktop:not-in-data-flux-sidebar-collapsed-desktop:-me-2" />
        </flux:sidebar.header>

        {{-- Navigation --}}
        <flux:sidebar.nav class="app-nav py-3">

            {{-- Workspace --}}
            <div class="app-nav-group">
                <div class="app-nav-heading in-data-flux-sidebar-collapsed-desktop:hidden">
                    {{ __('Workspace') }}
                </div>

                <flux:sidebar.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')"
                    class="app-nav-item" wire:navigate>
                    {{ __('Dashboard') }}
                </flux:sidebar.item>
            </div>

            {{-- Management: Users (Admin + Company Owner), Companies / Change Requests (Admin only) --}}
            @if ($showManagement)
                <div class="app-nav-group">
                    <div class="app-nav-divider hidden in-data-flux-sidebar-collapsed-desktop:block"></div>
                    <div class="app-nav-heading in-data-flux-sidebar-collapsed-desktop:hidden">
                        {{ __('Management') }}
                    </div>

                    @if ($canUsers)
                        <flux:sidebar.item icon="users" :href="route('users.index')"
                            :current="request()->routeIs('users.*')" class="app-nav-item" wire:navigate>
                            {{ __('Users') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($canCompanies)
                        <flux:sidebar.item icon="building-office-2" :href="route('companies.index')"
                            :current="request()->routeIs('companies.*')" class="app-nav-item" wire:navigate>
                            {{ __('Companies') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($canChangeRequests)
                        <flux:sidebar.item icon="clipboard-document-check"
                            :href="route('company-change-requests.index')"
                            :current="request()->routeIs('company-change-requests.*')" class="app-nav-item"
                            wire:navigate>
                            {{ __('Company Change Requests') }}
                        </flux:sidebar.item>
                    @endif
                </div>
            @endif

            {{-- Recruitment: Jobs (both), Job Categories (Admin), Applications (Company Owner) --}}
            @if ($showRecruitment)
                <div class="app-nav-group">
                    <div class="app-nav-divider hidden in-data-flux-sidebar-collapsed-desktop:block"></div>
                    <div class="app-nav-heading in-data-flux-sidebar-collapsed-desktop:hidden">
                        {{ __('Recruitment') }}
                    </div>

                    @if ($canJobs)
                        <flux:sidebar.item icon="briefcase" :href="route('jobs.index')"
                            :current="request()->routeIs('jobs.*')" class="app-nav-item" wire:navigate>
                            {{ __('Jobs') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($canCategories)
                        <flux:sidebar.item icon="squares-2x2" :href="route('categories.index')"
                            :current="request()->routeIs('categories.*')" class="app-nav-item" wire:navigate>
                            {{ __('Job Categories') }}
                        </flux:sidebar.item>
                    @endif

                    @if ($canApplications)
                        <flux:sidebar.item icon="document-text" :href="route('applications.index')"
                            :current="request()->routeIs('applications.*')" class="app-nav-item" wire:navigate>
                            {{ __('Applications') }}
                        </flux:sidebar.item>
                    @endif
                </div>
            @endif

            {{-- Company (Company Owner) --}}
            @if ($canMyCompany)
                <div class="app-nav-group">
                    <div class="app-nav-divider hidden in-data-flux-sidebar-collapsed-desktop:block"></div>
                    <div class="app-nav-heading in-data-flux-sidebar-collapsed-desktop:hidden">
                        {{ __('Company') }}
                    </div>

                    <flux:sidebar.item icon="building-office" :href="route('my-company.show')"
                        :current="request()->routeIs('my-company.*')" class="app-nav-item" wire:navigate>
                        {{ __('My Company') }}
                    </flux:sidebar.item>
                </div>
            @endif

            {{-- Account (both roles) --}}
            <div class="app-nav-group">
                <div class="app-nav-divider hidden in-data-flux-sidebar-collapsed-desktop:block"></div>
                <div class="app-nav-heading in-data-flux-sidebar-collapsed-desktop:hidden">
                    {{ __('Account') }}
                </div>

                <flux:sidebar.item icon="user-circle" :href="route('profile.edit')"
                    :current="request()->routeIs('profile.edit')" class="app-nav-item" wire:navigate>
                    {{ __('Profile') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="shield-check" :href="route('security.edit')"
                    :current="request()->routeIs('security.edit')" class="app-nav-item" wire:navigate>
                    {{ __('Security') }}
                </flux:sidebar.item>

                <flux:sidebar.item icon="adjustments-horizontal" :href="route('appearance.edit')"
                    :current="request()->routeIs('appearance.edit')" class="app-nav-item" wire:navigate>
                    {{ __('Appearance') }}
                </flux:sidebar.item>
            </div>

        </flux:sidebar.nav>

        <flux:spacer />

        {{-- User menu (desktop + mobile drawer) --}}
        <div class="app-sidebar-footer">
            <x-desktop-user-menu />
        </div>

    </flux:sidebar>

    {{-- Page content (flux:main + topbar are provided by layouts/app.blade.php) --}}
    {{ $slot }}

    {{-- Toast Notifications --}}
    @persist('toast')
        <flux:toast.group>
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
    @livewireScripts
</body>

</html>