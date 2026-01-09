@php
    use App\Models\InvestmentPool;
    use Illuminate\Support\Facades\Auth;
@endphp

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

    <div wire:poll.5s="loadData" style="max-width: 1200px; margin: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; padding: 1rem;">

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
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.25);">
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
            
            <!-- Invest Request Button for Investors -->
            @if(auth()->user()->role === 'Investor' && $availableBalance > 0)
            <div style="
                background: linear-gradient(135deg, #3b82f6, #1d4ed8);
                border-radius: 0.75rem;
                padding: 1.2rem;
                text-align: center;
                box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
                transition: all 0.3s ease;
                cursor: pointer;
            "
            onmouseover="this.style.background='linear-gradient(135deg, #2563eb, #1e40af)'; this.style.transform='scale(1.05)'; this.style.boxShadow='0 6px 20px rgba(59, 130, 246, 0.4)'"
            onmouseout="this.style.background='linear-gradient(135deg, #3b82f6, #1d4ed8)'; this.style.transform='scale(1)'; this.style.boxShadow='0 4px 15px rgba(59, 130, 246, 0.3)'"
            onclick="alert('Invest Request feature coming soon!')">
                <div style="font-size: 0.7rem; opacity: 0.9; letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.5rem;">Investment</div>
                <div style="font-size: 1.2rem; font-weight: 700; color: white;">Request Investment</div>
                <div style="font-size: 0.6rem; opacity: 0.8; margin-top: 0.5rem;">Available: PKR {{ number_format($availableBalance, 0, ',', ',') }}</div>
            </div>
            @else
            <!-- Always show 4th column - either button or placeholder -->
            <div style="
                background: rgba(255, 255, 255, 0.05);
                border-radius: 0.75rem;
                padding: 1.2rem;
                text-align: center;
                border: 1px solid rgba(255, 255, 255, 0.1);
            ">
                @if(auth()->user() && auth()->user()->role === 'Investor')
                    <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">No Balance</div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">Add funds to invest</div>
                @else
                    <div style="font-size: 0.7rem; opacity: 0.7; text-transform: uppercase;">Info</div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">Owner controls</div>
                @endif
            </div>
            @endif
        </div>

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
                "></div>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;"
                     x-show="$store.pools.some(pool => statusFilter === 'all' || pool.status === statusFilter)">
                    @foreach($pools->take(3) as $pool)
                    <div x-show="statusFilter === 'all' || '{{ $pool->status }}' === statusFilter"
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
                                <div style="font-size: 0.7rem; opacity: 0.6;">Status</div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: #22c55e;">
                                    Pool requirement amount is complete
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
                
                @if($pools->count() > 3)
                <div style="text-align: center; margin-top: 1rem;">
                    <button style="
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
                        View All Pools ({{ $pools->count() - 3 }} more)
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
                    <div style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.5rem;">Pools Invested In</div>
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
            onclick="window.location.href='{{ \App\Filament\Resources\Wallet\WalletResource::getUrl('index') }}'">
                View Details
            </div>
        </div>

        </div>
        <!-- END CARD -->
    @endforeach

    </div>

        <!-- CSS Animations -->
        <style>
            @keyframes pulseGlow {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.8; }
            }
            
            .wallet-card {
                min-width: unset !important;
            }
        }
    </style>
</x-filament-panels::page>
