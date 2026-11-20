<x-jobs.layout title="Notifications">
    <div class="space-y-6">
        <x-jobs.page-heading
            title="Notifications"
            description="Updates about your company and applications."
        >
            <x-slot:action>
                @if (auth()->user()->unreadNotifications()->exists())
                    <form method="POST" action="{{ route('notifications.read-all') }}">
                        @csrf
                        <button type="submit"
                            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </x-slot:action>
        </x-jobs.page-heading>

        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
            @forelse ($notifications as $notification)
                <form method="POST" action="{{ route('notifications.read', $notification->id) }}"
                    class="border-b border-slate-100 last:border-b-0 dark:border-slate-800">
                    @csrf
                    <button type="submit"
                        class="flex w-full items-start gap-4 px-5 py-4 text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 {{ $notification->read_at === null ? 'font-semibold' : '' }}">
                        <span class="mt-1.5 size-2 shrink-0 rounded-full {{ $notification->read_at === null ? 'bg-indigo-600' : 'bg-transparent' }}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm text-slate-900 dark:text-slate-100">
                                {{ $notification->data['message'] ?? \Illuminate\Support\Str::headline(class_basename($notification->type)) }}
                            </span>
                            @if (! empty($notification->data['job_title']))
                                <span class="mt-1 block text-xs text-slate-500">
                                    {{ $notification->data['job_title'] }}
                                </span>
                            @endif
                        </span>
                        <span class="shrink-0 text-xs text-slate-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </button>
                </form>
            @empty
                <p class="px-5 py-12 text-center text-sm text-slate-500">
                    No notifications yet.
                </p>
            @endforelse
        </section>

        {{ $notifications->links() }}
    </div>
</x-jobs.layout>
