<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#EEF2FF] antialiased">

        {{-- page background drifting glows --}}
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="auth-drift absolute -top-32 -right-24 size-[28rem] rounded-full bg-indigo-300/40 blur-3xl"></div>
            <div class="auth-drift-rev absolute -bottom-40 left-1/3 size-[26rem] rounded-full bg-sky-300/30 blur-3xl"></div>
        </div>

        <main class="relative flex min-h-screen items-center justify-center p-4 sm:p-6 lg:p-10">
            <div class="relative flex w-full max-w-5xl items-center">

                {{-- ========== Brand panel (left, floating, overlaps the card) ========== --}}
                <aside class="auth-panel-in relative z-20 hidden w-[46%] shrink-0 self-stretch overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-600 via-indigo-700 to-[#17243B] p-10 text-white shadow-2xl lg:-mr-20 lg:flex lg:flex-col lg:justify-between">

                    {{-- background decoration --}}
                    <div class="auth-float absolute -top-16 -left-16 size-56 rounded-full bg-indigo-400/30 blur-2xl"></div>
                    <div class="auth-float absolute -right-10 bottom-24 size-48 rounded-full bg-sky-400/20 blur-2xl" style="animation-delay:-3s"></div>

                    {{-- illustration --}}
                    <div class="relative z-10 mx-auto mt-6 h-72 w-full max-w-sm">

                        {{-- back card with morphing blob --}}
                        <div class="absolute top-8 left-14 h-56 w-48 rounded-2xl bg-white/95 shadow-2xl">
                            <div class="auth-blob absolute inset-6 bg-indigo-400/70"></div>
                            <div class="auth-blob absolute inset-10 bg-indigo-600/80" style="animation-delay:-2s"></div>
                        </div>

                        {{-- avatar badge --}}
                        <div class="auth-float absolute top-1 left-28 z-10 flex size-10 items-center justify-center rounded-full bg-indigo-400 shadow-lg" style="animation-delay:-1s">
                            <svg class="size-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0"/></svg>
                        </div>

                        {{-- stats card --}}
                        <div class="auth-float absolute top-4 right-0 z-10 w-40 rounded-2xl bg-white p-4 text-slate-800 shadow-2xl">
                            <p class="text-2xl font-bold leading-none">128</p>
                            <p class="mt-1 text-[10px] text-slate-400">Applications</p>
                            <svg viewBox="0 0 140 50" class="mt-3 w-full overflow-visible">
                                <path class="auth-draw" d="M2 40 C20 38 25 20 40 24 S60 40 75 28 S100 8 138 14"
                                      fill="none" stroke="#4F46E5" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="138" cy="14" r="3.5" fill="#EF4444"/>
                                <circle cx="138" cy="14" r="3.5" fill="#EF4444" class="animate-ping" style="transform-origin:138px 14px"/>
                            </svg>
                        </div>

                        {{-- applicants list --}}
                        <div class="absolute bottom-0 left-0 z-10 w-44 space-y-2">
                            @foreach ([['Laravel Developer', false], ['UI Designer', false], ['Data Analyst', true], ['QA Engineer', false]] as $i => [$label, $active])
                                <div class="auth-row flex items-center gap-2 rounded-lg px-3 py-2 text-[10px] shadow-lg {{ $active ? 'bg-indigo-500 text-white' : 'bg-white text-slate-500' }}"
                                     style="animation-delay: {{ 0.3 + $i * 0.25 }}s">
                                    <span class="size-4 rounded-full {{ $active ? 'bg-white' : 'bg-slate-200' }}"></span>
                                    {{ $label }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- rotating text + dots (pure CSS) --}}
                    <div class="relative z-10 text-center">
                        <div class="relative h-28">
                            @foreach ([
                                ['Post jobs in minutes', 'Publish vacancies directly, no approval queue.'],
                                ['Review applicants with AI', 'Get a matching score and feedback to support your decision.'],
                                ['Track every application', 'Move candidates through each stage and keep them informed.'],
                            ] as $i => [$title, $text])
                                <div class="auth-slide absolute inset-x-0 top-0" style="animation-delay: {{ $i * 3 }}s">
                                    <h2 class="text-xl font-semibold">{{ $title }}</h2>
                                    <p class="mx-auto mt-2 max-w-xs text-sm text-indigo-100">{{ $text }}</p>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-2 flex items-center justify-center gap-1.5">
                            @foreach ([0, 3, 6] as $delay)
                                <span class="auth-dot h-1.5 rounded-full bg-white" style="animation-delay: {{ $delay }}s"></span>
                            @endforeach
                        </div>
                    </div>
                </aside>

                {{-- ========== Login card (right) ========== --}}
                <section class="auth-card-in relative z-10 w-full min-w-0 flex-1 overflow-hidden rounded-3xl bg-white px-6 py-10 shadow-xl sm:px-12 lg:my-8 lg:py-14 lg:pr-12 lg:pl-28">

                    {{-- soft moving shadows inside the card --}}
                    <div class="auth-drift pointer-events-none absolute -top-24 -right-24 size-72 rounded-full bg-indigo-200/50 blur-3xl"></div>
                    <div class="auth-drift-rev pointer-events-none absolute -bottom-28 right-1/4 size-72 rounded-full bg-sky-200/50 blur-3xl"></div>
                    <div class="auth-drift pointer-events-none absolute top-1/2 -left-16 size-56 rounded-full bg-violet-200/40 blur-3xl" style="animation-delay:-6s"></div>

                    <div class="relative z-10 mx-auto w-full max-w-sm">
                        <a href="{{ route('home') }}" class="mb-8 flex items-center gap-3">
                            <span class="flex size-11 items-center justify-center rounded-xl bg-indigo-600 text-xl font-bold text-white shadow-lg shadow-indigo-600/30">S</span>
                            <span class="flex flex-col">
                                <span class="text-base font-semibold tracking-tight text-slate-900">Shagalni</span>
                                <span class="text-xs text-slate-500">Management</span>
                            </span>
                        </a>

                        {{ $slot }}
                    </div>
                </section>
            </div>
        </main>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>