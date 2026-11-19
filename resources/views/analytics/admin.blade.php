<x-jobs.layout title="Dashboard">
    <div class="flex flex-col gap-8">
        {{-- Dashboard Hero --}}
        <x-dashboard.hero
            eyebrow="Platform overview"
            :title="__('Welcome back, :name', ['name' => auth()->user()->first_name])"
            :subtitle="__('Platform-wide totals, the last 30 days of activity, and how applications are progressing across the platform.')"
        >
            <x-slot:actions>
                @can('viewAny', \Shagalni\Shared\Models\User::class)
                    <a href="{{ route('users.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white">
                        <flux:icon.users class="size-4 text-slate-500 dark:text-slate-400" />
                        {{ __('Manage Users') }}
                    </a>
                @endcan

                @can('viewAny', \Shagalni\Shared\Models\Company::class)
                    <a href="{{ route('companies.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white">
                        <flux:icon.building-office-2 class="size-4 text-slate-500 dark:text-slate-400" />
                        {{ __('Companies') }}
                    </a>
                @endcan

                @can('viewAny', \Shagalni\Shared\Models\CompanyChangeRequest::class)
                    <a href="{{ route('company-change-requests.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-xs transition hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white">
                        <flux:icon.clipboard-document-check class="size-4 text-slate-500 dark:text-slate-400" />
                        {{ __('Change Requests') }}
                    </a>
                @endcan

                @can('viewAny', \Shagalni\Shared\Models\JobVacancy::class)
                    <a href="{{ route('jobs.index') }}" wire:navigate
                        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-3.5 py-2 text-xs font-semibold text-white shadow-xs transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 dark:bg-indigo-500 dark:hover:bg-indigo-600">
                        <flux:icon.briefcase class="size-4" />
                        {{ __('All Jobs') }}
                    </a>
                @endcan
            </x-slot:actions>
        </x-dashboard.hero>

        {{-- Platform Totals --}}
        <x-dashboard.section
            :title="__('Platform Totals')"
            :description="__('All-time cumulative statistics across the platform.')"
            aria-label="Platform totals"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <x-dashboard.stat-card
                    :label="__('Users')"
                    :value="number_format($totals['users'])"
                    :description="__('Excluding anonymized accounts.')"
                    icon="users"
                    accent="indigo"
                />

                <x-dashboard.stat-card
                    :label="__('Companies')"
                    :value="number_format($totals['companies'])"
                    :description="__('All registered companies.')"
                    icon="building-office-2"
                    accent="sky"
                />

                <x-dashboard.stat-card
                    :label="__('Jobs')"
                    :value="number_format($totals['jobs'])"
                    :description="__('All jobs across platform.')"
                    icon="briefcase"
                    accent="indigo"
                />

                <x-dashboard.stat-card
                    :label="__('Applications')"
                    :value="number_format($totals['applications'])"
                    :description="__('All submitted applications.')"
                    icon="document-text"
                    accent="amber"
                />

                <x-dashboard.stat-card
                    :label="__('Acceptance rate')"
                    :value="$totals['acceptance_rate'] === null ? '—' : $totals['acceptance_rate'] . '%'"
                    :description="__('Accepted ÷ (Accepted + Rejected).')"
                    icon="check-circle"
                    accent="emerald"
                />

                <x-dashboard.stat-card
                    :label="__('Rejection rate')"
                    :value="$totals['rejection_rate'] === null ? '—' : $totals['rejection_rate'] . '%'"
                    :description="__('Rejected ÷ (Accepted + Rejected).')"
                    icon="x-circle"
                    accent="rose"
                />
            </div>
        </x-dashboard.section>

        {{-- Last 30 Days Activity --}}
        <x-dashboard.section
            :title="__('Last 30 days')"
            :badge="__('Velocity')"
            :description="__('Rolling 30-day activity metrics reflecting recent platform momentum.')"
            aria-label="Last 30 days"
        >
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
                <x-dashboard.stat-card
                    :label="__('New users')"
                    :value="number_format($activity['new_users'])"
                    :description="__('New member registrations.')"
                    icon="user-plus"
                    accent="indigo"
                />

                <x-dashboard.stat-card
                    :label="__('Company registrations')"
                    :value="number_format($activity['company_registrations'])"
                    :description="__('New company onboarding requests.')"
                    icon="building-office"
                    accent="sky"
                />

                <x-dashboard.stat-card
                    :label="__('Jobs published')"
                    :value="number_format($activity['jobs_published'])"
                    :description="__('New openings posted.')"
                    icon="briefcase"
                    accent="indigo"
                />

                <x-dashboard.stat-card
                    :label="__('Applications submitted')"
                    :value="number_format($activity['applications_submitted'])"
                    :description="__('Candidate applications sent.')"
                    icon="paper-airplane"
                    accent="amber"
                />

                <x-dashboard.stat-card
                    :label="__('Status changes')"
                    :value="number_format($activity['status_changes'])"
                    :description="__('Application pipeline transitions.')"
                    icon="arrow-path"
                    accent="emerald"
                />
            </div>
        </x-dashboard.section>

        {{-- Analytics Visualizations --}}
        <x-dashboard.section
            :title="__('Platform Analytics')"
            :description="__('Trends, application funnel distribution, and top participating employers.')"
        >
            <div class="flex flex-col gap-6">
                <div class="grid gap-6 lg:grid-cols-3">
                    <x-analytics.chart-card
                        class="lg:col-span-2"
                        :title="__('Applications per day')"
                        :description="__('Submitted applications over the last 30 days.')"
                    >
                        <x-analytics.chart
                            type="line"
                            label="Applications submitted"
                            :labels="$dailyLabels"
                            :values="$dailyValues"
                        />
                    </x-analytics.chart-card>

                    <x-analytics.chart-card
                        :title="__('Applications by status')"
                        :description="__('Current stage distribution across all submitted applications.')"
                    >
                        <x-analytics.status-chart :distribution="$distribution" />
                    </x-analytics.chart-card>
                </div>

                <x-analytics.chart-card
                    :title="__('Top companies')"
                    :description="__('Employers receiving the highest volume of applications.')"
                >
                    <x-analytics.chart
                        type="bar"
                        :horizontal="true"
                        label="Applications"
                        :labels="$topCompanyNames"
                        :values="$topCompanyValues"
                    />
                </x-analytics.chart-card>
            </div>
        </x-dashboard.section>
    </div>
</x-jobs.layout>
