<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Mail::raw('Test email from Laravel at ' . date('Y-m-d H:i:s'), function($message) {
        $message->to('moeezahmed015@gmail.com')
                ->subject('Test Email - Invitation System');
    });
    
    echo "✅ Email sent successfully!\n";
} catch (\Exception $e) {
    echo "❌ Error sending email: " . $e->getMessage() . "\n";
}
