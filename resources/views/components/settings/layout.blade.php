<div class="grid w-full gap-6 max-md:grid-cols-1 md:grid-cols-[200px_minmax(0,1fr)] md:gap-10">
    <div class="w-full md:sticky md:top-6 md:self-start">
        <flux:navlist aria-label="{{ __('Settings') }}" class="rounded-2xl bg-slate-50 p-2 dark:bg-slate-900">
            <flux:navlist.item :href="route('profile.edit')" wire:navigate>{{ __('Profile') }}</flux:navlist.item>
            <flux:navlist.item :href="route('security.edit')" wire:navigate>{{ __('Security') }}</flux:navlist.item>
            <flux:navlist.item :href="route('appearance.edit')" wire:navigate>{{ __('Appearance') }}</flux:navlist.item>
        </flux:navlist>
    </div>

    <div class="min-w-0">
        <div class="mb-6">
            <flux:heading size="lg">{{ $heading ?? '' }}</flux:heading>
            <flux:subheading>{{ $subheading ?? '' }}</flux:subheading>
        </div>

        <div class="w-full max-w-5xl">
            {{ $slot }}
        </div>
    </div>
</div>
