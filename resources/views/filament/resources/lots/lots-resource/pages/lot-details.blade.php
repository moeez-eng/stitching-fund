<x-filament::page>
    <form wire:submit.prevent="save">
        {{ $this->form }}
        <button type="submit" class="filament-button filament-button-color-primary mt-4">
            Save Materials
        </button>
    </form>
</x-filament::page>
