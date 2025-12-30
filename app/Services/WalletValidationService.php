<?php

namespace App\Services;

use App\Models\Wallet as WalletModel;
use App\Models\WalletAllocation;
use Exception;

class WalletValidationService
{
    public static function validateAllocation(int $investorId, float $requestedAmount): array
    {
        $wallet = WalletModel::where('investor_id', $investorId)->first();
        
        if (!$wallet) {
            return [
                'valid' => false,
                'message' => 'Investor wallet not found',
                'code' => 'WALLET_NOT_FOUND'
            ];
        }

        $availableBalance = $wallet->available_balance;

        // Check if wallet is empty
        if ($availableBalance == 0) {
            return [
                'valid' => false,
                'message' => 'Investor wallet is empty - no funds available for allocation',
                'code' => 'EMPTY_WALLET',
                'available_balance' => 0
            ];
        }

        // Check if sufficient funds
        if ($availableBalance < $requestedAmount) {
            return [
                'valid' => false,
                'message' => "Insufficient funds - requested: PKR " . number_format($requestedAmount, 0) . ", available: PKR " . number_format($availableBalance, 0),
                'code' => 'INSUFFICIENT_FUNDS',
                'requested_amount' => $requestedAmount,
                'available_balance' => $availableBalance
            ];
        }

        // Check minimum allocation amount
        if ($requestedAmount < 1000) {
            return [
                'valid' => false,
                'message' => 'Minimum allocation amount is PKR 1,000',
                'code' => 'MINIMUM_AMOUNT',
                'minimum_amount' => 1000
            ];
        }

        // All validations passed
        return [
            'valid' => true,
            'message' => 'Allocation validated successfully',
            'code' => 'VALID',
            'available_balance' => $availableBalance,
            'remaining_balance' => $availableBalance - $requestedAmount
        ];
    }

    public static function getInvestorSummary(int $investorId): array
    {
        $wallet = WalletModel::where('investor_id', $investorId)
            ->with(['allocations' => function($query) {
                $query->with('investmentPool');
            }])
            ->first();

        if (!$wallet) {
            return [
                'wallet_id' => null,
                'investor_name' => 'Unknown',
                'agency_name' => 'Unknown',
                'total_deposited' => 0,
                'total_allocated' => 0,
                'available_balance' => 0,
                'wallet_status' => 'not_found',
                'recent_allocations' => []
            ];
        }

        $allocations = $wallet->allocations;
        $totalAllocated = $allocations->sum('amount');

        return [
            'wallet_id' => $wallet->id,
            'investor_name' => $wallet->investor->name ?? 'Unknown',
            'agency_name' => $wallet->agencyOwner->name ?? 'Unknown',
            'total_deposited' => $wallet->amount,
            'total_allocated' => $totalAllocated,
            'available_balance' => $wallet->amount - $totalAllocated,
            'wallet_status' => $wallet->wallet_status,
            'recent_allocations' => $allocations->take(5)->map(function($allocation) {
                return [
                    'pool_name' => $allocation->investmentPool->name ?? 'Unknown Pool',
                    'amount' => $allocation->amount,
                    'allocated_at' => $allocation->created_at->format('M d, Y'),
                    'percentage_of_pool' => $allocation->investmentPool ? 
                        round(($allocation->amount / $allocation->investmentPool->total_required) * 100, 1) : 0
                ];
            })->toArray()
        ];
    }

    public static function getAgencySummary(int $agencyOwnerId): array
    {
        $wallets = WalletModel::where('agency_owner_id', $agencyOwnerId)
            ->with(['investor', 'allocations'])
            ->get();

        $totalDeposited = $wallets->sum('amount');
        $totalAllocated = $wallets->sum(function($wallet) {
            return $wallet->allocations->sum('amount');
        });
        $totalAvailable = $totalDeposited - $totalAllocated;

        return [
            'total_investors' => $wallets->count(),
            'total_deposited' => $totalDeposited,
            'total_allocated' => $totalAllocated,
            'total_available' => $totalAvailable,
            'investors_with_funds' => $wallets->filter(function($wallet) {
                return ($wallet->amount - $wallet->allocations->sum('amount')) > 0;
            })->count(),
            'investors_with_empty_wallets' => $wallets->filter(function($wallet) {
                return ($wallet->amount - $wallet->allocations->sum('amount')) == 0;
            })->count(),
            'investor_wallets' => $wallets->map(function($wallet) {
                $available = $wallet->amount - $wallet->allocations->sum('amount');
                return [
                    'investor_id' => $wallet->investor_id,
                    'investor_name' => $wallet->investor->name ?? 'Unknown',
                    'total_deposited' => $wallet->amount,
                    'total_allocated' => $wallet->allocations->sum('amount'),
                    'available_balance' => $available,
                    'wallet_status' => $available == 0 ? 'empty' : ($available < 50000 ? 'low' : 'healthy'),
                    'can_allocate' => $available > 0
                ];
            })->toArray()
        ];
    }
}
