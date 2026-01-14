<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class WalletController extends Controller
{
    public function withdrawRequest(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
            Notification::make()
                ->title('Error')
                ->body('Unauthorized access')
                ->danger()
                ->send();
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'wallet_id' => 'required|exists:wallets,id',
            'investor_id' => 'required|exists:users,id',
            'requested_amount' => 'required|numeric|min:100',
        ]);
        
        $wallet = Wallet::find($validated['wallet_id']);
        
        // Check permissions
        if ($user->role === 'Investor' && $wallet->investor_id !== $user->id) {
            Notification::make()
                ->title('Error')
                ->body('Access denied')
                ->danger()
                ->send();
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'Agency Owner' && $wallet->agency_owner_id !== $user->id) {
            Notification::make()
                ->title('Error')
                ->body('Access denied')
                ->danger()
                ->send();
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        // Check if amount is available
        if ($validated['requested_amount'] > $wallet->available_balance) {
            Notification::make()
                ->title('Error')
                ->body('Insufficient balance')
                ->danger()
                ->send();
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }
        
        // Create withdrawal request
        $withdrawalRequest = WithdrawalRequest::create([
            'wallet_id' => $validated['wallet_id'],
            'investor_id' => $validated['investor_id'],
            'investor_name' => $user->name,
            'requested_amount' => $validated['requested_amount'],
            'status' => 'pending',
        ]);
        
        // Send notification to Agency Owner
        $agencyOwner = $wallet->agencyOwner;
        if ($agencyOwner) {
            Notification::make()
                ->title('New Withdrawal Request')
                ->body("Investor '{$user->name}' has requested withdrawal of {$validated['requested_amount']} PKR.")
                ->warning()
                ->sendToDatabase($agencyOwner);
        } else {
            // Debug if agency owner not found
            Log::warning('Agency Owner not found for withdrawal notification', [
                'wallet_id' => $wallet->id,
                'agency_owner_id' => $wallet->agency_owner_id,
                'investor_id' => $user->id
            ]);
        }
        
        // Show success notification to current user
        Notification::make()
            ->title('Success')
            ->body('Withdrawal request submitted successfully')
            ->success()
            ->send();
            
        return response()->json(['success' => true, 'message' => 'Withdrawal request submitted successfully']);
    }        

    public function getAvailablePools(Request $request)
    {
        $user = Auth::user();
        
        // Debug: Log user info
        Log::info('User ID: ' . $user->id . ', Role: ' . $user->role);
        
        if (!$user || !in_array($user->role, ['Investor', 'Super Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized - User role: ' . ($user ? $user->role : 'null')], 401);
        }
        
        // Get all open pools first
        $openPools = \App\Models\InvestmentPool::where('status', 'open')->with('lat')->get();
        
        // Debug: Log total open pools
        Log::info('Total open pools: ' . $openPools->count());
        
        // Filter pools where investor has no investment
        $availablePools = $openPools->filter(function($pool) use ($user) {
            $hasInvestment = \App\Models\WalletAllocation::where([
                'investment_pool_id' => $pool->id,
                'investor_id' => $user->id
            ])->exists();
            
            Log::info('Pool ' . $pool->id . ' has investment: ' . ($hasInvestment ? 'yes' : 'no'));
            
            return !$hasInvestment;
        });
        
        Log::info('Available pools count: ' . $availablePools->count());
        
        return response()->json(['success' => true, 'pools' => $availablePools->values()->map(function($pool) {
            return [
                'id' => $pool->id,
                'design_name' => $pool->design_name,
                'amount_required' => $pool->amount_required,
                'total_collected' => $pool->total_collected,
                'percentage_collected' => $pool->percentage_collected,
                'remaining_amount' => $pool->remaining_amount,
                'status' => $pool->status,
                'lat_no' => $pool->lat ? $pool->lat->lat_no : null,
                'lat' => $pool->lat ? [
                    'id' => $pool->lat->id,
                    'lat_no' => $pool->lat->lat_no
                ] : null
            ];
        })]);
    }

    public function requestInvestment(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, ['Investor', 'Super Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'pool_id' => 'required|exists:investment_pools,id',
            'amount' => 'required|numeric|min:100',
        ]);
        
        $pool = \App\Models\InvestmentPool::find($validated['pool_id']);
        
        // Verify pool is still open and investor has no existing investment
        if ($pool->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'Pool is not open for investment'], 400);
        }
        
        $hasExistingInvestment = \App\Models\WalletAllocation::where([
            'investment_pool_id' => $validated['pool_id'],
            'investor_id' => $user->id
        ])->exists();
        
        if ($hasExistingInvestment) {
            return response()->json(['success' => false, 'message' => 'You already have an investment in this pool'], 400);
        }
        
        // Send notification to Agency Owner
        $agencyOwner = $pool->user;
        if ($agencyOwner) {
            Notification::make()
                ->title('New Investment Request')
                ->body("Investor '{$user->name}' wants to invest {$validated['amount']} PKR in Pool {$pool->id} ({$pool->design_name}).")
                ->info()
                ->sendToDatabase($agencyOwner);
        }
        
        // Show success notification to investor
        Notification::make()
            ->title('Investment Request Sent')
            ->body('Your investment request has been sent to the Agency Owner.')
            ->success()
            ->send();
            
        return response()->json(['success' => true, 'message' => 'Investment request sent successfully']);
    }

    public function investRequest(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, ['Investor', 'Super Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'pool_id' => 'required|exists:investment_pools,id',
            'amount' => 'required|numeric|min:100',
        ]);
        
        $pool = \App\Models\InvestmentPool::find($validated['pool_id']);
        
        // Verify pool is still open and investor has no existing investment
        if ($pool->status !== 'open') {
            return response()->json(['success' => false, 'message' => 'Pool is not open for investment'], 400);
        }
        
        $hasExistingInvestment = \App\Models\WalletAllocation::where([
            'investment_pool_id' => $validated['pool_id'],
            'investor_id' => $user->id
        ])->exists();
        
        if ($hasExistingInvestment) {
            return response()->json(['success' => false, 'message' => 'You already have an investment in this pool'], 400);
        }
        
        // Send notification to Agency Owner
        $agencyOwner = $pool->user;
        if ($agencyOwner) {
            Notification::make()
                ->title('New Investment Request')
                ->body("Investor '{$user->name}' wants to invest {$validated['amount']} PKR in Pool {$pool->id} ({$pool->design_name}).")
                ->info()
                ->sendToDatabase($agencyOwner);
        }
        
        // Show success notification to investor
        Notification::make()
            ->title('Investment Request Sent')
            ->body('Your investment request has been sent to the Agency Owner.')
            ->success()
            ->send();
            
        return response()->json(['success' => true, 'message' => 'Investment request sent successfully']);
    }

    public function getPoolData(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || !in_array($user->role, ['Investor', 'Super Admin'])) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }
        
        // Get all open pools first
        $openPools = \App\Models\InvestmentPool::where('status', 'open')->get();
        
        // Filter pools where investor has no investment
        $availablePools = $openPools->filter(function($pool) use ($user) {
            $hasInvestment = \App\Models\WalletAllocation::where([
                'investment_pool_id' => $pool->id,
                'investor_id' => $user->id
            ])->exists();
            
            return !$hasInvestment;
        });
        
        return response()->json(['success' => true, 'pools' => $availablePools->values()]);
    }

    public function simpleWallet()
    {
        $user = Auth::user();
        $statusFilter = request()->query('pool_status', 'all');
        
        // Get wallet data
        if ($user->role === 'Investor') {
            $wallets = Wallet::where('investor_id', $user->id)
                ->with(['investor', 'agencyOwner', 'allocations'])
                ->get();
        } else {
            $wallets = Wallet::where('agency_owner_id', $user->id)
                ->with(['investor', 'agencyOwner', 'allocations'])
                ->get();
        }
        
        // Get pools data
        $allPools = \App\Models\InvestmentPool::orderBy('created_at', 'desc')->get();
        $pools = $allPools;
        if ($statusFilter !== 'all') {
            $pools = $pools->where('status', $statusFilter);
        }
        
        return view('filament.wallet.pages.simple-wallet', compact('wallets', 'pools', 'statusFilter'));
    }
}