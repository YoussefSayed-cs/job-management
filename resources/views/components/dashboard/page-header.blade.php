@props([
    'title' => 'Dashboard',
    'subtitle' => null,
    'date' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between border-b border-slate-200 pb-4 dark:border-slate-800']) }}>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2.5">
            <h1 class="text-xl font-semibold tracking-tight text-slate-900 sm:text-2xl dark:text-white">
                {{ $title }}
            </h1>

            @if (isset($badge))
                {{ $badge }}
            @endif
        </div>

        @if ($subtitle || isset($meta) || $date !== false)
            <div class="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                @if ($subtitle)
                    <span>{{ $subtitle }}</span>
                @endif

                @if (isset($meta))
                    {{ $meta }}
                @endif

                @if ($date !== false)
                    <span>{{ $date ?? now()->format('l, F j, Y') }}</span>
                @endif
            </div>
        @endif
    </div>

    @if (isset($actions))
        <div class="flex shrink-0 items-center gap-2.5 pt-1 sm:pt-0">
            {{ $actions }}
        </div>
    @endif
</div>

