<?php

namespace App\Filament\Resources\InvestmentPool\Schemas;

use App\Models\Lat;
use Filament\Forms;
use Filament\Forms\Form;
use Illuminate\Support\Str;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\View;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class InvestmentPoolForm
{
    public static function configure(Schema $schema): Schema
    {
        // Get available LATs for current user and find current one
        $user = Auth::user();
        $availableLats = [];
        $latestLatId = null;
        $currentLatId = null;
        
        if ($user) {
            $query = $user->role === 'Investor' && $user->agency_owner_id
                ? Lat::where('user_id', $user->agency_owner_id)
                : Lat::where('user_id', $user->id);
                
            // Get all available LATs and latest one
            $availableLats = $query->pluck('lat_no', 'id')->toArray();
            $latestLat = $query->orderBy('id', 'desc')->first();
            $latestLatId = $latestLat ? $latestLat->id : null;
            
            // Check if lat_id is in URL parameter (from LAT relation manager)
            $urlLatId = request()->get('lat_id');
            if ($urlLatId && isset($availableLats[$urlLatId])) {
                $currentLatId = $urlLatId;
            } else {
                // Fallback to most recently updated LAT
                $currentLat = $query->orderBy('updated_at', 'desc')->first();
                $currentLatId = $currentLat ? $currentLat->id : $latestLatId;
            }
        }
        
        return $schema
            ->schema([
                Hidden::make('user_id')
                    ->default(Auth::id()),

                // Client-side validation for investor wallets
                Hidden::make('_validate_wallets')
                    ->default(0)
                    ->afterStateHydrated(function (callable $set) {
                        $set('_validate_wallets', 1);
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                        if ($state) {
                            $partners = $get('partners') ?? [];
                            
                            foreach ($partners as $index => $partner) {
                                if (!empty($partner['investor_id'])) {
                                    $investor = \App\Models\User::find($partner['investor_id']);
                                    if ($investor) {
                                        $wallet = \App\Models\Wallet::where('investor_id', $investor->id)->first();
                                        if (!$wallet) {
                                            $set('_wallet_error', "Investor '{$investor->name}' does not have a wallet. Please create a wallet first.");
                                            return;
                                        }
                                        
                                        // Check if investment amount exceeds wallet balance
                                        if (isset($partner['investment_amount'])) {
                                            $investmentAmount = floatval($partner['investment_amount']);
                                            if ($wallet->amount < $investmentAmount) {
                                                $set('_wallet_error', "Insufficient wallet balance for '{$investor->name}'. Available: PKR " . number_format($wallet->amount, 0));
                                                return;
                                            }
                                        }
                                    }
                                }
                            }
                            $set('_wallet_error', null);
                        }
                    }),
                    
                // Dedicated trigger for helper text refresh
                Hidden::make('_helper_text_trigger')
                    ->default(1)
                    ->reactive()
                    ->live(),
                    
                // Error display field (hidden but used to show errors)
                TextInput::make('_wallet_error')
                    ->hidden()
                    ->dehydrated(false)
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if ($state) {
                            throw new \Exception($state);
                        }
                    }),

                Section::make('Investment Details')
                    ->schema([
                        Select::make('lat_id')
                            ->label('Lot Number')
                            ->options($availableLats)
                            ->default($currentLatId)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $lat = Lat::find($state);
                                    if ($lat) {
                                        $set('design_name', $lat->design_name);
                                    }
                                } else {
                                    $set('design_name', null);
                                }
                            }),

                        TextInput::make('design_name')
                            ->label('Design Name')
                            ->disabled()
                            ->required()
                            ->dehydrated()
                            ->default(function () use ($latestLatId) {
                                if ($latestLatId) {
                                    $lat = Lat::find($latestLatId);
                                    return $lat ? $lat->design_name : null;
                                }
                                return null;
                            }),

                        TextInput::make('amount_required')
                            ->label('Amount Required (PKR)')
                            ->numeric()
                            ->prefix('PKR')
                            ->required()
                            ->minValue(1)
                            ->live(debounce: 1000)
                            ->afterStateUpdated(function (callable $set, $get, $state) {
                                // Only recalculate when the field loses focus (debounced)
                                $amountRequired = floatval($state);
                                $numberOfPartners = intval($get('number_of_partners')) ?? 2;
                                
                                if ($numberOfPartners > 0 && $amountRequired > 0) {
                                    $perPartnerAmount = $amountRequired / $numberOfPartners;
                                    
                                    // Update all partner fields with equal amounts
                                    for ($i = 0; $i < 6; $i++) {
                                        if ($i < $numberOfPartners) {
                                            $set("partners.{$i}.investment_amount", number_format($perPartnerAmount, 0, '.', ''));
                                            $set("partners.{$i}.investment_percentage", round(($perPartnerAmount / $amountRequired) * 100, 2));
                                        }
                                    }
                                }
                                
                                // Trigger wallet validation
                                $set('_validate_wallets', time());
                            }),

                        TextInput::make('number_of_partners')
                            ->label('Number of Partners')
                            ->numeric()
                            ->default(2)
                            ->minValue(1)
                            ->required()
                            ->reactive()
                            ->live(debounce: 300)
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                $currentPartners = $get('partners') ?? [];
                                $newCount = (int)$state;
                                $currentCount = count($currentPartners);
                                
                                // Preserve existing partners and add/remove as needed
                                if ($newCount > $currentCount) {
                                    // Add new empty partners
                                    $partnersToAdd = $newCount - $currentCount;
                                    for ($i = 0; $i < $partnersToAdd; $i++) {
                                        $currentPartners[] = [];
                                    }
                                } elseif ($newCount < $currentCount) {
                                    // Remove excess partners
                                    $currentPartners = array_slice($currentPartners, 0, $newCount);
                                }
                                
                                $set('partners', $currentPartners);
                                
                                // Recalculate investment amounts for all partners
                                $amountRequired = floatval($get('amount_required') ?? 0);
                                if ($newCount > 0 && $amountRequired > 0) {
                                    $perPartnerAmount = $amountRequired / $newCount;
                                    
                                    foreach ($currentPartners as $index => $partner) {
                                        $currentPartners[$index]['investment_amount'] = number_format($perPartnerAmount, 0, '.', '');
                                        $currentPartners[$index]['investment_percentage'] = round(100 / $newCount, 2);
                                    }
                                    
                                    $set('partners', $currentPartners);
                                }
                                
                                // Force refresh of helper text by updating wallet validation
                                $set('_validate_wallets', time());
                                // Also trigger helper text refresh
                                $set('_helper_text_trigger', time());
                                // Update partners count to trigger helper text
                                $set('_partners_count', $newCount);
                            }),
                    ])
                    ->columns(2),

                Section::make('Partner Details')
                    ->schema([
                        // Hidden field to track current partners count for helper text
                        Hidden::make('_partners_count')
                            ->default(2)
                            ->reactive()
                            ->live(),
                        
                        // Dynamic partner fields based on number_of_partners
                        ...collect(range(0, 5))->map(function ($index) {
                            return Section::make("Partner " . ($index + 1))
                                ->schema([
                                    Select::make("partners.{$index}.investor_id")
                                        ->label('Partner Name')
                                        ->options(function () {
                                            $user = Auth::user();
                                            if (!$user) return [];
                                            
                                            $query = \App\Models\User::where('role', 'Investor');
                                            
                                            if ($user->role === 'Agency Owner') {
                                                $query->where('invited_by', $user->id);
                                            } elseif ($user->role === 'Super Admin') {
                                                // Super admin can see all investors
                                            } else {
                                                return [];
                                            }
                                            
                                            return $query->pluck('name', 'id');
                                        })
                                        ->reactive()
                                        ->afterStateUpdated(function ($state, callable $set, callable $get) use ($index) {
                                            if ($state) {
                                                $wallet = \App\Models\Wallet::where('investor_id', $state)->first();
                                                $set("partners.{$index}.wallet_balance", $wallet ? $wallet->amount : 0);
                                            } else {
                                                $set("partners.{$index}.wallet_balance", 0);
                                            }
                                        }),
                                        
                                    TextInput::make("partners.{$index}.investment_amount")
                                        ->label('Investment Amount')
                                        ->numeric()
                                        ->prefix('PKR')
                                        ->required()
                                        ->minValue(0)
                                        ->step(1)
                                        ->live()
                                        ->reactive()
                                        ->helperText(function (callable $get) use ($index) {
                                            // Hide helper text on index page, show on create/edit
                                            if (!str_contains(request()->path(), '/investment-pools') || str_contains(request()->path(), 'create') || str_contains(request()->path(), 'edit')) {
                                                $walletBalance = $get("partners.{$index}.wallet_balance");
                                                $investorId = $get("partners.{$index}.investor_id");
                                                $investmentAmount = floatval($get("partners.{$index}.investment_amount") ?? 0);
                                                $trigger = $get('_partners_count');
                                                
                                                // Hide if this partner index exceeds number of partners
                                                if ($index >= ($get('number_of_partners') ?? 2)) {
                                                    return "";
                                                }
                                                
                                                if ($investorId) {
                                                    $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                                                    if (!$wallet) {
                                                        return "No investor wallet found";
                                                    }
                                                }
                                                
                                                if ($walletBalance > 0) {
                                                    if ($investmentAmount > 0 && $investmentAmount > $walletBalance) {
                                                        return " Insufficient wallet balance. Available: PKR " . number_format($walletBalance, 0);
                                                    }
                                                    
                                                    return " Available balance: PKR " . number_format($walletBalance, 0);
                                                }
                                                
                                                return "Select a partner to see wallet balance";
                                            }
                                            
                                            return ""; // Hide helper text on index page
                                        })
                                        ->rules([
                                            function (callable $get) use ($index) {
                                                return function (string $attribute, $value, $fail) use ($get, $index) {
                                                    $investorId = $get("partners.{$index}.investor_id");
                                                    
                                                    if ($investorId) {
                                                        $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                                                        if (!$wallet) {
                                                            $fail('No wallet found for this investor');
                                                            return;
                                                        }
                                                        
                                                        $amount = floatval($value);
                                                        if ($amount > 0 && $amount > $wallet->amount) {
                                                            $fail("Insufficient wallet balance. Available: PKR " . number_format($wallet->amount, 2));
                                                        }
                                                    }
                                                };
                                            },
                                        ])
                                        ->afterStateUpdated(function (callable $set, callable $get, $state) use ($index) {
                                            $investorId = $get("partners.{$index}.investor_id");
                                            
                                            if ($investorId) {
                                                $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                                                if ($wallet) {
                                                    $amount = floatval($state);
                                                    if ($amount > 0 && $amount > $wallet->amount) {
                                                        $set("partners.{$index}.investment_percentage", 0);
                                                        return;
                                                    }
                                                }
                                            }
                                            
                                            // Calculate percentage
                                            $amountRequired = floatval($get('amount_required') ?? 0);
                                            $percentage = $amountRequired > 0 ? round(($state / $amountRequired) * 100, 2) : 0;
                                            $set("partners.{$index}.investment_percentage", $percentage);
                                        }),

                                    TextInput::make("partners.{$index}.investment_percentage")
                                        ->label('Investment Percentage')
                                        ->disabled()
                                        ->dehydrated()
                                        ->numeric()
                                        ->suffix('%')
                                        ->required()
                                        ->default(0),
                                
                                    // Hidden field to store wallet balance
                                    Hidden::make("partners.{$index}.wallet_balance")
                                        ->dehydrated(false)
                                        ->reactive(),
                                ])
                                ->visible(fn (callable $get) => $index < ($get('number_of_partners') ?? 2))
                                ->columns(2);
                        })->all(),
                    ]),
            ]);
        }
    }