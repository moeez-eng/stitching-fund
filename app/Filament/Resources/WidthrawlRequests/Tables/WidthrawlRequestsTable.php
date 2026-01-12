<?php

namespace App\Filament\Resources\WidthrawlRequests\Tables;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Columns\TextInputColumn;

class WidthrawlRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('investor_name')
                    ->searchable()
                    ->sortable(),
                    TextColumn::make('wallet_id')
                    ->label('Wallet ID')
                    ->sortable(),
                    TextColumn::make('requested_amount')
                    ->money('PKR')
                    ->sortable(),
                    TextInputColumn::make('approved_amount')
                    ->label('Approved Amount')
                    ->type('number')
                    ->visible(fn ($record) => $record && $record->status === 'pending')
                    ->afterStateUpdated(function ($record, $state) {
                        if (!$record) return;
                        $availableBalance = $record->wallet->available_balance;
                        if ($state > $availableBalance) {
                            throw new \Exception('Approved amount cannot exceed available balance');
                        }
                    }),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Requested On')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ])
            ->filters([
               SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('approved_amount')
                            ->label('Approved Amount')
                            ->numeric()
                            ->prefix('PKR')
                            ->default(fn ($record) => $record->requested_amount)
                            ->required(),
                        \Filament\Forms\Components\Textarea::make('owner_notes')
                            ->label('Notes (Optional)')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        $user = Auth::user();
                        $approvedAmount = $data['approved_amount'];
                        
                        if ($approvedAmount > $record->wallet->available_balance) {
                            throw new \Exception('Approved amount exceeds available balance');
                        }
                        
                        $success = $record->approve($user, $approvedAmount);
                        
                        if (!$success) {
                            throw new \Exception('Failed to approve request');
                        }
                        
                        if (!empty($data['owner_notes'])) {
                            $record->update(['owner_notes' => $data['owner_notes']]);
                        }
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn ($record) => $record->status === 'pending')
                    ->form([
                        \Filament\Forms\Components\Textarea::make('owner_notes')
                            ->label('Rejection Reason')
                            ->rows(3)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $user = Auth::user();
                        $success = $record->reject($user, $data['owner_notes']);
                        
                        if (!$success) {
                            throw new \Exception('Failed to reject request');
                        }
                    }),
                EditAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->status === 'pending'),
               
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->poll('10s');
            
    }
}
