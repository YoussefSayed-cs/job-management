<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#EEF2FF] antialiased">
        <!-- Main container with overlapping panels -->
        <main class="flex min-h-screen">
            <!-- Branding Panel (visible on lg+ screens) -->
            <section class="hidden lg:flex w-1/2 bg-gradient-to-b from-[#17243B] to-[#4F46E5] text-white p-12 relative rounded-r-3xl shadow-xl overflow-hidden">
                <!-- Decorative geometry (simple circles) -->
                <div class="absolute -right-24 -bottom-24 size-[34rem] rounded-full border border-white/10"></div>
                <div class="absolute -right-12 -bottom-12 size-[22rem] rounded-full border border-white/10"></div>

                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 mb-8">
                    <span class="flex size-11 items-center justify-center rounded-xl bg-indigo-500 text-xl font-bold text-white shadow-lg shadow-indigo-950/30">S</span>
                    <div class="flex flex-col">
                        <span class="block text-base font-semibold tracking-tight">Shagalni</span>
                        <span class="block text-xs text-indigo-200">Management</span>
                    </div>
                </a>

                <!-- Branding copy -->
                <div class="mt-8 max-w-md">
                    <p class="text-sm font-medium uppercase tracking-wider text-indigo-300 mb-4">Recruitment Management</p>
                    <h1 class="text-3xl font-bold leading-tight mb-4">Your hiring operations, all in one place.</h1>
                    <p class="text-base text-indigo-100 mb-6">Manage companies, job vacancies, and applications through one focused workspace.</p>
                    <ul class="space-y-3">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Company Management
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Job Management
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Application Tracking
                        </li>
                    </ul>
                </div>
                <p class="absolute bottom-4 left-8 text-xs text-indigo-200">© {{ date('Y') }} Shagalni. All rights reserved.</p>
            </section>

            <!-- Login Card -->
            <section class="flex flex-1 items-center justify-center px-4 py-12 lg:px-12 lg:py-0">
                <div class="w-full max-w-md lg:max-w-lg bg-white rounded-2xl shadow-xl p-8 lg:-ml-12 relative z-10">
                    {{ $slot }}
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