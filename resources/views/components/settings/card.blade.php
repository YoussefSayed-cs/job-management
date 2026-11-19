@props([
    'title' => null,
    'description' => null,
    'titleClass' => 'text-slate-900 dark:text-slate-100',
])

<section {{ $attributes->merge(['class' => 'rounded-xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900 sm:p-6']) }}>
    @if ($title || $description)
        <header class="space-y-1">
            @if ($title)
                <h2 class="text-sm font-semibold {{ $titleClass }}">{{ $title }}</h2>
            @endif

            @if ($description)
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
            @endif
        </header>
    @endif

    @if (isset($body))
        <div @class(['mt-5' => $title || $description])>{{ $body }}</div>
    @elseif (trim((string) $slot) !== '')
        <div @class(['mt-5' => $title || $description])>{{ $slot }}</div>
    @endif

    @if (isset($footer))
        <footer class="mt-5 flex justify-end border-t border-slate-200 pt-4 dark:border-slate-800">
            {{ $footer }}
        </footer>
    @endif
</section>