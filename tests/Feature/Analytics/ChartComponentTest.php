<?php

use Illuminate\Support\Facades\Blade;

it('renders a canvas with chart data when there are values', function () {
    $html = Blade::render(
        '<x-analytics.chart type="bar" label="Applications per job" :labels="$labels" :values="$values" />',
        ['labels' => ['Backend', 'Frontend'], 'values' => [2, 1]],
    );

    expect($html)
        ->toContain('<canvas')
        ->toContain('data-chart=')
        ->toContain('Applications per job')
        ->not->toContain('No data yet');
});

it('shows an empty state instead of a canvas when all values are zero', function () {
    $html = Blade::render(
        '<x-analytics.chart type="doughnut" label="Status" :labels="$labels" :values="$values" />',
        ['labels' => ['Pending', 'Accepted'], 'values' => [0, 0]],
    );

    expect($html)->toContain('No data yet')->not->toContain('<canvas');   
});