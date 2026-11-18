@props([
    'title',
    'description',
    'icon' => 'inbox',
    'actionText' => null,
    'actionUrl' => null,
    'actionIcon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-200/90 bg-white/60 px-6 py-12 text-center dark:border-slate-800 dark:bg-slate-900/40']) }}>
    <div class="flex size-12 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 shadow-xs dark:bg-indigo-400/10 dark:text-indigo-400">
        @if (isset($customIcon))
            {{ $customIcon }}
        @else
            <flux:icon :icon="$icon" class="size-6" />
        @endif
    </div>

    <h3 class="mt-4 text-sm font-semibold text-slate-900 dark:text-white">
        {{ $title }}
    </h3>

    <p class="mx-auto mt-1 max-w-sm text-xs leading-5 text-slate-500 dark:text-slate-400">
        {{ $description }}
    </p>

    @if ($actionText && $actionUrl)
        <div class="mt-5">
            <a href="{{ $actionUrl }}" wire:navigate
                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                @if ($actionIcon)
                    <flux:icon :icon="$actionIcon" class="size-3.5" />
                @endif
                {{ $actionText }}
            </a>
        </div>
    @endif

    @if (isset($action))
        <div class="mt-5">
            {{ $action }}
        </div>
    @endif
</div>
