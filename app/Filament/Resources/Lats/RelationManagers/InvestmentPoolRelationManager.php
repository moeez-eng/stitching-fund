<?php

namespace App\Filament\Resources\Lats\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Resources\RelationManagers\RelationManager;
use App\Models\User;
use App\Models\Lat;
use App\Models\WalletAllocation;
use App\Filament\Resources\InvestmentPool\Tables\InvestmentPoolTable;

class InvestmentPoolRelationManager extends RelationManager
{
    protected static ?string $relationshipTitle = 'Investment Pool';

    protected static string $relationship = 'investmentPool';

    public function table(Table $table): Table
    {
        // Use the same table configuration as InvestmentPoolResource
        return InvestmentPoolTable::configure($table)
            ->headerActions([
                Action::make('create_investment_pool')
                    ->label('Create Investment Pool')
                    ->icon('heroicon-o-plus-circle')
                    ->color('primary')
                    ->visible(fn () => !$this->getOwnerRecord()->investmentPool)
                    ->form([
                        Hidden::make('user_id')
                            ->default(Auth::id()),

                        Section::make('Investment Details')
                            ->schema([
                                Hidden::make('design_name')
                                    ->default(fn () => $this->getOwnerRecord()->design_name),

                                TextInput::make('amount_required')
                                    ->label('Amount Required')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->required()
                                    ->default(function () {
                                        $lat = $this->getOwnerRecord();
                                        $materials = $lat->materials;
                                        $expenses = $lat->expenses;
                                        $materialsTotal = $materials->sum('price');
                                        $expensesTotal = $expenses->sum('price');
                                        return $materialsTotal + $expensesTotal;
                                    }),

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
                                        Select::make('name')
                                            ->label('Partner Name')
                                            ->required()
                                            ->options(function () {
                                                return User::where('role', 'Investor')
                                                    ->where('id', '!=', Auth::id())
                                                    ->pluck('name', 'name')
                                                    ->toArray();
                                            })
                                            ->searchable(),

                                        TextInput::make('investment_amount')
                                            ->label('Investment Amount')
                                            ->numeric()
                                            ->prefix('PKR')
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                $amountRequired = $get('../../amount_required');
                                                $percentage = $amountRequired ? round(($state / $amountRequired) * 100) : 0;
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
                    ])
                    ->action(function (array $data) {
                        $lat = $this->getOwnerRecord();

                        $lat->investmentPool()->create([
                            'lat_id' => $lat->id,
                            'design_name' => $data['design_name'],
                            'amount_required' => $data['amount_required'],
                            'number_of_partners' => $data['number_of_partners'],
                            'partners' => $data['partners'],
                            'user_id' => $data['user_id'],
                        ]);

                        Notification::make()
                            ->title('Investment Pool Created!')
                            ->success()
                            ->body('Investment pool has been created successfully.')
                            ->send();
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->label('Edit Pool')
                    ->icon('heroicon-o-pencil')
                    ->color('primary')
                    ->form([
                        Hidden::make('user_id')
                            ->default(Auth::id()),

                        Section::make('Investment Details')
                            ->schema([
                                TextInput::make('amount_required')
                                    ->label('Amount Required')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->required(),

                                TextInput::make('number_of_partners')
                                    ->label('Number of Partners')
                                    ->numeric()
                                    ->required(),
                            ])
                            ->columns(2),

                        Section::make('Partner Details')
                            ->schema([
                                Repeater::make('partners')
                                    ->label('Partners')
                                    ->schema([
                                        Select::make('name')
                                            ->label('Partner Name')
                                            ->required()
                                            ->options(function () {
                                                return User::where('role', 'Investor')
                                                    ->where('id', '!=', Auth::id())
                                                    ->pluck('name', 'name')
                                                    ->toArray();
                                            })
                                            ->searchable(),

                                        TextInput::make('investment_amount')
                                            ->label('Investment Amount')
                                            ->numeric()
                                            ->prefix('PKR')
                                            ->required()
                                            ->reactive()
                                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                                $amountRequired = $get('../../amount_required');
                                                $percentage = $amountRequired ? round(($state / $amountRequired) * 100) : 0;
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
                    ])
                    ->action(function (array $data, $record) {
                        $record->update([
                            'amount_required' => $data['amount_required'],
                            'number_of_partners' => $data['number_of_partners'],
                            'partners' => $data['partners'],
                            'user_id' => $data['user_id'],
                        ]);

                        // Create wallet allocations for each partner
                        if (isset($data['partners']) && is_array($data['partners'])) {
                            foreach ($data['partners'] as $partner) {
                                if (isset($partner['name']) && isset($partner['investment_amount'])) {
                                    $investor = User::where('name', $partner['name'])->first();
                                    if ($investor) {
                                        // Create or update wallet allocation
                                        WalletAllocation::updateOrCreate(
                                            [
                                                'investor_id' => $investor->id,
                                                'investment_pool_id' => $record->id,
                                            ],
                                            [
                                                'amount' => $partner['investment_amount'],
                                            ]
                                        );

                                        // Send notification to investor
                                        Notification::make()
                                            ->title('Investment Allocation')
                                            ->success()
                                            ->body("You have been allocated PKR " . number_format($partner['investment_amount']) . " to investment pool: " . $record->name)
                                            ->sendToDatabase($investor);
                                    }
                                }
                            }
                        }

                        Notification::make()
                            ->title('Investment Pool Updated!')
                            ->success()
                            ->body('Investment pool has been updated successfully and wallet allocations have been created.')
                            ->send();
                    }),

                DeleteAction::make()
                    ->label('Delete Pool')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->title('Investment Pool Deleted!')
                            ->success()
                            ->body('Investment pool has been deleted successfully.')
                            ->send();
                    }),
            ]);
    }
}
