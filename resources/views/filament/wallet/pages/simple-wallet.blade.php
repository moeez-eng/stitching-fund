@extends('filament::layouts.app')

@section('content')
<div>
    <div id="walletContainer" style="max-width: 1200px; margin: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; padding: 1rem;">
        @php
            use App\Models\InvestmentPool;
            use Illuminate\Support\Facades\Auth;
            
            // Get data directly
            $user = Auth::user();
            $statusFilter = request()->query('pool_status', 'all');
            
            // Get wallet data
            if ($user->role === 'Investor') {
                $wallets = \App\Models\Wallet::where('investor_id', $user->id)
                    ->with(['investor', 'agencyOwner', 'allocations'])
                    ->get();
            } else {
                $wallets = \App\Models\Wallet::where('agency_owner_id', $user->id)
                    ->with(['investor', 'agencyOwner', 'allocations'])
                    ->get();
            }
            
            // Get pools data
            $allPools = InvestmentPool::orderBy('created_at', 'desc')->get();
            $pools = $allPools;
            if ($statusFilter !== 'all') {
                $pools = $pools->where('status', $statusFilter);
            }
        @endphp

        @foreach($wallets as $wallet)
            @php
                $lifetimeDeposited = $wallet->lifetime_deposited;
                $activeInvested = $wallet->active_invested;
                $totalReturned = $wallet->total_returned;
                $availableBalance = $wallet->available_balance;
                
                // Calculate wallet status
                if ($availableBalance > 50000) {
                    $status = 'healthy';
                    $statusColor = '#22c55e';
                    $glowColor = 'rgba(34, 197, 94, 0.8)';
                } elseif ($availableBalance > 10000) {
                    $status = 'low';
                    $statusColor = '#f59e0b';
                    $glowColor = 'rgba(245, 158, 11, 0.8)';
                } else {
                    $status = 'critical';
                    $statusColor = '#ef4444';
                    $glowColor = 'rgba(239, 68, 68, 0.8)';
                }
                
                $userName = $wallet->investor->name ?? $user->name;
                $growthPercentage = 12.5;
            @endphp

            <!-- WALLET CARD -->
            <div style="
                border-radius: 1rem;
                overflow: hidden;
                background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%);
                border: 1px solid rgba(139, 92, 246, 0.35);
                box-shadow: 
                    0 0 40px rgba(139, 92, 246, 0.35),
                    0 30px 60px rgba(0, 0, 0, 0.7),
                    inset 0 1px 0 rgba(255, 255, 255, 0.1);
                position: relative;
                color: white;
                font-family: 'Inter', system-ui, sans-serif;
                transition: all 0.3s ease;
                transform: translateY(0);
            " 
            onmouseover="this.style.transform='translateY(-4px)'"
            onmouseout="this.style.transform='translateY(0)'">

                <!-- Header -->
                <div style="position: relative; display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; background: rgba(0, 0, 0, 0.3);">
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div style="width: 3rem; height: 3rem; border-radius: 0.5rem; background: linear-gradient(135deg, #8b5cf6, #7c3aed); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem;">
                            @if($wallet->investor->profile_photo)
                                <img src="{{ $wallet->investor->profile_photo }}" alt="{{ $userName }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                {{ strtoupper(substr($userName, 0, 1)) }}
                            @endif
                        </div>
                        <div>
                            <div style="font-weight: 700; font-size: 1.1rem;">{{ $userName }}</div>
                            <div style="opacity: 0.7; font-size: 0.85rem;">{{ $wallet->investor->agency->name ?? 'No Agency' }}</div>
                        </div>
                    </div>

                    <span style="padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; background: rgba({{ $statusColor == '#22c55e' ? '34, 197, 94' : ($statusColor == '#f59e0b' ? '245, 158, 11' : '239, 68, 68') }}, 0.2); color: {{ $statusColor == '#22c55e' ? '#86efac' : ($statusColor == '#f59e0b' ? '#fde68a' : '#fca5a5') }};">
                        {{ strtoupper($status) }}
                    </span>
                </div>

                <!-- Balance Section -->
                <div style="text-align: center; padding: 2rem; background: rgba(0, 0, 0, 0.15);">
                    <div style="font-size: 0.8rem; color: #22c55e; opacity: 0.8; text-transform: uppercase; font-weight: 500;">Available Balance</div>
                    <div style="font-size: 3rem; font-weight: 900; margin-top: 0.5rem; color: #22c55e;">
                        PKR {{ number_format($availableBalance, 0, ',', ',') }}
                    </div>
                </div>

                <!-- Stats Grid -->
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.25);">
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: 0.75rem; padding: 1.2rem; text-align: center;">
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Lifetime Deposited</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">PKR {{ number_format($lifetimeDeposited, 0, ',', ',') }}</div>
                    </div>
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: 0.75rem; padding: 1.2rem; text-align: center;">
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Active Invested</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">PKR {{ number_format($activeInvested, 0, ',', ',') }}</div>
                    </div>
                    <div style="background: rgba(255, 255, 255, 0.05); border-radius: 0.75rem; padding: 1.2rem; text-align: center;">
                        <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Total Returned</div>
                        <div style="font-size: 1.5rem; font-weight: 700;">PKR {{ number_format($totalReturned, 0, ',', ',') }}</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                @if(auth()->user()->role === 'Investor' && $availableBalance > 0)
                <div style="display: flex; gap: 1rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.25);">
                    <button onclick="alert('Invest Request feature coming soon!')" style="flex: 1; padding: 1.2rem; background: linear-gradient(135deg, #6b46c1, #553c9a); color: white; border: none; border-radius: 0.75rem; font-weight: 600; cursor: pointer;">
                        Request Investment
                    </button>
                    <button onclick="openWithdrawModal({{ $wallet->id }}, '{{ auth()->user()->name }}', {{ auth()->id() }}, {{ $availableBalance }})" style="flex: 1; padding: 1.2rem; background: linear-gradient(135deg, #6b46c1, #553c9a); color: white; border: none; border-radius: 0.75rem; font-weight: 600; cursor: pointer;">
                        Request Withdraw
                    </button>
                </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- Withdraw Modal -->
    <div id="withdrawModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%); padding: 2rem; border-radius: 1rem; max-width: 400px; width: 90%;">
            <h3 style="color: white; margin-bottom: 1rem;">Request Withdrawal</h3>
            
            <form id="withdrawForm" onsubmit="submitWithdrawRequest(event)">
                <input type="hidden" id="wallet_id" name="wallet_id">
                <input type="hidden" id="investor_id" name="investor_id">
                <input type="hidden" id="investor_name" name="investor_name">
                
                <div style="margin-bottom: 1rem;">
                    <label style="color: white; display: block; margin-bottom: 0.5rem;">Amount (PKR)</label>
                    <input type="number" id="requested_amount" name="requested_amount" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(139, 92, 246, 0.3); background: rgba(255,255,255,0.1); color: white;" step="100" required>
                    <small id="availableBalance" style="color: #a78bfa;">Available: PKR 0</small>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" style="flex: 1; padding: 0.75rem; background: #22c55e; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">
                        Submit Request
                    </button>
                    <button type="button" onclick="closeWithdrawModal()" style="flex: 1; padding: 0.75rem; background: #ef4444; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentWalletId = null;
        let currentAvailableBalance = 0;

        function openWithdrawModal(walletId, investorName, investorId, availableBalance) {
            currentWalletId = walletId;
            currentAvailableBalance = availableBalance;
            
            document.getElementById('wallet_id').value = walletId;
            document.getElementById('investor_id').value = investorId;
            document.getElementById('investor_name').value = investorName;
            document.getElementById('availableBalance').textContent = 'Available: PKR ' + availableBalance.toLocaleString();
            document.getElementById('requested_amount').max = availableBalance;
            document.getElementById('withdrawModal').style.display = 'flex';
        }

        function closeWithdrawModal() {
            document.getElementById('withdrawModal').style.display = 'none';
        }

        function submitWithdrawRequest(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            const amount = formData.get('requested_amount');
            
            if (amount > currentAvailableBalance) {
                alert('Amount cannot exceed available balance');
                return;
            }
            
            fetch('/wallet/withdraw-request', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Withdrawal request submitted successfully!');
                    closeWithdrawModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error submitting request');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error submitting request');
            });
        }
    </script>
</div>
@endsection
