<x-jobs.layout title="{{ $company->name }} analytics">
    <div class="flex flex-col gap-8">
        <x-jobs.page-heading
            title="{{ $company->name }} analytics"
            description="Read-only company-level job and application performance for this workspace."
        />

        <section aria-label="Company overview" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-jobs.stat-card
                label="Total jobs"
                :value="number_format($overview['jobs'])"
                description="All jobs belonging to this company."
            />
            <x-jobs.stat-card
                label="Total applications"
                :value="number_format($overview['applications'])"
                description="All applications connected to this company."
            />
            <x-jobs.stat-card
                label="Unique applicants"
                :value="number_format($overview['applicants'])"
                description="Distinct job seekers who applied."
            />
            <x-jobs.stat-card
                label="Acceptance rate"
                :value="$overview['acceptance_rate'] === null ? '—' : $overview['acceptance_rate'] . '%'"
                description="Accepted ÷ (Accepted + Rejected)."
            />
            <x-jobs.stat-card
                label="Rejection rate"
                :value="$overview['rejection_rate'] === null ? '—' : $overview['rejection_rate'] . '%'"
                description="Rejected ÷ (Accepted + Rejected)."
            />
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <x-analytics.chart-card title="Applications by status" description="Current company application distribution.">
                <x-analytics.status-chart :distribution="$distribution" />
            </x-analytics.chart-card>

            <x-analytics.chart-card title="Applications per job" description="Jobs within this company with the most applications.">
                @if ($jobBreakdown->isEmpty())
                    <div class="rounded-xl border border-dashed border-slate-200 bg-slate-50 p-6 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-800/40 dark:text-slate-400">
                        No jobs have been published for this company yet.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($jobBreakdown as $job)
                            <div class="flex items-center justify-between gap-3 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 dark:border-slate-700 dark:bg-slate-800/40">
                                <span class="truncate font-medium text-slate-700 dark:text-slate-100">{{ $job->title }}</span>
                                <span class="inline-flex min-w-10 justify-center rounded-full bg-indigo-100 px-2 py-0.5 text-xs font-semibold text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-200">
                                    {{ $job->applications_count }}
                                </span>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4">
                        {{ $jobBreakdown->withQueryString()->links() }}
                    </div>
                @endif
            </x-analytics.chart-card>
        </div>
    </div>
</x-jobs.layout>
