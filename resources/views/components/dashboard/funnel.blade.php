@props([
    'distribution' => [],
])

@php
    $total = array_sum($distribution);

    $statusMeta = [
        'pending' => [
            'label' => __('Pending'),
            'bar' => 'bg-indigo-300 dark:bg-indigo-400',
            'dot' => 'bg-indigo-400',
        ],
        'under_review' => [
            'label' => __('Under Review'),
            'bar' => 'bg-indigo-400 dark:bg-indigo-500',
            'dot' => 'bg-indigo-500',
        ],
        'shortlisted' => [
            'label' => __('Shortlisted'),
            'bar' => 'bg-indigo-500 dark:bg-indigo-600',
            'dot' => 'bg-indigo-600',
        ],
        'interview' => [
            'label' => __('Interview'),
            'bar' => 'bg-indigo-600 dark:bg-indigo-700',
            'dot' => 'bg-indigo-700',
        ],
        'accepted' => [
            'label' => __('Accepted'),
            'bar' => 'bg-blue-800 dark:bg-blue-600',
            'dot' => 'bg-blue-800 dark:bg-blue-600',
        ],
        'rejected' => [
            'label' => __('Rejected'),
            'bar' => 'bg-rose-500 dark:bg-rose-600',
            'dot' => 'bg-rose-500',
        ],
        'withdrawn' => [
            'label' => __('Withdrawn'),
            'bar' => 'bg-slate-400 dark:bg-slate-500',
            'dot' => 'bg-slate-400',
        ],
    ];
@endphp

<div class="flex flex-col gap-4">
    @if ($total > 0)
        {{-- Horizontal stacked bar --}}
        <div class="flex h-3.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800" role="progressbar" aria-valuenow="{{ $total }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
            @foreach ($statusMeta as $key => $meta)
                @php
                    $count = $distribution[$key] ?? 0;
                    $pct = $total > 0 ? ($count / $total) * 100 : 0;
                @endphp
                @if ($count > 0)
                    <div
                        style="width: {{ max($pct, 1.5) }}%"
                        class="{{ $meta['bar'] }} transition-all duration-300"
                        title="{{ $meta['label'] }}: {{ number_format($count) }} ({{ round($pct, 1) }}%)"
                    ></div>
                @endif
            @endforeach
        </div>

        {{-- Legend underneath with counts --}}
        <div class="grid grid-cols-2 gap-x-4 gap-y-2 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($statusMeta as $key => $meta)
                @php
                    $count = $distribution[$key] ?? 0;
                    $pct = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                @endphp
                <div class="flex items-center gap-2 text-xs">
                    <span class="size-2 shrink-0 rounded-full {{ $meta['dot'] }}"></span>
                    <span class="truncate text-slate-600 dark:text-slate-400">{{ $meta['label'] }}</span>
                    <span class="ms-auto font-medium tabular-nums text-slate-900 dark:text-white">{{ number_format($count) }}</span>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex h-3.5 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800"></div>
        <p class="py-2 text-center text-xs text-slate-400 dark:text-slate-500">
            {{ __('No applications submitted yet.') }}
        </p>
    @endif
</div>
