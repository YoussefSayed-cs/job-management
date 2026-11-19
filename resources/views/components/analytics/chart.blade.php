@props([
    'type' => 'bar',
    'labels' => [],
    'values' => [],
    'label' => '',
    'colors' => [],
    'horizontal' => false,
    'height' => 'h-64',
])

@php
    $labels = array_values($labels);
    $values = array_values($values);
    $hasData = array_sum($values) > 0;
@endphp

@if ($hasData)
    <div class="relative {{ $height }}">
        <canvas role="img" aria-label="{{ $label }}"
            data-chart="{{ json_encode(compact('type', 'labels', 'values', 'label', 'colors', 'horizontal')) }}"></canvas>
    </div>
@else
    <div
        class="flex {{ $height }} items-center justify-center rounded-xl border border-dashed border-slate-200 text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
        {{ __('No data yet') }}
    </div>
@endif