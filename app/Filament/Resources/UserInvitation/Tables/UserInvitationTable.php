<?php

namespace App\Filament\Resources\UserInvitation\Tables;

use Filament\Tables\Table;
use Filament\Actions\Action;
use App\Models\UserInvitation;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Illuminate\Support\Facades\Log;
use App\Mail\InvestorInvitationMail;
use Illuminate\Support\Facades\Mail;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;

class UserinvitationTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')
                    ->label('Investor Email')
                    ->searchable(),

                TextColumn::make('company_name')
                    ->label('Company Name')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color('success'),

                TextColumn::make('inviter.name')
                    ->label('Invited By')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->getStateUsing(function (UserInvitation $record): string {
                        if ($record->isAccepted()) {
                            return 'Accepted';
                        } elseif ($record->isExpired()) {
                            return 'Expired';
                        } else {
                            return 'Pending';
                        }
                    })
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'Accepted' => 'success',
                        'Expired' => 'danger',
                        'Pending' => 'warning',
                    }),

                TextColumn::make('expires_at')
                    ->label('Expires')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Sent')
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
                    ->query(function ($query, $data) {
                        if ($data['value'] === 'pending') {
                            $query->whereNull('accepted_at')->where('expires_at', '>', now());
                        } elseif ($data['value'] === 'accepted') {
                            $query->whereNotNull('accepted_at');
                        } elseif ($data['value'] === 'expired') {
                            $query->where('expires_at', '<', now())->whereNull('accepted_at');
                        }
                    }),
            ])
            ->recordActions([
                Action::make('resend')
                    ->label('Resend Email')
                    ->icon('heroicon-o-paper-airplane')
                    ->visible(fn (UserInvitation $record) => ! $record->isAccepted())
                    ->action(function (UserInvitation $record) {
                        try {
                            Mail::to($record->email)->send(new InvestorInvitationMail($record));
                            Notification::make()
                                ->title('Invitation resent successfully')
                                ->body('Email sent to ' . $record->email)
                                ->success()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Failed to resend invitation')
                                ->body('Error: ' . $e->getMessage())
                                 ->danger()
                                ->send();
                            Log::error('Failed to resend invitation email: ' . $e->getMessage());
                        }
                    }),
                 Action::make('copy_link')
                         ->label('Copy link')
                         ->icon('heroicon-o-clipboard-document')
                         ->visible(fn (UserInvitation $record) => $record->isValid())
                         ->action(function (UserInvitation $record, $livewire) {
                                $link = $record->getInvitationLink();
                                // Execute JavaScript to copy the URL and show a notification
                                $livewire->js("
                                    navigator.clipboard.writeText('{$link}').then(() => {
                                        \$tooltip('Link copied!', { timeout: 1500 });
                                    });
                                ");
                                Notification::make()
                                 ->title('Invitation link copied!')
                                 ->body($link)
                                 ->success()
                                 ->send();
                }),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->poll(10)
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}