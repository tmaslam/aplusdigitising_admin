<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$siteId = 1; // aplusdigitizing.com

// 1. Show current profiles
echo "=== CURRENT PRICING PROFILES FOR SITE_ID=$siteId ===\n";
$current = DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('is_active', 1)
    ->orderBy('id')
    ->get();

foreach ($current as $p) {
    echo "  ID {$p->id}: {$p->profile_name} | work={$p->work_type} | turn={$p->turnaround_code} | rate={$p->per_thousand_rate} | min={$p->minimum_charge} | mode={$p->pricing_mode}\n";
}

// 2. Deactivate ALL current profiles for this site
echo "\n=== DEACTIVATING OLD PROFILES ===\n";
$deactivated = DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('is_active', 1)
    ->update(['is_active' => 0, 'updated_at' => now()]);
echo "Deactivated $deactivated profile(s).\n";

// 3. Create new embroidery digitizing profiles
$now = now();

// Embroidery Digitizing - Standard (24h): $0.80/1000 stitches, min $12.00
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId,
    'profile_name' => 'Embroidery Standard',
    'work_type' => 'digitizing',
    'turnaround_code' => 'standard',
    'pricing_mode' => 'customer_rate',
    'per_thousand_rate' => 0.80,
    'minimum_charge' => 12.00,
    'included_units' => 15000,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);

// Embroidery Digitizing - Priority (12h): Standard + $5.00 = $5.80/1000 stitches
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId,
    'profile_name' => 'Embroidery Priority',
    'work_type' => 'digitizing',
    'turnaround_code' => 'priority',
    'pricing_mode' => 'customer_rate',
    'per_thousand_rate' => 5.80,
    'minimum_charge' => 12.00,
    'included_units' => 15000,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);

// Embroidery Digitizing - Super Rush (6h): Standard + $10.00 = $10.80/1000 stitches
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId,
    'profile_name' => 'Embroidery Super Rush',
    'work_type' => 'digitizing',
    'turnaround_code' => 'superrush',
    'pricing_mode' => 'customer_rate',
    'per_thousand_rate' => 10.80,
    'minimum_charge' => 12.00,
    'included_units' => 15000,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);

// Vector Art - Standard (24h): $6.00/hour, min $12.00
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId,
    'profile_name' => 'Vector Standard',
    'work_type' => 'vector',
    'turnaround_code' => 'standard',
    'pricing_mode' => 'hourly',
    'overage_rate' => 6.00,
    'minimum_charge' => 12.00,
    'included_units' => 1,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);

// Vector Art - Priority (12h): Standard + $5.00 = $11.00/hour
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId,
    'profile_name' => 'Vector Priority',
    'work_type' => 'vector',
    'turnaround_code' => 'priority',
    'pricing_mode' => 'hourly',
    'overage_rate' => 11.00,
    'minimum_charge' => 12.00,
    'included_units' => 1,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);

// Vector Art - Super Rush (6h): Standard + $10.00 = $16.00/hour
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId,
    'profile_name' => 'Vector Super Rush',
    'work_type' => 'vector',
    'turnaround_code' => 'superrush',
    'pricing_mode' => 'hourly',
    'overage_rate' => 16.00,
    'minimum_charge' => 12.00,
    'included_units' => 1,
    'is_active' => 1,
    'created_at' => $now,
    'updated_at' => $now,
]);

// 4. Show final state
echo "\n=== NEW ACTIVE PRICING PROFILES FOR SITE_ID=$siteId ===\n";
$new = DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('is_active', 1)
    ->orderBy('id')
    ->get();

foreach ($new as $p) {
    $rate = $p->per_thousand_rate ? "\${$p->per_thousand_rate}/1k stitches" : "\${$p->overage_rate}/hour";
    echo "  ID {$p->id}: {$p->profile_name} | work={$p->work_type} | turn={$p->turnaround_code} | rate=$rate | min=\${$p->minimum_charge} | mode={$p->pricing_mode}\n";
}

echo "\n=== DONE ===\n";
echo "Old profiles deactivated (is_active=0) but preserved in database.\n";
echo "New profiles are active and will be used immediately.\n";
