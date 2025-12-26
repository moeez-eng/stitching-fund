<?php

namespace App\Filament\Resources\InvestmentPool;

use BackedEnum;
use UnitEnum;
use App\Models\InvestmentPool;
use App\Models\Lat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use App\Filament\Resources\InvestmentPool\Pages\EditInvestmentPool;
use App\Filament\Resources\InvestmentPool\Pages\ListInvestmentPools;
use App\Filament\Resources\InvestmentPool\Pages\CreateInvestmentPool;
use App\Filament\Resources\InvestmentPool\Schemas\InvestmentPoolForm;
use App\Filament\Resources\InvestmentPool\Tables\InvestmentPoolTable;

class InvestmentPoolResource extends Resource
{
    protected static ?string $model = InvestmentPool::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Banknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Investment Management';

    public static function form(Schema $schema): Schema
    {
        return InvestmentPoolForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvestmentPoolTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInvestmentPools::route('/'),
            'create' => CreateInvestmentPool::route('/create'),
            'edit' => EditInvestmentPool::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        // Temporarily remove user filter to test if data shows
        return parent::getEloquentQuery();
    }

    protected static function mutateFormDataBeforeSave(array $data): array
    {
        // Debug logging
        Log::info('mutateFormDataBeforeSave called with data: ', $data);

        // Ensure design_name is set from lat_id if not provided
        if (isset($data['lat_id']) && empty($data['design_name'])) {
            $designName = Lat::find($data['lat_id'])?->design_name;
            $data['design_name'] = $designName;
            Log::info('Setting design_name to: ' . $designName);
        }

        // Process partners data to include investment_percentage
        if (isset($data['partners']) && is_array($data['partners'])) {
            $amountRequired = $data['amount_required'] ?? 0;
            Log::info('Processing partners with amount_required: ' . $amountRequired);
            
            $data['partners'] = collect($data['partners'])->map(function ($partner) use ($amountRequired) {
                if (isset($partner['investment_amount']) && $amountRequired > 0) {
                    $partner['investment_percentage'] = round(($partner['investment_amount'] / $amountRequired) * 100);
                    Log::info('Partner percentage calculated: ' . $partner['investment_percentage']);
                } else {
                    $partner['investment_percentage'] = 0;
                }
                return $partner;
            })->toArray();
        }

        Log::info('Final data before save: ', $data);
        return $data;
    }
}
