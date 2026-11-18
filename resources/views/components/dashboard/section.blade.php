@props([
    'title',
    'description' => null,
    'badge' => null,
])

<section {{ $attributes->merge(['class' => 'flex flex-col gap-4']) }}>
    <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <h2 class="text-base font-semibold tracking-tight text-slate-900 sm:text-lg dark:text-white">
                    {{ $title }}
                </h2>
                @if ($badge)
                    <span class="inline-flex items-center rounded-full border border-teal-200/80 bg-teal-50 px-2 py-0.5 text-[0.6875rem] font-semibold text-teal-800 dark:border-teal-800/40 dark:bg-teal-950/40 dark:text-teal-300">
                        {{ $badge }}
                    </span>
                @endif
            </div>

            @if ($description)
                <p class="mt-0.5 text-xs text-slate-500 sm:text-sm dark:text-slate-400">
                    {{ $description }}
                </p>
            @endif
        </div>

        @if (isset($action))
            <div class="shrink-0 pt-2 sm:pt-0">
                {{ $action }}
            </div>
        @endif
    </div>

    <div>
        {{ $slot }}
    </div>
</section>
