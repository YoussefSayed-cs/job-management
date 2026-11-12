<x-jobs.layout title="Company Change Requests">
    <x-jobs.page-heading>
        Company Change Requests
    </x-jobs.page-heading>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-green-100 p-4 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 rounded-lg bg-red-100 p-4 text-red-800">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('company-change-requests.index') }}" class="mb-6 flex items-center gap-3">

        <label for="status">Filter by status:</label>

        <select name="status" id="status" onchange="this.form.submit()" class="rounded-lg border-gray-300">

            <option value="pending" @selected($selectedStatus === 'pending')>
                Pending
            </option>

            <option value="approved" @selected($selectedStatus === 'approved')>
                Approved
            </option>

            <option value="rejected" @selected($selectedStatus === 'rejected')>
                Rejected
            </option>

            <option value="all" @selected($selectedStatus === 'all')>
                All
            </option>
        </select>
    </form>

    <div class="overflow-x-auto rounded-lg border">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="p-3">Company</th>
                    <th class="p-3">Owner</th>
                    <th class="p-3">Current Name</th>
                    <th class="p-3">Proposed Name</th>
                    <th class="p-3">Current Industry</th>
                    <th class="p-3">Proposed Industry</th>
                    <th class="p-3">Submitted At</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($changeRequests as $changeRequest)
                    <tr class="border-t">
                        <td class="p-3">
                            {{ $changeRequest->company->name }}
                        </td>

                        <td class="p-3">
                            {{ $changeRequest->company->owner->full_name ?? 'N/A' }}
                        </td>

                        <td class="p-3">
                            {{ $changeRequest->company->name }}
                        </td>

                        <td class="p-3">
                            {{ $changeRequest->new_name ?? '—' }}
                        </td>

                        <td class="p-3">
                            {{ $changeRequest->company->industry }}
                        </td>

                        <td class="p-3">
                            {{ $changeRequest->new_industry ?? '—' }}
                        </td>

                        <td class="p-3">
                            {{ $changeRequest->created_at->format('Y-m-d H:i') }}
                        </td>

                        <td class="p-3">
                            {{ ucfirst($changeRequest->status->value) }}
                        </td>

                        <td class="p-3">
                            @if ($changeRequest->status === \App\Enums\ChangeRequestStatus::Pending)
                                <div class="flex gap-2">
                                    <form method="POST" action="{{ route('company-change-requests.approve', $changeRequest) }}"
                                        onsubmit="return confirm('Approve this change request?')">
                                        @csrf

                                        <button type="submit" class="rounded bg-green-600 px-3 py-2 text-white">
                                            Approve
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('company-change-requests.reject', $changeRequest) }}"
                                        onsubmit="return confirm('Reject this change request?')">
                                        @csrf

                                        <button type="submit" class="rounded bg-red-600 px-3 py-2 text-white">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div>
                                    <p>Reviewed by:
                                        {{ $changeRequest->reviewer->full_name ?? 'N/A' }}
                                    </p>

                                    <p>
                                        {{ $changeRequest->reviewed_at?->format('Y-m-d H:i') ?? '—' }}
                                    </p>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="p-6 text-center text-gray-500">
                            No change requests found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $changeRequests->links() }}
    </div>
</x-jobs.layout>