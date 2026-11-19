@props([
    'stats' => [],
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900']) }}>
    <div class="grid grid-cols-2 divide-y divide-slate-200 sm:grid-cols-2 sm:divide-y-0 lg:grid-cols-4 lg:divide-x dark:divide-slate-800 sm:[&>*:nth-child(even)]:border-l sm:[&>*:nth-child(even)]:border-slate-200 lg:[&>*:nth-child(even)]:border-l-0 dark:sm:[&>*:nth-child(even)]:border-slate-800 sm:[&>*:nth-child(n+3)]:border-t sm:[&>*:nth-child(n+3)]:border-slate-200 lg:[&>*:nth-child(n+3)]:border-t-0 dark:sm:[&>*:nth-child(n+3)]:border-slate-800">
        @if (!empty($stats))
            @foreach ($stats as $stat)
                <div class="flex flex-col justify-between p-5">
                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        {{ $stat['label'] }}
                    </span>

                    <span class="mt-2 text-2xl font-semibold tracking-tight tabular-nums sm:text-3xl {{ !empty($stat['accent']) ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-900 dark:text-white' }}">
                        {{ $stat['value'] }}
                    </span>

                    @if (!empty($stat['subtext']) || !empty($stat['delta']))
                        <span class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                            {{ $stat['subtext'] ?? $stat['delta'] }}
                        </span>
                    @else
                        <span class="mt-1 text-xs text-transparent select-none">&nbsp;</span>
                    @endif
                </div>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </div>
</div>
