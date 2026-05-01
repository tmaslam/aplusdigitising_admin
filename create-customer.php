<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Support\PasswordManager;

echo "=== CREATE CUSTOMER ===\n\n";

// Customer data from screenshot
$customerData = [
    'user_name'     => 'demond_thompson',
    'user_email'    => 'sales@targetbahamas.com',
    'first_name'    => 'Demond',
    'last_name'     => 'Thompson',
    'company'       => 'Target Promotions',
    'company_type'  => 'Embroiders',
    'company_address' => '2205 SW 57th Ave-Dept 10-',
    'zip_code'      => '33023',
    'user_city'     => 'West Park',
    'user_country'  => 'United States of America',
    'user_phone'    => '305-414-5606',
    'contact_person' => 'Demond Thompson',
    'usre_type_id'  => 1, // customer
    'is_active'     => 1,
    'website'       => '1dollar',
    'site_id'       => 1,
    'date_added'    => now()->format('Y-m-d H:i:s'),
];

$plainPassword = '1experts';
$topupAmount   = 19.28;

echo "Creating customer: {$customerData['first_name']} {$customerData['last_name']}\n";
echo "Email: {$customerData['user_email']}\n";
echo "TopUp Balance: \${$topupAmount}\n\n";

// Check if customer already exists
$existing = DB::table('users')->where('user_email', $customerData['user_email'])->first();
if ($existing) {
    echo "WARNING: Customer with email {$customerData['user_email']} already exists (ID: {$existing->user_id})\n";
    echo "Updating existing account...\n";
    $customerId = $existing->user_id;
} else {
    $customerId = null;
}

// Build password payload
$passwordPayload = PasswordManager::payload($plainPassword);

// Build full insert/update array with all required columns
$insertData = array_merge($customerData, $passwordPayload, [
    'security_key'    => Str::random(40),
    'alternate_email' => '',
    'user_fax'        => '',
    'normal_fee'      => 1.00,
    'middle_fee'      => 1.50,
    'urgent_fee'      => 1.50,
    'super_fee'       => 1.50,
    'payment_terms'   => 7,
    'max_num_stiches' => 0,
    'customer_approval_limit' => 25.00,
    'single_approval_limit'   => 15.00,
    'customer_pending_order_limit' => 3,
    'userip_addrs'    => '',
    'digitzing_format' => '',
    'vertor_format'    => '',
    'topup'            => '',
    'exist_customer'   => '0',
    'user_term'        => '',
    'package_type'     => '',
    'real_user'        => '',
    'ref_code'         => '',
    'ref_code_other'   => '',
    'register_by'      => '',
]);

if ($customerId) {
    DB::table('users')->where('user_id', $customerId)->update($insertData);
    echo "Updated existing customer ID: {$customerId}\n";
} else {
    $customerId = DB::table('users')->insertGetId($insertData);
    echo "Created new customer ID: {$customerId}\n";
}

// Add balance via customer_credit_ledger (the proper way the app tracks balance)
if ($topupAmount > 0 && Schema::hasTable('customer_credit_ledger')) {
    $ledgerData = [
        'user_id'      => $customerId,
        'website'      => '1dollar',
        'entry_type'   => 'topup',
        'amount'       => $topupAmount,
        'reference_no' => 'migration:' . $customerId . ':' . time(),
        'notes'        => 'Balance migrated from old website',
        'created_by'   => 'admin',
        'date_added'   => now()->format('Y-m-d H:i:s'),
        'end_date'     => null,
        'deleted_by'   => null,
    ];

    if (Schema::hasColumn('customer_credit_ledger', 'site_id')) {
        $ledgerData['site_id'] = 1;
    }

    DB::table('customer_credit_ledger')->insert($ledgerData);
    echo "Added \${$topupAmount} to customer_credit_ledger.\n";
}

// Also record in customer_topups for audit trail
if ($topupAmount > 0 && Schema::hasTable('customer_topups')) {
    $topupData = [
        'user_id'    => $customerId,
        'site_id'    => 1,
        'website'    => '1dollar',
        'amount'     => $topupAmount,
        'status'     => 'completed',
        'completed_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ];

    if (Schema::hasColumn('customer_topups', 'plan_option')) {
        $topupData['plan_option'] = null;
    }

    DB::table('customer_topups')->insert($topupData);
    echo "Added \${$topupAmount} to customer_topups.\n";
}

echo "\n=== CUSTOMER CREATED ===\n";
echo "ID: {$customerId}\n";
echo "Login: {$customerData['user_email']}\n";
echo "Password: {$plainPassword}\n";
echo "Balance: \${$topupAmount}\n";
echo "\nTest login at: https://aplusdigitising.com/login\n";
