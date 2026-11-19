@props([
    'title',
    'subtitle' => null,
    'description' => null,
])

@php
    $sub = $subtitle ?? $description;
@endphp

<section {{ $attributes->merge(['class' => 'flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900']) }}>
    <div>
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

        <div class="mt-4">
            {{ $slot }}
        </div>
    </div>

    @if (isset($footer))
        <div class="mt-4 border-t border-slate-100 pt-3 text-xs text-slate-500 dark:border-slate-800/80 dark:text-slate-400">
            {{ $footer }}
        </div>
    @endif
</section>

