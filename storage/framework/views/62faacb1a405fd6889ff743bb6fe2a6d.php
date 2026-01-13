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
    
    <div class="filter-pills">
        <button onclick="filterTransactions('all')" 
                class="filter-btn <?php if(request()->get('filter', 'all') === 'all'): ?> active <?php endif; ?>"
                data-filter="all">
                All
        </button>
        <button onclick="filterTransactions('deposit')" 
                class="filter-btn <?php if(request()->get('filter', 'all') === 'deposit'): ?> active <?php endif; ?>"
                data-filter="deposit">
                Deposits
        </button>
        <button onclick="filterTransactions('invest')" 
                class="filter-btn <?php if(request()->get('filter', 'all') === 'invest'): ?> active <?php endif; ?>"
                data-filter="invest">
                Investments
        </button>
        <button onclick="filterTransactions('withdrawal')" 
                class="filter-btn <?php if(request()->get('filter', 'all') === 'withdrawal'): ?> active <?php endif; ?>"
                data-filter="withdrawal">
                Withdrawals
        </button>
        <button onclick="filterTransactions('profit')" 
                class="filter-btn <?php if(request()->get('filter', 'all') === 'profit'): ?> active <?php endif; ?>"
                data-filter="profit">
                Profits
        </button>
    </div>

    
    <div class="transactions-container">
        <?php
            $transactions = $this->getTableRecords();
        ?>
        
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(empty($transactions)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox empty-icon"></i>
                <h3>No transactions found</h3>
                <p>No <?php echo e(request()->get('filter', 'all') === 'all' ? 'transactions' : request()->get('filter', 'all') . ' transactions'); ?> have been recorded yet.</p>
            </div>
        <?php else: ?>
            <div class="main-transaction-card">
                <div class="transaction-card-header">
                    <div class="header-left">
                        <i class="fas fa-history"></i>
                        <h3>All Transactions</h3>
                    </div>
                    <div class="header-right">
                        <span class="total-count"><?php echo e(count($transactions)); ?> Transactions</span>
                    </div>
                </div>
                
                <div class="transactions-list">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $transaction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $amountClass = in_array($transaction->type, ['deposit', 'return', 'profit']) ? 'positive' : 'negative';
                            $icon = $this->getTransactionIcon($transaction->type);
                            $label = $this->getTransactionLabel($transaction->type);
                        ?>
                        
                        <div class="transaction-row" data-type="<?php echo e($transaction->type); ?>">
                            <div class="transaction-left">
                                <div class="transaction-icon <?php echo e($transaction->type); ?>">
                                    <?php echo $icon; ?>

                                </div>
                                <div class="transaction-info">
                                    <div class="transaction-title"><?php echo e($label); ?></div>
                                    <div class="transaction-description"><?php echo e($transaction->description); ?></div>
                                    <div class="transaction-time"><?php echo e($transaction->transaction_date->diffForHumans()); ?></div>
                                </div>
                            </div>
                            <div class="transaction-right">
                                <div class="transaction-amount <?php echo e($amountClass); ?>">
                                    <?php echo e(in_array($transaction->type, ['deposit', 'return', 'profit']) ? '+' : '-'); ?>PKR <?php echo e(number_format($transaction->amount, 0, ',', ',')); ?>

                                </div>
                                <div class="transaction-date"><?php echo e($transaction->transaction_date->format('M d, Y')); ?></div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>

    <style>
        /* --------------------
           Filter Pills
        -------------------- */
        .filter-pills {
            display: flex;
            gap: 0.75rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: linear-gradient(135deg, #374151, #1f2937);
            color: rgba(255, 255, 255, 0.7);
            padding: 0.5rem 1.5rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(55, 65, 81, 0.3);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
        }

        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(0, 0, 0, 0.4);
        }

        .filter-btn.active {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            color: white;
            border-color: rgba(139, 92, 246, 0.5);
            box-shadow: 0 0 20px rgba(139, 92, 246, 0.4);
        }

        .filter-btn.deposit.active {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-color: rgba(34, 197, 94, 0.5);
        }

        .filter-btn.invest.active {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-color: rgba(245, 158, 11, 0.5);
        }

        .filter-btn.withdrawal.active {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            border-color: rgba(239, 68, 68, 0.5);
        }

        .filter-btn.profit.active {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            border-color: rgba(34, 197, 94, 0.5);
        }

        /* --------------------
           Main Transaction Card (Like Wallet Pools)
        -------------------- */
        .transactions-container {
            padding: 1rem;
            width: 100%;
            max-width: none;
            margin: 0;
        }

        .main-transaction-card {
            background: linear-gradient(145deg, #1e1b4b, #581c87, #8b5cf6);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 1rem;
            overflow: hidden;
            position: relative;
            box-shadow: 
                0 0 40px rgba(139, 92, 246, 0.3),
                0 20px 60px rgba(0, 0, 0, 0.7),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            margin: 0;
        }

        .main-transaction-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at top right, rgba(139, 92, 246, 0.25), transparent 50%),
                radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.2), transparent 50%);
            pointer-events: none;
            z-index: 0;
        }

        .transaction-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .header-left i {
            font-size: 1.5rem;
            color: #8b5cf6;
        }

        .header-left h3 {
            color: white;
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
        }

        .header-right {
            background: rgba(139, 92, 246, 0.2);
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }

        .total-count {
            color: #c4b5fd;
            font-weight: 600;
            font-size: 0.875rem;
        }

        /* --------------------
           Transaction Rows (Like Real Apps)
        -------------------- */
        .transactions-list {
            position: relative;
            z-index: 1;
        }

        .transaction-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            position: relative;
        }

        .transaction-row:hover {
            background: rgba(139, 92, 246, 0.1);
        }

        .transaction-row:last-child {
            border-bottom: none;
        }

        .transaction-left {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex: 1;
        }

        .transaction-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .transaction-icon.deposit {
            color: #86efac;
        }

        .transaction-icon.invest {
            color: #c4b5fd;
        }

        .transaction-icon.withdrawal {
            color: #fca5a5;
        }

        .transaction-icon.profit {
            color: #86efac;
        }

        .transaction-icon.return {
            color: #93c5fd;
        }

        .transaction-icon.pool_adjustment {
            color: #d1d5db;
        }

        .transaction-info {
            flex: 1;
        }

        .transaction-title {
            color: white;
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        .transaction-description {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .transaction-time {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .transaction-right {
            text-align: right;
        }

        .transaction-amount {
            font-size: 1.25rem;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            margin-bottom: 0.25rem;
        }

        .transaction-amount.positive {
            color: #22c55e;
        }

        .transaction-amount.negative {
            color: #ef4444;
        }

        .transaction-date {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: rgba(255, 255, 255, 0.8);
        }

        .empty-icon {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            opacity: 0.5;
            font-size: 4rem;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .transactions-container {
                padding: 0.5rem;
            }
            
            .transaction-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                padding: 1rem;
            }
            
            .transaction-left {
                width: 100%;
            }
            
            .transaction-right {
                width: 100%;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .transaction-icon {
                font-size: 1rem;
            }
            
            .transaction-title {
                font-size: 0.9rem;
            }
            
            .transaction-description {
                font-size: 0.8rem;
            }
            
            .transaction-time {
                font-size: 0.7rem;
            }
            
            .transaction-amount {
                font-size: 0.95rem;
            }
            
            .transaction-date {
                font-size: 0.7rem;
            }
            
            .filter-pills {
                justify-content: center;
                padding: 0 0.5rem;
            }
            
            .filter-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }
        }

        @media (max-width: 480px) {
            .transactions-container {
                padding: 0.25rem;
            }
            
            .transaction-row {
                padding: 0.75rem;
                gap: 0.5rem;
            }
            
            .transaction-icon {
                font-size: 0.9rem;
            }
            
            .transaction-title {
                font-size: 0.85rem;
            }
            
            .transaction-description {
                font-size: 0.75rem;
            }
            
            .transaction-time {
                font-size: 0.65rem;
            }
            
            .transaction-amount {
                font-size: 0.9rem;
            }
            
            .transaction-date {
                font-size: 0.65rem;
            }
            
            .filter-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.75rem;
            }
        }
    </style>

    <script>
        let currentFilter = 'all';
        
        function filterTransactions(type) {
            currentFilter = type;
            
            // Update button styles
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.querySelector(`[data-filter="${type}"]`).classList.add('active');
            
            // Show/hide transactions based on filter
            const rows = document.querySelectorAll('.transaction-row');
            rows.forEach(row => {
                const rowType = row.dataset.type;
                if (type === 'all' || rowType === type) {
                    row.style.display = 'flex';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
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
<?php /**PATH C:\xampp\htdocs\stitching-fund\resources\views/filament/wallet/pages/transaction-history.blade.php ENDPATH**/ ?>