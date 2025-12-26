<?php

namespace App\Filament\Resources\InvestmentPool\Schemas;

use App\Models\Lat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Placeholder;

class InvestmentPoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Investment Details')
                    ->schema([
                        Select::make('lat_id')
                            ->label('Lot Number')
                            ->options(Lat::pluck('lat_no', 'id'))
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('design_name', null)),

                        Forms\Components\Select::make('design_name')
                            ->label('Design Name')
                            ->options(function (callable $get) {
                                $latId = $get('lat_id');
                                if (!$latId) return [];
                                
                                $lat = Lat::find($latId);
                                return $lat ? [$lat->design_name => $lat->design_name] : [];
                            })
                            ->required()
                            ->disabled(fn (callable $get) => !$get('lat_id')),

                        Forms\Components\TextInput::make('amount_required')
                            ->label('Amount Required')
                            ->numeric()
                            ->prefix('PKR')
                            ->required()
                            ->reactive(),

                        Forms\Components\TextInput::make('number_of_partners')
                            ->label('Number of Partners')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Clear existing partners when number changes
                                $set('partners', []);
                            }),
                    ])
                    ->columns(2),

                Section::make('Partner Details')
                    ->schema([
                        Repeater::make('partners')
                            ->label('Partners')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Partner Name')
                                    ->required(),

                                TextInput::make('investment_amount')
                                    ->label('Investment Amount')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $totalRequired = $get('amount_required');
                                        if ($totalRequired > 0) {
                                            $percentage = ($state / $totalRequired) * 100;
                                            $set('investment_percentage', round($percentage, 2));
                                        }
                                    }),

                                TextInput::make('investment_percentage')
                                    ->label('Investment Percentage')
                                    ->numeric()
                                    ->suffix('%')
                                    ->disabled()
                                    ->dehydrated(false),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->collapsed()
                            ->itemLabel(fn (array $state): ?string => $state['name'] ?? null)
                            ->defaultItems(1)
                            ->reactive()
                            ->afterStateUpdated(function (array $state, callable $set) {
                                $totalCollected = collect($state)->sum('investment_amount');
                                $set('total_collected', $totalCollected);
                                
                                $amountRequired = $set('amount_required', $set('amount_required'));
                                if ($amountRequired > 0) {
                                    $percentageCollected = ($totalCollected / $amountRequired) * 100;
                                    $set('percentage_collected', round($percentageCollected, 2));
                                    $set('remaining_amount', $amountRequired - $totalCollected);
                                }
                            }),
                    ])
                    ->visible(fn (callable $get) => $get('number_of_partners') > 0),

                Section::make('Investment Summary')
                    ->schema([
                        Placeholder::make('total_collected')
                            ->label('Total Collected')
                            ->content(fn (callable $get) => 'PKR ' . number_format($get('total_collected') ?? 0, 2)),

                        Placeholder::make('percentage_collected')
                            ->label('Percentage Collected')
                            ->content(fn (callable $get) => number_format($get('percentage_collected') ?? 0, 2) . '%'),

                        Placeholder::make('remaining_amount')
                            ->label('Remaining Amount')
                            ->content(fn (callable $get) => 'PKR ' . number_format($get('remaining_amount') ?? 0, 2)),
                    ])
                    ->columns(3)
                    ->visible(fn (callable $get) => $get('amount_required') > 0),
            ]);
    }
}