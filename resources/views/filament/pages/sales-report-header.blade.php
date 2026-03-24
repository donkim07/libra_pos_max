{{-- <x-filament::section>
    <x-slot name="heading">Filters</x-slot>

    <form wire:submit.prevent="$refresh">
        {{ $this->form }}
    </form>
</x-filament::section> --}}

<x-filament::section>
    <x-slot name="heading">Filters</x-slot>

    {{ $this->form }}
</x-filament::section>
