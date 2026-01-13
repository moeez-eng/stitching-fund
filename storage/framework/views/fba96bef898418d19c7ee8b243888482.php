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
    <!-- Top Bar - Same as Wallet -->
    <div style="background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%); padding: 2rem; margin: -1rem -1rem 2rem -1rem; border-radius: 0 0 1rem 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h1 style="color: white; font-size: 2rem; font-weight: 700; margin: 0;"><?php echo e($this->getTitle()); ?></h1>
                <p style="color: rgba(255, 255, 255, 0.8); margin: 0.5rem 0 0 0;">Complete transaction history and ledger records</p>
            </div>
            
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($wallet || (auth()->user()->role === 'Investor' && !$walletId)): ?>
            <div style="text-align: center;">
                <div style="color: rgba(255, 255, 255, 0.7); font-size: 0.875rem; margin-bottom: 0.5rem;">AVAILABLE BALANCE</div>
                <div style="color: #22c55e; font-size: 2.5rem; font-weight: 900; font-family: 'Roboto Mono', monospace;">
                    PKR <?php echo e(number_format($walletBalance ?? 0, 0, ',', ',')); ?>

                </div>
                <div style="color: #22c55e; font-size: 0.875rem; margin-top: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M13 7h8m0 0v8m0-8l-8 8-8"/>
                    </svg>
                    +12.5%
                </div>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        
        <!-- Filter Buttons - Same as Wallet Style -->
        <div style="display: flex; gap: 0.75rem; margin-bottom: 2rem; flex-wrap: wrap;">
            <button onclick="filterTransactions('all')" 
                    id="filter-all"
                    style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; border: 1px solid rgba(139, 92, 246, 0.3); font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 0 20px rgba(139, 92, 246, 0.3);"
                    onmouseover="this.style.background='linear-gradient(135deg, #7c3aed, #6d28d9)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 0 25px rgba(139, 92, 246, 0.4)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #8b5cf6, #7c3aed)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 0 20px rgba(139, 92, 246, 0.3)'">
                All
            </button>
            <button onclick="filterTransactions('deposit')" 
                    id="filter-deposit"
                    style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; border: 1px solid rgba(34, 197, 94, 0.3); font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);"
                    onmouseover="this.style.background='linear-gradient(135deg, #16a34a, #15803d)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 0 25px rgba(34, 197, 94, 0.4)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #22c55e, #16a34a)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 0 20px rgba(34, 197, 94, 0.3)'">
                Deposits
            </button>
            <button onclick="filterTransactions('invest')" 
                    id="filter-invest"
                    style="background: linear-gradient(135deg, #f59e0b, #d97706); color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; border: 1px solid rgba(245, 158, 11, 0.3); font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 0 20px rgba(245, 158, 11, 0.3);"
                    onmouseover="this.style.background='linear-gradient(135deg, #d97706, #b45309)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 0 25px rgba(245, 158, 11, 0.4)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #f59e0b, #d97706)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 0 20px rgba(245, 158, 11, 0.3)'">
                Investments
            </button>
            <button onclick="filterTransactions('withdrawal')" 
                    id="filter-withdrawal"
                    style="background: linear-gradient(135deg, #ef4444, #dc2626); color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; border: 1px solid rgba(239, 68, 68, 0.3); font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 0 20px rgba(239, 68, 68, 0.3);"
                    onmouseover="this.style.background='linear-gradient(135deg, #dc2626, #b91c1c)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 0 25px rgba(239, 68, 68, 0.4)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #ef4444, #dc2626)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 0 20px rgba(239, 68, 68, 0.3)'">
                Withdrawals
            </button>
            <button onclick="filterTransactions('profit')" 
                    id="filter-profit"
                    style="background: linear-gradient(135deg, #22c55e, #16a34a); color: white; padding: 0.5rem 1.5rem; border-radius: 0.5rem; border: 1px solid rgba(34, 197, 94, 0.3); font-weight: 600; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);"
                    onmouseover="this.style.background='linear-gradient(135deg, #16a34a, #15803d)'; this.style.transform='translateY(-2px)'; this.style.boxShadow='0 0 25px rgba(34, 197, 94, 0.4)'"
                    onmouseout="this.style.background='linear-gradient(135deg, #22c55e, #16a34a)'; this.style.transform='translateY(0)'; this.style.boxShadow='0 0 20px rgba(34, 197, 94, 0.3)'">
                Profits
            </button>
        </div>
    </div>

    <!-- Activity Feed - Transaction Cards -->
    <div id="transactions-container" style="padding: 1rem; max-width: 900px; margin: 0 auto;">
        <div id="loading-spinner" style="text-align: center; padding: 3rem; color: rgba(255, 255, 255, 0.8);">
            <div style="display: inline-block; width: 40px; height: 40px; border: 3px solid rgba(139, 92, 246, 0.3); border-top: 3px solid #8b5cf6; border-radius: 50%; animation: spin 1s linear infinite;"></div>
            <p style="margin-top: 1rem;">Loading transactions...</p>
        </div>
        
        <div id="transactions-list" style="display: none;">
            <!-- Transactions will be loaded here -->
        </div>
    </div>

    <style>
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .transaction-card {
            background: linear-gradient(145deg, #1e1b4b 0%, #581c87 50%, #8b5cf6 100%);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 1rem;
            padding: 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 
                0 0 40px rgba(139, 92, 246, 0.3),
                0 20px 60px rgba(0, 0, 0, 0.7),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        
        .transaction-card:hover {
            transform: translateY(-4px);
            box-shadow: 
                0 0 60px rgba(139, 92, 246, 0.5),
                0 30px 80px rgba(0, 0, 0, 0.8),
                inset 0 1px 0 rgba(255, 255, 255, 0.15);
        }
        
        .transaction-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: 
                radial-gradient(circle at top right, rgba(139, 92, 246, 0.25), transparent 50%),
                radial-gradient(circle at bottom left, rgba(124, 58, 237, 0.2), transparent 50%);
            pointer-events: none;
        }
        
        .transaction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .transaction-type {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            backdrop-filter: blur(10px);
        }
        
        .transaction-amount {
            font-size: 1.5rem;
            font-weight: 900;
            font-family: 'Roboto Mono', monospace;
        }
        
        .transaction-details {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 0.75rem;
            padding: 1rem;
            margin-top: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .transaction-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }
        
        .meta-label {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .meta-value {
            color: rgba(255, 255, 255, 0.9);
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            backdrop-filter: blur(10px);
        }
        
        .deposit-type {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .invest-type {
            background: rgba(139, 92, 246, 0.2);
            color: #c4b5fd;
            border: 1px solid rgba(139, 92, 246, 0.3);
        }
        
        .withdrawal-type {
            background: rgba(239, 68, 68, 0.2);
            color: #fca5a5;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        
        .profit-type {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        
        .adjustment-type {
            background: rgba(107, 114, 128, 0.2);
            color: #d1d5db;
            border: 1px solid rgba(107, 114, 128, 0.3);
        }
        
        .positive-amount {
            color: #22c55e;
        }
        
        .negative-amount {
            color: #ef4444;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: rgba(255, 255, 255, 0.8);
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
    </style>

    <script>
        let currentFilter = 'all';
        let allTransactions = <?php echo json_encode($transactions ?? [], 15, 512) ?>;
        
        function filterTransactions(type) {
            currentFilter = type;
            
            // Update button styles
            document.querySelectorAll('[id^="filter-"]').forEach(btn => {
                btn.style.opacity = '0.7';
                btn.style.transform = 'scale(0.95)';
            });
            
            document.getElementById(`filter-${type}`).style.opacity = '1';
            document.getElementById(`filter-${type}`).style.transform = 'scale(1)';
            
            loadTransactions();
        }
        
        function loadTransactions() {
            const container = document.getElementById('transactions-list');
            const spinner = document.getElementById('loading-spinner');
            
            // Show loading
            spinner.style.display = 'block';
            container.style.display = 'none';
            
            // Filter transactions
            let filtered = allTransactions;
            if (currentFilter !== 'all') {
                filtered = allTransactions.filter(t => t.type === currentFilter);
            }
            
            // Simulate loading delay
            setTimeout(() => {
                renderTransactions(filtered);
                spinner.style.display = 'none';
                container.style.display = 'block';
            }, 500);
        }
        
        function renderTransactions(transactions) {
            const container = document.getElementById('transactions-list');
            
            if (transactions.length === 0) {
                container.innerHTML = `
                    <div class="empty-state">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h12m-6-8h12m-6-4h.01"/>
                        </svg>
                        <h3>No transactions found</h3>
                        <p>No ${currentFilter === 'all' ? 'transactions' : currentFilter + ' transactions'} have been recorded yet.</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = transactions.map(transaction => {
                const typeClass = `${transaction.type}-type`;
                const amountClass = ['deposit', 'return', 'profit'].includes(transaction.type) ? 'positive-amount' : 'negative-amount';
                const icon = getTransactionIcon(transaction.type);
                const statusInfo = getTransactionStatus(transaction);
                
                return `
                    <div class="transaction-card">
                        <div class="transaction-header">
                            <div class="transaction-type ${typeClass}">
                                ${icon}
                                ${getTransactionLabel(transaction.type)}
                            </div>
                            <div class="transaction-amount ${amountClass}">
                                ${['deposit', 'return', 'profit'].includes(transaction.type) ? '+' : '-'}PKR ${number_format(transaction.amount, 0, ',', ',')}
                            </div>
                        </div>
                        
                        <div class="transaction-details">
                            <div style="color: rgba(255, 255, 255, 0.9); margin-bottom: 0.5rem;">${transaction.description}</div>
                            
                            <div class="transaction-meta">
                                <div class="meta-item">
                                    <span class="meta-label">Date & Time</span>
                                    <span class="meta-value">${formatDateTime(transaction.transaction_date)}</span>
                                </div>
                                <div class="meta-item">
                                    <span class="meta-label">Reference</span>
                                    <span class="meta-value">${transaction.reference || 'N/A'}</span>
                                </div>
                                ${statusInfo ? `
                                    <div class="meta-item">
                                        <span class="meta-label">Status</span>
                                        <span class="meta-value">${statusInfo}</span>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        }
        
        function getTransactionIcon(type) {
            const icons = {
                'deposit': '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 14l-7 7m0 0l-7-7m7 7v-3"/></svg>',
                'invest': '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 7h8m0 0v8m0-8l-8 8-8"/></svg>',
                'return': '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>',
                'profit': '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 01.95.69h1.5a1 1 0 01.95-.69l1.07-3.292z"/></svg>',
                'withdrawal': '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 14l7 7m0 0l-7-7m7 7v-3"/></svg>',
                'pool_adjustment': '<svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065-2.572c1.756-.426 1.756-2.924 0-3.35a1.724 1.724 0 00-1.066-2.573c-.94-1.543-.826-3.31-.826-2.371z"/></svg>'
            };
            return icons[type] || '';
        }
        
        function getTransactionLabel(type) {
            const labels = {
                'deposit': 'Deposit',
                'invest': 'Investment',
                'return': 'Return',
                'profit': 'Profit',
                'withdrawal': 'Withdrawal',
                'pool_adjustment': 'Adjustment'
            };
            return labels[type] || type;
        }
        
        function getTransactionStatus(transaction) {
            // This would come from your database
            // For now, return empty or mock status
            return '';
        }
        
        function formatDateTime(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { 
                month: 'short', 
                day: 'numeric', 
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        
        // Load on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTransactions();
        });
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
<?php /**PATH C:\xampp\htdocs\stitching-fund\resources\views/filament/wallet/pages/transaction-history-new.blade.php ENDPATH**/ ?>