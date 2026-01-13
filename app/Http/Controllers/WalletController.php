<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\WithdrawalRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function withdrawRequest(Request $request)
    {
        $user = Auth::user();
        
        if (!$user) {
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
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        if ($user->role === 'Agency Owner' && $wallet->agency_owner_id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Access denied'], 403);
        }
        
        // Check if amount is available
        if ($validated['requested_amount'] > $wallet->available_balance) {
            return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
        }
        
        // Create withdrawal request
        $withdrawalRequest = WithdrawalRequest::create([
            'wallet_id' => $validated['wallet_id'],
            'investor_id' => $validated['investor_id'],
            'requested_amount' => $validated['requested_amount'],
            'status' => 'pending',
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Withdrawal request submitted successfully',
            'request_id' => $withdrawalRequest->id,
        ]);
    }
}