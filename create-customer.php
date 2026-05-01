<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "=== CREATE CUSTOMER ===\n\n";

// Customer data from old site
$customerData = [
    'user_name' => 'elizabeth_monnin',
    'user_email' => 'dream4catcher61@gmail.com',
    'user_password' => Hash::make('12Littlegirl'),
    'first_name' => 'elizabeth',
    'last_name' => 'monnin',
    'user_country' => 'United States of America',
    'user_city' => 'anna',
    'user_zip' => '45302',
    'user_phone' => '937-638-9556',
    'user_address' => '8525 hoying rd',
    'company' => 'triplemllc',
    'company_type' => 'Embroiders',
    'usre_type_id' => 1,
    'is_active' => 1,
    'website' => 'aplus',
    'date_added' => now()->format('Y-m-d H:i:s'),
    'created_at' => now(),
    'updated_at' => now(),
];

$topupAmount = 20.76;

echo "Creating customer: {$customerData['first_name']} {$customerData['last_name']}\n";
echo "Email: {$customerData['user_email']}\n";
echo "Password: 12Littlegirl (hashed)\n";
echo "TopUp Balance: \${$topupAmount}\n\n";

// Check if customer already exists
$existing = DB::table('users')->where('user_email', $customerData['user_email'])->first();
if ($existing) {
    echo "WARNING: Customer with email {$customerData['user_email']} already exists (ID: {$existing->user_id})\n";
    echo "Type 'UPDATE' to update existing account, or 'SKIP' to cancel: ";
    $handle = fopen('php://stdin', 'r');
    $confirm = trim(fgets($handle));
    if ($confirm !== 'UPDATE') {
        echo "Cancelled.\n";
        exit(0);
    }
    
    // Update existing customer
    DB::table('users')->where('user_id', $existing->user_id)->update($customerData);
    $customerId = $existing->user_id;
    echo "Updated existing customer ID: {$customerId}\n";
} else {
    // Create new customer
    $customerId = DB::table('users')->insertGetId($customerData);
    echo "Created new customer ID: {$customerId}\n";
}

// Add topup balance
if ($topupAmount > 0) {
    // Check if topup already exists for this customer
    $existingTopup = DB::table('customer_topups')
        ->where('user_id', $customerId)
        ->where('amount', $topupAmount)
        ->first();
    
    if (!$existingTopup) {
        DB::table('customer_topups')->insert([
            'user_id' => $customerId,
            'amount' => $topupAmount,
            'status' => 'completed',
            'payment_method' => 'migration',
            'reference' => 'manual_migration',
            'completed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        echo "Added \${$topupAmount} topup to customer balance.\n";
    } else {
        echo "Topup of \${$topupAmount} already exists.\n";
    }
}

// Also add to customerpayments table if needed for balance calculation
$existingPayment = DB::table('customerpayments')
    ->where('user_id', $customerId)
    ->where('amount', $topupAmount)
    ->first();

if (!$existingPayment) {
    DB::table('customerpayments')->insert([
        'user_id' => $customerId,
        'amount' => $topupAmount,
        'payment_type' => 'topup',
        'status' => 'completed',
        'description' => 'Balance migrated from old website',
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Added \${$topupAmount} to customerpayments.\n";
}

// Update any customer balance field in users table if it exists
if (Schema::hasColumn('users', 'available_balance')) {
    DB::table('users')->where('user_id', $customerId)->update([
        'available_balance' => $topupAmount,
    ]);
}

echo "\n=== CUSTOMER CREATED ===\n";
echo "ID: {$customerId}\n";
echo "Login: {$customerData['user_email']}\n";
echo "Password: 12Littlegirl\n";
echo "Balance: \${$topupAmount}\n";
echo "\nTest login at: https://user.aplusdigitising.com/login\n";
