<x-jobs.layout title="Dashboard">
    @php
        $totalApplications = number_format($applicationStats['total']);
        $totalApplicants = number_format($applicationStats['applicants']);
    @endphp

    <div class="flex flex-col gap-8">
        <x-jobs.page-heading
            title="Dashboard"
            :description="'Company workspace for '.auth()->user()->full_name.'."
        />

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">My Company</h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div>
                    <p class="text-sm text-slate-500">Company</p>
                    <p class="mt-1 font-medium">{{ $company->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Industry</p>
                    <p class="mt-1 font-medium">{{ $company->industry }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Location</p>
                    <p class="mt-1 font-medium">{{ $company->location }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Website</p>
                    @if ($company->website)
                        <a href="{{ $company->website }}" class="mt-1 block truncate font-medium text-indigo-600 hover:underline">
                            {{ $company->website }}
                        </a>
                    @else
                        <p class="mt-1 font-medium">—</p>
                    @endif
                </div>
            </div>

            @if ($company->description)
                <p class="mt-5 border-t border-slate-100 pt-5 text-sm leading-6 text-slate-600 dark:border-slate-800 dark:text-slate-300">
                    {{ $company->description }}
                </p>
            @endif
        </section>

        <section aria-label="Applicant statistics" class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Total applications</p>
                <p class="mt-2 text-3xl font-semibold">{{ $totalApplications }}</p>
                <p class="mt-2 text-sm text-slate-500">All applications submitted to your jobs.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Applicants</p>
                <p class="mt-2 text-3xl font-semibold">{{ $totalApplicants }}</p>
                <p class="mt-2 text-sm text-slate-500">Unique users who applied to your jobs.</p>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-base font-semibold">Applications by status</h2>
            <p class="mt-1 text-sm text-slate-500">Only applications submitted to your company.</p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($statusDistribution as $status => $total)
                    <div class="rounded-xl bg-slate-50 p-4 dark:bg-slate-800/60">
                        <p class="text-sm text-slate-500">{{ \Illuminate\Support\Str::headline($status) }}</p>
                        <p class="mt-1 text-2xl font-semibold">{{ number_format($total) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section aria-label="Job statistics" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Total jobs</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($jobStats['total']) }}</p>
                <p class="mt-2 text-sm text-slate-500">All jobs in your company workspace.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Active jobs</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($jobStats['active']) }}</p>
                <p class="mt-2 text-sm text-slate-500">Active jobs whose deadline has not passed.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Expired jobs</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($jobStats['expired']) }}</p>
                <p class="mt-2 text-sm text-slate-500">Active jobs whose deadline has passed.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Closed jobs</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($jobStats['closed']) }}</p>
                <p class="mt-2 text-sm text-slate-500">Jobs closed by your company.</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm font-medium text-slate-500">Archived jobs</p>
                <p class="mt-2 text-3xl font-semibold">{{ number_format($jobStats['archived']) }}</p>
                <p class="mt-2 text-sm text-slate-500">Jobs archived by your company.</p>
            </div>
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
