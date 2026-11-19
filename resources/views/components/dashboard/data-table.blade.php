@props([
    'title' => null,
    'subtitle' => null,
    'headers' => [],
    'viewAllUrl' => null,
    'viewAllText' => null,
])

<div {{ $attributes->merge(['class' => 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xs transition-[border-color,box-shadow] duration-200 hover:border-slate-300 hover:shadow-sm motion-reduce:transition-none dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700']) }}>
    @if (isset($header))
        <div class="border-b border-slate-200 px-5 py-3.5 dark:border-slate-800">
            {{ $header }}
        </div>
    @elseif ($title || $viewAllUrl)
        <div class="flex items-center justify-between border-b border-slate-200 px-5 py-3.5 dark:border-slate-800">
            <div>
                @if ($title)
                    <h2 class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white">
                        {{ $title }}
                    </h2>
                @endif
                @if ($subtitle)
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ $subtitle }}
                    </p>
                @endif
            </div>

            @if ($viewAllUrl)
                <a href="{{ $viewAllUrl }}" wire:navigate
                    class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 transition hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                    {{ $viewAllText ?? __('View all') }}
                    <flux:icon.arrow-right class="size-3" />
                </a>
            @endif
        </div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-left text-xs">
            @if (!empty($headers))
                <thead class="border-b border-slate-200 bg-slate-50/75 text-[0.6875rem] font-medium uppercase tracking-wider text-slate-500 dark:border-slate-800 dark:bg-slate-800/40 dark:text-slate-400">
                    <tr>
                        @foreach ($headers as $header)
                            <th scope="col" class="px-5 py-2.5 {{ is_array($header) && ($header['align'] ?? '') === 'right' ? 'text-right' : '' }}">
                                {{ is_array($header) ? $header['label'] : $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-slate-100 text-slate-700 dark:divide-slate-800/60 dark:text-slate-300">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
