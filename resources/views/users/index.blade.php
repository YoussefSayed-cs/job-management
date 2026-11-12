<x-jobs.layout title="User Management">
    <div class="space-y-6">
        <x-jobs.page-heading title="User Management" description="Manage company owners and job seekers.">
            <x-slot:action>
                @can('create', \App\Models\User::class)
                    <a href="{{ route('company-owners.create') }}"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Invite Company Owner
                    </a>
                @endcan
            </x-slot:action>
        </x-jobs.page-heading>
        {{-- Filters --}}
        <section
            class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <form method="GET" action="{{ route('users.index') }}" class="grid gap-4 md:grid-cols-4">

                <div class="md:col-span-2">
                    <label for="search" class="mb-2 block text-sm font-medium">
                        Search users
                    </label>
                    <input id="search" type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                        placeholder="Name or email..."
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>

                <div>
                    <label for="role" class="mb-2 block text-sm font-medium">
                        Role
                    </label>
                    <select id="role" name="role"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All roles</option>
                        <option value="company_owner" @selected(($filters['role'] ?? '') === 'company_owner')>
                            Company Owner
                        </option>
                        <option value="job_seeker" @selected(($filters['role'] ?? '') === 'job_seeker')>
                            Job Seeker
                        </option>
                    </select>
                </div>

                <div>
                    <label for="status" class="mb-2 block text-sm font-medium">
                        Status
                    </label>
                    <select id="status" name="status"
                        class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">All statuses</option>

                        @foreach (\App\Enums\AccountStatus::cases() as $status)
                            <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                                {{ ucfirst($status->value) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-wrap items-center gap-3 md:col-span-4">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Apply Filters
                    </button>

                    <a href="{{ route('users.index') }}"
                        class="text-sm font-medium text-slate-600 hover:text-slate-900 dark:text-slate-300">
                        Clear Filters
                    </a>
                </div>
            </form>
        </section>

        {{-- Users Table --}}
        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h2 class="font-semibold">Users</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $users->total() }} users found
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-sm">
                    <thead
                        class="bg-slate-50 text-xs font-semibold uppercase text-slate-500 dark:bg-slate-800 dark:text-slate-400">
                        <tr>
                            <th class="px-6 py-3">Name</th>
                            <th class="px-6 py-3">Email</th>
                            <th class="px-6 py-3">Role</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3">Joined</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($users as $user)
                            <td class="px-6 py-4 font-medium">
                                <a href="{{ route('users.show', $user) }}" class="hover:underline">
                                    {{ $user->full_name }}
                                </a>
                            </td>

                            <td class="px-6 py-4 text-slate-600 dark:text-slate-300">
                                {{ $user->email }}
                            </td>

                            <td class="px-6 py-4">
                                {{ $user->role->value === 'company_owner' ? 'Company Owner' : 'Job Seeker' }}
                            </td>

                            <td class="px-6 py-4">
                                <x-jobs.status-badge :status="$user->status" />
                            </td>

                            <td class="px-6 py-4 text-slate-500">
                                {{ $user->created_at->format('M j, Y') }}
                            </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                {{ $users->links() }}
            </div>
        </section>
    </div>
</x-jobs.layout>