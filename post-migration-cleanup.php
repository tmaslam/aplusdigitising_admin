<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== POST-MIGRATION CLEANUP ===\n\n";

// 1. Ensure aplusdigitizing.com domain exists
$domain = DB::table('site_domains')->where('host', 'aplusdigitizing.com')->first();
if (!$domain) {
    DB::table('site_domains')->insert([
        'site_id' => 1,
        'host' => 'aplusdigitizing.com',
        'is_active' => 1,
        'is_primary' => 0,
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    echo "Added aplusdigitizing.com to site_domains\n";
} else {
    echo "aplusdigitizing.com already in site_domains\n";
}

// 2. Update site reference to use correct domain
$site = DB::table('sites')->where('id', 1)->first();
if ($site) {
    DB::table('sites')->where('id', 1)->update([
        'website_address' => 'https://aplusdigitising.com',
        'support_email' => 'support@aplusdigitising.com',
        'from_email' => 'support@aplusdigitising.com',
        'primary_domain' => 'aplusdigitising.com',
    ]);
    echo "Updated site #1 domain references\n";
}

// 3. Update all old email domain references in users table
$updated = DB::table('users')
    ->where('user_email', 'like', '%@aplusdigitizing.com')
    ->update(['user_email' => DB::raw("REPLACE(user_email, 'aplusdigitizing.com', 'aplusdigitising.com')")]);
echo "Updated {$updated} user email(s) to new domain\n";

// 4. Count migrated data
$customers = DB::table('users')->where('usre_type_id', 1)->count();
$orders = DB::table('orders')->count();
$attachments = DB::table('attach_files')->count();
$billing = DB::table('billing')->count();

// 5. Show summary
echo "\n=== MIGRATION SUMMARY ===\n";
echo "Customers: {$customers}\n";
echo "Orders: {$orders}\n";
echo "Attachments (metadata): {$attachments}\n";
echo "Billing records: {$billing}\n";

echo "\n=== DONE ===\n";
echo "Run: php artisan config:clear && php artisan cache:clear && php artisan view:clear\n";
