@props(['label', 'value', 'description'])

<article {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900']) }}>
    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">{{ $value }}</p>
    <p class="mt-2 text-xs leading-5 text-slate-500 dark:text-slate-400">{{ $description }}</p>
</article>
