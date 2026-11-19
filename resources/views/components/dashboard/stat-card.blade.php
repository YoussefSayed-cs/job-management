@props([
    'label',
    'value',
    'description' => null,
    'accent' => 'indigo',
    'icon' => null,
    'badge' => null,
])

@php
    $accentStyles = match ($accent) {
        'indigo', 'blue', 'cobalt' => [
            'icon_bg' => 'border border-indigo-200/80 bg-indigo-50 text-indigo-700 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-300',
            'border_hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700',
        ],
        'teal' => [
            'icon_bg' => 'border border-teal-200/80 bg-teal-50 text-teal-800 dark:border-teal-800/40 dark:bg-teal-950/40 dark:text-teal-300',
            'border_hover' => 'hover:border-teal-300 dark:hover:border-teal-700',
        ],
        'emerald' => [
            'icon_bg' => 'border border-emerald-200/80 bg-emerald-50 text-emerald-800 dark:border-emerald-800/40 dark:bg-emerald-950/40 dark:text-emerald-300',
            'border_hover' => 'hover:border-emerald-300 dark:hover:border-emerald-700',
        ],
        'amber' => [
            'icon_bg' => 'border border-amber-200/80 bg-amber-50 text-amber-900 dark:border-amber-800/40 dark:bg-amber-950/40 dark:text-amber-300',
            'border_hover' => 'hover:border-amber-300 dark:hover:border-amber-700',
        ],
        'rose' => [
            'icon_bg' => 'border border-rose-200/80 bg-rose-50 text-rose-800 dark:border-rose-800/40 dark:bg-rose-950/40 dark:text-rose-300',
            'border_hover' => 'hover:border-rose-300 dark:hover:border-rose-700',
        ],
        'sky' => [
            'icon_bg' => 'border border-sky-200/80 bg-sky-50 text-sky-800 dark:border-sky-800/40 dark:bg-sky-950/40 dark:text-sky-300',
            'border_hover' => 'hover:border-sky-300 dark:hover:border-sky-700',
        ],
        'violet', 'purple' => [
            'icon_bg' => 'border border-purple-200/80 bg-purple-50 text-purple-800 dark:border-purple-800/40 dark:bg-purple-950/40 dark:text-purple-300',
            'border_hover' => 'hover:border-purple-300 dark:hover:border-purple-700',
        ],
        'slate' => [
            'icon_bg' => 'border border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700/60 dark:bg-slate-800/80 dark:text-slate-300',
            'border_hover' => 'hover:border-slate-300 dark:hover:border-slate-700',
        ],
        default => [
            'icon_bg' => 'border border-indigo-200/80 bg-indigo-50 text-indigo-700 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-300',
            'border_hover' => 'hover:border-indigo-300 dark:hover:border-indigo-700',
        ],
    };
@endphp

<article {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200 bg-white p-5 shadow-xs transition-all duration-200 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900 ' . $accentStyles['border_hover']]) }}>
    <div>
        <div class="flex items-center justify-between gap-3">
            @if ($icon)
                <div class="flex size-10 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-105 {{ $accentStyles['icon_bg'] }}">
                    <flux:icon :icon="$icon" class="size-5" />
                </div>
            @elseif (isset($customIcon))
                <div class="flex size-10 items-center justify-center rounded-xl transition-transform duration-200 group-hover:scale-105 {{ $accentStyles['icon_bg'] }}">
                    {{ $customIcon }}
                </div>
            @endif

            @if ($badge)
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $accentStyles['icon_bg'] }}">
                    {{ $badge }}
                </span>
            @endif
        </div>

        <div class="mt-4">
            <p class="text-xs font-semibold tracking-wider text-slate-500 uppercase dark:text-slate-400">
                {{ $label }}
            </p>
            <p class="mt-1.5 font-mono text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
                {{ $value }}
            </p>
        </div>
    </div>

    @if ($description)
        <p class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">
            {{ $description }}
        </p>
    @endif
</article>
