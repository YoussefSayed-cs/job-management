<x-jobs.layout title="Create Job">
    <div class="mx-auto max-w-4xl space-y-6">

        <x-jobs.page-heading
            title="Create Job"
            description="Create and publish a new vacancy for your company."
        />

        <form method="POST" action="{{ route('jobs.store') }}"
              class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @csrf

            @include('jobs._form', ['job' => null])
        </form>

    </div>
</x-jobs.layout>