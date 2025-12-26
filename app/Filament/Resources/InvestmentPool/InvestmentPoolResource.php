<?php

namespace App\Filament\Resources\InvestmentPool;

use BackedEnum;
use UnitEnum;
use App\Models\InvestmentPool;
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
}
