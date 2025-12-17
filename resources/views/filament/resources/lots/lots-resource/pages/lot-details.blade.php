<x-filament::page>

    {{-- SECTION 1 --}}
    <x-filament::section>
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">
                Material Purchase (Lot Cloths)
            </h2>

            <x-filament::button
                size="sm"
                wire:click="$set('editing', 'material')">
                Add / Edit
            </x-filament::button>
        </div>

        {{-- DEFAULT VIEW --}}
        @if ($editing !== 'material')
            <p class="text-gray-500">
                No material purchase data added yet.
            </p>
        @endif

        {{-- FORM VIEW --}}
        @if ($editing === 'material')
            <form wire:submit="save">
                {{ $this->form }}
                
                <div class="mt-4 space-x-2">
                    <x-filament::button type="submit">
                        Save
                    </x-filament::button>
                    
                    <x-filament::button
                        color="gray"
                        wire:click="closeForm">
                        Close
                    </x-filament::button>
                </div>
            </form>
        @endif
    </x-filament::section>


    {{-- SECTION 2 --}}
    <x-filament::section class="mt-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold">
                Stitching Process & Lot Preparation Cost
            </h2>

            <x-filament::button
                size="sm"
                wire:click="$set('editing', 'stitching')">
                Add / Edit
            </x-filament::button>
        </div>

        {{-- DEFAULT VIEW --}}
        @if ($editing !== 'stitching')
            <p class="text-gray-500">
                No stitching process data added yet.
            </p>
        @endif

        {{-- FORM VIEW --}}
        @if ($editing === 'stitching')
            {{ $this->stitchingForm }}

            <x-filament::button
                color="gray"
                class="mt-4"
                wire:click="closeForm">
                Close
            </x-filament::button>
        @endif
    </x-filament::section>

</x-filament::page>
