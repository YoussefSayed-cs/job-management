<?php

use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReviewCompanyRegistrationController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\EnsureManagementPortalAccess;
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

require __DIR__.'/settings.php';