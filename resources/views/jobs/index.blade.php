<x-jobs.layout title="Jobs">
    <div class="flex flex-col gap-7">
        <x-jobs.page-heading
            title="Jobs"
            description="Search, filter, and manage the vacancies in your workspace."
        >
            <x-slot:action>
                <a href="{{ route('jobs.create') }}" class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Create a job
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5 dark:border-slate-800 dark:bg-slate-900">
            <form method="GET" action="{{ route('jobs.index') }}" class="grid gap-3 md:grid-cols-[minmax(12rem,1fr)_12rem_12rem_auto_auto] md:items-end">
                <div>
                    <label for="search" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Search by title</label>
                    <input id="search" name="search" type="search" value="{{ request('search') }}" placeholder="Search job titles" class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500">
                </div>
                <div>
                    <label for="status" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">All statuses</option>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ \Illuminate\Support\Str::headline($status->value) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="category" class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-300">Category</label>
                    <select id="category" name="category" class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                        <option value="">All categories</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(request('category') === $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-white">Apply filters</button>
                <a href="{{ route('jobs.index') }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Clear</a>
            </form>
        </section>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between border-b border-slate-100 px-5 py-4 sm:px-6 dark:border-slate-800">
                <p class="text-sm text-slate-600 dark:text-slate-400"><span class="font-semibold text-slate-900 dark:text-slate-100">{{ $jobs->total() }}</span> {{ \Illuminate\Support\Str::plural('job', $jobs->total()) }}</p>
            </div>

            @if ($jobs->isEmpty())
                <div class="px-6 py-14 text-center">
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">No jobs found</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Try changing your search or filters, or create a new job.</p>
                    <a href="{{ route('jobs.create') }}" class="mt-5 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Create a job</a>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[850px] text-left text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold tracking-wide text-slate-500 uppercase dark:bg-slate-800 dark:text-slate-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Job title</th>
                                <th scope="col" class="px-6 py-3">Company</th>
                                <th scope="col" class="px-6 py-3">Category</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Created</th>
                                <th scope="col" class="px-6 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($jobs as $job)
                                <tr class="hover:bg-slate-50/70 dark:hover:bg-slate-800/60">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('jobs.show', $job) }}" class="font-semibold text-slate-900 hover:text-indigo-700 dark:text-slate-100 dark:hover:text-indigo-300">{{ $job->title }}</a>
                                        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ \Illuminate\Support\Str::headline($job->employment_type->value) }} · {{ \Illuminate\Support\Str::headline($job->work_location_type->value) }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $job->company->name }}</td>
                                    <td class="px-6 py-4 text-slate-600 dark:text-slate-300">{{ $job->category->name }}</td>
                                    <td class="px-6 py-4"><x-jobs.status-badge :status="$job->status" :expired="$job->isExpired()" /></td>
                                    <td class="whitespace-nowrap px-6 py-4 text-slate-500 dark:text-slate-400">{{ $job->created_at->format('M j, Y') }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                            <a href="{{ route('jobs.show', $job) }}" class="font-medium text-indigo-700 hover:text-indigo-900 dark:text-indigo-300 dark:hover:text-indigo-200">View</a>
                                            <a href="{{ route('jobs.edit', $job) }}" class="font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300 dark:hover:text-slate-100">Edit</a>
                                            <form method="POST" action="{{ route('jobs.destroy', $job) }}" onsubmit="return confirm('Delete this job? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="font-medium text-rose-700 hover:text-rose-900">Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($jobs->hasPages())
                    <div class="border-t border-slate-100 px-5 py-4 sm:px-6">
                        {{ $jobs->links() }}
                    </div>
                @endif
            @endif
        </section>
    </div>
</x-jobs.layout>
