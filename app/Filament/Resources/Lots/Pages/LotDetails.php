<?php

namespace App\Filament\Resources\Lots\Pages;

use Filament\Forms;
use App\Models\Lots;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use App\Filament\Resources\Lots\LotsResource;

class LotDetails extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = LotsResource::class;
    protected string $view = 'filament.resources.lots.lots-resource.pages.lot-details';

    public Lots $record;
    public ?array $data = [];

    public function mount(Lots $record): void
    {
        $this->record = $record;
        
        // Load materials data
        $this->form->fill([
            'materials' => $record->materials->map(fn($m) => [
                'material' => $m->material,
                'colour' => $m->colour,
                'unit' => $m->unit,
                'rate' => $m->rate,
                'quantity' => $m->quantity,
                'price' => $m->price,
            ])->toArray() ?: [[]],
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Repeater::make('materials')
                ->label('Materials')
                ->schema([
                    TextInput::make('material')
                        ->label('Material')
                        ->required()
                        ->columnSpan(1),
                    
                    TextInput::make('colour')
                        ->label('Colour')
                        ->columnSpan(1),
                    
                    Select::make('unit')
                        ->label('Unit')
                        ->options([
                            'Yards' => 'Yards',
                            'Roll' => 'Roll',
                            'Packet' => 'Packet',
                            'Cone' => 'Cone',
                        ])
                        ->required()
                        ->columnSpan(1),
                    
                    TextInput::make('rate')
                        ->label('Rate')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                            $set('price', $state * ($get('quantity') ?? 0))
                        )
                        ->columnSpan(1),
                    
                    TextInput::make('quantity')
                        ->label('Quantity')
                        ->numeric()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, callable $set, callable $get) => 
                            $set('price', $state * ($get('rate') ?? 0))
                        )
                        ->columnSpan(1),
                    
                    TextInput::make('price')
                        ->label('Price')
                        ->numeric()
                        ->disabled()
                        ->dehydrated()
                        ->columnSpan(1),
                ])
                ->columns(2)
                ->defaultItems(1)
                ->addActionLabel('Add Material')
                ->collapsible()
                ->itemLabel(fn (array $state): ?string => $state['material'] ?? null),
        ];
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Delete existing materials
        $this->record->materials()->delete();

        // Create new materials
        if (!empty($data['materials'])) {
            foreach ($data['materials'] as $material) {
                if (empty($material['material'])) {
                    continue;
                }

                $this->record->materials()->create([
                    'material' => $material['material'],
                    'colour' => $material['colour'] ?? null,
                    'unit' => $material['unit'],
                    'rate' => $material['rate'],
                    'quantity' => $material['quantity'],
                    'price' => $material['price'] ?? ($material['rate'] * $material['quantity']),
                    'dated' => now(),
                ]);
            }
        }

        // Show success notification
        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();

        // Refresh the record and reload form
        $this->record->refresh();
        
        $this->form->fill([
            'materials' => $this->record->materials->map(fn($m) => [
                'material' => $m->material,
                'colour' => $m->colour,
                'unit' => $m->unit,
                'rate' => $m->rate,
                'quantity' => $m->quantity,
                'price' => $m->price,
            ])->toArray() ?: [[]],
        ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Materials')
                ->action('save'),
        ];
    }
}