<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Tables\Table;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\TextInput;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role')
                    ->searchable()
                    ->sortable(),
                ToggleColumn::make('status')
                    ->label('Active')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return $record->status === 'active';
                    })
                    ->updateStateUsing(function ($record, $state) {
                        $oldStatus = $record->status;
                        $record->update(['status' => $state ? 'active' : 'inactive']);
                        
                        // If user is being set to inactive, logout their sessions
                        if (!$state && $oldStatus === 'active') {
                            // Logout all sessions for this user
                            \Illuminate\Support\Facades\DB::table('sessions')
                                ->where('user_id', $record->id)
                                ->delete();
                        }
                    }),

            ])
            ->filters([
                Filter::make('name')
                    ->form([
                        TextInput::make('name')
                            ->label('Name'),
                    ])
                    ->query(function ($query, array $data) {
                        $query->when($data['name'] ?? null, function ($query, $name) {
                            $query->where('name', 'like', '%'.$name.'%');
                        });
                    }),
                Filter::make('email')
                    ->form([
                        TextInput::make('email')
                            ->label('Email'),
                    ])
                    ->query(function ($query, array $data) {
                        $query->when($data['email'] ?? null, function ($query, $email) {
                            $query->where('email', 'like', '%'.$email.'%');
                        });
                    }),
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'invester' => 'Investor',
                        'agency owner' => 'Agency Owner',
                        'user' => 'User',
                    ]),
            ])
   
              
          
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn ($record) => $record->role !== 'Super Admin'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function ($records) {
                            // Prevent deletion of Super Admin users
                            $superAdminCount = $records->where('role', 'Super Admin')->count();
                            if ($superAdminCount > 0) {
                                throw new \Exception('Cannot delete Super Admin users');
                            }
                        }),
                ]),
            ]);
    }
}
