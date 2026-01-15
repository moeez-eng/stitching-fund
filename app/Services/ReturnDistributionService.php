<?php

namespace App\Services;

use App\Models\InvestmentPool;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Models\WalletAllocation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;

class ReturnDistributionService
{
    /**
     * Distribute returns to all partners in an investment pool
     */
    public function distributeReturns(InvestmentPool $pool): array
    {
        try {
            DB::beginTransaction();

            $lat = $pool->lat;
            if (!$lat) {
                throw new \Exception('No LAT found for this investment pool');
            }
 
            // Calculate total selling price
            $materialsTotal = $lat->materials->sum('price');
            $expensesTotal = $lat->expenses->sum('price');
            $totalCost = $materialsTotal + $expensesTotal;
            $profitPercentage = $lat->profit_percentage ?? 10;
            $profitAmount = ($totalCost * $profitPercentage) / 100;
            $sellingPrice = $totalCost + $profitAmount;

            $marketPaymentsReceived = $lat->market_payments_received ?? 0;
            
            if ($marketPaymentsReceived <= 0) {
                throw new \Exception('No market payments received to distribute');
            }

            // Get partners data
            if (!$pool->partners || !is_array($pool->partners)) {
                throw new \Exception('No partners found in this investment pool');
            }

            $distributionResults = [];
            $totalDistributed = 0;

            foreach ($pool->partners as $partner) {
                $partnerName = $partner['name'] ?? 'Unknown Partner';
                $partnerPercentage = $partner['investment_percentage'] ?? 0;
                $investorId = $partner['investor_id'] ?? null;

                if ($partnerPercentage <= 0 || !$investorId) {
                    continue;
                }

                // Calculate partner's share
                $partnerShare = ($marketPaymentsReceived * $partnerPercentage) / 100;
                
                // Update or create wallet record for investor
                $wallet = Wallet::where('investor_id', $investorId)->first();
                
                if (!$wallet) {
                    $wallet = Wallet::create([
                        'investor_id' => $investorId,
                        'amount' => 0,
                        'total_deposits' => 0
                    ]);
                }

                // Add distributed amount to wallet
                $wallet->amount += $partnerShare;
                $wallet->save();

                // Create transaction record for return
                WalletLedger::createReturn($wallet, $partnerShare, "Return distribution from investment pool: {$pool->lat_no}", $pool->id);

                // Create transaction record to reduce active investment
                $wallet->ledgers()->create([
                    'type' => 'invest',
                    'amount' => -$partnerShare,
                    'description' => "Investment return from pool: {$pool->lat_no}",
                    'reference_type' => 'investment_pool',
                    'reference_id' => $pool->id,
                    'transaction_date' => now()
                ]);

                // Remove or update wallet allocation to reduce active invested amount
                $allocation = WalletAllocation::where('wallet_id', $wallet->id)
                    ->where('investment_pool_id', $pool->id)
                    ->first();
                
                if ($allocation) {
                    if ($allocation->amount <= $partnerShare) {
                        // Delete allocation if fully returned
                        $allocation->delete();
                    } else {
                        // Reduce allocation amount if partially returned
                        $allocation->amount -= $partnerShare;
                        $allocation->save();
                    }
                }

                // Send notification to investor
                $user = \App\Models\User::find($investorId);
                if ($user) {
                    Log::info('Sending return distribution notification', [
                        'user_id' => $user->id,
                        'amount' => $partnerShare,
                        'pool_lat_no' => $pool->lat_no
                    ]);
                    
                   Notification::make()
                        ->title('Returns Distributed')
                        ->body("You have received PKR " . number_format($partnerShare, 0) . " in returns from investment pool: {$pool->lat_no}")
                        ->success()
                        ->sendToDatabase($user);
                        
                   Log::info('Notification sent successfully', ['user_id' => $user->id]);
                } else {
                    Log::warning('User not found for notification', ['investor_id' => $investorId]);
                }

                $distributionResults[] = [
                    'partner_name' => $partnerName,
                    'percentage' => $partnerPercentage,
                    'amount_distributed' => $partnerShare,
                    'wallet_balance_after' => $wallet->amount
                ];

                $totalDistributed += $partnerShare;
            }

            // Mark the pool as returns distributed and closed
            $pool->returns_distributed = true;
            $pool->returns_distributed_at = now();
            $pool->status = 'closed';
            $pool->saveQuietly();

            DB::commit();

            return [
                'success' => true,
                'message' => 'Returns distributed successfully',
                'total_distributed' => $totalDistributed,
                'distribution_details' => $distributionResults
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Return distribution failed: ' . $e->getMessage(), [
                'pool_id' => $pool->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Failed to distribute returns: ' . $e->getMessage(),
                'total_distributed' => 0,
                'distribution_details' => []
            ];
        }
    }

    /**
     * Check if returns can be distributed for a pool
     */
    public function canDistributeReturns(InvestmentPool $pool): bool
    {
        $lat = $pool->lat;
        
        if (!$lat) {
            return false;
        }

        if ($lat->market_payments_received <= 0) {
            return false;
        }

        if ($pool->returns_distributed) {
            return false;
        }

        if (!$pool->partners || !is_array($pool->partners)) {
            return false;
        }

        return true;
    }
}