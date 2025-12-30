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
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Auth;

class InvestmentPoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Hidden::make('user_id')
                    ->default(Auth::id()),

                Section::make('Investment Details')
                    ->schema([
                        Select::make('lat_id')
                            ->label('Lot Number')
                            ->options(function () {
                                $user = Auth::user();
                                if (!$user) return collect();
                                
                                // For investors, get LAT records from their agency owner
                                if ($user->role === 'Investor' && $user->agency_owner_id) {
                                    return Lat::where('user_id', $user->agency_owner_id)->pluck('lat_no', 'id');
                                }
                                
                                // For others, get their own LAT records
                                return Lat::where('user_id', $user->id)->pluck('lat_no', 'id');
                            })
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('design_name', Lat::find($state)?->design_name)),

                        TextInput::make('design_name')
                            ->label('Design Name')
                            ->disabled()
                            ->required()
                            ->default(''),

                        TextInput::make('amount_required')
                            ->label('Amount Required')
                            ->numeric()
                            ->prefix('PKR')
                            ->required(),

                        TextInput::make('number_of_partners')
                            ->label('Number of Partners')
                            ->numeric()
                            ->default(2)
                            ->required(),
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
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        $amountRequired = floatval($get('../../amount_required') ?? 0);
                                        $percentage = $amountRequired > 0 ? round(($state / $amountRequired) * 100, 2) : 0;
                                        $set('investment_percentage', $percentage);
                                    }),
                                TextInput::make('investment_percentage')
                                    ->label('Investment Percentage')
                                    ->disabled()
                                    ->numeric()
                                    ->suffix('%')
                                    ->required()
                                    ->default(0),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}