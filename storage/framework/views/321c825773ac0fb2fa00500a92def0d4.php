// resources/views/filament/wallet/pages/list-wallets.blade.php

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

    <!-- Existing wallet list code remains the same until the Invest button -->

    <!-- Replace the existing Invest button with this modal trigger -->
    <button onclick="openInvestModal(<?php echo e($wallet->id); ?>, <?php echo e($wallet->available_balance); ?>)" 
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
        📊 Invest
    </button>

    <!-- Add this modal at the bottom of the file, before the closing x-filament-panels::page tag -->
    <div id="investModal" 
         class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg w-full max-w-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-semibold">Invest in Pool</h3>
                <button onclick="closeInvestModal()" class="text-gray-500 hover:text-gray-700">
                    &times;
                </button>
            </div>

            <form id="investmentForm">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="wallet_id" id="walletId">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Select Pool</label>
                    <select name="pool_id" id="poolSelect" 
                            class="w-full border border-gray-300 rounded-md p-2" 
                            onchange="updatePoolInfo()" required>
                        <option value="">Select a pool</option>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $pools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pool): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($pool->id); ?>" 
                                    data-required="<?php echo e($pool->amount_required); ?>"
                                    data-collected="<?php echo e($pool->collected_amount); ?>"
                                    data-remaining="<?php echo e($pool->remaining_amount); ?>">
                                <?php echo e($pool->name); ?> (<?php echo e(number_format($pool->remaining_amount)); ?> PKR remaining)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Amount (PKR)
                        <span id="maxAmount" class="text-xs text-gray-500"></span>
                    </label>
                    <input type="number" 
                           name="amount" 
                           id="investAmount"
                           class="w-full border border-gray-300 rounded-md p-2"
                           min="1"
                           step="0.01"
                           oninput="validateAmount()"
                           required>
                    <div id="amountError" class="text-red-500 text-xs mt-1 hidden"></div>
                </div>

                <div id="poolInfo" class="bg-gray-50 p-3 rounded-md mb-4 text-sm hidden">
                    <div class="flex justify-between mb-1">
                        <span>Pool Target:</span>
                        <span id="poolTarget">0 PKR</span>
                    </div>
                    <div class="flex justify-between mb-1">
                        <span>Collected:</span>
                        <span id="poolCollected">0 PKR</span>
                    </div>
                    <div class="flex justify-between font-medium">
                        <span>Remaining:</span>
                        <span id="poolRemaining">0 PKR</span>
                    </div>
                    <div class="mt-2">
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div id="poolProgress" class="bg-indigo-600 h-2.5 rounded-full" 
                                 style="width: 0%"></div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" 
                            onclick="closeInvestModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            id="submitButton"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 disabled:opacity-50">
                        Confirm Investment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
        let currentWalletBalance = 0;
        let currentPoolRemaining = 0;

        function openInvestModal(walletId, availableBalance) {
            document.getElementById('walletId').value = walletId;
            currentWalletBalance = parseFloat(availableBalance);
            document.getElementById('maxAmount').textContent = `(Max: ${formatCurrency(availableBalance)} PKR)`;
            document.getElementById('investModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function closeInvestModal() {
            document.getElementById('investModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
            resetForm();
        }

        function updatePoolInfo() {
            const select = document.getElementById('poolSelect');
            const poolInfo = document.getElementById('poolInfo');
            const selectedOption = select.options[select.selectedIndex];
            
            if (!selectedOption.value) {
                poolInfo.classList.add('hidden');
                return;
            }

            const required = parseFloat(selectedOption.dataset.required);
            const collected = parseFloat(selectedOption.dataset.collected);
            const remaining = parseFloat(selectedOption.dataset.remaining);
            currentPoolRemaining = remaining;

            document.getElementById('poolTarget').textContent = formatCurrency(required) + ' PKR';
            document.getElementById('poolCollected').textContent = formatCurrency(collected) + ' PKR';
            document.getElementById('poolRemaining').textContent = formatCurrency(remaining) + ' PKR';
            
            const progress = (collected / required) * 100;
            document.getElementById('poolProgress').style.width = `${progress}%`;
            
            poolInfo.classList.remove('hidden');
            validateAmount();
        }

        function validateAmount() {
            const amountInput = document.getElementById('investAmount');
            const amount = parseFloat(amountInput.value) || 0;
            const errorElement = document.getElementById('amountError');
            const submitButton = document.getElementById('submitButton');
            let isValid = true;

            if (amount <= 0) {
                showError('Amount must be greater than zero');
                isValid = false;
            } else if (amount > currentWalletBalance) {
                showError(`Amount exceeds your available balance of ${formatCurrency(currentWalletBalance)} PKR`);
                isValid = false;
            } else if (amount > currentPoolRemaining) {
                showError(`Amount exceeds pool's remaining capacity of ${formatCurrency(currentPoolRemaining)} PKR`);
                isValid = false;
            } else {
                hideError();
            }

            submitButton.disabled = !isValid;
            return isValid;
        }

        function showError(message) {
            const errorElement = document.getElementById('amountError');
            errorElement.textContent = message;
            errorElement.classList.remove('hidden');
        }

        function hideError() {
            document.getElementById('amountError').classList.add('hidden');
        }

        function resetForm() {
            document.getElementById('investmentForm').reset();
            document.getElementById('poolInfo').classList.add('hidden');
            document.getElementById('amountError').classList.add('hidden');
            currentWalletBalance = 0;
            currentPoolRemaining = 0;
        }

        function formatCurrency(amount) {
            return new Intl.NumberFormat('en-PK').format(amount.toFixed(2));
        }

        // Handle form submission
        document.getElementById('investmentForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validateAmount()) return;
            
            const formData = new FormData(this);
            const submitButton = document.getElementById('submitButton');
            const originalButtonText = submitButton.innerHTML;
            
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processing...
            `;

            try {
                const response = await fetch('<?php echo e(route("api.investments.store")); ?>', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        wallet_id: formData.get('wallet_id'),
                        pool_id: formData.get('pool_id'),
                        amount: parseFloat(formData.get('amount'))
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Investment failed');
                }

                // Show success message
                const notification = document.createElement('div');
                notification.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-md shadow-lg';
                notification.textContent = 'Investment successful!';
                document.body.appendChild(notification);
                
                // Close modal and refresh page after a short delay
                setTimeout(() => {
                    closeInvestModal();
                    window.location.reload();
                }, 1500);

                // Remove notification
                setTimeout(() => {
                    notification.remove();
                }, 3000);

            } catch (error) {
                console.error('Error:', error);
                alert(error.message || 'An error occurred. Please try again.');
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });

        // Close modal when clicking outside
        document.getElementById('investModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeInvestModal();
            }
        }); 
    </script>
    <?php $__env->stopPush(); ?>
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