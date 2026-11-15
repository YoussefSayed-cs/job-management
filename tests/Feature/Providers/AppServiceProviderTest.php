<?php

use App\Policies\ApplicationPolicy;
use App\Policies\CompanyChangeRequestPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\JobCategoryPolicy;
use App\Policies\JobPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\CompanyChangeRequest;
use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\JobVacancy;
use Shagalni\Shared\Models\User;

test('each shared model resolves to its registered policy', function () {
    $policies = [
        User::class => UserPolicy::class,
        Company::class => CompanyPolicy::class,
        CompanyChangeRequest::class => CompanyChangeRequestPolicy::class,
        JobVacancy::class => JobPolicy::class,
        Application::class => ApplicationPolicy::class,
        JobCategory::class => JobCategoryPolicy::class,
    ];

    foreach ($policies as $model => $policy) {
        expect(Gate::getPolicyFor($model))->toBeInstanceOf($policy);
    }
});
