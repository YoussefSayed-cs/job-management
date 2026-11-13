<x-jobs.layout title="Category Management">
    <div class="space-y-6">

        <x-jobs.page-heading title="Category Management"
            description="Manage the job categories available across the platform.">
            <x-slot:action>
                <a href="{{ route('categories.create') }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    Add Category
                </a>
            </x-slot:action>
        </x-jobs.page-heading>

        @if ($errors->any())
            <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-700">
                <ul class="list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <div class="border-b border-slate-100 px-5 py-4 dark:border-slate-800">
                <h2 class="font-semibold">Job Categories</h2>
                <p class="mt-1 text-sm text-slate-500">
                    {{ $categories->total() }} categories registered
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500 dark:bg-slate-800">
                        <tr>
                            <th class="px-5 py-3">Category Name</th>
                            <th class="px-5 py-3">Associated Jobs</th>
                            <th class="px-5 py-3">Created At</th>
                            <th class="px-5 py-3 text-right">Actions</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                        @forelse ($categories as $category)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/60">

                                <td class="px-5 py-4 font-semibold">
                                    {{ $category->name }}
                                </td>

                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium dark:bg-slate-800">
                                        {{ $category->job_vacancies_count }}
                                    </span>
                                </td>

                                <td class="px-5 py-4 text-slate-500">
                                    {{ $category->created_at->format('M j, Y') }}
                                </td>

                                <td class="px-5 py-4">
                                    <div class="flex items-center justify-end gap-3">

                                        <a href="{{ route('categories.edit', $category) }}"
                                            class="font-semibold text-indigo-600 hover:text-indigo-800">
                                            Edit
                                        </a>

                                        @if ($category->job_vacancies_count === 0)
                                            <form method="POST" action="{{ route('categories.destroy', $category) }}"
                                                onsubmit="return confirm('Are you sure you want to delete this category?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="font-semibold text-rose-600 hover:text-rose-800">
                                                    Delete
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-slate-400"
                                                title="This category is currently used by jobs.">
                                                In Use
                                            </span>
                                        @endif

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-12 text-center text-slate-500">
                                    No categories found. Create your first category.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-5 py-4 dark:border-slate-800">
                {{ $categories->links() }}
            </div>

        </section>
    </div>
</x-jobs.layout>