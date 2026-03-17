<x-layouts::app.sidebar :title="$title ?? null">
    <livewire:toast />
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
