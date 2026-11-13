<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Runs every day at 02:00; withoutOverlapping prevents two runs at the same time
Schedule::command('accounts:anonymize-expired')
    ->dailyAt('02:00')
    ->withoutOverlapping();

Schedule::command('jobs:purge-archived')
    ->dailyAt('02:30')
    ->withoutOverlapping();
