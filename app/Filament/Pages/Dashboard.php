<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-home';
    
    protected function getHeaderWidgets(): array
    {
        return [
            // Add your dashboard widgets here
        ];
    }
}
