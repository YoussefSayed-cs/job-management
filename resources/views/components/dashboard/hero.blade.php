@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'date' => null,
])

<section class="relative overflow-hidden rounded-3xl border border-slate-200/80 bg-white p-6 shadow-xs sm:p-8 dark:border-slate-800 dark:bg-slate-900">
    {{-- Soft ambient gradient glows (Login Page visual identity) --}}
    <div aria-hidden="true" class="pointer-events-none absolute -top-20 -right-20 size-72 rounded-full bg-gradient-to-br from-indigo-200/40 via-sky-200/30 to-violet-200/20 blur-3xl dark:from-indigo-900/25 dark:via-sky-900/15 dark:to-violet-900/15"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -bottom-24 right-1/4 size-64 rounded-full bg-indigo-100/30 blur-2xl dark:from-indigo-950/20"></div>

    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                @if ($eyebrow)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-100 bg-indigo-50/90 px-3 py-1 text-xs font-semibold tracking-wide text-indigo-700 uppercase dark:border-indigo-400/20 dark:bg-indigo-400/10 dark:text-indigo-300">
                        {{ $eyebrow }}
                    </span>
                @endif

                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500 dark:text-slate-400">
                    <flux:icon.calendar class="size-3.5 text-slate-400 dark:text-slate-500" />
                    <time datetime="{{ now()->toDateString() }}">
                        {{ $date ?? now()->format('l, F j, Y') }}
                    </time>
                </span>

                @if (isset($badge))
                    {{ $badge }}
                @endif
            </div>

            <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl lg:text-4xl dark:text-white">
                {{ $title }}
            </h1>

            @if ($subtitle)
                <p class="mt-2 text-sm leading-6 text-slate-600 sm:text-base dark:text-slate-400">
                    {{ $subtitle }}
                </p>
            @endif
        </div>

        @if (isset($actions))
            <div class="flex flex-wrap items-center gap-3 pt-2 lg:pt-0">
                {{ $actions }}
            </div>
        @endif
    </div>
</section>
