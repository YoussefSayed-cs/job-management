<x-jobs.layout title="Application Review">

    <div class="space-y-8">

        <x-jobs.page-heading
            title="Application Review"
            description="Review applicant information, resume and application history."
        >
            <x-slot:action>
                <a href="{{ route('applications.index') }}"
                   class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold dark:border-slate-700">
                    Back to Applications
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200">
                {{ session('status') }}
            </div>
        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Applicant Information</h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500">Full Name</p>
                    <p class="mt-1 font-medium">
                        {{ $application->user?->full_name ?? 'Unavailable' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Email</p>
                    <p class="mt-1 font-medium">
                        {{ $application->user?->email ?? 'Unavailable' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Phone</p>
                    <p class="mt-1 font-medium">
                        {{ $application->user?->phone ?? 'Not provided' }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Job Applied For</p>
                    <p class="mt-1 font-medium">{{ $application->job_title }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Application Information</h2>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <p class="text-sm text-slate-500">Current Status</p>
                    <p class="mt-1 font-semibold">
                        {{ \Illuminate\Support\Str::headline($application->status->value) }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Submitted At</p>
                    <p class="mt-1 font-medium">
                        {{ $application->submitted_at?->format('M j, Y h:i A') }}
                    </p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Company</p>
                    <p class="mt-1 font-medium">{{ $application->company_name }}</p>
                </div>

                <div>
                    <p class="text-sm text-slate-500">Job Record</p>
                    <p class="mt-1 font-medium">
                        {{ $application->jobVacancy ? 'Available' : 'Permanently deleted' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Resume Snapshot</h2>

            @if ($application->resumeSnapshot)
                <div class="mt-4 flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="font-medium">
                            {{ $application->resumeSnapshot->original_name }}
                        </p>
                        <p class="mt-1 text-sm text-slate-500">
                            The resume preserved when this application was submitted.
                        </p>
                    </div>

                    <a href="{{ route('applications.resume.download', $application) }}"
                       class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Download Resume
                    </a>
                </div>
            @else
                <p class="mt-4 text-sm text-slate-500">
                    No Resume Snapshot is available.
                </p>
            @endif
        </section>

        {{-- AI integration will be completed in AI Resume Processing module. --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">AI Resume Analysis</h2>
            <p class="mt-3 text-sm text-slate-500">
                AI analysis will be available after the AI Resume Processing module is implemented.
            </p>
        </section>

        @if ($availableStatuses && $application->job_vacancy_id)

            <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-lg font-semibold">Update Application Status</h2>

                <form method="POST"
                      action="{{ route('applications.status.update', $application) }}"
                      class="mt-5 space-y-5">

                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="status" class="mb-2 block text-sm font-medium">
                            New Status
                        </label>

                        <select name="status" id="status" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">

                            @foreach ($availableStatuses as $status)
                                <option value="{{ $status->value }}"
                                    @selected(old('status', $availableStatuses[0]->value) === $status->value)>
                                    {{ \Illuminate\Support\Str::headline($status->value) }}
                                </option>
                            @endforeach
                        </select>

                        @error('status')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="message_template" class="mb-2 block text-sm font-medium">
                            Message Template
                        </label>

                        <select id="message_template"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">

                            <option value="">Choose a template (optional)</option>

                            @foreach ($messageTemplates as $statusKey => $templates)
                                @foreach ($templates as $template)
                                    <option
                                        value="{{ $statusKey }}"
                                        data-message="{{ $template['message'] }}">
                                        {{ $template['label'] }}
                                    </option>
                                @endforeach
                            @endforeach
                        </select>

                        <p class="mt-2 text-xs text-slate-500">
                            Choose a predefined message, then edit it before sending.
                        </p>
                    </div>

                    <div>
                        <label for="message" class="mb-2 block text-sm font-medium">
                            Status Message
                        </label>

                        <textarea name="message"
                                  id="message"
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Write a message for the applicant..."
                                  class="w-full rounded-lg border border-slate-300 px-3 py-2.5 text-sm dark:border-slate-700 dark:bg-slate-950">{{ old('message') }}</textarea>

                        @error('message')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit"
                            class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                        Update Status
                    </button>
                </form>
            </section>

        @elseif (!$application->job_vacancy_id)

            <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 dark:border-amber-900 dark:bg-amber-950">
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    This is a historical application. Its original Job has been permanently deleted, so its status cannot be changed here.
                </p>
            </section>

        @else

            <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500">
                    This application has reached a final status. No further changes are allowed.
                </p>
            </section>

        @endif

        <section class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-lg font-semibold">Status History</h2>

            <div class="mt-6 space-y-6">
                @forelse ($application->statusHistories as $history)
                    <div class="border-l-2 border-indigo-500 pl-4">
                        <p class="font-semibold">
                            @if ($history->from_status)
                                {{ \Illuminate\Support\Str::headline($history->from_status->value) }} →
                            @else
                                Initial Status:
                            @endif

                            {{ \Illuminate\Support\Str::headline($history->to_status->value) }}
                        </p>

                        @if ($history->message)
                            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                {{ $history->message }}
                            </p>
                        @endif

                        <p class="mt-2 text-xs text-slate-500">
                            {{ $history->created_at?->format('M j, Y h:i A') }}
                            ·
                            {{ $history->changedBy?->full_name ?? 'System' }}
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">
                        No status history available.
                    </p>
                @endforelse
            </div>
        </section>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const templateSelect = document.getElementById('message_template');
            const messageField = document.getElementById('message');
            const statusSelect = document.getElementById('status');

            if (!templateSelect || !messageField || !statusSelect) {
                return;
            }

            templateSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];

                if (selectedOption.dataset.message) {
                    messageField.value = selectedOption.dataset.message;
                }
            });

            statusSelect.addEventListener('change', function () {
                const selectedTemplate = templateSelect.options[
                    templateSelect.selectedIndex
                ];

                if (
                    selectedTemplate.dataset.status &&
                    selectedTemplate.dataset.status !== this.value
                ) {
                    templateSelect.value = '';
                }
            });
        });
    </script>

</x-jobs.layout>
