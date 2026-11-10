@props(['status', 'expired' => false])

@php
    $statusValue = $expired ? 'expired' : $status->value;
    $label = \Illuminate\Support\Str::headline($statusValue);
    $classes = match ($statusValue) {
        'active', 'approved' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-950 dark:text-emerald-300 dark:ring-emerald-800',
        'closed', 'expired', 'deactivated' => 'bg-slate-100 text-slate-700 ring-slate-600/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
        'archived', 'pending_approval' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-950 dark:text-amber-300 dark:ring-amber-800',
        'rejected', 'suspended' => 'bg-rose-50 text-rose-700 ring-rose-600/20 dark:bg-rose-950 dark:text-rose-300 dark:ring-rose-800',
        default => 'bg-slate-100 text-slate-700 ring-slate-600/20 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold capitalize ring-1 ring-inset '.$classes]) }}>
    {{ $label }}
</span>