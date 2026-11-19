@props(['title' => null])

@php
    $user = auth()->user();
    $company = $user->company;
    $isAdmin = $user->role === \Shagalni\Shared\Enums\UserRole::Admin;

    $pageTitle = $title ?: match (true) {
        request()->routeIs('profile.edit') => __('Profile'),
        request()->routeIs('security.edit') => __('Security'),
        request()->routeIs('appearance.edit') => __('Appearance'),
        default => __('Workspace'),
    };
@endphp

<header class="app-topbar">
    <div class="mx-auto flex h-16 w-full max-w-screen-2xl items-center gap-3 px-4 sm:px-6 lg:px-8">

        {{-- Mobile drawer toggle --}}
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

        {{-- Mobile: current page title --}}
        <span class="min-w-0 truncate text-base font-semibold text-slate-900 sm:hidden dark:text-white">
            {{ $pageTitle }}
        </span>

        {{-- Desktop: breadcrumb --}}
        <nav aria-label="{{ __('Breadcrumb') }}" class="hidden min-w-0 items-center gap-2 text-sm sm:flex">
            <a href="{{ route('dashboard') }}" wire:navigate
                class="font-medium text-slate-500 transition-colors hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-400">
                Shagalni
            </a>

            <flux:icon.chevron-right class="size-3.5 shrink-0 text-slate-400 dark:text-slate-600" />

            <span aria-current="page" class="truncate font-semibold text-slate-900 dark:text-white">
                {{ $pageTitle }}
            </span>
        </nav>

        <div class="ms-auto flex items-center gap-2">
            @if ($isAdmin)
                <span
                    class="hidden items-center gap-1.5 rounded-full border border-indigo-200 bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700 sm:inline-flex dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-300">
                    <flux:icon.shield-check class="size-3.5" />
                    {{ __('Administrator') }}
                </span>
            @elseif ($company)
                <span
                    class="hidden max-w-56 items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-700 sm:inline-flex dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                    <flux:icon.building-office class="size-3.5 shrink-0" />
                    <span class="truncate">{{ $company->name }}</span>
                </span>
            @endif
        </div>

    </div>
</header>