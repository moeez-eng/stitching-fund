<?php

namespace App\Services\Demo;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletLedger;
use App\Models\WalletAllocation;
use App\Models\WithdrawalRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DemoDataCleanupService
{
    /**
     * Clean up all data for a demo user when their account expires
     */
    public function cleanupExpiredDemoUser(User $user): void
    {
        try {
            DB::beginTransaction();
            
            Log::info('Starting demo data cleanup', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
            // 1. Get user's wallet if exists
            $wallet = Wallet::where('investor_id', $user->id)->first();
            
            if ($wallet) {
                // 2. Delete wallet allocations for this user
                WalletAllocation::where('investor_id', $user->id)->delete();
                
                // 3. Delete wallet ledger entries for this wallet
                WalletLedger::where('wallet_id', $wallet->id)->delete();
                
                // 4. Delete withdrawal requests for this user
                WithdrawalRequest::where('user_id', $user->id)->delete();
                
                // 5. Delete the wallet itself
                $wallet->delete();
                
                Log::info('Wallet data cleaned up', [
                    'wallet_id' => $wallet->id,
                    'user_id' => $user->id
                ]);
            }
            
            // 6. Delete any investment pools created by this user (if agency owner)
            if ($user->role === 'agency_owner') {
                $poolsDeleted = DB::table('investment_pools')->where('user_id', $user->id)->delete();
                Log::info('Investment pools deleted', [
                    'user_id' => $user->id,
                    'pools_deleted' => $poolsDeleted
                ]);
            }
            
            // 7. Delete any LATs created by this user
            $latsDeleted = DB::table('lats')->where('user_id', $user->id)->delete();
            Log::info('LATs deleted', [
                'user_id' => $user->id,
                'lats_deleted' => $latsDeleted
            ]);
            
            // 8. Delete any designs created by this user
            $designsDeleted = DB::table('designs')->where('user_id', $user->id)->delete();
            Log::info('Designs deleted', [
                'user_id' => $user->id,
                'designs_deleted' => $designsDeleted
            ]);
            
            // 9. Delete any contacts created by this user
            $contactsDeleted = DB::table('contacts')->where('user_id', $user->id)->delete();
            Log::info('Contacts deleted', [
                'user_id' => $user->id,
                'contacts_deleted' => $contactsDeleted
            ]);
            
            // 10. Delete any expenses created by this user
            $expensesDeleted = DB::table('expenses')->where('user_id', $user->id)->delete();
            Log::info('Expenses deleted', [
                'user_id' => $user->id,
                'expenses_deleted' => $expensesDeleted
            ]);
            
            // 11. Delete any user invitations sent by this user
            $invitationsDeleted = DB::table('user_invitations')->where('invited_by', $user->id)->delete();
            Log::info('User invitations deleted', [
                'user_id' => $user->id,
                'invitations_deleted' => $invitationsDeleted
            ]);
            
            // 12. Delete any notifications for this user
            $notificationsDeleted = DB::table('notifications')->where('notifiable_type', User::class)
                ->where('notifiable_id', $user->id)->delete();
            Log::info('Notifications deleted', [
                'user_id' => $user->id,
                'notifications_deleted' => $notificationsDeleted
            ]);
            
            // 13. Keep the user record but mark as expired (already done in middleware)
            // User is NOT deleted - only their data is cleaned up
            
            DB::commit();
            
            Log::info('Demo data cleanup completed successfully', [
                'user_id' => $user->id,
                'email' => $user->email
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Demo data cleanup failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * Clean up all expired demo users (can be run as a scheduled job)
     */
    public function cleanupAllExpiredDemoUsers(): int
    {
        $expiredUsers = User::where('is_demo', true)
            ->where('status', 'expired')
            ->get();
        
        $cleanedCount = 0;
        
        foreach ($expiredUsers as $user) {
            try {
                $this->cleanupExpiredDemoUser($user);
                $cleanedCount++;
            } catch (\Exception $e) {
                Log::error('Failed to cleanup expired demo user data', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        Log::info('Batch demo data cleanup completed', [
            'total_expired' => $expiredUsers->count(),
            'successfully_cleaned' => $cleanedCount
        ]);
        
        return $cleanedCount;
    }
}
