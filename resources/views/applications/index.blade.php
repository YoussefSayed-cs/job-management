<x-jobs.layout title="Applications">

    <div class="space-y-8">

        <x-jobs.page-heading title="Applications" description="Review applicants who applied to your company's jobs." />

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">

            <x-jobs.stat-card label="Matching Applications" :value="number_format($applications->total())"
                description="Applications matching the current filters." />

        </section>

        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <form method="GET" action="{{ route('applications.index') }}" class="grid gap-4 sm:grid-cols-3">

                <div>
                    <label class="mb-2 block text-sm font-medium">Search</label>

                    <input type="text" name="search" value="{{ $search }}" placeholder="Applicant or job title"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium">Status</label>

                    <select name="status"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950">

                        <option value="">All statuses</option>

                        @foreach ($statuses as $status)
                            <option value="{{ $status->value }}" @selected($statusFilter === $status->value)>
                                {{ \Illuminate\Support\Str::headline($status->value) }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="flex items-end gap-2">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Filter
                    </button>

                    <a href="{{ route('applications.index') }}"
                        class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold dark:border-slate-700">
                        Reset
                    </a>
                </div>

            </form>

        </section>

        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="overflow-x-auto">

                <table class="w-full min-w-[750px] text-left text-sm">

                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800">

                        <tr>
                            <th class="px-5 py-3">Applicant</th>
                            <th class="px-5 py-3">Job</th>
                            <th class="px-5 py-3">Submitted</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3"></th>
                        </tr>

                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                        @forelse ($applications as $application)

                            <tr>

                                <td class="px-5 py-4">

                                    <div class="font-semibold">
                                        {{ $application->user?->full_name ?? 'Unavailable' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $application->user?->email }}
                                    </div>

                                </td>

                                <td class="px-5 py-4">

                                    <div class="font-medium">
                                        {{ $application->job_title }}
                                    </div>

                                    @if (!$application->job_vacancy_id)
                                        <span class="mt-1 inline-block text-xs text-slate-500">
                                            Historical application
                                        </span>
                                    @endif

                                </td>

                                <td class="px-5 py-4 text-slate-500">
                                    {{ $application->submitted_at?->format('M j, Y') }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ \Illuminate\Support\Str::headline($application->status->value) }}
                                </td>

                                <td class="px-5 py-4 text-right">

                                    <a href="{{ route('applications.show', $application) }}"
                                        class="font-semibold text-indigo-600 hover:text-indigo-800">
                                        Review
                                    </a>

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                                    No applications found.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                {{ $applications->links() }}
            </div>

        </section>

    </div>

</x-jobs.layout>