<x-jobs.layout title="Dashboard">
    <div class="flex flex-col gap-8">
        <x-jobs.page-heading
            title="Dashboard"
            description="A clear overview of your job vacancies and recent activity."
        >
            <x-slot:action>
                <a href="{{ route('jobs.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Create a job
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        <section aria-label="Job statistics" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-jobs.stat-card label="Total jobs" :value="$totalJobs" description="All vacancies in your workspace." />
            <x-jobs.stat-card label="Active jobs" :value="$activeJobs" description="Published and not past their application deadline." />
            <x-jobs.stat-card label="Pending jobs" :value="$pendingJobs" description="The current job status model has no pending status." />
            <x-jobs.stat-card label="Closed / expired" :value="$closedJobs" description="Closed, archived, or past their application deadline." />
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex flex-col gap-3 border-b border-slate-100 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 dark:border-slate-800">
                <div>
                    <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Recently created jobs</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The latest vacancies added to your workspace.</p>
                </div>
                <a href="{{ route('jobs.index') }}" class="text-sm font-semibold text-indigo-700 hover:text-indigo-800 dark:text-indigo-300 dark:hover:text-indigo-200">View all jobs</a>
            </div>

            @if ($recentJobs->isEmpty())
                <div class="px-6 py-14 text-center">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">No jobs yet</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Create your first job to see it here.</p>
                    <a href="{{ route('jobs.create') }}" class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create a job</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[700px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase dark:bg-slate-800 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Job</th>
                                <th scope="col" class="px-6 py-3">Company</th>
                                <th scope="col" class="px-6 py-3">Category</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($recentJobs as $job)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('jobs.show', $job) }}" class="font-semibold text-slate-900 hover:text-indigo-700 dark:text-slate-100 dark:hover:text-indigo-300">{{ $job->title }}</a>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $job->company->name }}</td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $job->category->name }}</td>
                                    <td class="px-6 py-4"><x-jobs.status-badge :status="$job->status" :expired="$job->isExpired()" /></td>
                                    <td class="px-6 py-4 text-slate-500 dark:text-slate-400">{{ $job->created_at->format('M j, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-jobs.layout>
