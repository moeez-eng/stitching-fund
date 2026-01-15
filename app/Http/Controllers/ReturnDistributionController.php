<?php

namespace App\Http\Controllers;

use App\Models\InvestmentPool;
use App\Services\ReturnDistributionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ReturnDistributionController extends Controller
{
    protected ReturnDistributionService $distributionService;

    public function __construct(ReturnDistributionService $distributionService)
    {
        $this->distributionService = $distributionService;
    }

    /**
     * Distribute returns for a specific investment pool
     */
    public function distribute(InvestmentPool $pool): JsonResponse
    {
        try {
            // Check if user can distribute returns (Agency Owner or Super Admin)
            $user = Auth::user();
            if (!$user || !in_array($user->role, ['Agency Owner', 'Super Admin'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized to distribute returns'
                ], 403);
            }

            // Check if returns can be distributed
            if (!$this->distributionService->canDistributeReturns($pool)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Returns cannot be distributed for this pool. Please check if market payments have been received and returns haven\'t been distributed already.'
                ], 400);
            }

            // Perform the distribution
            $result = $this->distributionService->distributeReturns($pool);

            if ($result['success']) {
                Log::info('Returns distributed successfully', [
                    'pool_id' => $pool->id,
                    'user_id' => $user->id,
                    'total_distributed' => $result['total_distributed']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'data' => [
                        'total_distributed' => $result['total_distributed'],
                        'distribution_details' => $result['distribution_details']
                    ]
                ]);
            } else {
                Log::error('Return distribution failed', [
                    'pool_id' => $pool->id,
                    'user_id' => $user->id,
                    'error' => $result['message']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Unexpected error during return distribution', [
                'pool_id' => $pool->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while distributing returns'
            ], 500);
        }
    }

    /**
     * Check if returns can be distributed for a pool
     */
    public function checkDistributionStatus(InvestmentPool $pool): JsonResponse
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 401);
            }

            $canDistribute = $this->distributionService->canDistributeReturns($pool);
            $lat = $pool->lat;

            return response()->json([
                'success' => true,
                'data' => [
                    'can_distribute' => $canDistribute,
                    'returns_distributed' => $pool->returns_distributed ?? false,
                    'returns_distributed_at' => $pool->returns_distributed_at,
                    'market_payments_received' => $lat?->market_payments_received ?? 0,
                    'has_partners' => !empty($pool->partners) && is_array($pool->partners)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error checking distribution status', [
                'pool_id' => $pool->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to check distribution status'
            ], 500);
        }
    }
}