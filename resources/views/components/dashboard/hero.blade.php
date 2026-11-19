@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'date' => null,
])

<section class="relative overflow-hidden rounded-2xl border border-slate-200 bg-white p-6 shadow-xs sm:p-8 dark:border-slate-800 dark:bg-slate-900">
    {{-- Ambient accent (matching login identity) --}}
    <div aria-hidden="true" class="pointer-events-none absolute -top-16 -right-16 size-72 rounded-full bg-indigo-500/5 blur-3xl dark:bg-indigo-400/5"></div>

    <div class="relative z-10 flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
        <div class="max-w-3xl">
            <div class="flex flex-wrap items-center gap-2.5">
                @if ($eyebrow)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-indigo-200/80 bg-indigo-50 px-3 py-1 text-xs font-semibold tracking-wider text-indigo-700 uppercase dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-300">
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
