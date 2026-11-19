@props([
    'chips' => [],
])

<div {{ $attributes->merge(['class' => 'flex flex-wrap items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400']) }}>
    @foreach ($chips as $index => $chip)
        @if ($index > 0)
            <span class="text-slate-300 dark:text-slate-600 select-none">·</span>
        @endif

        @if (!empty($chip['url']))
            <a href="{{ $chip['url'] }}" wire:navigate
                class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 font-medium transition hover:bg-slate-100 hover:text-slate-900 dark:hover:bg-slate-800 dark:hover:text-white">
                <span>{{ $chip['label'] }}</span>
                <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[0.6875rem] font-semibold tabular-nums text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    {{ number_format($chip['count']) }}
                </span>
            </a>
        @else
            <span class="inline-flex items-center gap-1.5 px-2 py-1 font-medium">
                <span>{{ $chip['label'] }}</span>
                <span class="rounded bg-slate-100 px-1.5 py-0.2 text-[0.6875rem] font-semibold tabular-nums text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    {{ number_format($chip['count']) }}
                </span>
            </span>
        @endif
    @endforeach
</div>
