@props(['title', 'description'])

<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl dark:text-slate-100">{{ $title }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $description }}</p>
    </div>
    @if (isset($action))
        <div class="shrink-0">{{ $action }}</div>
    @endif
</div>
