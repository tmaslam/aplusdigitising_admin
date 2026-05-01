<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$siteId = 1;

// Deactivate ALL current profiles for this site
echo "Deactivating old profiles...\n";
DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('is_active', 1)
    ->update(['is_active' => 0, 'updated_at' => now()]);

$now = now();

// EMBROIDERY - all same rate $0.80/1000 stitches, min $12.00
// Upcharges: Standard=$0, Priority=$5, Super Rush=$10 (stored in config_json)
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId, 'profile_name' => 'Embroidery Standard', 'work_type' => 'digitizing', 'turnaround_code' => 'standard',
    'pricing_mode' => 'customer_rate', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'included_units' => 15000,
    'config_json' => '{"flat_upcharge":0}', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
]);
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId, 'profile_name' => 'Embroidery Priority', 'work_type' => 'digitizing', 'turnaround_code' => 'priority',
    'pricing_mode' => 'customer_rate', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'included_units' => 15000,
    'config_json' => '{"flat_upcharge":5}', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
]);
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId, 'profile_name' => 'Embroidery Super Rush', 'work_type' => 'digitizing', 'turnaround_code' => 'superrush',
    'pricing_mode' => 'customer_rate', 'per_thousand_rate' => 0.80, 'minimum_charge' => 12.00, 'included_units' => 15000,
    'config_json' => '{"flat_upcharge":10}', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
]);

// VECTOR - all same rate $6.00/hour, min $12.00
// Upcharges: Standard=$0, Priority=$5, Super Rush=$10
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId, 'profile_name' => 'Vector Standard', 'work_type' => 'vector', 'turnaround_code' => 'standard',
    'pricing_mode' => 'hourly', 'overage_rate' => 6.00, 'minimum_charge' => 12.00, 'included_units' => 1,
    'config_json' => '{"flat_upcharge":0}', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
]);
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId, 'profile_name' => 'Vector Priority', 'work_type' => 'vector', 'turnaround_code' => 'priority',
    'pricing_mode' => 'hourly', 'overage_rate' => 6.00, 'minimum_charge' => 12.00, 'included_units' => 1,
    'config_json' => '{"flat_upcharge":5}', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
]);
DB::table('site_pricing_profiles')->insert([
    'site_id' => $siteId, 'profile_name' => 'Vector Super Rush', 'work_type' => 'vector', 'turnaround_code' => 'superrush',
    'pricing_mode' => 'hourly', 'overage_rate' => 6.00, 'minimum_charge' => 12.00, 'included_units' => 1,
    'config_json' => '{"flat_upcharge":10}', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now,
]);

echo "New profiles created with same base rates + flat upcharges in config_json.\n";

// Show final state
$new = DB::table('site_pricing_profiles')->where('site_id', $siteId)->where('is_active', 1)->orderBy('id')->get();
foreach ($new as $p) {
    $cfg = json_decode($p->config_json ?? '{}', true);
    $upcharge = $cfg['flat_upcharge'] ?? 0;
    $rate = $p->per_thousand_rate ? "\${$p->per_thousand_rate}/1k stitches" : "\${$p->overage_rate}/hour";
    echo "  {$p->profile_name}: rate=$rate | min=\${$p->minimum_charge} | upcharge=+$" . number_format($upcharge, 2) . "\n";
}
