@php
    use Illuminate\Support\Str;
@endphp
<x-jobs.layout :title="$user->full_name">
    <div class="space-y-6">
        <x-jobs.page-heading :title="$user->full_name" description="User account details.">
            <x-slot:action>
                <a href="{{ route('users.index') }}"
                    class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                    ← Back to users
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        @if ($errors->has('account'))
            <div role="alert"
                class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                {{ $errors->first('account') }}
            </div>
        @endif

        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Account</h2>
                <x-jobs.status-badge :status="$user->status" />
            </div>

            <dl class="mt-4 grid gap-4 text-sm md:grid-cols-2">
                <div>
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-medium">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Phone</dt>
                    <dd class="font-medium">{{ $user->phone ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Role</dt>
                    <dd class="font-medium">{{ Str::headline($user->role->value) }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Email verified</dt>
                    <dd class="font-medium">{{ $user->email_verified_at?->format('M j, Y') ?? 'Not verified' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Joined</dt>
                    <dd class="font-medium">{{ $user->created_at->format('M j, Y') }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500">Deletion requested</dt>
                    <dd class="font-medium">{{ $user->deletion_requested_at?->format('M j, Y') ?? '—' }}</dd>
                </div>
            </dl>

            @if ($user->company)
                <a href="{{ route('companies.show', $user->company) }}"
                    class="mt-5 inline-block text-sm font-medium text-indigo-600 hover:underline">
                    View company: {{ $user->company->name }} →
                </a>
            @endif
        </section>
    </div>
</x-jobs.layout>