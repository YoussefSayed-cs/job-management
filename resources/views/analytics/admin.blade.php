<x-jobs.layout title="Dashboard">
    <div class="flex flex-col gap-8">
        <x-jobs.page-heading title="Platform overview"
            description="Platform-wide totals, the last 30 days of activity, and how applications are progressing." />

        <section aria-label="Platform totals" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <x-jobs.stat-card label="Users" :value="number_format($totals['users'])"
                description="Excluding anonymized accounts." />
            <x-jobs.stat-card label="Companies" :value="number_format($totals['companies'])"
                description="All registered companies." />
            <x-jobs.stat-card label="Jobs" :value="number_format($totals['jobs'])"
                description="All jobs across the platform." />
            <x-jobs.stat-card label="Applications" :value="number_format($totals['applications'])"
                description="All submitted applications." />
            <x-jobs.stat-card label="Acceptance rate" :value="$totals['acceptance_rate'] === null ? '—' : $totals['acceptance_rate'] . '%'" description="Accepted ÷ (Accepted + Rejected)." />
            <x-jobs.stat-card label="Rejection rate" :value="$totals['rejection_rate'] === null ? '—' : $totals['rejection_rate'] . '%'" description="Rejected ÷ (Accepted + Rejected)." />
        </section>

        <section aria-label="Last 30 days" class="flex flex-col gap-4">
            <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Last 30 days</h2>

            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                <x-jobs.stat-card label="New users" :value="number_format($activity['new_users'])"
                    description="Registrations." />
                <x-jobs.stat-card label="Company registrations"
                    :value="number_format($activity['company_registrations'])" description="New company requests." />
                <x-jobs.stat-card label="Jobs published" :value="number_format($activity['jobs_published'])"
                    description="Published by companies." />
                <x-jobs.stat-card label="Applications submitted"
                    :value="number_format($activity['applications_submitted'])" description="Sent by job seekers." />
                <x-jobs.stat-card label="Status changes" :value="number_format($activity['status_changes'])"
                    description="Application stage updates." />
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-3">
            <x-analytics.chart-card class="lg:col-span-2" title="Applications per day"
                description="Submitted over the last 30 days.">
                <x-analytics.chart type="line" label="Applications submitted" :labels="$dailyLabels"
                    :values="$dailyValues" />
            </x-analytics.chart-card>

            <x-analytics.chart-card title="Applications by status" description="Across the whole platform.">
                <x-analytics.status-chart :distribution="$distribution" />
            </x-analytics.chart-card>
        </div>

        <x-analytics.chart-card title="Top companies" description="Companies with the most applications.">
            <x-analytics.chart type="bar" :horizontal="true" label="Applications" :labels="$topCompanyNames"
                :values="$topCompanyValues" />
        </x-analytics.chart-card>
    </div>
</x-jobs.layout>