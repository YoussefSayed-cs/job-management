@props([
    'title',
    'subtitle' => null,
    'description' => null,
])

@php
    $sub = $subtitle ?? $description;
@endphp

<section {{ $attributes->merge(['class' => 'group flex h-full flex-col rounded-xl border border-slate-200 bg-white p-5 shadow-xs transition-[transform,border-color,box-shadow] duration-200 hover:border-indigo-200 hover:shadow-sm motion-safe:hover:-translate-y-0.5 motion-reduce:transition-none dark:border-slate-800 dark:bg-slate-900 dark:hover:border-indigo-900/70']) }}>
    <div class="flex flex-1 flex-col">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white">
                    {{ $title }}
                </h2>

                @if ($sub)
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ $sub }}
                    </p>
                @endif
            </div>

            @if (isset($action))
                <div class="shrink-0">
                    {{ $action }}
                </div>
            @endif
        </div>

        <div class="mt-4 flex-1 rounded-lg border border-slate-100 bg-slate-50/50 p-3 dark:border-slate-800/80 dark:bg-slate-950/30">
            {{ $slot }}
        </div>
    </div>

    @if (isset($footer))
        <div class="mt-auto border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
            {{ $footer }}
        </div>
    @endif
</section>

