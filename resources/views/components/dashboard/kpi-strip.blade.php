@props([
    'stats' => [],
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs transition-[border-color,box-shadow] duration-200 hover:border-indigo-200 hover:shadow-sm motion-reduce:transition-none dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/70']) }}>
    <div class="grid grid-cols-2 gap-px bg-slate-200 lg:grid-cols-4 dark:bg-slate-800">
        @if (!empty($stats))
            @foreach ($stats as $stat)
                @if (!empty($stat['href']))
                    <a href="{{ $stat['href'] }}" wire:navigate
                        class="group/stat relative flex min-h-32 flex-col justify-between bg-white p-5 transition-colors duration-200 hover:bg-slate-50/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-inset focus-visible:outline-indigo-500 dark:bg-slate-900 dark:hover:bg-slate-800/50">
                @else
                    <div class="group/stat relative flex min-h-32 flex-col justify-between bg-white p-5 transition-colors duration-200 hover:bg-slate-50/80 dark:bg-slate-900 dark:hover:bg-slate-800/50">
                @endif
                    <span aria-hidden="true" class="absolute inset-x-5 top-0 h-px bg-indigo-500 opacity-0 transition-opacity duration-200 group-hover/stat:opacity-100 group-focus-visible/stat:opacity-100"></span>

                    <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                        {{ $stat['label'] }}
                    </span>

                    <span class="mt-2 text-2xl font-semibold tracking-tight tabular-nums transition-transform duration-200 group-hover/stat:translate-x-0.5 sm:text-3xl {{ !empty($stat['accent']) ? 'text-indigo-600 dark:text-indigo-400 font-bold' : 'text-slate-900 dark:text-white' }}">
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
