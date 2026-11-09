<x-jobs.layout title="Edit job">
    <div class="flex flex-col gap-7">
        <x-jobs.page-heading
            title="Edit job"
            description="Update the vacancy details and save your changes."
        />

        <form method="POST" action="{{ route('jobs.update', $job) }}" class="flex flex-col gap-6">
            @csrf
            @method('PUT')
            @include('jobs._form')
            <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-5 sm:flex-row sm:justify-end dark:border-slate-800">
                <a href="{{ route('jobs.show', $job) }}" class="rounded-lg border border-slate-300 px-4 py-2.5 text-center text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Save changes</button>
            </div>
        </form>
    </div>
</x-jobs.layout>
