<?php

namespace App\Filament\Resources\Lots\Pages;

use Filament\Forms;
use App\Models\Lots;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\Lots\LotsResource;

class LotDetails extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $resource = LotsResource::class;

    protected string $view = 'filament.resources.lots.lots-resource.pages.lot-details';

    public Lots $record;

    public ?string $editing = null; // null | material | stitching

    public array $materialData = [];
    public array $stitchingData = [];

    public function mount(Lots $record): void
    {
        $this->record = $record;
    }

    protected function getFormSchema(): array
    {
        return [
            Repeater::make('materials')
                ->relationship('materials')
                ->schema([
                    DateTimePicker::make('dated')
                        ->default(now())
                        ->required(),

                    TextInput::make('material')
                        ->required(),

                    TextInput::make('colour'),

                    Select::make('unit')
                        ->options([
                            'Yards' => 'Yards',
                            'Roll' => 'Roll',
                            'Packet' => 'Packet',
                            'Cone' => 'Cone',
                        ])
                        ->required(),

                    TextInput::make('rate')
                        ->numeric()
                        ->reactive()
                        ->required(),

                    TextInput::make('quantity')
                        ->numeric()
                        ->reactive()
                        ->required(),

                    TextInput::make('price')
                        ->numeric()
                        ->disabled()
                        ->dehydrated(false)
                        ->afterStateUpdated(fn ($set, $get) =>
                            $set('price', ($get('rate') ?? 0) * ($get('quantity') ?? 0))
                        ),
                ])
                ->columns(4)
                ->defaultItems(1)
                ->addActionLabel('Add Material'),
        ];
    }

    
    public function save(): void
    {
        $this->form->validate();
        $this->editing = null;
    }

    public function closeForm(): void
    {
        $this->editing = null;
    }
}
