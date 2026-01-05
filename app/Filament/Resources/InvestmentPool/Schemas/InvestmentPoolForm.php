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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class InvestmentPoolForm
{
    public static function configure(Schema $schema): Schema
    {
        // Get available LATs for the current user
        $user = Auth::user();
        $availableLats = [];
        $latestLatId = null;
        
        if ($user) {
            $query = $user->role === 'Investor' && $user->agency_owner_id
                ? Lat::where('user_id', $user->agency_owner_id)
                : Lat::where('user_id', $user->id);
                
            // Get all available LATs and the latest one
            $availableLats = $query->pluck('lat_no', 'id')->toArray();
            $latestLat = $query->latest('id')->first();
            $latestLatId = $latestLat ? $latestLat->id : null;
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
                                                $set('_wallet_error', "Insufficient wallet balance for '{$investor->name}'. Available: PKR " . number_format($wallet->amount, 2));
                                                return;
                                            }
                                        }
                                    }
                                }
                            }
                            $set('_wallet_error', null);
                        }
                    }),
                    
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
                            ->default($latestLatId)
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
                            ->step(0.01)
                            ->default(0.00)
                            ->afterStateUpdated(function (callable $set, $get, $state) {
                                // Update the total required amount when changed
                                $set('amount_required', number_format((float)$state, 2, '.', ''));
                                
                                // Trigger wallet validation
                                $set('_validate_wallets', time());
                            }),

                        TextInput::make('number_of_partners')
                            ->label('Number of Partners')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $set('partners', array_fill(0, (int)$state, []));
                            }),
                    ])
                    ->columns(2),

                Section::make('Partner Details')
                    ->schema([
                        Repeater::make('partners')
                            ->label('Partners')
                            ->minItems(fn (callable $get) => (int) ($get('number_of_partners') ?? 1))
                            ->maxItems(fn (callable $get) => (int) ($get('number_of_partners') ?? 1))
                            ->schema([
                                Select::make('investor_id')
                                    ->label('Partner Name')
                                    ->options(function () {
                                        $user = Auth::user();
                                        if (!$user) return [];
                                        
                                        $query = \App\Models\User::where('role', 'Investor');
                                        
                                        if ($user->role === 'Agency Owner') {
                                            // Get investors invited by this agency owner
                                            $query->where('invited_by', $user->id);
                                        } elseif ($user->role === 'Super Admin') {
                                            // Super admin can see all investors
                                            // No additional filtering needed
                                        } else {
                                            // For other roles, show no options
                                            return [];
                                        }
                                        
                                        return $query->pluck('name', 'id');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->getOptionLabelUsing(fn ($value): ?string => \App\Models\User::find($value)?->name)
                                    ->afterStateHydrated(function (callable $set, $state) {
                                        if ($state) {
                                            $investor = \App\Models\User::find($state);
                                            if ($investor && $investor->wallet) {
                                                $set('wallet_balance', $investor->wallet->amount);
                                            }
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        if ($state) {
                                            $investor = \App\Models\User::find($state);
                                            if ($investor) {
                                                $set('wallet_balance', $investor->wallet?->amount);
                                                
                                                // Auto-calculate investment amount if not set
                                                $amountRequired = floatval($get('../../amount_required') ?? 0);
                                                $numberOfPartners = intval($get('../../number_of_partners') ?? 1);
                                                if ($numberOfPartners > 0) {
                                                    $investmentAmount = $amountRequired / $numberOfPartners;
                                                    $set('investment_amount', number_format($investmentAmount, 2, '.', ''));
                                                }
                                            }
                                        }
                                    }),

                                TextInput::make('investment_amount')
                                    ->label('Investment Amount')
                                    ->numeric()
                                    ->prefix('PKR')
                                    ->required()
                                    ->reactive()
                                    ->rules([
                                        function (callable $get) {
                                            return function (string $attribute, $value, $fail) use ($get) {
                                                $partners = $get('../../partners') ?? [];
                                                $currentIndex = array_key_last($partners);
                                                
                                                if ($currentIndex !== null) {
                                                    $investorId = $get("../../partners.{$currentIndex}.investor_id");
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
                                                }
                                            };
                                        },
                                    ])
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        // Get the current partner's data
                                        $partners = $get('../../partners') ?? [];
                                        $currentIndex = array_key_last($partners);
                                        
                                        if ($currentIndex !== null) {
                                            $investorId = $get("../../partners.{$currentIndex}.investor_id");
                                            
                                            if ($investorId) {
                                                $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                                                if ($wallet) {
                                                    $amount = floatval($state);
                                                    if ($amount > 0 && $amount > $wallet->amount) {
                                                        $set('_wallet_error', "Insufficient wallet balance. Available: PKR " . number_format($wallet->amount, 2));
                                                        $set('investment_percentage', 0);
                                                        return;
                                                    }
                                                    
                                                    // If we get here, the amount is valid
                                                    $amountRequired = floatval($get('../../amount_required') ?? 0);
                                                    $percentage = $amountRequired > 0 ? round(($amount / $amountRequired) * 100, 2) : 0;
                                                    $set('investment_percentage', $percentage);
                                                    $set('_wallet_error', null);
                                                } else {
                                                    $set('_wallet_error', "No wallet found for this investor");
                                                    $set('investment_percentage', 0);
                                                }
                                            } else {
                                                // If no investor is selected, just calculate the percentage
                                                $amountRequired = floatval($get('../../amount_required') ?? 0);
                                                $percentage = $amountRequired > 0 ? round(($state / $amountRequired) * 100, 2) : 0;
                                                $set('investment_percentage', $percentage);
                                            }
                                        }
                                    })
                                    ->helperText(function (callable $get) {
                                        $partners = $get('../../partners') ?? [];
                                        $currentIndex = array_key_last($partners);
                                        
                                        if ($currentIndex !== null) {
                                            $investorId = $get("../../partners.{$currentIndex}.investor_id");
                                            if ($investorId) {
                                                $wallet = \App\Models\Wallet::where('investor_id', $investorId)->first();
                                                if ($wallet) {
                                                    $amount = floatval($get("../../partners.{$currentIndex}.investment_amount") ?? 0);
                                                    
                                                    if ($amount > 0 && $amount > $wallet->amount) {
                                                        return "Insufficient wallet balance. Available: PKR " . number_format($wallet->amount, 2);
                                                    }
                                                    
                                                    return "Available balance: PKR " . number_format($wallet->amount, 2);
                                                }
                                                return "No wallet found for this investor";
                                            }
                                        }
                                        return null;
                                    }),

                                TextInput::make('investment_percentage')
                                    ->label('Investment Percentage')
                                    ->disabled()
                                    ->numeric()
                                    ->suffix('%')
                                    ->required()
                                    ->default(0),
                            ])
                            ->columns(2),
                    ]),
            ]);
    }
}