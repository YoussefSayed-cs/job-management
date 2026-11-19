<div class="mx-auto flex w-full max-w-3xl flex-col gap-6">
    <header class="flex flex-col gap-1 border-b border-slate-200 pb-4 dark:border-slate-800">
        <h1 class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $heading ?? '' }}</h1>
        <p class="text-sm text-slate-500 dark:text-slate-400">{{ $subheading ?? '' }}</p>
    </header>

    {{ $slot }}
</div>
