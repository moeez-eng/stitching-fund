<?php

namespace App\Http\Controllers;

use App\Models\WithdrawalRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function withdrawRequest(Request $request)
    {
        try {
            $request->validate([
                'wallet_id' => 'required|exists:wallets,id',
                'investor_id' => 'required|exists:users,id',
                'investor_name' => 'required|string',
                'requested_amount' => 'required|numeric|min:100',
            ]);

            $wallet = Wallet::findOrFail($request->wallet_id);
            
            // Verify wallet belongs to the investor
            if ($wallet->investor_id != $request->investor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized: Wallet does not belong to investor'
                ], 403);
            }

            // Check available balance
            $availableBalance = $wallet->available_balance;
            if ($request->requested_amount > $availableBalance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Requested amount exceeds available balance'
                ], 400);
            }

            // Create withdrawal request
            $withdrawalRequest = WithdrawalRequest::create([
                'wallet_id' => $request->wallet_id,
                'investor_id' => $request->investor_id,
                'investor_name' => $request->investor_name,
                'requested_amount' => $request->requested_amount,
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdrawal request submitted successfully',
                'request_id' => $withdrawalRequest->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
