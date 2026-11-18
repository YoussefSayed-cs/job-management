@props(['title', 'description' => null])

<section {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/80 bg-white p-5 sm:p-6 shadow-xs transition-shadow duration-200 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">{{ $title }}</h2>

            @if ($description)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </div>

        @if (isset($action))
            <div class="shrink-0">{{ $action }}</div>
        @endif
    </div>

    <div class="mt-5">
        {{ $slot }}
    </div>
</section>