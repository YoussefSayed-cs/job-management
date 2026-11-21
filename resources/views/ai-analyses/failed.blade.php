<x-jobs.layout title="Failed AI analyses">
    <div class="space-y-6">
        <x-jobs.page-heading
            title="Failed AI analyses"
            description="Retry analyses that failed. Scores and feedback are not shown to admins."
        />

        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
                {{ session('status') }}
            </div>
        @endif

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Job</th>
                            <th class="px-6 py-3">Company</th>
                            <th class="px-6 py-3">Reason</th>
                            <th class="px-6 py-3">Attempts</th>
                            <th class="px-6 py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($analyses as $analysis)
                            <tr>
                                <td class="px-6 py-4 font-medium">{{ $analysis->application?->job_title ?? 'Unavailable' }}</td>
                                <td class="px-6 py-4">{{ $analysis->application?->company_name ?? 'Unavailable' }}</td>
                                <td class="max-w-md whitespace-normal px-6 py-4 text-slate-600 dark:text-slate-300">
                                    {{ $analysis->failure_reason }}
                                </td>
                                <td class="px-6 py-4">{{ $analysis->attempts }}</td>
                                <td class="px-6 py-4">
                                    <form method="POST" action="{{ route('ai-analyses.retry', $analysis) }}">
                                        @csrf
                                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                                            Retry
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    No failed analyses.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                {{ $analyses->links() }}
            </div>
        </section>
    </div>
</x-jobs.layout>