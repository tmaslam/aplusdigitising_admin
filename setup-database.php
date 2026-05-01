<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== SETUP DATABASE ===\n\n";

echo "1. Running migrations...\n";
Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();

echo "\n2. Adding site configuration...\n";

// Add primary site
$siteId = DB::table('sites')->insertGetId([
    'legacy_key' => '1dollar',
    'slug' => 'aplus',
    'name' => 'A Plus Digitizing',
    'brand_name' => 'A Plus Digitizing',
    'primary_domain' => 'aplusdigitising.com',
    'website_address' => 'https://aplusdigitising.com',
    'support_email' => 'support@aplusdigitising.com',
    'from_email' => 'support@aplusdigitising.com',
    'is_active' => 1,
    'is_primary' => 1,
    'settings_json' => json_encode([
        'logo' => '/images/logo.png',
        'favicon' => '/images/favicon.ico',
        'primary_color' => '#F26522',
        'primary_dark_color' => '#D94E0F',
        'accent_color' => '#2563EB',
    ]),
]);

echo "Created site ID: {$siteId}\n";

// Add domain
DB::table('site_domains')->insert([
    'site_id' => $siteId,
    'host' => 'aplusdigitising.com',
    'is_active' => 1,
    'is_primary' => 1,
]);

DB::table('site_domains')->insert([
    'site_id' => $siteId,
    'host' => 'user.aplusdigitising.com',
    'is_active' => 1,
    'is_primary' => 0,
]);

DB::table('site_domains')->insert([
    'site_id' => $siteId,
    'host' => 'aplusdigitizing.com',
    'is_active' => 1,
    'is_primary' => 0,
]);

echo "Added domains\n";

// Add pricing profiles
$profiles = [
    ['site_id' => $siteId, 'work_type' => 'digitizing', 'turnaround_code' => 'standard', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 0])],
    ['site_id' => $siteId, 'work_type' => 'digitizing', 'turnaround_code' => 'priority', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 5])],
    ['site_id' => $siteId, 'work_type' => 'digitizing', 'turnaround_code' => 'superrush', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 10])],
    ['site_id' => $siteId, 'work_type' => 'vector', 'turnaround_code' => 'standard', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 6.00, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 0, 'minimum_hours' => 2])],
    ['site_id' => $siteId, 'work_type' => 'vector', 'turnaround_code' => 'priority', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 6.00, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 5, 'minimum_hours' => 2])],
    ['site_id' => $siteId, 'work_type' => 'vector', 'turnaround_code' => 'superrush', 'pricing_mode' => 'per_unit', 'per_thousand_rate' => 6.00, 'minimum_charge' => 12.00, 'config_json' => json_encode(['flat_upcharge' => 10, 'minimum_hours' => 2])],
];

foreach ($profiles as $profile) {
    $profile['is_active'] = 1;
    $profile['created_at'] = now();
    $profile['updated_at'] = now();
    DB::table('site_pricing_profiles')->insert($profile);
}

echo "Added pricing profiles\n";

echo "\n3. Clearing caches...\n";
Artisan::call('config:clear');
Artisan::call('cache:clear');
Artisan::call('view:clear');

echo "\n=== DATABASE SETUP COMPLETE ===\n";
echo "Site ID: {$siteId}\n";
echo "\nNow run: php create-customer.php\n";
