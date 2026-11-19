@props([
    'label',
    'value',
    'subtext' => null,
    'description' => null,
    'delta' => null,
    'accent' => 'indigo',
    'icon' => null,
    'badge' => null,
])

@php
    $accentStyles = match ($accent) {
        'indigo', 'blue' => [
            'icon_bg' => 'border border-indigo-200/80 bg-indigo-50 text-indigo-600 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-400',
            'border_hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700/60',
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800/40',
        ],
        'amber' => [
            'icon_bg' => 'border border-amber-200/80 bg-amber-50 text-amber-700 dark:border-amber-800/40 dark:bg-amber-950/40 dark:text-amber-300',
            'border_hover' => 'hover:border-amber-300 dark:hover:border-amber-700/60',
            'badge' => 'bg-amber-50 text-amber-700 border-amber-200/80 dark:bg-amber-950/50 dark:text-amber-300 dark:border-amber-800/40',
        ],
        'rose' => [
            'icon_bg' => 'border border-rose-200/80 bg-rose-50 text-rose-700 dark:border-rose-800/40 dark:bg-rose-950/40 dark:text-rose-300',
            'border_hover' => 'hover:border-rose-300 dark:hover:border-rose-700/60',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200/80 dark:bg-rose-950/50 dark:text-rose-300 dark:border-rose-800/40',
        ],
        'sky' => [
            'icon_bg' => 'border border-sky-200/80 bg-sky-50 text-sky-700 dark:border-sky-800/40 dark:bg-sky-950/40 dark:text-sky-300',
            'border_hover' => 'hover:border-sky-300 dark:hover:border-sky-700/60',
            'badge' => 'bg-sky-50 text-sky-700 border-sky-200/80 dark:bg-sky-950/50 dark:text-sky-300 dark:border-sky-800/40',
        ],
        'slate' => [
            'icon_bg' => 'border border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700/60 dark:bg-slate-800/80 dark:text-slate-300',
            'border_hover' => 'hover:border-slate-300 dark:hover:border-slate-700',
            'badge' => 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700',
        ],
        default => [
            'icon_bg' => 'border border-indigo-200/80 bg-indigo-50 text-indigo-600 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-400',
            'border_hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700/60',
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200/80 dark:bg-indigo-950/50 dark:text-indigo-300 dark:border-indigo-800/40',
        ],
    };

    $infoText = $subtext ?? $description ?? $delta;
@endphp

<article {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between overflow-hidden rounded-xl border border-slate-200 bg-white p-5 shadow-xs transition-all duration-200 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900 ' . $accentStyles['border_hover']]) }}>
    <div>
        <div class="flex items-center justify-between gap-3">
            <span class="text-xs font-medium text-slate-500 dark:text-slate-400">
                {{ $label }}
            </span>

            @if ($icon)
                <div class="flex size-9 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-105 {{ $accentStyles['icon_bg'] }}">
                    <flux:icon :icon="$icon" class="size-4.5" />
                </div>
            @elseif (isset($customIcon))
                <div class="flex size-9 items-center justify-center rounded-lg transition-transform duration-200 group-hover:scale-105 {{ $accentStyles['icon_bg'] }}">
                    {{ $customIcon }}
                </div>
            @endif
        </div>

        <div class="mt-3">
            <p class="text-2xl font-bold tracking-tight text-slate-900 tabular-nums sm:text-3xl dark:text-white">
                {{ $value }}
            </p>
        </div>
    </div>

    <div class="mt-3 flex items-center justify-between text-xs">
        @if ($infoText)
            <span class="text-slate-500 dark:text-slate-400">
                {{ $infoText }}
            </span>
        @else
            <span class="text-transparent select-none">&nbsp;</span>
        @endif

        @if ($badge)
            <span class="inline-flex items-center rounded-full border px-2 py-0.5 text-[0.6875rem] font-semibold {{ $accentStyles['badge'] }}">
                {{ $badge }}
            </span>
        @endif
    </div>
</article>
