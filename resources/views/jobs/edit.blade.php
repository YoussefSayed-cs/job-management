<x-jobs.layout title="Edit Job">
    <div class="mx-auto max-w-4xl space-y-6">

        <x-jobs.page-heading title="Edit Job" description="Update the information of this job vacancy." />

        <form method="POST" action="{{ route('jobs.update', $job) }}"
            class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf
            @method('PUT')

            @include('jobs._form')
        </form>

    </div>
</x-jobs.layout>