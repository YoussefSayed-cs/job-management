<x-jobs.layout title="Dashboard">
    @php
        $totalApplications = number_format($applicationStats['total']);
        $totalApplicants = number_format($applicationStats['applicants']);
    @endphp

    <div class="flex flex-col gap-8">
        <x-jobs.page-heading
            title="Dashboard"
            :description="'Company workspace for '.auth()->user()->full_name.'.'"
        />

        <section aria-label="Company overview" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Total applications</p>
                <p class="mt-2 text-3xl font-semibold">{{ $totalApplications }}</p>
                <p class="mt-2 text-sm text-slate-500">All applications submitted to your jobs.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Applicants</p>
                <p class="mt-2 text-3xl font-semibold">{{ $totalApplicants }}</p>
                <p class="mt-2 text-sm text-slate-500">Unique applicants across your company.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Total jobs</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($jobStats['total']) }}</p>
                <p class="mt-2 text-sm text-slate-500">All jobs in your workspace.</p>
            </div>
        </section>

        <div class="grid gap-6 xl:grid-cols-2">
            <x-analytics.chart-card
                title="Applications by status"
                description="Distribution of applications submitted to your company."
            >
                <x-analytics.status-chart :distribution="$statusDistribution" />
            </x-analytics.chart-card>

            <x-analytics.chart-card
                title="Jobs by status"
                description="Active jobs exclude those whose application deadline has passed."
            >
                <x-analytics.chart
                    type="bar"
                    label="Jobs by status"
                    :labels="$jobStatusChart['labels']"
                    :values="$jobStatusChart['values']"
                    :colors="['#22c55e']"
                />
            </x-analytics.chart-card>
        </div>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-5 py-5 sm:px-6 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Recently created jobs</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Latest additions across your workspace.</p>
                </div>
            </div>

            @if ($recentJobs->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left dark:divide-slate-800">
                        <thead class="bg-slate-50 dark:bg-slate-800/50">
                            <tr>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Job</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Status</th>
                                <th class="px-5 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500 sm:px-6">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($recentJobs as $job)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-5 py-4 sm:px-6">
                                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $job->title }}</p>
                                    </td>
                                    <td class="px-5 py-4 sm:px-6">
                                        <span class="inline-flex rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200">
                                            {{ ucfirst($job->status->value) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-sm text-slate-500 sm:px-6 dark:text-slate-400">
                                        <time datetime="{{ $job->created_at->toAtomString() }}">
                                            {{ $job->created_at->format('M j, Y') }}
                                        </time>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300" aria-hidden="true">▤</span>
                    <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">No jobs yet</p>
                    <p class="mx-auto mt-1 max-w-lg text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Jobs you create will appear here.
                    </p>
                </div>
            @endif
        </section>
    </div>
</x-jobs.layout>
