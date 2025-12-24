<?php

namespace App\Filament\Resources\UserInvitations\Tables;

use App\Models\UserInvitation;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

class UserInvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('unique_code')
                    ->label('Unique Code')
                    ->copyable()
                    ->copyMessage('Unique code copied')
                    ->copyMessageDuration(1500),
                TextColumn::make('role')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('inviter.name')
                    ->label('Invited By')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('status')
                    ->getStateUsing(fn ($record) => $record->isAccepted() ? 'accepted' : ($record->isExpired() ? 'expired' : 'pending'))
                    ->icon(fn ($state) => match($state) {
                        'accepted' => 'heroicon-o-check-circle',
                        'expired' => 'heroicon-o-x-circle',
                        'pending' => 'heroicon-o-clock',
                    })
                    ->color(fn ($state) => match($state) {
                        'accepted' => 'success',
                        'expired' => 'danger',
                        'pending' => 'warning',
                    }),
                TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'accepted' => 'Accepted',
                        'expired' => 'Expired',
                    ])
                    ->query(function ($query, array $data) {
                        if ($data['value'] === 'pending') {
                            $query->pending();
                        } elseif ($data['value'] === 'accepted') {
                            $query->whereNotNull('accepted_at');
                        } elseif ($data['value'] === 'expired') {
                            $query->where('expires_at', '<', now())->whereNull('accepted_at');
                        }
                    }),
            ])
            ->recordActions([
                Action::make('copy_link')
                    ->label('Get Invitation Link')
                    ->icon('heroicon-o-link')
                    ->action(function ($record) {
                        $url = $record->getInvitationUrl();
                        \Filament\Notifications\Notification::make()
                            ->title('Invitation Link')
                            ->body($url)
                            ->success()
                            ->persistent()
                            ->send();
                    })
                    ->visible(fn ($record) => !$record->isAccepted() && !$record->isExpired()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
