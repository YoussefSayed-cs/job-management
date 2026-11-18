<x-jobs.layout title="Dashboard">
    @php
        $totalApplications = number_format($applicationStats['total']);
        $totalApplicants = number_format($applicationStats['applicants']);
        $pendingApplications = number_format($statusDistribution[\Shagalni\Shared\Enums\ApplicationStatus::Pending->value] ?? 0);
    @endphp

    <div class="flex flex-col gap-8">
        {{-- Dashboard Hero --}}
        <x-dashboard.hero
            eyebrow="Company Workspace"
            :title="__('Welcome back, :name', ['name' => auth()->user()->first_name])"
            :subtitle="__('Recruitment overview and vacancy performance for :company.', ['company' => $company->name])"
        >
            <x-slot:actions>
                @can('create', \Shagalni\Shared\Models\JobVacancy::class)
                    <a href="{{ route('jobs.create') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                        <flux:icon.plus class="size-4" />
                        {{ __('Post a Job') }}
                    </a>
                @endcan

                @can('viewAny', \Shagalni\Shared\Models\Application::class)
                    <a href="{{ route('applications.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white">
                        <flux:icon.document-text class="size-4 text-slate-500 dark:text-slate-400" />
                        {{ __('Applications') }}
                    </a>
                @endcan

                @can('viewAny', \Shagalni\Shared\Models\JobVacancy::class)
                    <a href="{{ route('jobs.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white">
                        <flux:icon.briefcase class="size-4 text-slate-500 dark:text-slate-400" />
                        {{ __('All Jobs') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-dashboard.hero>

        {{-- Company Overview Card --}}
        <section aria-label="Company overview" class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white p-5 shadow-xs sm:p-6 dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-700 text-xl font-bold text-white shadow-md shadow-indigo-600/20">
                        {{ strtoupper(substr($company->name, 0, 1)) }}
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <h2 class="text-lg font-bold tracking-tight text-slate-900 truncate dark:text-white">
                                {{ $company->name }}
                            </h2>
                            <x-jobs.status-badge :status="$company->registration_status" />
                        </div>

                        <div class="mt-1.5 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                            @if ($company->industry)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.tag class="size-3.5 text-slate-400 dark:text-slate-500" />
                                    {{ $company->industry }}
                                </span>
                            @endif

                            @if ($company->location)
                                <span class="inline-flex items-center gap-1">
                                    <flux:icon.map-pin class="size-3.5 text-slate-400 dark:text-slate-500" />
                                    {{ $company->location }}
                                </span>
                            @endif

                            @if ($company->website)
                                <a href="{{ $company->website }}" target="_blank" rel="noopener noreferrer"
                                    class="inline-flex items-center gap-1 font-medium text-indigo-600 hover:text-indigo-700 hover:underline dark:text-indigo-400">
                                    <flux:icon.globe-alt class="size-3.5" />
                                    {{ parse_url($company->website, PHP_URL_HOST) ?? $company->website }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="shrink-0 pt-2 sm:pt-0">
                    <a href="{{ route('my-company.show') }}" wire:navigate
                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                        {{ __('Manage Company Profile') }}
                        <flux:icon.chevron-right class="size-3.5" />
                    </a>
                </div>
            </div>
        </section>

        {{-- Company Key Statistics --}}
        <x-dashboard.section
            :title="__('Recruitment Metrics')"
            :description="__('Application funnel and candidate engagement across all company jobs.')"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card
                    :label="__('Total applications')"
                    :value="$totalApplications"
                    :description="__('All applications submitted to your jobs.')"
                    icon="document-text"
                    accent="indigo"
                />

                <x-dashboard.stat-card
                    :label="__('Unique applicants')"
                    :value="$totalApplicants"
                    :description="__('Distinct candidates who applied.')"
                    icon="users"
                    accent="sky"
                />

                <x-dashboard.stat-card
                    :label="__('Total jobs')"
                    :value="number_format($jobStats['total'])"
                    :description="__('All jobs in your workspace.')"
                    icon="briefcase"
                    accent="violet"
                />

                <x-dashboard.stat-card
                    :label="__('Pending review')"
                    :value="$pendingApplications"
                    :description="__('Awaiting initial screening.')"
                    icon="clock"
                    accent="amber"
                />
            </div>
        </x-dashboard.section>

        {{-- Job Status Overview --}}
        <x-dashboard.section
            :title="__('Job Status Breakdown')"
            :description="__('Current status distribution of jobs published under :company.', ['company' => $company->name])"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <x-dashboard.stat-card
                    :label="__('Active jobs')"
                    :value="number_format($jobStats['active'])"
                    :description="__('Open and accepting applications.')"
                    icon="check-circle"
                    accent="emerald"
                />

                <x-dashboard.stat-card
                    :label="__('Expired jobs')"
                    :value="number_format($jobStats['expired'])"
                    :description="__('Application deadline has passed.')"
                    icon="clock"
                    accent="slate"
                />

                <x-dashboard.stat-card
                    :label="__('Closed jobs')"
                    :value="number_format($jobStats['closed'])"
                    :description="__('Manually closed vacancies.')"
                    icon="no-symbol"
                    accent="slate"
                />

                <x-dashboard.stat-card
                    :label="__('Archived jobs')"
                    :value="number_format($jobStats['archived'])"
                    :description="__('Stored in company archive.')"
                    icon="archive-box"
                    accent="amber"
                />
            </div>
        </x-dashboard.section>

        {{-- Analytics Visualizations --}}
        <div class="grid gap-6 xl:grid-cols-2">
            <x-analytics.chart-card
                :title="__('Applications by status')"
                :description="__('Distribution of applications submitted to your company.')"
            >
                <x-analytics.status-chart :distribution="$statusDistribution" />
            </x-analytics.chart-card>

            <x-analytics.chart-card
                :title="__('Jobs by status')"
                :description="__('Active jobs exclude those whose application deadline has passed.')"
            >
                <x-analytics.chart
                    type="bar"
                    label="Jobs by status"
                    :labels="$jobStatusChart['labels']"
                    :values="$jobStatusChart['values']"
                    :colors="['#4f46e5', '#94a3b8', '#64748b', '#f59e0b']"
                />
            </x-analytics.chart-card>
        </div>

        {{-- Recently Created Jobs --}}
        <section class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-xs dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-semibold tracking-tight text-slate-900 dark:text-white">
                        {{ __('Recently created jobs') }}
                    </h2>
                    <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                        {{ __('Latest additions across your workspace.') }}
                    </p>
                </div>

                @can('viewAny', \Shagalni\Shared\Models\JobVacancy::class)
                    <a href="{{ route('jobs.index') }}" wire:navigate
                        class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400 dark:hover:text-indigo-300">
                        {{ __('View all jobs') }}
                        <flux:icon.chevron-right class="size-3" />
                    </a>
                @endcan
            </div>

            @if ($recentJobs->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200/80 text-left dark:divide-slate-800">
                        <thead class="bg-slate-50/75 dark:bg-slate-800/40">
                            <tr>
                                <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6 dark:text-slate-400">
                                    {{ __('Job') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6 dark:text-slate-400">
                                    {{ __('Status') }}
                                </th>
                                <th scope="col" class="px-5 py-3 text-xs font-semibold uppercase tracking-wider text-slate-500 sm:px-6 dark:text-slate-400">
                                    {{ __('Created') }}
                                </th>
                                <th scope="col" class="relative px-5 py-3 sm:px-6">
                                    <span class="sr-only">{{ __('Actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200/80 dark:divide-slate-800">
                            @foreach ($recentJobs as $job)
                                <tr class="transition-colors hover:bg-slate-50/80 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-3.5 sm:px-6">
                                        @can('view', $job)
                                            <a href="{{ route('jobs.show', $job) }}" wire:navigate
                                                class="font-medium text-slate-900 transition hover:text-indigo-600 dark:text-white dark:hover:text-indigo-400">
                                                {{ $job->title }}
                                            </a>
                                        @else
                                            <p class="font-medium text-slate-900 dark:text-white">
                                                {{ $job->title }}
                                            </p>
                                        @endcan
                                    </td>
                                    <td class="px-5 py-3.5 sm:px-6">
                                        <x-jobs.status-badge :status="$job->status" />
                                    </td>
                                    <td class="px-5 py-3.5 text-xs text-slate-500 sm:px-6 dark:text-slate-400">
                                        <time datetime="{{ $job->created_at->toAtomString() }}">
                                            {{ $job->created_at->format('M j, Y') }}
                                        </time>
                                    </td>
                                    <td class="px-5 py-3.5 text-end text-xs font-medium sm:px-6">
                                        @can('view', $job)
                                            <a href="{{ route('jobs.show', $job) }}" wire:navigate
                                                class="inline-flex items-center gap-1 text-slate-500 transition hover:text-indigo-600 dark:text-slate-400 dark:hover:text-indigo-300">
                                                {{ __('View') }}
                                                <flux:icon.arrow-right class="size-3" />
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-6">
                    <x-dashboard.empty-state
                        :title="__('No jobs yet')"
                        :description="__('Jobs you create will appear here.')"
                        icon="briefcase"
                        :action-text="auth()->user()->can('create', \Shagalni\Shared\Models\JobVacancy::class) ? __('Post your first job') : null"
                        :action-url="auth()->user()->can('create', \Shagalni\Shared\Models\JobVacancy::class) ? route('jobs.create') : null"
                        action-icon="plus"
                    />
                </div>
            @endif
        </section>
    </div>
</x-jobs.layout>
