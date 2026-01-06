<?php

namespace App\Filament\Resources\InvestmentPool\Pages;

use App\Filament\Resources\InvestmentPool\InvestmentPoolResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Log;

class EditInvestmentPool extends EditRecord
{
    protected static string $resource = InvestmentPoolResource::class;

    public function mount($record): void
    {
        Log::info('=== EDIT INVESTMENT POOL PAGE MOUNTED ===');
        Log::info('Record ID: ' . $record);
        
        parent::mount($record);
        
        Log::info('Edit page mounted successfully');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record !== null),
        ];
    }
}
