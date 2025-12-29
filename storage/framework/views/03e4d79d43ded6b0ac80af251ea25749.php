<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lotrix - Professional Lat Management System</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <h1 class="text-2xl font-bold text-purple-600">Lotrix</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->check()): ?>
                        <a href="<?php echo e(url('/admin/dashboard')); ?>" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                            Dashboard
                        </a>
                    <?php else: ?>
                        <a href="<?php echo e(route('login')); ?>" class="text-gray-700 hover:text-purple-600 px-3 py-2 rounded-md text-sm font-medium">
                            Login
                        </a>
                        <a href="<?php echo e(route('filament.admin.auth.register')); ?>" class="bg-purple-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-purple-700">
                            Get Started
                        </a>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section with Video Background -->
    <section class="relative bg-gradient-to-r from-purple-600 to-purple-800 text-white py-20 overflow-hidden">
        <!-- Video Background -->
        <div class="absolute inset-0 z-0">
            <video autoplay muted loop playsinline class="w-full h-full object-cover opacity-30">
                <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl md:text-5xl font-bold mb-6">
                Professional Cloths Production Management System
            </h2>
            <p class="text-xl md:text-2xl mb-8 max-w-3xl mx-auto">
                Streamline your manufacturing business with real-time cost tracking, profit calculations, and production management.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="<?php echo e(route('filament.admin.auth.register')); ?>" class="bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                    Start Free Trial
                </a>
                <a href="<?php echo e(route('login')); ?>" class="border-2 border-white text-white px-8 py-3 rounded-lg font-semibold hover:bg-white hover:text-purple-600 transition">
                    Login to Dashboard
                </a>
            </div>
        </div>
    </section>

    <!-- Dashboard Preview Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    See Your Production at a Glance
                </h3>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Intuitive dashboard designed for cloth production management
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gray-800 px-4 py-3 flex items-center space-x-2">
                    <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                    <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                    <span class="text-gray-400 text-sm ml-4">Lotrix Dashboard</span>
                </div>
                <img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=1200&h=600&fit=crop" 
                     alt="Dashboard Preview" 
                     class="w-full h-auto">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Everything You Need to Manage Your Business
                </h3>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Powerful features designed specifically for manufacturing businesses
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-purple-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Real-time Cost Tracking</h4>
                    <p class="text-gray-600">Monitor materials, labor, and expenses in real-time with our intuitive tracking system.</p>
                </div>

                <!-- Feature 2 -->
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-green-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Profit Calculations</h4>
                    <p class="text-gray-600">Automatic profit margin and pricing calculations to help you make informed business decisions.</p>
                </div>

                <!-- Feature 3 -->
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-blue-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Material Management</h4>
                    <p class="text-gray-600">Track inventory, manage suppliers, and optimize material usage for maximum efficiency.</p>
                </div>

                <!-- Feature 4 -->
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-yellow-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Production Tracking</h4>
                    <p class="text-gray-600">Monitor production progress, track pieces, and manage workflow efficiently.</p>
                </div>

                <!-- Feature 5 -->
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-red-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">PDF Reports</h4>
                    <p class="text-gray-600">Generate professional PDF reports for costs, profits, and production analytics.</p>
                </div>

                <!-- Feature 6 -->
                <div class="bg-gray-50 p-8 rounded-xl hover:shadow-lg transition">
                    <div class="bg-indigo-100 w-12 h-12 rounded-lg flex items-center justify-center mb-6">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h4 class="text-xl font-semibold text-gray-900 mb-3">Multi-user Support</h4>
                    <p class="text-gray-600">Collaborate with your team using role-based access and permissions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Video Demo Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    See Lotrix in Action
                </h3>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    Watch how our system transforms cloth production management
                </p>
            </div>

            <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-w-4xl mx-auto">
                <div class="relative">
                    <video controls class="w-full" poster="https://images.unsplash.com/photo-1600880292203-757bb62b4baf?w=1200&h=600&fit=crop">
                        <source src="https://www.w3schools.com/html/mov_bbb.mp4" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="bg-white bg-opacity-90 rounded-full p-6">
                            <svg class="w-12 h-12 text-purple-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Production Process Gallery -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h3 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Complete Production Workflow
                </h3>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                    From raw materials to finished products - track every step
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="text-center">
                    <img src="https://images.unsplash.com/photo-1528459801416-a9e53bbf4e35?w=400&h=300&fit=crop" 
                         alt="Raw Materials" 
                         class="w-full h-48 object-cover rounded-lg mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Raw Materials</h4>
                    <p class="text-gray-600">Track fabric inventory and manage suppliers efficiently</p>
                </div>
                <div class="text-center">
                    <img src="https://images.unsplash.com/photo-1580927752452-23d9ec7f4423?w=400&h=300&fit=crop" 
                         alt="Production Process" 
                         class="w-full h-48 object-cover rounded-lg mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Production Process</h4>
                    <p class="text-gray-600">Monitor cutting, stitching, and quality control stages</p>
                </div>
                <div class="text-center">
                    <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?w=400&h=300&fit=crop" 
                         alt="Finished Products" 
                         class="w-full h-48 object-cover rounded-lg mb-4">
                    <h4 class="text-lg font-semibold text-gray-900 mb-2">Finished Products</h4>
                    <p class="text-gray-600">Manage inventory and track orders from completion to delivery</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-purple-600 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h3 class="text-3xl font-bold text-white mb-4">
                Ready to streamline your manufacturing business?
            </h3>
            <p class="text-xl text-purple-100 mb-8 max-w-2xl mx-auto">
                Join thousands of businesses using Lotrix to manage their production efficiently.
            </p>
            <a href="<?php echo e(route('filament.admin.auth.register')); ?>" class="bg-white text-purple-600 px-8 py-3 rounded-lg font-semibold hover:bg-gray-100 transition">
                Get Started Today
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-300 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; 2024 Lotrix. Professional Lat Management System.</p>
        </div>
    </footer>

</body>
</html>
<?php /**PATH C:\xampp\htdocs\stitching-fund\resources\views/welcome.blade.php ENDPATH**/ ?>