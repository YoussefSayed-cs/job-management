<?php

use App\Http\Controllers\JobVacancyController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [JobVacancyController::class, 'dashboard'])->name('dashboard');
    Route::resource('jobs', JobVacancyController::class)->parameters(['jobs' => 'job']);
});

require __DIR__.'/settings.php';
