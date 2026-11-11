@props(['title'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    @include('partials.head')
</head>

<body class="min-h-screen bg-slate-50 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100">
    <div class="min-h-screen lg:flex">
        <aside
            class="hidden w-64 shrink-0 border-r border-slate-200 bg-white lg:flex lg:flex-col dark:border-slate-800 dark:bg-slate-900">
            <a href="{{ route('dashboard') }}"
                class="flex h-20 items-center gap-3 border-b border-slate-100 px-6 dark:border-slate-800">
                <span
                    class="flex size-10 items-center justify-center rounded-xl bg-indigo-600 text-lg font-bold text-white">S</span>
                <span>
                    <span
                        class="block text-sm font-bold tracking-tight text-slate-900 dark:text-slate-100">Shagalni</span>
                    <span class="block text-xs text-slate-500 dark:text-slate-400">Job management</span>
                </span>
            </a>

            <nav aria-label="Main navigation" class="flex flex-1 flex-col gap-1 px-4 py-6">
                <p class="px-3 pb-2 text-xs font-semibold tracking-wider text-slate-400 uppercase dark:text-slate-500">
                    Workspace</p>
                <a href="{{ route('dashboard') }}" @class([
                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium',
                    'bg-indigo-50 text-indigo-700' => request()->routeIs('dashboard'),
                    'dark:bg-indigo-400/10 dark:text-indigo-300' => request()->routeIs('dashboard'),
                    'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' => !request()->routeIs('dashboard'),
                ])>
                    <span aria-hidden="true">⌂</span>
                    Dashboard

                    @can('viewAny', \App\Models\Company::class)
                        <a href="{{ route('companies.index') }}" @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium',
                            'bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300' => request()->routeIs('companies.*'),
                            'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' => !request()->routeIs('companies.*'),
                        ])>
                            <span aria-hidden="true">▣</span>
                            Companies
                        </a>
                    @endcan
                    @can('viewAny', \App\Models\User::class)
                        <a href="{{ route('users.index') }}" @class([
                            'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium',
                            'bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300' => request()->routeIs('users.*'),
                            'text-slate-600 hover:bg-slate-50 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white' => !request()->routeIs('users.*'),
                        ])>
                            <span aria-hidden="true">☺</span>
                            Users
                        </a>
                    @endcan




            </nav>

            <div class="border-t border-slate-100 p-4 dark:border-slate-800">
                <p class="truncate px-2 text-sm font-semibold text-slate-800 dark:text-slate-100">
                    {{ auth()->user()->full_name }}
                </p>
                <p class="truncate px-2 pt-1 text-xs text-slate-500 dark:text-slate-400">{{ auth()->user()->email }}</p>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header
                class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
                <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-2 lg:hidden">
                            <span
                                class="flex size-9 items-center justify-center rounded-lg bg-indigo-600 font-bold text-white">S</span>
                            <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Shagalni</span>
                        </a>
                        <div class="hidden min-w-0 lg:block">
                            <p class="truncate text-sm font-semibold text-slate-800 dark:text-slate-100">{{ $title }}
                            </p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Job management workspace</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <x-theme-toggle />
                        <details class="relative lg:hidden">
                            <summary
                                class="cursor-pointer list-none rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">
                                Menu</summary>
                            <nav aria-label="Mobile navigation"
                                class="absolute right-0 mt-2 flex w-52 flex-col gap-1 rounded-xl border border-slate-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-900">
                                <a href="{{ route('dashboard') }}"
                                    class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">Dashboard</a>
                            </nav>

                            @can('viewAny', \App\Models\Company::class)
                                <a href="{{ route('companies.index') }}"
                                    class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">Companies</a>
                            @endcan
                            @can('viewAny', \App\Models\User::class)
                                <a href="{{ route('users.index') }}"
                                    class="rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">Users</a>
                            @endcan
                        </details>

                        <a href="{{ route('profile.edit') }}"
                            class="hidden items-center gap-2 text-sm font-medium text-slate-600 hover:text-slate-900 sm:flex dark:text-slate-300 dark:hover:text-white">
                            @if (auth()->user()->profile_picture_url)
                                <img src="{{ auth()->user()->profile_picture_url }}" alt=""
                                    class="size-8 rounded-full object-cover">
                            @endif
                            {{ auth()->user()->full_name }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium text-slate-600 hover:border-slate-300 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800 dark:hover:text-white">
                                Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="w-full flex-1 px-4 py-8 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    @if (session('status'))
                        <div role="status"
                            class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200">
                            {{ session('status') }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</body>

</html>