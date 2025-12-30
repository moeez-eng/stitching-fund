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
        
        <div class="space-y-4">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $walletCollection; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wallet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $availableBalance = $wallet->available_balance;
                $totalInvested = $wallet->total_invested;
                $walletStatus = $wallet->wallet_status;
                $userName = $wallet->investor->name ?? 'Unknown Investor';
                $userEmail = $wallet->investor->email ?? '';
                
                $statusColor = match($walletStatus['status']) {
                    'empty' => 'text-red-500',
                    'low' => 'text-yellow-500', 
                    'healthy' => 'text-green-500',
                    default => 'text-blue-500'
                };
            ?>
            
            <div style="max-width: 600px; margin: 0 auto; border-radius: 16px; background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04); overflow: hidden;">
                <!-- Header with User Info -->
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 20px; background: rgba(109, 40, 217, 0.7);">
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(255, 255, 255, 0.2); display: flex; align-items: center; justify-content: center; color: white; font-size: 20px; font-weight: bold;">
                            <?php echo e(strtoupper(substr($userName, 0, 1))); ?>

                        </div>
                        <div>
                            <div style="color: white; font-weight: bold; font-size: 18px;"><?php echo e($userName); ?></div>
                            <div style="color: #e9d5ff; font-size: 14px; margin-top: 4px;"><?php echo e($wallet->agencyOwner->name ?? 'ABC Agency'); ?></div>
                        </div>
                    </div>
                    
                    <span style="padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; background: rgba(34, 197, 94, 0.2); color: #86efac; border: 1px solid rgba(134, 239, 172, 0.3);">
                        <?php echo e(strtoupper($walletStatus['status'])); ?>

                    </span>
                </div>

                <!-- Balance Section with Wallet Icon -->
                <div style="padding: 24px; text-align: center; position: relative;">
                    <div style="position: absolute; top: 16px; left: 16px;">
                        <svg width="32" height="32" fill="#e9d5ff" viewBox="0 0 20 20">
                            <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"/>
                            <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div style="color: #e9d5ff; font-size: 14px; margin-bottom: 8px;">Available Balance</div>
                    <div style="color: white; font-size: 48px; font-weight: 800; margin-bottom: 8px;">PKR <?php echo e(number_format($availableBalance, 0)); ?></div>
                    <div style="color: #86efac; font-size: 14px; display: flex; align-items: center; justify-content: center; gap: 4px;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                        </svg>
                        +12.5% this month
                    </div>
                </div>

                <!-- Three Column Tabs -->
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; text-align: center; background: rgba(109, 40, 217, 0.5); border-top: 1px solid #7c3aed;">
                    <div style="padding: 16px; border-right: 1px solid #7c3aed;">
                        <div style="color: #e9d5ff; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Deposited</div>
                        <div style="color: white; font-weight: bold; font-size: 18px;">PKR <?php echo e(number_format($wallet->amount, 0)); ?></div>
                    </div>
                    <div style="padding: 16px; border-right: 1px solid #7c3aed;">
                        <div style="color: #e9d5ff; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Invested</div>
                        <div style="color: white; font-weight: bold; font-size: 18px;">PKR <?php echo e(number_format($totalInvested, 0)); ?></div>
                    </div>
                    <div style="padding: 16px;">
                        <div style="color: #e9d5ff; font-size: 12px; font-weight: 500; margin-bottom: 4px;">Available</div>
                        <div style="color: white; font-weight: bold; font-size: 18px;">PKR <?php echo e(number_format($availableBalance, 0)); ?></div>
                    </div>
                </div>

                <!-- Status Message -->
                <div style="padding: 20px; background: rgba(109, 40, 217, 0.3); border-top: 1px solid #7c3aed;">
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                        <div style="width: 24px; height: 24px; background: #22c55e; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                            <svg width="16" height="16" fill="white" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span style="color: #86efac; font-weight: 600;">Healthy Balance</span>
                    </div>
                    <div style="color: #e9d5ff; font-size: 14px;">Ready for investments</div>
                </div>

                <!-- Action Buttons -->
                <div style="padding: 20px; background: rgba(109, 40, 217, 0.5); border-top: 1px solid #7c3aed;">
                    <div style="display: flex; gap: 12px;">
                        <a href="<?php echo e(\App\Filament\Resources\Wallet\WalletResource::getUrl('edit', ['record' => $wallet->id])); ?>"
                           style="flex: 1; padding: 8px 16px; background: #374151; color: white; border-radius: 8px; text-align: center; font-size: 14px; font-weight: 500; text-decoration: none; display: block;"
                           <?php if($user->role === 'Investor'): ?> onclick="return false;" style="opacity: 0.5; cursor: not-allowed;" <?php endif; ?>>
                            Edit
                        </a>
                        <button style="flex: 1; padding: 8px 16px; background: #7c3aed; color: white; border-radius: 8px; font-size: 14px; font-weight: 500; cursor: pointer; border: none;">
                            View
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
<?php endif; ?>

<?php /**PATH C:\xampp\htdocs\stitching-fund\resources\views/filament/wallet/pages/list-wallets.blade.php ENDPATH**/ ?>