<?php

namespace App\Filament\Widgets;

use App\Models\Lat;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Query\Builder;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseTableWidget;


class RecentInvestmentsWidget extends BaseTableWidget
{
    protected static ?string $heading = 'Recent Investments';
    protected static ?string $description = 'Latest investments from your investors';
    protected int | string | array $columnSpan = 'full';
    
    public static function canView(): bool
    {
        return Auth::user()?->role === 'Agency Owner';
    }
    
    public function table(Table $table): Table
    {
        $user = Auth::user();
        
        return $table
            ->query(
                Lat::with(['user'])
                    ->whereHas('user.wallets', function (Builder $query) use ($user) {
                        $query->where('agency_owner_id', $user->id);
                    })
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('customer_name')
                    ->label('Investor')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('total_price')
                    ->label('Amount')
                    ->money('USD')
                    ->sortable(),
                    
                TextColumn::make('payment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'danger',
                        'partial' => 'warning',
                        'complete' => 'success',
                        'lose' => 'gray',
                        default => 'gray',
                    }),
                    
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M j, Y')
                    ->sortable(),
            ]);
    }
}