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
        'emerald' => [
            'icon_bg' => 'bg-emerald-50 text-emerald-600 dark:bg-emerald-400/10 dark:text-emerald-400',
            'border_hover' => 'hover:border-emerald-200 dark:hover:border-emerald-800',
        ],
        'amber' => [
            'icon_bg' => 'bg-amber-50 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400',
            'border_hover' => 'hover:border-amber-200 dark:hover:border-amber-800',
        ],
        'rose' => [
            'icon_bg' => 'bg-rose-50 text-rose-600 dark:bg-rose-400/10 dark:text-rose-400',
            'border_hover' => 'hover:border-rose-200 dark:hover:border-rose-800',
        ],
        'sky' => [
            'icon_bg' => 'bg-sky-50 text-sky-600 dark:bg-sky-400/10 dark:text-sky-400',
            'border_hover' => 'hover:border-sky-200 dark:hover:border-sky-800',
        ],
        'violet' => [
            'icon_bg' => 'bg-violet-50 text-violet-600 dark:bg-violet-400/10 dark:text-violet-400',
            'border_hover' => 'hover:border-violet-200 dark:hover:border-violet-800',
        ],
        'slate' => [
            'icon_bg' => 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
            'border_hover' => 'hover:border-slate-300 dark:hover:border-slate-700',
        ],
        default => [
            'icon_bg' => 'bg-indigo-50 text-indigo-600 dark:bg-indigo-400/10 dark:text-indigo-400',
            'border_hover' => 'hover:border-indigo-200 dark:hover:border-indigo-800',
        ],
    };
@endphp

<article {{ $attributes->merge(['class' => 'group relative flex flex-col justify-between overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs transition-all duration-200 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900 ' . $accentStyles['border_hover']]) }}>
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
            <p class="mt-1.5 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl dark:text-white">
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
