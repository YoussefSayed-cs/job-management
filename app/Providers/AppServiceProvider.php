<?php

namespace App\Providers;

use App\Policies\ApplicationPolicy;
use App\Policies\AiAnalysisPolicy;
use App\Policies\CompanyChangeRequestPolicy;
use App\Policies\CompanyPolicy;
use App\Policies\JobCategoryPolicy;
use App\Policies\JobPolicy;
use App\Policies\UserPolicy;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Shagalni\Shared\Models\Application;
use Shagalni\Shared\Models\AiAnalysis;
use Shagalni\Shared\Models\Company;
use Shagalni\Shared\Models\CompanyChangeRequest;
use Shagalni\Shared\Models\JobCategory;
use Shagalni\Shared\Models\JobVacancy;
use Shagalni\Shared\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        EloquentBuilder::macro('whereEscapedLike', function (string $column, string $value, string $boolean = 'and') {
            /** @var EloquentBuilder $this */
            $escaped = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $value);

            return $this->whereRaw(
                $this->getModel()->qualifyColumn($column)." like ? escape '!'",
                ["%{$escaped}%"],
                $boolean
            );
        });

        Gate::policy(JobVacancy::class, JobPolicy::class);
        Gate::policy(AiAnalysis::class, AiAnalysisPolicy::class);
        Gate::policy(Application::class, ApplicationPolicy::class);
        Gate::policy(JobCategory::class, JobCategoryPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Company::class, CompanyPolicy::class);
        Gate::policy(CompanyChangeRequest::class, CompanyChangeRequestPolicy::class);

        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn (): ?Password => app()->isProduction()
                ? Password::min(12)
                    ->mixedCase()
                    ->letters()
                    ->numbers()
                    ->symbols()
                    ->uncompromised()
                : null,
        );
    }
}
