<?php

use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReviewCompanyRegistrationController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureManagementPortalAccess;
use App\Http\Controllers\AccountReactivationController;
use App\Http\Controllers\UserStatusController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware([
    'auth',
    'verified',
    EnsureManagementPortalAccess::class,
])->group(function () {
    Route::view('/dashboard', 'account.home')->name('dashboard');


    Route::get('/users', [UserController::class, 'index'])
        ->name('users.index');

    Route::get('/users/{user}', [UserController::class, 'show'])
        ->name('users.show');
    Route::post('/users/{user}/suspend', [UserStatusController::class, 'suspend'])
        ->name('users.suspend');

    Route::post('/users/{user}/deactivate', [UserStatusController::class, 'deactivate'])
        ->name('users.deactivate');

    Route::post('/users/{user}/reactivate', [UserStatusController::class, 'reactivate'])
        ->name('users.reactivate');

    Route::get('/companies', [CompanyController::class, 'index'])
        ->name('companies.index');

    Route::get('/companies/{company}', [CompanyController::class, 'show'])
        ->name('companies.show');

    Route::post('/companies/{company}/approve', [ReviewCompanyRegistrationController::class, 'approve'])
        ->name('companies.approve');

    Route::post('/companies/{company}/reject', [ReviewCompanyRegistrationController::class, 'reject'])
        ->name('companies.reject');
});

Route::get('/account-deletion/{user}/cancel', [
    AccountDeletionController::class,
    'cancel',
])->middleware('signed')->name('account-deletion.cancel');

Route::middleware('guest')->group(function () {
    Route::get('/account-reactivation', [AccountReactivationController::class, 'create'])
        ->name('account-reactivation.create');

    Route::post('/account-reactivation', [AccountReactivationController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('account-reactivation.store');
});

Route::get('/account-reactivation/{user}/confirm', [AccountReactivationController::class, 'confirm'])
    ->middleware('signed')
    ->name('account-reactivation.confirm');

require __DIR__ . '/settings.php';
