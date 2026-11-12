<x-jobs.layout title="Company Change Requests">
    <div class="space-y-6">
        <x-jobs.page-heading
            title="Company Change Requests"
            description="Request changes to your approved company name or industry. Changes take effect only after Admin approval."
        />

        @if (session('success'))
            <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Current Approved Information</h2>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500">Company Name</p>
                    <p class="font-medium">{{ $company->name }}</p>
                </div>
                <div>
                    <p class="text-sm text-slate-500">Industry</p>
                    <p class="font-medium">{{ $company->industry }}</p>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Submit a Change Request</h2>
            <p class="mt-1 text-sm text-slate-500">
                Fill in only the fields you want to change. Leave the other field empty.
            </p>

            <form method="POST" action="{{ route('my-company.change-requests.store') }}" class="mt-5 space-y-5">
                @csrf

                <div>
                    <label for="new_name" class="mb-2 block text-sm font-medium">New Company Name</label>
                    <input
                        type="text"
                        id="new_name"
                        name="new_name"
                        value="{{ old('new_name') }}"
                        maxlength="255"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                        placeholder="Leave empty if you do not want to change the name"
                    >
                    @error('new_name')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="new_industry" class="mb-2 block text-sm font-medium">New Industry</label>
                    <input
                        type="text"
                        id="new_industry"
                        name="new_industry"
                        value="{{ old('new_industry') }}"
                        maxlength="255"
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"
                        placeholder="Leave empty if you do not want to change the industry"
                    >
                    @error('new_industry')
                        <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end border-t border-slate-200 pt-5 dark:border-slate-700">
                    <button
                        type="submit"
                        class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700"
                    >
                        Submit for Review
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Request History</h2>

            @if ($changeRequests->isEmpty())
                <p class="mt-4 text-sm text-slate-500">You have not submitted any change requests yet.</p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead>
                            <tr class="text-left text-slate-500">
                                <th class="px-3 py-3 font-medium">Requested Name</th>
                                <th class="px-3 py-3 font-medium">Requested Industry</th>
                                <th class="px-3 py-3 font-medium">Status</th>
                                <th class="px-3 py-3 font-medium">Submitted</th>
                                <th class="px-3 py-3 font-medium">Reviewed By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($changeRequests as $changeRequest)
                                <tr>
                                    <td class="px-3 py-3">{{ $changeRequest->new_name ?: '—' }}</td>
                                    <td class="px-3 py-3">{{ $changeRequest->new_industry ?: '—' }}</td>
                                    <td class="px-3 py-3">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                            'bg-amber-50 text-amber-700' => $changeRequest->status->value === 'pending',
                                            'bg-emerald-50 text-emerald-700' => $changeRequest->status->value === 'approved',
                                            'bg-rose-50 text-rose-700' => $changeRequest->status->value === 'rejected',
                                        ])>
                                            {{ str($changeRequest->status->value)->replace('_', ' ')->title() }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3">{{ $changeRequest->created_at?->format('M d, Y') }}</td>
                                    <td class="px-3 py-3">{{ $changeRequest->reviewer?->full_name ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $changeRequests->links() }}
                </div>
            @endif
        </div>

        <div>
            <a href="{{ route('my-company.show') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-700">
                Back to Company Profile
            </a>
        </div>
    </div>
</x-jobs.layout>
