@props(['distribution'])

@php
    $palette = [
        'pending' => '#d97706',
        'under_review' => '#0284c7',
        'shortlisted' => '#4f46e5',
        'interview' => '#7e22ce',
        'accepted' => '#2563eb',
        'rejected' => '#e11d48',
        'withdrawn' => '#64748b',
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
