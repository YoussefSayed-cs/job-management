<x-jobs.layout title="Dashboard">
    <div class="flex flex-col gap-8">
        <x-jobs.page-heading
            title="Dashboard"
            :description="'You’re signed in, '.auth()->user()->full_name.'. Your job management workspace is ready.'"
        />

        <section aria-label="Job statistics" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <x-jobs.stat-card
                label="Total jobs"
                :value="number_format($jobStats['total'])"
                description="All jobs in your company workspace."
            />
            <x-jobs.stat-card
                label="Active jobs"
                :value="number_format($jobStats['active'])"
                description="Active jobs with no deadline or a deadline that has not passed."
            />
            <x-jobs.stat-card
                label="Expired jobs"
                :value="number_format($jobStats['expired'])"
                description="Active jobs whose application deadline has passed."
            />
            <x-jobs.stat-card
                label="Closed jobs"
                :value="number_format($jobStats['closed'])"
                description="Jobs closed by your company."
            />
            <x-jobs.stat-card
                label="Archived jobs"
                :value="number_format($jobStats['archived'])"
                description="Jobs archived by your company."
            />
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6 dark:border-slate-800">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Recently created jobs</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The latest vacancies added to your workspace.</p>
            </div>

            @forelse ($recentJobs as $job)
                <div class="flex flex-col gap-2 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                    <div>
                        <p class="font-medium text-slate-900 dark:text-slate-100">{{ $job->title }}</p>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                            {{ ucfirst($job->status->value) }}
                        </p>
                    </div>
                    <time
                        class="text-sm text-slate-500 dark:text-slate-400"
                        datetime="{{ $job->created_at->toAtomString() }}"
                    >
                        {{ $job->created_at->format('M j, Y') }}
                    </time>
                </div>
            @empty
                <div class="px-6 py-14 text-center">
                    <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300" aria-hidden="true">▤</span>
                    <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">No jobs yet</p>
                    <p class="mx-auto mt-1 max-w-lg text-sm leading-6 text-slate-500 dark:text-slate-400">
                        Jobs you create will appear here.
                    </p>
                </div>
            @endforelse
        </section>
    </div>
</x-jobs.layout>
