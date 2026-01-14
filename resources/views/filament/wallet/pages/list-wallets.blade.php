@php
    use App\Models\InvestmentPool;
    use Illuminate\Support\Facades\Auth;
@endphp

<div>
<x-filament-panels::page>
    @php
        $wallets = $this->getWalletData();
        $user = Auth::user();
        
        // Get all pools with their status - sort by latest first
        $allPools = InvestmentPool::orderBy('created_at', 'desc')->get();
        $pools = $allPools; // Default to showing all pools
        $statusFilter = request()->query('pool_status', 'all');
        
        // Apply status filter if selected
        if ($statusFilter !== 'all') {
            $pools = $pools->where('status', $statusFilter);
        }
    @endphp

    <div id="walletContainer" style="max-width: 1200px; margin: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; padding: 1rem;">

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
            $growthPercentage = 12.5; // This should be calculated dynamically
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
        onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 0 60px rgba(139, 92, 246, 0.5), 0 40px 80px rgba(0, 0, 0, 0.8), inset 0 1px 0 rgba(255, 255, 255, 0.15)'"
        onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 0 40px rgba(139, 92, 246, 0.35), 0 30px 60px rgba(0, 0, 0, 0.7), inset 0 1px 0 rgba(255, 255, 255, 0.1)'">

        <!-- Animated Neon overlay -->
        <div style="
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at top right, rgba(139, 92, 246, 0.25), transparent 50%),
                radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.2), transparent 50%);
            pointer-events: none;
            animation: pulseGlow 3s ease-in-out infinite;
        "></div>

        <!-- Header -->
        <div style="position: relative; display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; background: rgba(0, 0, 0, 0.3); backdrop-filter: blur(10px);">
            <div style="display: flex; gap: 1rem; align-items: center;">
                <!-- Avatar with photo fallback -->
                <div style="
                    width: 3rem;
                    height: 3rem;
                    border-radius: 0.5rem;
                    background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-weight: 800;
                    font-size: 1.3rem;
                    box-shadow: 0 0 15px rgba(139, 92, 246, 0.6);
                    transition: all 0.3s ease;
                    overflow: hidden;
                "
                onmouseover="this.style.boxShadow='0 0 25px rgba(139, 92, 246, 0.9)'"
                onmouseout="this.style.boxShadow='0 0 15px rgba(139, 92, 246, 0.6)'">
                    @if($wallet->investor->profile_photo)
                        <img src="{{ $wallet->investor->profile_photo }}" alt="{{ $userName }}" style="width: 100%; height: 100%; object-fit: cover;">
                    @else
                        {{ strtoupper(substr($userName, 0, 1)) }}
                    @endif
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 1.1rem; letter-spacing: -0.025em;">{{ $userName }}</div>
                    <div style="opacity: 0.7; font-size: 0.85rem;">{{ $wallet->investor->agency->name ?? 'No Agency' }}</div>
                </div>
            </div>

            <!-- Animated Status Badge -->
            <span style="
                padding: 0.4rem 0.8rem;
                border-radius: 0.5rem;
                font-size: 0.7rem;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.05em;
                background: rgba({{ $statusColor == '#22c55e' ? '34, 197, 94' : ($statusColor == '#f59e0b' ? '245, 158, 11' : '239, 68, 68') }}, 0.2);
                color: {{ $statusColor == '#22c55e' ? '#86efac' : ($statusColor == '#f59e0b' ? '#fde68a' : '#fca5a5') }};
                border: 1px solid {{ $statusColor == '#22c55e' ? 'rgba(34, 197, 94, 0.3)' : ($statusColor == '#f59e0b' ? 'rgba(245, 158, 11, 0.3)' : 'rgba(239, 68, 68, 0.3)') }};
                box-shadow: 0 0 10px {{ $glowColor }};
                animation: statusPulse 2s ease-in-out infinite;
            ">
                {{ strtoupper($status) }}
            </span>
        </div>

    

        <!-- Balance Hero Section -->
        <div style="text-align: center; padding: 2rem 2rem; background: rgba(0, 0, 0, 0.15); position: relative;">
            <div style="font-size: 0.8rem;  color: #22c55e; letter-spacing: 0.15em; opacity: 0.8; text-transform: uppercase; font-weight: 500;">Available Balance</div>
            <div style="
                font-size: 3.2rem;
                font-weight: 900;
                margin-top: 0.5rem;
                font-family: 'Roboto Mono', monospace;
                color: #22c55e;
                animation: countUp 1.5s ease-out;
            " data-target="{{ $availableBalance }}">
                PKR {{ number_format($availableBalance, 0, ',', ',') }}
            </div>
            <div style="
                margin-top: 1rem;
                color: #a78bfa;
                font-size: 0.9rem;
                font-weight: 500;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                animation: slideUp 1s ease-out 0.5s both;
            ">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
                +{{ $growthPercentage }}% this month
            </div>
        </div>

        <!-- Interactive Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.25);">
            @php
                $lifetimeDeposited = $wallet->lifetime_deposited;
                $activeInvested = $wallet->active_invested;
                $totalReturned = $wallet->total_returned;
                $availableBalance = $wallet->available_balance;
                
                $stats = [
                    ['Lifetime Deposited', $lifetimeDeposited, 'trending_up'],
                    ['Active Invested', $activeInvested, 'trending_down'],
                    ['Total Returned', $totalReturned, 'refresh']
                ];
            @endphp
            
            @foreach($stats as $stat)
            <div style="
                background: rgba(255, 255, 255, 0.05);
                border-radius: 0.75rem;
                padding: 1.2rem;
                text-align: center;
                box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.transform='scale(1.05)'"
            onmouseout="this.style.background='rgba(255, 255, 255, 0.05)'; this.style.transform='scale(1)'">
                <div style="font-size: 0.7rem; opacity: 0.7; letter-spacing: 0.1em; text-transform: uppercase;">{{ $stat[0] }}</div>
                <div style="font-size: 1.5rem; font-weight: 700; letter-spacing: -0.025em; line-height: 1.2;">
                    PKR {{ number_format($stat[1], 0, ',', ',') }}
                </div>
            </div>
            @endforeach
            
          
        </div>
          <!-- Invest/Withdraw Request Buttons for Investors -->
            @if(auth()->user()->role === 'Investor' && $availableBalance > 0)
            <div style="display: flex; gap: 1rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.25); border-top: 1px solid rgba(255, 255, 255, 0.1);">
                <div style="
                    flex: 1;
                    background: linear-gradient(135deg, #6b46c1, #553c9a);
                    border-radius: 0.75rem;
                    padding: 1.2rem;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(107, 70, 193, 0.3);
                    transition: all 0.3s ease;
                    cursor: pointer;
                    margin-bottom: 0.5rem;
                "
               onmouseover="this.style.background='linear-gradient(135deg, #553c9a, #44337a)'; this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(107, 70, 193, 0.4)'"
                onmouseout="this.style.background='linear-gradient(135deg, #6b46c1, #553c9a)'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(107, 70, 193, 0.3)'"
                onclick="event.preventDefault(); openInvestmentModal();">
                    <div style="font-size: 1.2rem; font-weight: 700; color: white;">Request Investment</div>
                </div>
                
                <div style="
                    flex: 1;
                    background: linear-gradient(135deg, #6b46c1, #553c9a);
                    border-radius: 0.75rem;
                    padding: 1.2rem;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(107, 70, 193, 0.3);
                    transition: all 0.3s ease;
                    cursor: pointer;
                    margin-bottom: 0.5rem;
                "
                onmouseover="this.style.background='linear-gradient(135deg, #553c9a, #44337a)'; this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(107, 70, 193, 0.4)'"
                onmouseout="this.style.background='linear-gradient(135deg, #6b46c1, #553c9a)'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(107, 70, 193, 0.3)'"
                onclick="openWithdrawModal({{ $wallet->id }}, '{{ auth()->user()->name }}', {{ auth()->id() }}, {{ $availableBalance }})">
                    <div style="font-size: 1.2rem; font-weight: 700; color: white;">Request Withdraw</div>
                    @php
                        $lastRequest = $wallet->withdrawalRequests()->latest()->first();
                    @endphp
                    @if($lastRequest)
                        <div style="font-size: 0.7rem; color: rgba(255, 255, 255, 0.8); margin-top: 0.5rem;">
                            Last: {{ $lastRequest->created_at->format('d M Y') }}
                            <span style="
                                padding: 0.2rem 0.4rem; 
                                border-radius: 0.25rem; 
                                font-size: 0.65rem;
                                font-weight: 600;
                                background: {{ $lastRequest->status === 'pending' ? '#fbbf24' : ($lastRequest->status === 'approved' ? '#22c55e' : '#ef4444') }};
                                color: {{ $lastRequest->status === 'pending' ? '#92400e' : ($lastRequest->status === 'approved' ? '#166534' : '#991b1b') }};
                                margin-left: 0.25rem;
                            ">
                                {{ ucfirst($lastRequest->status) }}
                            </span>
                        </div>
                    @endif
                </div>
                
                </div>
            @endif

        <!-- Investment Pools Section -->
        <div style="padding: 1.5rem; background: rgba(0, 0, 0, 0.15);" x-data="{ statusFilter: 'all' }">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div>
                    <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.7;">Investment Pools</div>
                    <div style="position: relative; display: inline-block; margin-top: 0.5rem;">
                        <select x-model="statusFilter" style="
                            background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%);
                            border: 1px solid rgba(139, 92, 246, 0.35);
                            color: white;
                            padding: 0.25rem 1.5rem 0.25rem 0.75rem;
                            border-radius: 0.5rem;
                            font-size: 0.7rem;
                            font-weight: 500;
                            -webkit-appearance: none;
                            -moz-appearance: none;
                            appearance: none;
                            cursor: pointer;
                            box-shadow: 0 0 20px rgba(139, 92, 246, 0.2);
                            transition: all 0.3s ease;
                        "
                        onmouseover="this.style.background='linear-gradient(145deg, #2e1b5b 0%, #682c97 50%, #9b6cf7 100%)'; this.style.borderColor='rgba(159, 108, 247, 0.45)'; this.style.boxShadow='0 0 25px rgba(139, 92, 246, 0.3)'"
                        onmouseout="this.style.background='linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%)'; this.style.borderColor='rgba(139, 92, 246, 0.35)'; this.style.boxShadow='0 0 20px rgba(139, 92, 246, 0.2)'">
                            <option value="all" style="background-color: #581c87; color: white;">All</option>
                            <option value="open" style="background-color: #581c87; color: white;">Open</option>
                            <option value="active" style="background-color: #581c87; color: white;">Active</option>
                            <option value="closed" style="background-color: #581c87; color: white;">Closed</option>
                        </select>
                        <div style="position: absolute; right: 0.5rem; top: 50%; transform: translateY(-50%); pointer-events: none;">
                            <svg width="12" height="12" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M4 6H11L7.5 10.5L4 6Z" fill="currentColor"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div style="font-size: 0.75rem; opacity: 0.8; margin-top: 0.5rem;">
                    <span style="color: rgba(0, 0, 0, 0.3; font-weight: 600;">{{ $pools->count() }}</span> Pools
                </div>
            </div>
            
            @if(isset($pools) && $pools->count() > 0)
                <div x-init="
                    $store.pools = {{ json_encode($pools->map(function($pool) {
                        return [
                            'id' => $pool->id,
                            'status' => $pool->status,
                            // Add other pool properties you need in the template
                        ];
                    })) }};
                    $store.showAllPools = false;
                "></div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;"
                     x-show="$store.pools.some(pool => statusFilter === 'all' || pool.status === statusFilter)">
                    @foreach($pools as $index => $pool)
                    <div x-show="({{ $index }} < 3 || $store.showAllPools) && (statusFilter === 'all' || '{{ $pool->status }}' === statusFilter)"
                    @php
                        // Get actual investment count for this pool
                        $investmentCount = $wallet->allocations->where('investment_pool_id', $pool->id)->count();
                        $hasInvested = $investmentCount > 0;
                        $totalInvestedInPool = $wallet->allocations->where('investment_pool_id', $pool->id)->sum('amount') ?? 0;
                    @endphp
                    <div style="
                        background: rgba(255, 255, 255, 0.05);
                        border: 1px solid rgba(255, 255, 255, 0.1);
                        border-radius: 0.75rem;
                        padding: 1rem;
                        transition: all 0.3s ease;
                        cursor: pointer;
                        position: relative;
                    "
                    onmouseover="this.style.background='rgba(255, 255, 255, 0.08)'; this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.background='rgba(255, 255, 255, 0.05)'; this.style.transform='translateY(0)'"
                    onclick="window.location.href='{{ route('filament.admin.resources.investment-pool.investment-pools.view', $pool->id) }}'">
                        
                        @if($hasInvested)
                        <div style="
                            position: absolute;
                            top: -8px;
                            right: -8px;
                            background: #22c55e;
                            color: white;
                            border-radius: 50%;
                            width: 24px;
                            height: 24px;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            font-size: 0.7rem;
                            font-weight: 700;
                            box-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
                        ">✓</div>
                        @endif
                        
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <div>
                                <div style="font-weight: 600; font-size: 0.95rem;">{{ $pool->name }}</div>
                                <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.25rem;">{{ $pool->description ?? 'High growth opportunity' }}</div>
                            </div>
                           
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem; margin-top: 0.75rem; overflow-x: auto; white-space: nowrap;">
                            <div style="text-align: center; min-width: 100px;">
                                <div style="font-size: 0.7rem; opacity: 0.6;">Your Investments</div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: {{ $hasInvested ? '#86efac' : '#fbbf24' }};">
                                    {{ $investmentCount }} {{ $investmentCount == 1 ? 'time' : 'times' }}
                                </div>
                            </div>
                            <div style="text-align: center; min-width: 80px;">
                                <div style="font-size: 0.7rem; opacity: 0.6;">Status</div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: {{ $pool->remaining_amount > 0 ? '#86efac' : '#fbbf24' }};">
                                    {{ $pool->remaining_amount > 0 ? 'Open' : 'Active' }}
                                </div>
                            </div>
                            @if($pool->remaining_amount > 0)
                            <div style="text-align: center; min-width: 100px;">
                                <div style="font-size: 0.7rem; opacity: 0.6;">Required</div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: #86efac;">
                                    PKR {{ number_format($pool->amount_required, 0, ',', ',') }}
                                </div>
                            </div>
                            @else
                            <div style="text-align: center; min-width: 100px;">
                                <div style="font-size: 0.9rem; font-weight: 600; color: #22c55e;">Pool requirement</div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: #22c55e;">
                                     amount is complete
                                </div>
                            </div>
                            @endif
                        </div>
                        
                        <div style="margin-top: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <span style="font-size: 0.7rem; opacity: 0.6;">Your Total Investment</span>
                                <span style="font-size: 0.7rem; opacity: 0.8; font-family: 'Roboto Mono', monospace;">
                                    PKR {{ number_format($totalInvestedInPool, 0) }}
                                </span>
                            </div>
                            <div style="
                                height: 4px;
                                background: rgba(255, 255, 255, 0.1);
                                border-radius: 2px;
                                overflow: hidden;
                            ">
                                <div style="
                                    height: 100%;
                                    background: linear-gradient(90deg, #8b5cf6, #a78bfa);
                                    border-radius: 2px;
                                    width: {{ min($totalInvestedInPool / ($pool->amount_required ?? 1000000) * 100, 100) }}%;
                                    transition: width 0.3s ease;
                                "></div>
                            </div>
                        </div>
                        
                    </div>
                    @endforeach
                </div>
                <div x-show="!$store.pools.some(pool => statusFilter === 'all' || pool.status === statusFilter)" 
                     style="text-align: center; padding: 1rem; color: #9ca3af; font-size: 0.875rem;">
                    No pools found with the selected filter.
                </div>
                
                @php
                    $visiblePools = $pools->filter(function($pool) {
                        return request('statusFilter', 'all') === 'all' || $pool->status === request('statusFilter');
                    });
                @endphp
                @if($visiblePools->count() > 3)
                <div style="text-align: center; margin-top: 1rem;">
                    <button 
                        x-text="$store.showAllPools ? 'Show Less' : 'View All Pools (' + ({{ $visiblePools->count() }} - 3) + ' more)'"
                        @click="$store.showAllPools = !$store.showAllPools"
                        style="
                            padding: 0.5rem 1rem;
                            border-radius: 0.5rem;
                            background: rgba(139, 92, 246, 0.2);
                            border: 1px solid rgba(139, 92, 246, 0.3);
                            color: #a78bfa;
                            font-size: 0.875rem;
                            font-weight: 500;
                            cursor: pointer;
                            transition: all 0.2s ease;
                        "
                        onmouseover="this.style.background='rgba(139, 92, 246, 0.3)'"
                        onmouseout="this.style.background='rgba(139, 92, 246, 0.2)'">
                    </button>
                </div>
                @endif
            @else
                <div style="
                    text-align: center;
                    padding: 2rem;
                    background: rgba(255, 255, 255, 0.02);
                    border-radius: 0.5rem;
                    border: 1px dashed rgba(255, 255, 255, 0.2);
                ">
                    <div style="font-size: 0.875rem; opacity: 0.7;">No active investment pools available</div>
                    <div style="font-size: 0.75rem; opacity: 0.5; margin-top: 0.5rem;">Check back later for new opportunities</div>
                </div>
            @endif
            
            @if($wallet->allocations->count() > 0)
            <div style="margin-top: 1rem; padding: 0.75rem; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.2); border-radius: 0.5rem; text-align: center;">
                <div style="font-size: 0.8rem; color: #86efac; font-weight: 600;">Last investment: {{ $wallet->allocations->sortByDesc('created_at')->first()?->created_at->format('M j, Y') ?? 'Unknown' }}</div>
            </div>
            @endif
        </div>

        <!-- Investment Summary -->
        <div style="padding: 1.5rem; background: rgba(0, 0, 0, 0.1);">
            <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.7; margin-bottom: 1rem;">Investment Summary</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem;">
                <div style="
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 0.5rem;
                    padding: 1rem;
                ">
                    <div style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.5rem;">Total Pools Available</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #a78bfa;">{{ $pools->count() }}</div>
                </div>
                <div style="
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 0.5rem;
                    padding: 1rem;
                ">
                    <div style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.5rem;">You Invested In Pools</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #86efac;">
                        {{ $wallet->allocations->pluck('investment_pool_id')->unique()->count() }}
                    </div>
                </div>
            </div>
        </div>
        <div style="padding: 1.5rem; display: flex; gap: 1rem; background: rgba(0, 0, 0, 0.3);">
            @if($user->role !== 'Investor')
            <a href="{{ \App\Filament\Resources\Wallet\WalletResource::getUrl('edit', ['record' => $wallet->id]) }}"
               style="
                   flex: 1;
                   text-align: center;
                   padding: 0.9rem;
                   border-radius: 0.6rem;
                   background: linear-gradient(135deg, #111827, #1f2937);
                   color: white;
                   font-weight: 600;
                   box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);
                   transition: all 0.3s ease;
                   text-decoration: none;
                   display: block;
               "
               onmouseover="this.style.background='linear-gradient(135deg, #1f2937, #374151)'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='linear-gradient(135deg, #111827, #1f2937)'; this.style.transform='translateY(0)'">
                Manage Wallet
            </a>
            @endif

            <div style="
                flex: 1;
                text-align: center;
                padding: 0.9rem;
                border-radius: 0.6rem;
                background: linear-gradient(135deg, #8b5cf6, #7c3aed);
                color: white;
                font-weight: 600;
                box-shadow: 0 0 25px rgba(139, 92, 246, 0.7);
                cursor: pointer;
                transition: all 0.3s ease;
            "
            onmouseover="this.style.background='linear-gradient(135deg, #7c3aed, #6d28d9)'; this.style.transform='translateY(-2px)'"
            onmouseout="this.style.background='linear-gradient(135deg, #8b5cf6, #7c3aed)'; this.style.transform='translateY(0)'"
            onclick="window.location.href='{{ \App\Filament\Resources\Wallet\WalletResource::getUrl('transaction-history', ['walletId' => $wallet->id]) }}'">
               Transaction History 
            </div>
        </div>

        </div>
        <!-- END CARD -->
    @endforeach

    </div>

    <!-- Withdraw Request Modal -->
    <div id="withdrawModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%); padding: 2rem; border-radius: 1rem; max-width: 400px; width: 90%;">
            <h3 style="color: white; margin-bottom: 1rem;">Request Withdrawal</h3>
            
            <form id="withdrawForm" onsubmit="submitWithdrawRequest(event)">
                <!-- Hidden fields -->
                <input type="hidden" id="wallet_id" name="wallet_id">
                <input type="hidden" id="investor_id" name="investor_id">
                <input type="hidden" id="investor_name" name="investor_name">
                
                <!-- Amount field -->
                <div style="margin-bottom: 1rem;">
                    <label style="color: white; display: block; margin-bottom: 0.5rem;">Amount (PKR)</label>
                    <input type="number" id="requested_amount" name="requested_amount" 
                           style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(139, 92, 246, 0.3); background: rgba(255,255,255,0.1); color: white;"
                           step="100" required>
                    <small id="availableBalance" style="color: #a78bfa;">Available: PKR 0</small>
                </div>
                
                <!-- Buttons -->
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


        <!-- CSS Animations -->
        <style>
            @keyframes pulseGlow {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.8; }
            }
            
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            
            .wallet-card {
                min-width: unset !important;
            }
        </style>

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
            
            // Prevent page refresh while modal is open
            window.beforeunload = function(e) {
                if (document.getElementById('withdrawModal').style.display === 'flex') {
                    e.preventDefault();
                    e.returnValue = '';
                    return '';
                }
            };
        }

        function closeWithdrawModal() {
            document.getElementById('withdrawModal').style.display = 'none';
            document.getElementById('withdrawForm').reset();
            
            // Remove beforeunload listener when modal is closed
            window.onbeforeunload = null;
        }

        function submitWithdrawRequest(event) {
            event.preventDefault();
            
            console.log('Submit function called'); // Debug log
            
            const formData = new FormData(document.getElementById('withdrawForm'));
            const amount = parseFloat(formData.get('requested_amount'));
            
          
            
            // Disable submit button to prevent double submission
            const submitBtn = document.querySelector('#withdrawForm button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            fetch('/wallet/withdraw-request', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    wallet_id: formData.get('wallet_id'),
                    investor_id: formData.get('investor_id'),
                    investor_name: formData.get('investor_name'),
                    requested_amount: amount
                })
            })
            .then(response => {
                // Request submitted successfully - Filament notification will show from controller
                closeWithdrawModal();
                
                // Reset the form
                const form = document.getElementById('withdrawForm');
                if (form) form.reset();
                
                // Re-enable button
                submitBtn.disabled = false;
                submitBtn.textContent = 'Submit Request';
            });
        }
    </script>

    <!-- Investment Pool Selection Modal -->
    <div id="investmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%); padding: 2rem; border-radius: 1rem; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
            <h3 style="color: white; margin-bottom: 1rem;">Select Investment Pool</h3>
            <div id="poolsContainer" style="display: grid; gap: 1rem; margin-bottom: 1rem;">
                <div style="display: flex; align-items: center; justify-content: center; padding: 2rem; color: #a78bfa;">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite; margin-right: 0.5rem;">
                        <path d="M21 12a9 9 0 11-6.219-8.56"/>
                    </svg>
                    <span>Loading investment pools...</span>
                </div>
            </div>
            <button onclick="closeInvestmentModal()" style="width: 100%; padding: 0.75rem; background: #ef4444; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">
                Cancel
            </button>
        </div>
    </div>

    <!-- Investment Amount Modal -->
    <div id="investmentAmountModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%); padding: 2rem; border-radius: 1rem; max-width: 400px; width: 90%;">
            <h3 style="color: white; margin-bottom: 1rem;">Invest in Pool</h3>
            <div id="selectedPoolInfo" style="color: #a78bfa; margin-bottom: 1rem; font-size: 0.9rem;"></div>
            
            <form id="investmentForm" onsubmit="submitInvestmentRequest(event)">
                <input type="hidden" id="pool_id" name="pool_id">
                
                <div style="margin-bottom: 1rem;">
                    <label style="color: white; display: block; margin-bottom: 0.5rem;">Investment Amount (PKR)</label>
                    <input type="number" id="investment_amount" name="amount" style="width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: 1px solid rgba(139, 92, 246, 0.3); background: rgba(255,255,255,0.1); color: white;" step="100" min="100" required>
                </div>
                
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" style="flex: 1; padding: 0.75rem; background: #22c55e; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">
                        Send Request
                    </button>
                    <button type="button" onclick="closeInvestmentAmountModal()" style="flex: 1; padding: 0.75rem; background: #581c87 50%; color: white; border: none; border-radius: 0.5rem; cursor: pointer;">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let selectedPool = null;

        function openInvestmentModal() {
            document.getElementById('investmentModal').style.display = 'flex';
            loadAvailablePools();
        }

        function closeInvestmentModal() {
            document.getElementById('investmentModal').style.display = 'none';
        }

        function closeInvestmentAmountModal() {
            document.getElementById('investmentAmountModal').style.display = 'none';
        }

        function loadAvailablePools() {
            console.log('Loading pools...');
            
            fetch('/investor/available-pools')
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Response data:', data);
                    const container = document.getElementById('poolsContainer');
                    container.innerHTML = '';
                    
                    if (data.success && data.pools.length > 0) {
                        data.pools.forEach(pool => {
                            const poolCard = createPoolCard(pool);
                            container.appendChild(poolCard);
                        });
                    } else {
                        container.innerHTML = '<div style="color: #a78bfa; text-align: center; padding: 2rem;">No available pools found</div>';
                    }
                })
                .catch(error => {
                    console.error('Error loading pools:', error);
                    document.getElementById('poolsContainer').innerHTML = '<div style="color: #ef4444; text-align: center; padding: 2rem;">Error loading pools: ' + error.message + '</div>';
                });
        }

        function createPoolCard(pool) {
            const card = document.createElement('div');
            card.style.cssText = `
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(139, 92, 246, 0.3);
                border-radius: 0.75rem;
                padding: 1.2rem;
                cursor: pointer;
                transition: all 0.3s ease;
            `;
            card.onmouseover = () => card.style.background = 'rgba(255, 255, 255, 0.1)';
            card.onmouseout = () => card.style.background = 'rgba(255, 255, 255, 0.05)';
            card.onclick = () => selectPool(pool);
            
            card.innerHTML = `
                <div style="color: white; font-weight: 600; margin-bottom: 0.5rem;"> Lot no:${pool.lat ? pool.lat.lat_no : ''}</div>
                <div style="color: white; font-weight: 600; margin-bottom: 0.5rem;">Design:${pool.design_name}</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.8rem;">
                    <div style="color: #22c55e;">Required: PKR ${pool.amount_required.toLocaleString()}</div>
                    <div style="color: #f59e0b;">Collected: PKR ${pool.total_collected.toLocaleString()}</div>
                    <div style="color: #a78bfa;">Progress Collected: ${pool.percentage_collected}%</div>
                    <div style="color: #ef4444;">Remaining: PKR ${pool.remaining_amount.toLocaleString()}</div>
                </div>
            `;
            
            return card;
        }

        function selectPool(pool) {
            selectedPool = pool;
            document.getElementById('pool_id').value = pool.id;
            document.getElementById('selectedPoolInfo').textContent = `Lot no: ${pool.lat_no} - ${pool.design_name}`;
            document.getElementById('investmentAmountModal').style.display = 'flex';
            document.getElementById('investmentModal').style.display = 'none';
        }

        function submitInvestmentRequest(event) {
            event.preventDefault();
            
            const formData = new FormData(event.target);
            
            fetch('/investor/request-investment', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            })
            .then(response => {
                closeInvestmentAmountModal();
                setTimeout(() => {
                    window.location.reload();
                }, 50);
            });
        }
    </script>
</x-filament-panels::page>
</div>
