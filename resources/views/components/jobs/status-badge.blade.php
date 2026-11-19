@props(['status', 'expired' => false])

@php
    $statusValue = $expired ? 'expired' : $status->value;
    $label = \Illuminate\Support\Str::headline($statusValue);
    $classes = match ($statusValue) {
        'active', 'approved' => 'border-indigo-200/80 bg-indigo-50 text-indigo-700 dark:border-indigo-800/40 dark:bg-indigo-950/40 dark:text-indigo-300',
        'closed', 'expired', 'deactivated', 'withdrawn' => 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700/60 dark:bg-slate-800/80 dark:text-slate-300',
        'pending', 'pending_approval', 'archived' => 'border-amber-200/80 bg-amber-50 text-amber-800 dark:border-amber-800/40 dark:bg-amber-950/40 dark:text-amber-300',
        'under_review' => 'border-sky-200/80 bg-sky-50 text-sky-800 dark:border-sky-800/40 dark:bg-sky-950/40 dark:text-sky-300',
        'shortlisted' => 'border-blue-200/80 bg-blue-50 text-blue-800 dark:border-blue-800/40 dark:bg-blue-950/40 dark:text-blue-300',
        'interview' => 'border-purple-200/80 bg-purple-50 text-purple-800 dark:border-purple-800/40 dark:bg-purple-950/40 dark:text-purple-300',
        'rejected', 'suspended' => 'border-rose-200/80 bg-rose-50 text-rose-800 dark:border-rose-800/40 dark:bg-rose-950/40 dark:text-rose-300',
        default => 'border-slate-200 bg-slate-100 text-slate-700 dark:border-slate-700/60 dark:bg-slate-800/80 dark:text-slate-300',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold capitalize ' . $classes]) }}>
    {{ $label }}
</span>
