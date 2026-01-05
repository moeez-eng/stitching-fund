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

                    return new HtmlString("
                        <div style='background: #1f2937; padding: 24px; border-radius: 8px;'>
                            <!-- Cost Breakdown Section -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>COST BREAKDOWN</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%;'>Materials</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($materialsTotal, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%;'>Labor</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($expensesTotal, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%; font-weight: 600;'>Total Cost</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 16px; text-align: right; width: 50%; font-weight: 600;'>PKR " . number_format($totalCost, 0) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pricing Section -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>PRICING</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%;'>Profit Margin</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>" . $profitPercentage . "%</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%;'>Profit Amount</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($profitAmount, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%; font-weight: 600;'>Selling Price</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 16px; text-align: right; width: 50%; font-weight: 600;'>PKR " . number_format($sellingPrice, 0) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Status Section -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>PAYMENT STATUS</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%;'>Amount Received</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($marketPaymentsReceived, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 50%; font-weight: 600;'>Remaining Balance</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 16px; text-align: right; width: 50%; font-weight: 600;'>PKR " . number_format($balanceRemaining, 0) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Investment Status -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>INVESTMENT COLLECTION STATUS</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 12px; background: #374151; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: #9ca3af; font-size: 12px; margin-bottom: 4px;'>Total Collected</div>
                                            <div style='color: white; font-size: 16px; font-weight: 600;'>PKR " . number_format($record->total_collected, 0) . "</div>
                                        </div>
                                        <div style='display: table-cell; padding: 12px; background: #374151; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: #9ca3af; font-size: 12px; margin-bottom: 4px;'>Collection Progress</div>
                                            <div style='color: white; font-size: 16px; font-weight: 600;'>" . number_format($record->percentage_collected, 2) . "%</div>
                                        </div>
                                        <div style='display: table-cell; padding: 12px; background: #374151; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: #9ca3af; font-size: 12px; margin-bottom: 4px;'>Remaining Amount</div>
                                            <div style='color: white; font-size: 16px; font-weight: 600;'>PKR " . number_format($record->remaining_amount, 0) . "</div>
                                        </div>
                                        <div style='display: table-cell; padding: 12px; background: #374151; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: #9ca3af; font-size: 12px; margin-bottom: 4px;'>Total Partners</div>
                                            <div style='color: white; font-size: 16px; font-weight: 600;'>" . $record->number_of_partners . "</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    ");
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
                    <div style="background: #1f2937; padding: 24px; border-radius: 8px;">
                        <!-- Partner Distribution Overview -->
                        <div style="margin-bottom: 32px;">
                            <h3 style="color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;">PARTNER DISTRIBUTION OVERVIEW</h3>
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-row;">
                                    <div style="display: table-cell; padding: 12px; background: #374151; border-radius: 6px; text-align: center; width: 33.33%;">
                                        <div style="color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Total Partners</div>
                                        <div style="color: white; font-size: 16px; font-weight: 600;">' . count($record->partners) . '</div>
                                    </div>
                                    <div style="display: table-cell; padding: 12px; background: #374151; border-radius: 6px; text-align: center; width: 33.33%;">
                                        <div style="color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Total Investment</div>
                                        <div style="color: white; font-size: 16px; font-weight: 600;">PKR ' . number_format($record->total_collected, 0) . '</div>
                                    </div>
                                    <div style="display: table-cell; padding: 12px; background: #374151; border-radius: 6px; text-align: center; width: 33.33%;">
                                        <div style="color: #9ca3af; font-size: 12px; margin-bottom: 4px;">Expected Returns</div>
                                        <div style="color: white; font-size: 16px; font-weight: 600;">PKR ' . number_format($sellingPrice, 0) . '</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Individual Partners -->
                        <div style="margin-bottom: 32px;">
                            <h3 style="color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;">INDIVIDUAL PARTNER DETAILS</h3>';
                    
                    foreach ($record->partners as $index => $partner) {
                        $partnerName = $partner['name'] ?? "Partner " . ($index + 1);
                        $partnerAmount = $partner['investment_amount'] ?? 0;
                        $partnerPercentage = $partner['investment_percentage'] ?? 0;
                        $expectedReturn = $sellingPrice * ($partnerPercentage / 100);
                        
                        $partnersHtml .= '
                            <div style="background: #374151; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                                <div style="display: table; width: 100%; border-collapse: collapse; margin-bottom: 12px;">
                                    <div style="display: table-row;">
                                        <div style="display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 25%;">Partner Name</div>
                                        <div style="display: table-cell; padding: 8px; color: white; font-size: 14px; font-weight: 600; width: 75%;">' . $partnerName  ($index + 1) . ')</div>
                                    </div>
                                    <div style="display: table-row;">
                                        <div style="display: table-cell; padding: 8px; color: #9ca3af; font-size: 14px; width: 25%;">Investment Share</div>
                                        <div style="display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 75%; font-weight: 600;">' . $partnerPercentage . '%</div>
                                    </div>
                                </div>
                                
                                <div style="display: table; width: 100%; border-collapse: collapse;">
                                    <div style="display: table-row;">
                                        <div style="display: table-cell; padding: 8px; background: #4b5563; border-radius: 6px; text-align: center; width: 33.33%;">
                                            <div style="color: #d1d5db; font-size: 12px; margin-bottom: 4px;">Invested Amount</div>
                                            <div style="color: white; font-size: 14px; font-weight: 600;">PKR ' . number_format($partnerAmount, 0) . '</div>
                                        </div>
                                        <div style="display: table-cell; padding: 8px; background: #4b5563; border-radius: 6px; text-align: center; width: 33.33%;">
                                            <div style="color: #d1d5db; font-size: 12px; margin-bottom: 4px;">Expected Return</div>
                                            <div style="color: white; font-size: 14px; font-weight: 600;">PKR ' . number_format($expectedReturn, 0) . '</div>
                                            <div style="color: #10b981; font-size: 11px;">' . round(($partnerAmount > 0 ? ($expectedReturn / $partnerAmount) * 100 : 0), 1) . '% ROI</div>
                                        </div>
                                        <div style="display: table-cell; padding: 8px; background: #4b5563; border-radius: 6px; text-align: center; width: 33.33%;">
                                            <div style="color: #d1d5db; font-size: 12px; margin-bottom: 4px;">Profit Share</div>
                                            <div style="color: white; font-size: 14px; font-weight: 600;">PKR ' . number_format($expectedReturn - $partnerAmount, 0) . '</div>
                                            <div style="color: #8b5cf6; font-size: 11px;">Net profit</div>
                                        </div>
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
