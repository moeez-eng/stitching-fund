<?php if (isset($component)) { $__componentOriginal166a02a7c5ef5a9331faf66fa665c256 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'filament-panels::components.page.index','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('filament-panels::page'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <?php
        $wallets = $this->getWalletData();
        $user = Auth::user();
    ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(!$wallets || (is_array($wallets) && empty($wallets)) || (!is_array($wallets) && $wallets->isEmpty())): ?>
        <div class="text-center py-12">
            <div class="text-6xl mb-4">💼</div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Wallet Found</h3>
            <p class="text-gray-600 mb-6">Contact your agency owner to create your investment wallet.</p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->role === 'Agency Owner'): ?>
                <button onclick="window.location.href='<?php echo e(\App\Filament\Resources\Wallet\WalletResource::getUrl('create')); ?>" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    Create Wallet
                </button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        <?php
            $walletCollection = is_array($wallets) ? collect($wallets) : $wallets;
        ?>
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $walletCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wallet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $availableBalance = $wallet->available_balance;
                $totalInvested = $wallet->total_invested;
                $walletStatus = $wallet->wallet_status;
                $userName = $wallet->investor->name ?? 'Unknown Investor';
                $userEmail = $wallet->investor->email ?? '';
            ?>
            
            <div style="max-width: 600px; margin: 0 auto; border-radius: 20px; background: rgba(124, 58, 237, 0.15); border: 1px solid rgba(124, 58, 237, 0.3); box-shadow: 0 8px 32px rgba(0,0,0,0.1); overflow: hidden;">
                <div style="padding: 24px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #7c3aed; color: white; display: flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold;">
                            <?php echo e(strtoupper(substr($userName, 0, 1))); ?>

                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 20px; color: #7c3aed;"><?php echo e($userName); ?></div>
                            <div style="color: #6b7280; font-size: 14px;"><?php echo e($wallet->agencyOwner->name ?? 'ABC Agency'); ?></div>
                        </div>
                    </div>
                    <span style="padding: 8px 16px; border-radius: 9999px; background: <?php echo e($walletStatus['status'] === 'healthy' ? '#22c55e' : ($walletStatus['status'] === 'low' ? '#eab308' : '#ef4444')); ?>20; color: <?php echo e($walletStatus['status'] === 'healthy' ? '#22c55e' : ($walletStatus['status'] === 'low' ? '#eab308' : '#ef4444')); ?>; font-weight: bold; font-size: 13px;">
                        <?php echo e(strtoupper($walletStatus['status'])); ?>

                    </span>
                </div>

                <div style="padding: 0 24px 32px;">
                    <div style="text-align: center; margin-bottom: 32px;">
                        <div style="color: #6b7280; font-size: 15px;">Available Balance</div>
                        <div style="font-size: 52px; font-weight: 800; color: #7c3aed;"> <?php echo e(number_format($availableBalance, 0)); ?></div>
                        <div style="color: #22c55e; font-size: 15px; margin-top: 8px;">↑ +12.5% this month</div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; text-align: center;">
                        <div style="background: rgba(124, 58, 237, 0.1); padding: 16px; border-radius: 12px;">
                            <div style="color: #6b7280; font-size: 13px;">Deposited</div>
                            <div style="font-weight: bold; font-size: 20px; color: #22c55e;"> <?php echo e(number_format($wallet->amount, 0)); ?></div>
                        </div>
                        <div style="background: rgba(124, 58, 237, 0.1); padding: 16px; border-radius: 12px;">
                            <div style="color: #6b7280; font-size: 13px;">Invested</div>
                            <div style="font-weight: bold; font-size: 20px; color: #22c55e;"> <?php echo e(number_format($totalInvested, 0)); ?></div>
                        </div>
                    </div>

                    <!-- Investment Pool Section -->
                    <div style="background: rgba(124, 58, 237, 0.08); padding: 16px; border-radius: 12px; margin-top: 20px; margin-bottom: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="color: #6b7280; font-size: 12px; margin-bottom: 4px;">Current Investment Pool</div>
                                <div style="font-size: 24px; font-weight: bold; color: #7c3aed;">PKR <?php echo e(number_format($wallet->pool_contribution ?? 0, 0)); ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="color: #22c55e; font-size: 12px;"><?php echo e($wallet->pool_investors ?? 1); ?> investors</div>
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
                                <span style="color: #22c55e; font-weight: 600;">+PKR <?php echo e(number_format($wallet->amount, 0)); ?></span>
                            </div>
                            <div style="color: #6b7280; font-size: 11px; margin-top: 4px;"><?php echo e($wallet->created_at->format('M d, Y')); ?></div>
                        </div>
                        <div style="background: rgba(124, 58, 237, 0.05); padding: 12px; border-radius: 8px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 8px; height: 8px; background: #7c3aed; border-radius: 50%;"></div>
                                    <span>Pool Investment</span>
                                </div>
                                <span style="color: #7c3aed; font-weight: 600;">PKR <?php echo e(number_format($totalInvested, 0)); ?></span>
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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->role === 'Agency Owner'): ?>
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; margin-top: 20px;">
                        <a href="<?php echo e(\App\Filament\Resources\Wallet\WalletResource::getUrl('create')); ?>" 
                           style="padding: 12px; background: #22c55e; color: white; border-radius: 8px; font-size: 12px; text-decoration: none; text-align: center; display: block; font-weight: 500;">
                            💰 Deposit
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
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div style="padding: 20px 24px; background: rgba(124, 58, 237, 0.05); display: flex; gap: 12px;">
                    <a href="<?php echo e(\App\Filament\Resources\Wallet\WalletResource::getUrl('edit', ['record' => $wallet->id])); ?>"
                       style="flex: 1; padding: 12px; background: #6b7280; color: white; border-radius: 12px; text-align: center; text-decoration: none;"
                       <?php if($user->role === 'Investor'): ?> onclick="return false;" style="opacity: 0.5; cursor: not-allowed;" <?php endif; ?>>
                        Edit
                    </a>
                    <button style="flex: 1; padding: 12px; background: #7c3aed; color: white; border-radius: 12px; border: none; font-weight: 500;">
                        View Details
                    </button>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $attributes = $__attributesOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__attributesOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256)): ?>
<?php $component = $__componentOriginal166a02a7c5ef5a9331faf66fa665c256; ?>
<?php unset($__componentOriginal166a02a7c5ef5a9331faf66fa665c256); ?>
<?php endif; ?><?php /**PATH C:\xampp\htdocs\stitching-fund\resources\views/filament/wallet/pages/list-wallets.blade.php ENDPATH**/ ?>