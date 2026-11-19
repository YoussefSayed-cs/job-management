@props([
    'stats' => [],
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs transition-[border-color,box-shadow] duration-200 hover:border-slate-300 hover:shadow-sm motion-reduce:transition-none dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700']) }}>
    <div class="grid grid-cols-2 lg:grid-cols-4 [&>*:nth-child(even)]:border-l [&>*:nth-child(even)]:border-slate-200 [&>*:nth-child(n+3)]:border-t [&>*:nth-child(n+3)]:border-slate-200 lg:[&>*:nth-child(n+2)]:border-l lg:[&>*:nth-child(n+2)]:border-slate-200 lg:[&>*:nth-child(n+3)]:border-t-0 dark:[&>*:nth-child(even)]:border-slate-800 dark:[&>*:nth-child(n+3)]:border-slate-800 lg:dark:[&>*:nth-child(n+2)]:border-slate-800">
        @if (!empty($stats))
            @foreach ($stats as $stat)
                @if (!empty($stat['href']))
                    <a href="{{ $stat['href'] }}" wire:navigate
                        class="flex flex-col justify-between p-5 transition-colors hover:bg-slate-50/70 focus-visible:outline focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-indigo-500 dark:hover:bg-slate-800/40">
                @else
                    <div class="flex flex-col justify-between p-5 transition-colors hover:bg-slate-50/70 dark:hover:bg-slate-800/40">
                @endif
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
                @if (!empty($stat['href']))
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        @else
            {{ $slot }}
        @endif
    </div>
</div>
