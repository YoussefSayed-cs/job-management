<x-jobs.layout title="Create Category">
    <div class="space-y-6">

        <x-jobs.page-heading title="Create Category" description="Add a new category to the platform." />

        <section
            class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <form method="POST" action="{{ route('categories.store') }}" class="space-y-6">
                @include('categories._form', [
                    'category' => null,
                    'submitLabel' => 'Create Category',
                ])
            </form>

        </section>
    </div>
</x-jobs.layout>