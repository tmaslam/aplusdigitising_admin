<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Support\PasswordManager;

$username = 'superadminAP';
$password = 'P@cistan1!@';
$email = 'tmaslam@gmail.com';

// Check if user already exists
$existing = DB::table('users')
    ->where('user_name', $username)
    ->where('usre_type_id', 3)
    ->first();

if ($existing) {
    echo "Admin user '{$username}' already exists (user_id: {$existing->user_id}).\n";
    exit(0);
}

$payload = PasswordManager::payload($password);

$userId = DB::table('users')->insertGetId([
    'user_name' => $username,
    'first_name' => 'Super',
    'last_name' => 'Admin',
    'user_email' => $email,
    'usre_type_id' => 3, // TYPE_ADMIN
    'is_active' => 1,
    'site_id' => 1,
    'website' => '1dollar',
    'user_password' => $payload['user_password'] ?? '',
    'password_hash' => $payload['password_hash'] ?? null,
    'password_migrated_at' => $payload['password_migrated_at'] ?? null,
    'date_added' => now()->format('Y-m-d H:i:s'),
    'normal_fee' => 1.00,
    'middle_fee' => 1.50,
    'urgent_fee' => 1.50,
    'super_fee' => 1.50,
    'payment_terms' => 7,
    'max_num_stiches' => 0,
    'customer_approval_limit' => 25.00,
    'single_approval_limit' => 15.00,
    'customer_pending_order_limit' => 3,
    'userip_addrs' => '',
    'digitzing_format' => '',
    'vertor_format' => '',
    'topup' => '',
    'exist_customer' => '0',
    'user_term' => '',
    'package_type' => '',
    'real_user' => '',
    'ref_code' => '',
    'ref_code_other' => '',
    'register_by' => '',
]);

echo "Admin user created successfully!\n";
echo "User ID: {$userId}\n";
echo "Username: {$username}\n";
echo "Email: {$email}\n";
echo "Password: [hidden]\n";
echo "\nLogin at: https://user.aplusdigitising.com/v\n";
