<x-filament-panels::page>
    @php
        $wallets = $this->getWalletData();
        $user = Auth::user();
    @endphp

    @if(!$wallets || (is_array($wallets) && empty($wallets)) || (!is_array($wallets) && $wallets->isEmpty()))
        <div class="text-center py-12">
            <div class="text-6xl mb-4">💼</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Wallet Found</h3>
            <p class="text-gray-600 mb-6">Contact your agency owner to create your investment wallet.</p>
            @if($user->role === 'Agency Owner')
                <button onclick="window.location.href='{{ \App\Filament\Resources\Wallet\WalletResource::getUrl('create') }}" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Create Wallet
                </button>
            @endif
        </div>
    @else
        @php
            $walletCollection = is_array($wallets) ? collect($wallets) : $wallets;
        @endphp
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
        @foreach($walletCollection as $wallet)
            @php
                $availableBalance = $wallet->available_balance;
                $totalInvested = $wallet->total_invested;
                $walletStatus = $wallet->wallet_status;
                $userName = $wallet->investor->name ?? 'Unknown Investor';
                $userEmail = $wallet->investor->email ?? '';
            @endphp
            
            <div style="width: 100%; border-radius: 20px; background: rgba(124, 58, 237, 0.15); border: 1px solid rgba(124, 58, 237, 0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
                <div style="padding: 24px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #7c3aed; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                            {{ strtoupper(substr($userName, 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 20px; color: #7c3aed;">{{ $userName }}</div>
                            <div style="color: #6b7280; font-size: 14px;">{{ $wallet->agencyOwner->name ?? 'ABC Agency' }}</div>
                        </div>
                    </div>
                    <span style="padding: 8px 16px; border-radius: 9999px; background: {{ $walletStatus['status'] === 'healthy' ? '#22c55e' : ($walletStatus['status'] === 'low' ? '#eab308' : '#ef4444') }}20; color: {{ $walletStatus['status'] === 'healthy' ? '#22c55e' : ($walletStatus['status'] === 'low' ? '#eab308' : '#ef4444') }}; font-weight: bold; font-size: 13px;">
                        {{ strtoupper($walletStatus['status']) }}
                    </span>
                </div>

                <div style="padding: 0 24px 32px;">
                    <div style="text-align: center; margin-bottom: 32px;">
                        <div style="color: #6b7280; font-size: 15px;">Available Balance</div>
                        <div style="font-size: 52px; font-weight: 800; color: #7c3aed;"> {{ number_format($availableBalance, 0) }}</div>
                        <div style="color: #22c55e; font-size: 15px; margin-top: 8px;">↑ +12.5% this month</div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; text-align: center; margin-bottom: 16px;">
                        <div style="background: rgba(124, 58, 237, 0.1); padding: 16px; border-radius: 12px;">
                            <div style="color: #6b7280; font-size: 13px;">Deposited</div>
                            <div style="font-weight: bold; font-size: 20px; color: #22c55e;">PKR {{ number_format($wallet->amount, 0) }}</div>
                        </div>
                        <div style="background: rgba(124, 58, 237, 0.1); padding: 16px; border-radius: 12px;">
                            <div style="color: #6b7280; font-size: 13px;">Invested</div>
                            <div style="font-weight: bold; font-size: 20px; color: #22c55e;">PKR {{ number_format($totalInvested, 0) }}</div>
                        </div>
                        <div style="background: rgba(16, 185, 129, 0.1); padding: 16px; border-radius: 12px;">
                            <div style="color: #6b7280; font-size: 13px;">Pool Balance</div>
                            <div style="font-weight: bold; font-size: 20px; color: #10b981;">PKR {{ number_format($wallet->pool_balance ?? 0, 0) }}</div>
                        </div>
                    </div>

                    @php
                        $poolPercentage = $wallet->pool_balance ? min(100, ($wallet->pool_balance / $wallet->amount) * 100) : 0;
                    @endphp
                    <div style="margin-bottom: 16px;
                                background: rgba(124, 58, 237, 0.1);
                                border-radius: 10px;
                                height: 10px;
                                width: 100%;">
                        <div style="height: 100%;
                                width: {{ $poolPercentage }}%;
                                background: linear-gradient(90deg, #8b5cf6, #7c3aed);
                                border-radius: 10px;
                                transition: width 0.5s ease-in-out;">
                        </div>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 16px; font-size: 12px; color: #6b7280;">
                        <span>Pool Progress</span>
                        <span>{{ number_format($poolPercentage, 1) }}%</span>
                    </div>

                    <!-- Investment Pool Section -->
                    <div style="background: rgba(124, 58, 237, 0.08); padding: 16px; border-radius: 12px; margin-top: 20px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">Current Investment Pool</div>
                                <div style="font-size: 24px; font-weight: bold; color: #7c3aed;">PKR {{ number_format($wallet->pool_contribution ?? 0, 0) }}</div>
                            </div>
                            <div style="text-align: right;">
                                <div style="color: #22c55e; font-size: 12px;">{{ $wallet->pool_investors ?? 1 }} investors</div>
                                <div style="color: #6b7280; font-size: 11px;">Active Pool</div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Transactions -->
                    <div style="margin-top: 20px;">
                        <div style="font-weight: 600; color: #1f2937; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="16" height="16" fill="#7c3aed" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            Recent Activity
                        </div>
                        <div style="background: rgba(124, 58, 237, 0.05); padding: 12px; border-radius: 8px; margin-bottom: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #22c55e; border-radius: 50%;"></div>
                                    <span>Deposit from Agency</span>
                                </div>
                                <span style="color: #22c55e; font-weight: 600;">+PKR {{ number_format($wallet->amount, 0) }}</span>
                            </div>
                            <div style="color: #6b7280; font-size: 11px; margin-top: 4px;">{{ $wallet->created_at->format('M d, Y') }}</div>
                        </div>
                        <div style="background: rgba(124, 58, 237, 0.05); padding: 12px; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #7c3aed; border-radius: 50%;"></div>
                                    <span>Pool Investment</span>
                                </div>
                                <span style="color: #7c3aed; font-weight: 600;">PKR {{ number_format($totalInvested, 0) }}</span>
                            </div>
                            <div style="color: #6b7280; font-size: 11px; margin-top: 4px;">Last month</div>
                        </div>
                    </div>

                    <!-- Performance Metrics -->
                    <div style="margin-top: 20px;">
                        <div style="color: #6b7280; font-size: 12px; margin-bottom: 8px;">6-Month Performance</div>
                        <div style="height: 60px; background: linear-gradient(135deg, #22c55e 0%, #7c3aed 100%); border-radius: 8px; position: relative; overflow: hidden;">
                            <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 40%; background: rgba(255,255,255,0.1);"></div>
                            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: white; font-weight: bold; font-size: 14px;">
                                +12.5% ROI
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions - Only for Agency Owners -->
                    @if($user->role === 'Agency Owner')
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 20px;">
                       <a href="{{ $wallet->deposit_slip ? Storage::url($wallet->deposit_slip) : '#' }}" 
                        target="_blank"
                        style="padding: 12px; background: #22c55e; color: white; border-radius: 8px; font-size: 12px; text-decoration: none; text-align: center; display: block; font-weight: 500;"
                        @if(!$wallet->deposit_slip) onclick="return false; event.preventDefault();" style="opacity: 0.7; cursor: not-allowed;" @endif>
                            @if($wallet->deposit_slip)
                                View Deposit Slip
                            @else
                                No Slip Available
                            @endif
                        </a>
                        <button onclick="alert('Investment feature coming soon! This will invest available balance into the pool.')" 
                                style="padding: 12px; background: #7c3aed; color: white; border-radius: 8px; font-size: 12px; border: none; cursor: pointer; font-weight: 500;">
                            📊 Invest
                        </button>
                        <button onclick="alert('Withdrawal feature coming soon! This will allow investors to withdraw from their available balance.')" 
                                style="padding: 12px; background: #6b7280; color: white; border-radius: 8px; font-size: 12px; border: none; cursor: pointer; font-weight: 500;">
                            💸 Withdraw
                        </button>
                    </div>
                    @endif
                </div>
                <div style="padding: 20px 24px; background: rgba(124, 58, 237, 0.05); display: flex; gap: 12px;">
                    @if($user->role === 'Agency Owner')
                        <a href="{{ \App\Filament\Resources\Wallet\WalletResource::getUrl('edit', ['record' => $wallet->id]) }}"
                           style="flex: 1; padding: 12px; background: #6b7280; color: white; border-radius: 12px; text-align: center; text-decoration: none;">
                            Edit
                        </a>
                    @else
                        <a href="{{ $wallet->deposit_slip ? Storage::url($wallet->deposit_slip) : '#' }}" 
                        target="_blank"
                        style="padding: 12px; background: flex #22c55e; color: white; border-radius: 8px; font-size: 12px; text-decoration: none; text-align: center; display: block; font-weight: 500;"
                        @if(!$wallet->deposit_slip) onclick="return false; event.preventDefault();" style="opacity: 0.7; cursor: not-allowed;" @endif>
                            @if($wallet->deposit_slip)
                                View Deposit Slip
                            @else
                                No Slip Available
                            @endif
                        </a>
                    @endif
                    <button style="flex: 1; padding: 12px; background: #7c3aed; color: white; border-radius: 12px; border: none; font-weight: 500;">
                        View Details
                    </button>
                </div>
            </div>
        @endforeach
        </div>
    @endif
</x-filament-panels::page>