@props(['distribution'])

@php
    $palette = [
        'pending' => '#f59e0b',
        'under_review' => '#3b82f6',
        'shortlisted' => '#6366f1',
        'interview' => '#a855f7',
        'accepted' => '#22c55e',
        'rejected' => '#ef4444',
        'withdrawn' => '#94a3b8',
    ];

    $statuses = array_keys($distribution);
    $labels = array_map(fn ($status) => \Illuminate\Support\Str::headline($status), $statuses);
    $values = array_values($distribution);
    $colors = array_map(fn ($status) => $palette[$status] ?? '#64748b', $statuses);
@endphp

<x-analytics.chart
    type="doughnut"
    label="Applications by status"
    :labels="$labels"
    :values="$values"
    :colors="$colors"
/>