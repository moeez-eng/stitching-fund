<?php

namespace App\Filament\Resources\Lats\Schemas;

use App\Models\Lat;
use App\Models\Design;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Cache;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form as FilamentForm;

class LatsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema(self::getFormSchema());
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('lat_no')
                ->label('Lat Number')
                ->required()
                ->numeric()
                ->disabled()
                ->dehydrated()
                ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('user_id', Auth::id());
                })
                ->default(fn () => Lat::forUser()->max('lat_no') + 1),
            
            Select::make('design_name')
                ->label('Design')
                ->options(function () {
                    return \App\Models\Design::forUser()
                        ->pluck('name', 'name')
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->createOptionForm([
                    TextInput::make('name')
                        ->label('New Design Name')
                        ->required()
                        ->maxLength(255)
                        ->unique(),
                ])
                ->createOptionUsing(function (array $data) {
                    $design = \App\Models\Design::create(['name' => $data['name']]);
                    return $design->name;
                })
                ->required(),
                
            Select::make('customer_name')
                ->label('Customer')
                ->options(function () {
                    return \App\Models\Contact::forUser()
                        ->where('ctype', 'customer')
                        ->get()
                        ->mapWithKeys(function ($contact) {
                            return [$contact->name => "{$contact->name} - {$contact->phone}"];
                        })
                        ->toArray();
                })
                ->searchable()
                ->preload()
                ->required(),
        ];
    }
    
   
    // ======================================================================
    // CACHED OPTIONS (v4: Optimized with pattern clearing)
    // ======================================================================

    public static function getDesignOptions(?string $search = null): array
    {
        $key = 'lats_design_options' . ($search ? '_s_' . md5($search) : '');
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
        Cache::forget('lats_design_options');
        
        // Clear search caches in a driver-agnostic way
        $prefix = config('cache.prefix');
        $keys = [
            $prefix . '_lats_design_options_s_*',
            $prefix . ':lats_design_options_s_*',
            'lats_design_options_s_*'
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
                ->where('key', 'like', $prefix . ':%lats_customer_options_s_%')
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
                'lats_customer_options',
                'lats_customer_options_s_*',
                $prefix . '_lats_customer_options',
                $prefix . '_lats_customer_options_s_*',
                $prefix . ':lats_customer_options',
                $prefix . ':lats_customer_options_s_*'
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