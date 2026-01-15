<?php

namespace App\Filament\Resources\InvestmentPool\Pages;

use Filament\Actions\Action;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

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
                        <div style='background: #581c87; padding: 24px; border-radius: 8px;'>
                            <!-- Cost Breakdown Section -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>COST BREAKDOWN</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%;'>Materials</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($materialsTotal, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%;'>Labor</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($expensesTotal, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%; font-weight: 600;'>Total Cost</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 16px; text-align: right; width: 50%; font-weight: 600;'>PKR " . number_format($totalCost, 0) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Pricing Section -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>PRICING</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%;'>Profit Margin</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>" . $profitPercentage . "%</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%;'>Profit Amount</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($profitAmount, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%; font-weight: 600;'>Selling Price</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 16px; text-align: right; width: 50%; font-weight: 600;'>PKR " . number_format($sellingPrice, 0) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Status Section -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>PAYMENT STATUS</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%;'>Amount Received</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 50%; font-weight: 500;'>PKR " . number_format($marketPaymentsReceived, 0) . "</div>
                                    </div>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 14px; width: 50%; font-weight: 600;'>Remaining Balance</div>
                                        <div style='display: table-cell; padding: 8px; color: white; font-size: 16px; text-align: right; width: 50%; font-weight: 600;'>PKR " . number_format($balanceRemaining, 0) . "</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Investment Status -->
                            <div style='margin-bottom: 32px;'>
                                <h3 style='color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;'>INVESTMENT COLLECTION STATUS</h3>
                                <div style='display: table; width: 100%; border-collapse: collapse;'>
                                    <div style='display: table-row;'>
                                        <div style='display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: white; font-size: 12px; margin-bottom: 4px;'>Total Collected</div>
                                            <div style='color: white; font-size: 16px; font-weight: 600;'>PKR " . number_format($record->total_collected, 0) . "</div>
                                        </div>
                                        <div style='display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: white; font-size: 12px; margin-bottom: 4px;'>Collection Progress</div>
                                            <div style='color: white; font-size: 16px; font-weight: 600;'>" . number_format($record->percentage_collected, 0) . "%</div>
                                        </div>
                                        <div style='display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: white; font-size: 12px; margin-bottom: 4px;'>Remaining Amount</div>
                                            <div style='color: white; font-size: 16px; font-weight: 600;'>PKR " . number_format($record->remaining_amount, 0) . "</div>
                                        </div>
                                        <div style='display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;'>
                                            <div style='color: white; font-size: 12px; margin-bottom: 4px;'>Total Partners</div>
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
                ->modalSubmitAction(false)
                ->modalContent(function () {
                    $record = $this->getRecord();
                    
                    if (!$record->partners || !is_array($record->partners)) {
                        return new HtmlString('<p>No partners found</p>');
                    }
                    
                    $lat = $record->lat;
                    $sellingPrice = 0;
                    $marketPaymentsReceived = 0;
                    
                    if ($lat) {
                        $materialsTotal = $lat->materials->sum('price');
                        $expensesTotal = $lat->expenses->sum('price');
                        $totalCost = $materialsTotal + $expensesTotal;
                        $profitPercentage = $lat->profit_percentage ?? 10;
                        $profitAmount = ($totalCost * $profitPercentage) / 100;
                        $sellingPrice = $totalCost + $profitAmount;
                        $marketPaymentsReceived = $lat->market_payments_received ?? 0;
                    }
                    
                    $partnersHtml = '
                    <div style="background: #581c87; padding: 24px; border-radius: 8px;">
                        <!-- Partner Distribution Overview -->
                        <div style="margin-bottom: 32px;">
                            <h3 style="color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;">PARTNER DISTRIBUTION OVERVIEW</h3>
                            <div style="display: table; width: 100%; border-collapse: collapse;">
                                <div style="display: table-row;">
                                    <div style="display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;">
                                        <div style="color: white; font-size: 12px; margin-bottom: 4px;">Total Partners</div>
                                        <div style="color: white; font-size: 16px; font-weight: 600;">' . count($record->partners) . '</div>
                                    </div>
                                    <div style="display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;">
                                        <div style="color: white; font-size: 12px; margin-bottom: 4px;">Total Investment</div>
                                        <div style="color: white; font-size: 16px; font-weight: 600;">PKR ' . number_format($record->total_collected, 0) . '</div>
                                    </div>
                                    <div style="display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;">
                                        <div style="color: white; font-size: 12px; margin-bottom: 4px;">Expected Returns</div>
                                        <div style="color: white; font-size: 16px; font-weight: 600;">PKR ' . number_format($sellingPrice, 0) . '</div>
                                    </div>
                                    <div style="display: table-cell; padding: 12px; background: #6b21a8; border-radius: 6px; text-align: center; width: 25%;">
                                        <div style="color: white; font-size: 12px; margin-bottom: 4px;">Payments Received</div>
                                        <div style="color: white; font-size: 16px; font-weight: 600;">PKR ' . number_format($marketPaymentsReceived, 0) . '</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Individual Partners -->
                        <div style="margin-bottom: 32px;">
                            <h3 style="color: white; font-size: 18px; font-weight: 600; margin-bottom: 16px; text-transform: uppercase;">INDIVIDUAL PARTNER DETAILS</h3>';
                    
                    foreach ($record->partners as $index => $partner) {
                        $investorId = $partner['investor_id'] ?? null;
                        $partnerAmount = $partner['investment_amount'] ?? 0;
                        $partnerPercentage = $partner['investment_percentage'] ?? 0;
                        
                        // Get actual partner name from database
                        $partnerName = 'Unknown Partner';
                        if ($investorId) {
                            $user = \App\Models\User::find($investorId);
                            if ($user) {
                                $partnerName = $user->name ?? 'Unknown Partner';
                            }
                        }
                        
                        // Calculate expected return based on selling price
                        $expectedReturn = $sellingPrice * ($partnerPercentage / 100);
                        
                        // Calculate actual return based on payments received
                        $actualReturn = $marketPaymentsReceived * ($partnerPercentage / 100);
                        
                        // Calculate profit/loss
                        $profitLoss = $actualReturn - $partnerAmount;
                        $roiPercentage = $partnerAmount > 0 ? (($actualReturn - $partnerAmount) / $partnerAmount) * 100 : 0;
                        
                        // Determine colors based on profit/loss
                        $profitColor = $profitLoss >= 0 ? '#1aeb3db9' : '#f70606ff';
                        $roiColor = $roiPercentage >= 0 ? '#1aeb3db9' : '#cc0202ff';
                        $profitLabel = $profitLoss >= 0 ? 'Net profit' : 'Net loss';
                        
                        $partnersHtml .= '
                            <div style="background: #6b21a8; padding: 16px; border-radius: 8px; margin-bottom: 16px;">
                                <div style="display: table; width: 100%; border-collapse: collapse; margin-bottom: 12px;">
                                    <div style="display: table-row;">
                                        <div style="display: table-cell; padding: 8px; color: #e9d5ff; font-size: 14px; width: 25%;">Partner Name</div>
                                        <div style="display: table-cell; padding: 8px; color: white; font-size: 14px; font-weight: 600; width: 75%;">' . $partnerName . ' (' . ($index + 1) . ')</div>
                                    </div>
                                    <div style="display: table-row;">
                                        <div style="display: table-cell; padding: 8px; color: #e9d5ff; font-size: 14px; width: 25%;">Investment Share</div>
                                        <div style="display: table-cell; padding: 8px; color: white; font-size: 14px; text-align: right; width: 75%; font-weight: 600;">' . $partnerPercentage . '%</div>
                                    </div>
                                </div>
                                
                                <div style="display: table; width: 100%; border-collapse: collapse;">
                                    <div style="display: table-row;">
                                        <div style="display: table-cell; padding: 8px; background: #581c87; border-radius: 6px; text-align: center; width: 33.33%;">
                                            <div style="color: #e9d5ff; font-size: 12px; margin-bottom: 4px;">Invested Amount</div>
                                            <div style="color: white; font-size: 14px; font-weight: 600;">PKR ' . number_format($partnerAmount, 0) . '</div>
                                        </div>
                                        <div style="display: table-cell; padding: 8px; background: #581c87; border-radius: 6px; text-align: center; width: 33.33%;">
                                            <div style="color: #e9d5ff; font-size: 12px; margin-bottom: 4px;">Actual Return</div>
                                            <div style="color: white; font-size: 14px; font-weight: 600;">PKR ' . number_format($actualReturn, 0) . '</div>
                                            <div style="color: ' . $roiColor . '; font-size: 11px;">' . number_format($roiPercentage, 1) . '% ROI</div>
                                        </div>
                                        <div style="display: table-cell; padding: 8px; background: #581c87; border-radius: 6px; text-align: center; width: 33.33%;">
                                            <div style="color: #e9d5ff; font-size: 12px; margin-bottom: 4px;">Profit/Loss</div>
                                            <div style="color: ' . $profitColor . '; font-size: 14px; font-weight: 600;">PKR ' . number_format($profitLoss, 0) . '</div>
                                            <div style="color: ' . $profitColor . '; font-size: 11px;">' . $profitLabel . '</div>
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
            Action::make('distribute_returns')
                ->label('Distribute Returns')
                ->icon('heroicon-o-banknotes')
                ->requiresConfirmation()           // Add confirmation dialog
                ->modalHeading('Distribute Returns to Partners')
                ->modalDescription('Are you sure you want to distribute returns to all partners?')
                ->modalSubmitActionLabel('Yes, Distribute')
                ->action(function () {
                    $pool = $this->getRecord();
                    
                    try {
                        // Call the ReturnDistributionController
                        $controller = new \App\Http\Controllers\ReturnDistributionController(
                            new \App\Services\ReturnDistributionService()
                        );
                        
                        $response = $controller->distribute($pool);
                        $result = $response->getData(true);
                        
                        if ($result['success']) {
                            Notification::make()
                                ->title('Returns Distributed Successfully')
                                ->body("Total PKR " . number_format($result['data']['total_distributed'], 0) . " distributed to " . count($result['data']['distribution_details']) . " partners.")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Distribution Failed')
                                ->body($result['message'])
                                ->danger()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error')
                            ->body('Failed to distribute returns: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(function () {
                    // Only show if pool has received payments
                    $pool = $this->getRecord();
                    $lat = $pool->lat;
                    // Hide button for investors
                    $user = Auth::user();
                    if ($user && $user->role === 'Investor') {
                        return false;
                    }
                    
                    return $lat && $lat->market_payments_received > 0;
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
