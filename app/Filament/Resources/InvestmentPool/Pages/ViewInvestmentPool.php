<?php

namespace App\Filament\Resources\InvestmentPool\Pages;

use Filament\Resources\Pages\ViewRecord;
use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Route;

class ViewInvestmentPool extends ViewRecord
{
    protected static string $resource = \App\Filament\Resources\InvestmentPool\InvestmentPoolResource::class;

    protected static ?string $title = 'Investment Pool Details';

    public function getBreadcrumb(): string
    {
        return 'View';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_summary')
                ->label('Pool Summary')
                ->icon('heroicon-o-chart-bar')
                ->color('info')
                ->modalSubmitAction(false)
                ->modalContent(function () {
                    $record = $this->getRecord();
                    $lat = $record->lat;
                    
                    if (!$lat) {
                        return new HtmlString('<p>No LAT data available</p>');
                    }
                    
                    $materials = $lat->materials;
                    $expenses = $lat->expenses;
                    $materialsTotal = $materials->sum('price');
                    $expensesTotal = $expenses->sum('price');
                    $totalCost = $materialsTotal + $expensesTotal;
                    
                    $profitPercentage = $lat->profit_percentage ?? 10;
                    $profitAmount = ($totalCost * $profitPercentage) / 100;
                    $sellingPrice = $totalCost + $profitAmount;
                    
                    $marketPaymentsReceived = $lat->market_payments_received ?? 0;
                    $paymentPercentage = $sellingPrice > 0 ? round(($marketPaymentsReceived / $sellingPrice) * 100, 1) : 0;
                    $balanceRemaining = $sellingPrice - $marketPaymentsReceived;

                    return new HtmlString('
                    <div class="space-y-4 p-2">
                        <!-- Cost Breakdown Section -->
                        <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-6 shadow-sm border border-red-200">
                            <h3 class="text-lg font-bold text-red-800 mb-4 flex items-center">
                                <span class="mr-2">💰</span> Cost Breakdown
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="text-2xl mb-1">📦</div>
                                    <div class="text-xs text-red-600 font-medium">Materials</div>
                                    <div class="text-xl font-bold text-red-800">PKR ' . number_format($materialsTotal, 0) . '</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="text-2xl mb-1">👷</div>
                                    <div class="text-xs text-red-600 font-medium">Labor</div>
                                    <div class="text-xl font-bold text-red-800">PKR ' . number_format($expensesTotal, 0) . '</div>
                                </div>
                                <div class="bg-red-600 rounded-lg p-4 shadow-sm text-white">
                                    <div class="text-2xl mb-1">📊</div>
                                    <div class="text-xs text-red-100 font-medium">Total Cost</div>
                                    <div class="text-xl font-bold">PKR ' . number_format($totalCost, 0) . '</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Pricing Section -->
                        <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-6 shadow-sm border border-green-200">
                            <h3 class="text-lg font-bold text-green-800 mb-4 flex items-center">
                                <span class="mr-2">💵</span> Pricing Details
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="text-2xl mb-1">📈</div>
                                    <div class="text-xs text-green-600 font-medium">Profit Margin</div>
                                    <div class="text-xl font-bold text-green-800">' . $profitPercentage . '%</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="text-2xl mb-1">💎</div>
                                    <div class="text-xs text-green-600 font-medium">Profit Amount</div>
                                    <div class="text-xl font-bold text-green-800">PKR ' . number_format($profitAmount, 0) . '</div>
                                </div>
                                <div class="bg-green-600 rounded-lg p-4 shadow-sm text-white">
                                    <div class="text-2xl mb-1">🏷️</div>
                                    <div class="text-xs text-green-100 font-medium">Selling Price</div>
                                    <div class="text-xl font-bold">PKR ' . number_format($sellingPrice, 0) . '</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Payment Status Section -->
                        <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-6 shadow-sm border border-blue-200">
                            <h3 class="text-lg font-bold text-blue-800 mb-4 flex items-center">
                                <span class="mr-2">💳</span> Payment Status
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="text-2xl mb-1">✅</div>
                                    <div class="text-xs text-blue-600 font-medium">Amount Received</div>
                                    <div class="text-xl font-bold text-blue-800">PKR ' . number_format($marketPaymentsReceived, 0) . '</div>
                                    <div class="text-xs text-blue-600 mt-1">' . $paymentPercentage . '% of selling price</div>
                                </div>
                                <div class="bg-blue-600 rounded-lg p-4 shadow-sm text-white">
                                    <div class="text-2xl mb-1">⏳</div>
                                    <div class="text-xs text-blue-100 font-medium">Remaining Balance</div>
                                    <div class="text-xl font-bold">PKR ' . number_format($balanceRemaining, 0) . '</div>
                                    <div class="text-xs text-blue-100 mt-1">' . round((100 - $paymentPercentage), 1) . '% pending</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Investment Collection Status -->
                        <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-6 shadow-sm border border-purple-200">
                            <h3 class="text-lg font-bold text-purple-800 mb-4 flex items-center">
                                <span class="mr-2">🏦</span> Investment Collection Status
                            </h3>
                            <div class="grid grid-cols-4 gap-3">
                                <div class="bg-white rounded-lg p-3 shadow-sm text-center">
                                    <div class="text-xl mb-1">💰</div>
                                    <div class="text-xs text-purple-600 font-medium">Total Collected</div>
                                    <div class="text-lg font-bold text-purple-800">PKR ' . number_format($record->total_collected, 0) . '</div>
                                </div>
                                <div class="bg-white rounded-lg p-3 shadow-sm text-center">
                                    <div class="text-xl mb-1">📊</div>
                                    <div class="text-xs text-purple-600 font-medium">Progress</div>
                                    <div class="text-lg font-bold text-purple-800">' . number_format($record->percentage_collected, 1) . '%</div>
                                </div>
                                <div class="bg-white rounded-lg p-3 shadow-sm text-center">
                                    <div class="text-xl mb-1">💸</div>
                                    <div class="text-xs text-purple-600 font-medium">Remaining</div>
                                    <div class="text-lg font-bold text-purple-800">PKR ' . number_format($record->remaining_amount, 0) . '</div>
                                </div>
                                <div class="bg-purple-600 rounded-lg p-3 shadow-sm text-white text-center">
                                    <div class="text-xl mb-1">👥</div>
                                    <div class="text-xs text-purple-100 font-medium">Partners</div>
                                    <div class="text-lg font-bold">' . $record->number_of_partners . '</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Partner Distribution Overview -->
                        <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-sm border border-indigo-200">
                            <h3 class="text-lg font-bold text-indigo-800 mb-4 flex items-center">
                                <span class="mr-2">🤝</span> Partner Distribution Overview
                            </h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm">
                                    <div class="text-2xl mb-1">🏦</div>
                                    <div class="text-xs text-indigo-600 font-medium">Total Investment</div>
                                    <div class="text-xl font-bold text-indigo-800">PKR ' . number_format($record->total_collected, 0) . '</div>
                                </div>
                                <div class="bg-indigo-600 rounded-lg p-4 shadow-sm text-white">
                                    <div class="text-2xl mb-1">📈</div>
                                    <div class="text-xs text-indigo-100 font-medium">Expected Returns</div>
                                    <div class="text-xl font-bold">PKR ' . number_format($sellingPrice, 0) . '</div>
                                </div>
                            </div>
                        </div>
                    </div>
                ');
                }),
                
            Action::make('view_partners')
                ->label('Partner Distribution')
                ->icon('heroicon-o-users')
                ->color('success')
                ->modalSubmitAction(false)
                ->modalContent(function () {
                    $record = $this->getRecord();
                    
                    if (!$record->partners || !is_array($record->partners)) {
                        return new HtmlString('<p>No partners found</p>');
                    }
                    
                    $lat = $record->lat;
                    $sellingPrice = 0;
                    if ($lat) {
                        $materialsTotal = $lat->materials->sum('price');
                        $expensesTotal = $lat->expenses->sum('price');
                        $totalCost = $materialsTotal + $expensesTotal;
                        $profitPercentage = $lat->profit_percentage ?? 10;
                        $profitAmount = ($totalCost * $profitPercentage) / 100;
                        $sellingPrice = $totalCost + $profitAmount;
                    }
                    
                    $partnersHtml = '
                    <div class="space-y-4 p-2">
                        <!-- Summary Header -->
                        <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-xl p-6 shadow-sm border border-indigo-200">
                            <h3 class="text-lg font-bold text-indigo-800 mb-2 flex items-center">
                                <span class="mr-2">👥</span> Partner Distribution Overview
                            </h3>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="bg-white rounded-lg p-4 shadow-sm text-center">
                                    <div class="text-2xl mb-1">👫</div>
                                    <div class="text-xs text-indigo-600 font-medium">Total Partners</div>
                                    <div class="text-xl font-bold text-indigo-800">' . count($record->partners) . '</div>
                                </div>
                                <div class="bg-white rounded-lg p-4 shadow-sm text-center">
                                    <div class="text-2xl mb-1">💰</div>
                                    <div class="text-xs text-indigo-600 font-medium">Total Investment</div>
                                    <div class="text-xl font-bold text-indigo-800">PKR ' . number_format($record->total_collected, 0) . '</div>
                                </div>
                                <div class="bg-indigo-600 rounded-lg p-4 shadow-sm text-white text-center">
                                    <div class="text-2xl mb-1">📈</div>
                                    <div class="text-xs text-indigo-100 font-medium">Expected Returns</div>
                                    <div class="text-xl font-bold">PKR ' . number_format($sellingPrice, 0) . '</div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Individual Partners -->
                        <div class="space-y-3">';
                    
                    foreach ($record->partners as $index => $partner) {
                        $partnerName = $partner['name'] ?? "Partner " . ($index + 1);
                        $partnerAmount = $partner['investment_amount'] ?? 0;
                        $partnerPercentage = $partner['investment_percentage'] ?? 0;
                        $expectedReturn = $sellingPrice * ($partnerPercentage / 100);
                        
                        $partnersHtml .= '
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-6 shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg mr-4">
                                            ' . strtoupper(substr($partnerName, 0, 1)) . '
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-bold text-gray-800">' . $partnerName . '</h4>
                                            <div class="text-sm text-gray-600">Partner #' . ($index + 1) . '</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs text-gray-500">Investment Share</div>
                                        <div class="text-2xl font-bold text-purple-600">' . $partnerPercentage . '%</div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-3 gap-4">
                                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                        <div class="flex items-center mb-2">
                                            <span class="text-xl mr-2">💵</span>
                                            <div class="text-xs text-blue-600 font-medium">Invested Amount</div>
                                        </div>
                                        <div class="text-xl font-bold text-blue-800">PKR ' . number_format($partnerAmount, 0) . '</div>
                                        <div class="text-xs text-blue-600 mt-1">of total investment</div>
                                    </div>
                                    
                                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                        <div class="flex items-center mb-2">
                                            <span class="text-xl mr-2">📊</span>
                                            <div class="text-xs text-green-600 font-medium">Expected Return</div>
                                        </div>
                                        <div class="text-xl font-bold text-green-800">PKR ' . number_format($expectedReturn, 0) . '</div>
                                        <div class="text-xs text-green-600 mt-1">' . round(($partnerAmount > 0 ? ($expectedReturn / $partnerAmount) * 100 : 0), 1) . '% ROI</div>
                                    </div>
                                    
                                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                        <div class="flex items-center mb-2">
                                            <span class="text-xl mr-2">🎯</span>
                                            <div class="text-xs text-purple-600 font-medium">Profit Share</div>
                                        </div>
                                        <div class="text-xl font-bold text-purple-800">PKR ' . number_format($expectedReturn - $partnerAmount, 0) . '</div>
                                        <div class="text-xs text-purple-600 mt-1">Net profit</div>
                                    </div>
                                </div>
                            </div>';
                    }
                    
                    $partnersHtml .= '
                        </div>
                    </div>';
                    
                    return new HtmlString($partnersHtml);
                }),
                
            Action::make('cancel')
                ->label('Cancel')
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->url(\App\Filament\Resources\InvestmentPool\InvestmentPoolResource::getUrl('index'))
                ->openUrlInNewTab(false),
        ];
    }
}
