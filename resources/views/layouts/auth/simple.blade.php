<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 antialiased dark:bg-slate-950">
        <main class="grid min-h-svh lg:grid-cols-[minmax(0,1fr)_minmax(28rem,0.9fr)]">
            <section class="relative hidden overflow-hidden bg-slate-950 px-12 py-10 text-white lg:flex lg:flex-col lg:justify-between xl:px-20">
                <div aria-hidden="true" class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-500/30 via-slate-950 to-slate-950"></div>
                <div aria-hidden="true" class="absolute -right-32 bottom-[-8rem] size-[34rem] rounded-full border border-white/10"></div>
                <div aria-hidden="true" class="absolute -right-16 bottom-[-4rem] size-[22rem] rounded-full border border-white/10"></div>

                <a href="{{ route('home') }}" class="relative flex w-fit items-center gap-3" wire:navigate>
                    <span class="flex size-11 items-center justify-center rounded-xl bg-indigo-500 text-xl font-bold text-white shadow-lg shadow-indigo-950/30">S</span>
                    <span>
                        <span class="block text-base font-semibold tracking-tight">Shagalni</span>
                        <span class="block text-xs text-slate-400">Job management</span>
                    </span>
                </a>

                <div class="relative max-w-xl">
                    <p class="mb-5 text-xs font-semibold tracking-[0.2em] text-indigo-300 uppercase">Built for better work</p>
                    <h1 class="text-4xl leading-tight font-semibold tracking-tight xl:text-5xl">
                        Good work starts with the right opportunity.
                    </h1>
                    <p class="mt-6 max-w-lg text-base leading-7 text-slate-300">
                        Sign in to access your Shagalni workspace.
                    </p>
                    <div class="mt-10 flex items-center gap-3 text-sm text-slate-300">
                        <span class="flex size-9 items-center justify-center rounded-full bg-white/10 text-indigo-200">✓</span>
                        A focused workspace for your next step
                    </div>
                </div>

                <p class="relative text-xs text-slate-500">© {{ date('Y') }} Shagalni. All rights reserved.</p>
            </section>

            <section class="flex min-h-svh flex-col bg-white dark:bg-slate-900">
                <div class="flex items-center justify-between px-5 py-5 sm:px-8 lg:justify-end lg:px-12">
                    <a href="{{ route('home') }}" class="flex items-center gap-2 lg:hidden" wire:navigate>
                        <span class="flex size-9 items-center justify-center rounded-lg bg-indigo-600 font-bold text-white">S</span>
                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Shagalni</span>
                    </a>
                    <div class="flex items-center gap-3">
                        <x-theme-toggle />
                        <a href="{{ route('home') }}" class="hidden text-sm font-medium text-slate-500 hover:text-slate-900 lg:inline dark:text-slate-400 dark:hover:text-white" wire:navigate>
                            Back to home
                        </a>
                    </div>
                </div>
                <div class="flex flex-1 items-center justify-center px-5 pb-12 sm:px-8 lg:px-12">
                    <div class="w-full max-w-md">
                        {{ $slot }}
                    </div>
                </div>
                <div class="px-5 pb-6 text-center text-xs text-slate-400 sm:px-8 lg:px-12 dark:text-slate-500">
                    Secure access to your Shagalni account
                </div>
            </section>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
