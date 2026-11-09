<x-jobs.layout title="Dashboard">
    <div class="flex flex-col gap-8">
        <x-jobs.page-heading
            title="Dashboard"
            :description="'You’re signed in, '.auth()->user()->full_name.'. Your job management workspace is ready.'"
        />

        <section aria-label="Job statistics" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-jobs.stat-card label="Total jobs" value="—" description="Workspace data is unavailable to this account." />
            <x-jobs.stat-card label="Active jobs" value="—" description="Workspace data is unavailable to this account." />
            <x-jobs.stat-card label="Pending jobs" value="—" description="Workspace data is unavailable to this account." />
            <x-jobs.stat-card label="Closed / expired" value="—" description="Workspace data is unavailable to this account." />
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="border-b border-slate-100 px-5 py-5 sm:px-6 dark:border-slate-800">
                <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Recently created jobs</h2>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">The latest vacancies added to your workspace.</p>
            </div>

            <div class="px-6 py-14 text-center">
                <span class="mx-auto flex size-12 items-center justify-center rounded-full bg-indigo-50 text-indigo-700 dark:bg-indigo-400/10 dark:text-indigo-300" aria-hidden="true">▤</span>
                <p class="mt-4 text-sm font-semibold text-slate-900 dark:text-slate-100">Job workspace access required</p>
                <p class="mx-auto mt-1 max-w-lg text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Job vacancy data is available to administrators and company owners. Contact your administrator if you need access.
                </p>
            </div>
        </section>
    </div>
</x-jobs.layout>
