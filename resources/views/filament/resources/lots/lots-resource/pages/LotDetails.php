<?php
use Filament\Forms;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Form;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class LotDetails extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;
public function materialForm(Form $form): Form
{
    return $form
        ->schema([
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
                        ->dehydrated()
                        ->afterStateUpdated(fn ($set, $get) =>
                            $set('price', ($get('rate') ?? 0) * ($get('quantity') ?? 0))
                        ),
                ])
                ->columns(4)
                ->defaultItems(1)
                ->addActionLabel('Add Material'),
        ]);
}
}