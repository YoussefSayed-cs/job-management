<?php
use App\Http\Middleware\EnsureManagementPortalAccess;
use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified',EnsureManagementPortalAccess::class,])->group(function () {
    Route::view('/dashboard', 'account.home')->name('dashboard');
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
});

Route::get('/account-deletion/{user}/cancel', [
    AccountDeletionController::class,
    'cancel',
])->middleware('signed')->name('account-deletion.cancel');

require __DIR__.'/settings.php';
