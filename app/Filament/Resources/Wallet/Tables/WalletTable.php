<?php

namespace App\Filament\Resources\Wallet\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Wallet;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;

class WalletTable
{
    public static function configure(Table $table): Table
    {
        return self::table($table);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('investor.name')
                    ->label('Investor')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Amount')
                    ->money('PKR')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('slip_type')
                    ->label('Payment Type')
                    ->badge()
                    ->color(fn (Wallet $record): string => 
                        match($record->slip_type) {
                            'bank_transfer' => 'success',
                            'cash' => 'warning',
                            'check' => 'info',
                            default => 'gray'
                        }
                    )
                    ->formatStateUsing(fn ($state) => 
                        match($state) {
                            'bank_transfer' => 'Bank Transfer',
                            'cash' => 'Cash',
                            'check' => 'Check',
                            default => $state
                        }
                    ),

                Tables\Columns\ImageColumn::make('slip_path')
                    ->label('Deposit Slip')
                    ->disk('public')
                    ->circular()
                    ->defaultImageUrl(url('/placeholder.png')),

                Tables\Columns\TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable()
                    ->copyable()
                    ->copyMessage('Reference copied!')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('deposited_at')
                    ->label('Deposit Date')
                    ->date()
                    ->sortable()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('investor_id')
                    ->label('Investor')
                    ->relationship('investor', 'name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('slip_type')
                    ->label('Payment Type')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'cash' => 'Cash',
                        'check' => 'Check',
                    ]),

                Tables\Filters\Filter::make('deposited_today')
                    ->label('Deposited Today')
                    ->query(fn ($query) => $query->whereDate('deposited_at', today())),

                Tables\Filters\Filter::make('deposited_this_week')
                    ->label('This Week')
                    ->query(fn ($query) => $query->whereBetween('deposited_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make()
                    ->visible(fn () => Auth::user()?->role !== 'Investor'),
                DeleteAction::make()
                    ->visible(fn () => Auth::user()?->role !== 'Investor'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn () => Auth::user()?->role !== 'Investor'),
                ]),
            ])
            ->emptyStateActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Create Wallet')
                    ->visible(fn () => Auth::user()?->role !== 'Investor'),
            ])
            ->emptyStateHeading('No wallet deposits found')
            ->emptyStateDescription('Create your first wallet deposit to get started.')
            ->emptyStateIcon('heroicon-o-wallet');
    }
}