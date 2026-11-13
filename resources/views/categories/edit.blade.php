<x-jobs.layout title="Edit Category">
    <div class="space-y-6">

        <x-jobs.page-heading title="Edit Category" description="Update the selected job category." />

        <section
            class="max-w-2xl rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">

            <form method="POST" action="{{ route('categories.update', $category) }}" class="space-y-6">

                @method('PUT')

                @include('categories._form', ['category' => $category, 'submitLabel' => 'Save Changes',])

            </form>

        </section>
    </div>
</x-jobs.layout>