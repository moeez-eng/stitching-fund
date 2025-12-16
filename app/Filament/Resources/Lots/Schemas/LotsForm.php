<?php

namespace App\Filament\Resources\Lots\Schemas;

use App\Models\Lots;
use App\Models\Design;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form as FilamentForm;

class LotsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(self::getFormSchema());
    }

    public static function getFormSchema(bool $isView = false): array
    {
        return [
            TextInput::make('lot_no')
                ->label('Lot Number')
                ->required()
                ->numeric()
                ->unique(ignoreRecord: true)
                ->default(fn () => Lots::max('lot_no') + 1)
                ->disabled(fn ($record) => $record !== null || $isView),

            $isView 
            ? TextInput::make('design.name')
                ->label('Design')
                ->disabled()
                ->dehydrated(false)
            : Select::make('design_id')
                ->label('Design')
                ->relationship('design', 'name')
                ->searchable()
                ->preload()
                ->distinct()
                ->createOptionForm([
                    TextInput::make('name')
                        ->label('New Design Name')
                        ->required()
                        ->maxLength(255)
                        ->unique(),
                ])
                 ->default(function ($livewire) {
                    $lastLot = \App\Models\Lots::latest('id')->first();
                    return $lastLot ? $lastLot->design_id : null;
                })
                ->required(),
        $isView 
            ? TextInput::make('customer.name')
                ->label('Customer')
                ->disabled()
                ->dehydrated(false)
            : Select::make('customer_id')
                ->label('Customer')
                ->options(function () {
                    return \App\Models\Contact::where('Ctype', 'Customer')
                        ->get()
                        ->mapWithKeys(function ($contact) {
                            return [$contact->id => "{$contact->name} - {$contact->phone}"];
                        })
                        ->toArray();
                })
                ->searchable()
                ->preload()
                 ->default(function ($livewire) {
                    $lastLot = \App\Models\Lots::latest('id')->first();
                    return $lastLot ? $lastLot->customer_id : null;
                })
                ->required(),
        ];
    }
    
    public static function getViewSchema(): array
    {
        return [
            TextInput::make('lot_no')
                ->label('Lot Number')
                ->disabled(),
            TextInput::make('design.name')
                ->label('Design')
                ->disabled()
                ->dehydrated(false)
                ->required()
                ->unique()
                ->formatStateUsing(fn ($record) => $record?->design?->name ?? 'N/A'),
            TextInput::make('customer.name')
                ->label('Customer')
                ->disabled()
                ->dehydrated(false)
                ->required()
                ->unique()
                ->formatStateUsing(fn ($record) => $record?->customer?->name ?? 'N/A'),
        ];
    }

    // ======================================================================
    // CACHED OPTIONS (v4: Optimized with pattern clearing)
    // ======================================================================

    public static function getDesignOptions(?string $search = null): array
    {
        $key = 'lots_design_options' . ($search ? '_s_' . md5($search) : '');
        return Cache::remember($key, now()->addHours(6), function () use ($search) {
            $q = \App\Models\Design::query()->select('id', 'name')->orderBy('name');
            if ($search) {
                $q->where('name', 'like', "%{$search}%");
            }
            return $q->take(200)->pluck('name', 'id')->toArray();
        });
    }

    public static function clearDesignCache(): void
    {
        // Clear the main cache
        Cache::forget('lots_design_options');
        
        // Clear search caches in a driver-agnostic way
        $prefix = config('cache.prefix');
        $keys = [
            $prefix . '_lots_design_options_s_*',
            $prefix . ':lots_design_options_s_*',
            'lots_design_options_s_*'
        ];
        
        // Try to clear using tags if supported
        try {
            if (method_exists(Cache::getStore(), 'tags')) {
                Cache::tags(['design_options'])->flush();
                return;
            }
        } catch (\Exception $e) {
            // Continue with other methods if tagging fails
        }
        
        // Fallback: Clear known patterns (less efficient but works with all drivers)
        foreach ($keys as $key) {
            try {
                Cache::forget(rtrim($key, '*'));
            } catch (\Exception $e) {
                // Ignore errors for individual keys
            }
        }
    }

    public static function getCustomerOptions(?string $search = null): array
    {
        $key = 'lots_customer_options' . ($search ? '_s_' . md5($search) : '');
        return Cache::remember($key, now()->addHours(6), function () use ($search) {
            $q = \App\Models\Customer::query()->select('id', 'name')->orderBy('name');
            if ($search) {
                $q->where('name', 'like', "%{$search}%");
            }
            return $q->take(200)->pluck('name', 'id')->toArray();
        });
    }

    public static function clearCustomerCache()
    {
        $cacheDriver = config('cache.default');
        $prefix = config('cache.prefix');
        
        // For database driver, use direct query to clear matching cache keys
        if ($cacheDriver === 'database') {
            $cacheTable = config('cache.stores.database.table', 'cache');
            
            DB::table($cacheTable)
                ->where('key', 'like', $prefix . ':%lots_customer_options_s_%')
                ->delete();
                
            // Also try without the prefix in case it's not included in the stored key
            DB::table($cacheTable)
                ->where('key', 'like', '%lots_customer_options_s_%')
                ->delete();
        } 
        // For other drivers, use a more reliable approach
        else {
            // Generate a list of possible cache keys that might have been used
            $keys = [
                'lots_customer_options',
                'lots_customer_options_s_*',
                $prefix . '_lots_customer_options',
                $prefix . '_lots_customer_options_s_*',
                $prefix . ':lots_customer_options',
                $prefix . ':lots_customer_options_s_*'
            ];
            
            // Try to clear using tags if supported
            try {
                if (method_exists(Cache::getStore(), 'tags')) {
                    Cache::tags(['lots_customer_options'])->flush();
                    return;
                }
            } catch (\Exception $e) {
                // Continue with other methods if tagging fails
            }
            
            // Try to clear each key pattern
            foreach ($keys as $key) {
                try {
                    Cache::forget($key);
                } catch (\Exception $e) {
                    // Ignore errors for individual keys
                }
            }
            
            // If using file cache, we can try to clear the cache directory
            if ($cacheDriver === 'file') {
                try {
                    $cachePath = storage_path('framework/cache/data');
                    if (is_dir($cachePath)) {
                        $files = glob($cachePath . '/*lots_customer_options*');
                        foreach ($files as $file) {
                            if (is_file($file)) {
                                @unlink($file);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore file system errors
                }
            }
        }
    }
}