<?php

use App\Http\Controllers\AccountDeletionController;
use App\Http\Controllers\AccountReactivationController;
use App\Http\Controllers\ApplicationReviewController;
use App\Http\Controllers\CompanyChangeRequestController;
use App\Http\Controllers\CompanyChangeRequestReviewController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyOwnerController;
use App\Http\Controllers\CompanyOwnerInvitationController;
use App\Http\Controllers\CompanyProfileController;
use App\Http\Controllers\JobCategoryController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\ReviewCompanyRegistrationController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserStatusController;
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

    Route::get('/users/{user}', [UserController::class, 'show'])
        ->name('users.show');
    Route::post('/users/{user}/suspend', [UserStatusController::class, 'suspend'])
        ->name('users.suspend');

    Route::post('/users/{user}/deactivate', [UserStatusController::class, 'deactivate'])
        ->name('users.deactivate');

    Route::post('/users/{user}/reactivate', [UserStatusController::class, 'reactivate'])
        ->name('users.reactivate');

    Route::get('/company-owners/create', [CompanyOwnerController::class, 'create'])
        ->name('company-owners.create');

    Route::post('/company-owners', [CompanyOwnerController::class, 'store'])
        ->name('company-owners.store');

    Route::post('/users/{user}/resend-invitation', [CompanyOwnerController::class, 'resend'])
        ->name('users.resend-invitation');

    Route::get('/companies', [CompanyController::class, 'index'])
        ->name('companies.index');

    Route::get('/companies/{company}', [CompanyController::class, 'show'])
        ->name('companies.show');

    Route::post('/companies/{company}/approve', [ReviewCompanyRegistrationController::class, 'approve'])
        ->name('companies.approve');

    Route::post('/companies/{company}/reject', [ReviewCompanyRegistrationController::class, 'reject'])
        ->name('companies.reject');

    Route::get('/my-company', [CompanyProfileController::class, 'show'])
        ->name('my-company.show');

    Route::patch('/my-company', [CompanyProfileController::class, 'update'])
        ->name('my-company.update');

    Route::get('/my-company/change-requests', [CompanyChangeRequestController::class, 'index'])
        ->name('my-company.change-requests.index');

    Route::post('/my-company/change-requests', [CompanyChangeRequestController::class, 'store'])
        ->name('my-company.change-requests.store');

    Route::get('/company-change-requests', [
        CompanyChangeRequestReviewController::class,
        'index',
    ])->name('company-change-requests.index');

    Route::post('/company-change-requests/{companyChangeRequest}/approve', [
        CompanyChangeRequestReviewController::class,
        'approve',
    ])->name('company-change-requests.approve');

    Route::post('/company-change-requests/{companyChangeRequest}/reject', [
        CompanyChangeRequestReviewController::class,
        'reject',
    ])->name('company-change-requests.reject');

    // Job Management

    Route::get('/jobs', [JobController::class, 'index'])->name('jobs.index');

    Route::get('/jobs/create', [JobController::class, 'create'])->name('jobs.create');

    Route::post('/jobs', [JobController::class, 'store'])
        ->name('jobs.store');

    Route::get('/jobs/{job}', [JobController::class, 'show'])
        ->name('jobs.show');

    Route::get('/jobs/{job}/edit', [JobController::class, 'edit'])
        ->name('jobs.edit');

    Route::put('/jobs/{job}', [JobController::class, 'update'])
        ->name('jobs.update');

    Route::post('/jobs/{job}/close', [JobController::class, 'close'])
        ->name('jobs.close');

    Route::post('/jobs/{job}/reopen', [JobController::class, 'reopen'])
        ->name('jobs.reopen');

    Route::post('/jobs/{job}/archive', [JobController::class, 'archive'])
        ->name('jobs.archive');

    Route::post('/jobs/{job}/restore', [JobController::class, 'restore'])
        ->name('jobs.restore');

    Route::delete('/jobs/{job}', [JobController::class, 'destroy'])
        ->name('jobs.destroy');

    // Application Management

    Route::get('/applications', [ApplicationReviewController::class, 'index'])->name('applications.index');

    Route::get('/applications/{application}', [ApplicationReviewController::class, 'show'])->name('applications.show');

    Route::patch('/applications/{application}/status', [ApplicationReviewController::class, 'updateStatus'])->name('applications.status.update');

    Route::get('/applications/{application}/resume', [ApplicationReviewController::class, 'downloadResume'])->name('applications.resume.download');

    // Job Categories Management

    Route::resource('categories', JobCategoryController::class)->except(['show']);

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

Route::middleware('signed')->group(function () {
    Route::get('/company-owner-invitation/{user}', [CompanyOwnerInvitationController::class, 'show'])->name('company-owner-invitation.show');
    Route::post('/company-owner-invitation/{user}', [CompanyOwnerInvitationController::class, 'store'])->middleware('throttle:10,1')->name('company-owner-invitation.store');
});

require __DIR__.'/settings.php';
