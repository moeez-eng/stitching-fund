<?php
    use App\Models\InvestmentPool;
    use Illuminate\Support\Facades\Auth;
?>

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
        $pools = InvestmentPool::where('status', 'open')->get();
    ?>

    <div wire:poll.5s="loadData" style="max-width: 1200px; margin: auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; padding: 1rem;">

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $wallets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wallet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $availableBalance = $wallet->amount - ($wallet->allocations->sum('amount') ?? 0);
            $totalInvested = $wallet->allocations->sum('amount') ?? 0;
            
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
        ?>

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
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wallet->investor->profile_photo): ?>
                        <img src="<?php echo e($wallet->investor->profile_photo); ?>" alt="<?php echo e($userName); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <?php echo e(strtoupper(substr($userName, 0, 1))); ?>

                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div>
                    <div style="font-weight: 700; font-size: 1.1rem; letter-spacing: -0.025em;"><?php echo e($userName); ?></div>
                    <div style="opacity: 0.7; font-size: 0.85rem;"><?php echo e($wallet->investor->agency->name ?? 'No Agency'); ?></div>
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
                background: rgba(<?php echo e($statusColor == '#22c55e' ? '34, 197, 94' : ($statusColor == '#f59e0b' ? '245, 158, 11' : '239, 68, 68')); ?>, 0.2);
                color: <?php echo e($statusColor == '#22c55e' ? '#86efac' : ($statusColor == '#f59e0b' ? '#fde68a' : '#fca5a5')); ?>;
                border: 1px solid <?php echo e($statusColor == '#22c55e' ? 'rgba(34, 197, 94, 0.3)' : ($statusColor == '#f59e0b' ? 'rgba(245, 158, 11, 0.3)' : 'rgba(239, 68, 68, 0.3)')); ?>;
                box-shadow: 0 0 10px <?php echo e($glowColor); ?>;
                animation: statusPulse 2s ease-in-out infinite;
            ">
                <?php echo e(strtoupper($status)); ?>

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
            " data-target="<?php echo e($availableBalance); ?>">
                PKR <?php echo e(number_format($availableBalance. 0)); ?>

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
                +<?php echo e($growthPercentage); ?>% this month
            </div>
        </div>

        <!-- Interactive Stats Grid -->
        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; padding: 1.5rem; background: rgba(0, 0, 0, 0.25);">
            <?php
                $stats = [
                    ['Deposited', $wallet->amount, 'trending_up'],
                    ['Invested', $totalInvested, 'trending_down'],
                    ['Available', $availableBalance, 'wallet']
                ];
            ?>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
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
                <div style="font-size: 0.7rem; opacity: 0.7; letter-spacing: 0.1em; text-transform: uppercase;"><?php echo e($stat[0]); ?></div>
                <div style="font-size: 1.2rem; font-weight: 700; margin-top: 0.5rem; font-family: 'Roboto Mono', monospace;">
                    PKR <?php echo e(number_format($stat[1], 0)); ?>

                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>

        <!-- Investment Pools Section -->
        <div style="padding: 1.5rem; background: rgba(0, 0, 0, 0.15);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; opacity: 0.7;">Available Investment Pools</div>
                <div style="font-size: 0.75rem; opacity: 0.8;">
                    <span style="color: #86efac; font-weight: 600;"><?php echo e($pools->count()); ?></span> Total Pools
                </div>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($pools) && $pools->count() > 0): ?>
                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pools->take(3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php
                        // Get actual investment count for this pool
                        $investmentCount = $wallet->allocations->where('investment_pool_id', $pool->id)->count();
                        $hasInvested = $investmentCount > 0;
                        $totalInvestedInPool = $wallet->allocations->where('investment_pool_id', $pool->id)->sum('amount') ?? 0;
                    ?>
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
                    onclick="investInPool(<?php echo e($pool->id); ?>, '<?php echo e($pool->name); ?>', <?php echo e($pool->minimum_investment ?? 10000); ?>)">
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasInvested): ?>
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
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <div>
                                <div style="font-weight: 600; font-size: 0.95rem;"><?php echo e($pool->name); ?></div>
                                <div style="font-size: 0.75rem; opacity: 0.7; margin-top: 0.25rem;"><?php echo e($pool->description ?? 'High growth opportunity'); ?></div>
                            </div>
                           
                        </div>
                        
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.5rem; margin-top: 0.75rem;">
                            <div style="text-align: center;">
                                <div style="font-size: 0.7rem; opacity: 0.6;">Your Investments</div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: <?php echo e($hasInvested ? '#86efac' : '#fbbf24'); ?>;">
                                    <?php echo e($investmentCount); ?> <?php echo e($investmentCount == 1 ? 'time' : 'times'); ?>

                                </div>
                            </div>
                            <div style="text-align: center;">
                                <div style="font-size: 0.7rem; opacity: 0.6;">Status</div>
                                <div style="font-size: 0.8rem; font-weight: 600; color: <?php echo e($pool->status == 'open' ? '#86efac' : '#fbbf24'); ?>;">
                                    <?php echo e($pool->status == 'open' ? 'Active' : 'Closed'); ?>

                                </div>
                            </div>
                        </div>
                        
                        <div style="margin-top: 0.75rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                                <span style="font-size: 0.7rem; opacity: 0.6;">Your Total Investment</span>
                                <span style="font-size: 0.7rem; opacity: 0.8; font-family: 'Roboto Mono', monospace;">
                                    PKR <?php echo e(number_format($totalInvestedInPool, 0)); ?>

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
                                    width: <?php echo e(min($totalInvestedInPool / ($pool->target_amount ?? 1000000) * 100, 100)); ?>%;
                                    transition: width 0.3s ease;
                                "></div>
                            </div>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($hasInvested): ?>
                        <div style="
                            margin-top: 0.75rem;
                            padding: 0.5rem;
                            background: rgba(34, 197, 94, 0.1);
                            border: 1px solid rgba(34, 197, 94, 0.2);
                            border-radius: 0.375rem;
                            text-align: center;
                        ">
                            <div style="font-size: 0.75rem; color: #86efac; font-weight: 600;">Active Investment</div>
                            <div style="font-size: 0.7rem; opacity: 0.8; margin-top: 0.25rem;">Last investment: <?php echo e($wallet->allocations->where('investment_pool_id', $pool->id)->sortByDesc('created_at')->first()?->created_at->format('M j, Y') ?? 'Unknown'); ?></div>
                        </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($pools->count() > 3): ?>
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
                        View All Pools (<?php echo e($pools->count() - 3); ?> more)
                    </button>
                </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
                    <div style="font-size: 1.25rem; font-weight: 700; color: #a78bfa;"><?php echo e($pools->count()); ?></div>
                </div>
                <div style="
                    background: rgba(255, 255, 255, 0.05);
                    border: 1px solid rgba(255, 255, 255, 0.1);
                    border-radius: 0.5rem;
                    padding: 1rem;
                ">
                    <div style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.5rem;">Pools Invested In</div>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #86efac;">
                        <?php echo e($wallet->allocations->pluck('investment_pool_id')->unique()->count()); ?>

                    </div>
                </div>
            </div>
        </div>
        <div style="padding: 1.5rem; display: flex; gap: 1rem; background: rgba(0, 0, 0, 0.3);">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($user->role !== 'Investor'): ?>
            <a href="<?php echo e(\App\Filament\Resources\Wallet\WalletResource::getUrl('edit', ['record' => $wallet->id])); ?>"
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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
            onclick="window.location.href='<?php echo e(\App\Filament\Resources\Wallet\WalletResource::getUrl('index')); ?>'">
                View Details
            </div>
        </div>

        </div>
        <!-- END CARD -->
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

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