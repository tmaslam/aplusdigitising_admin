<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$siteId = 1;
$now = now();

// 1. Fix Embroidery profiles: same rate $0.80, add flat_upcharge to config
DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('work_type', 'digitizing')
    ->where('turnaround_code', 'standard')
    ->where('is_active', 1)
    ->update([
        'per_thousand_rate' => 0.80,
        'minimum_charge' => 12.00,
        'config_json' => '{"flat_upcharge":0}',
        'updated_at' => $now,
    ]);

DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('work_type', 'digitizing')
    ->where('turnaround_code', 'priority')
    ->where('is_active', 1)
    ->update([
        'per_thousand_rate' => 0.80,
        'minimum_charge' => 12.00,
        'config_json' => '{"flat_upcharge":5}',
        'updated_at' => $now,
    ]);

DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('work_type', 'digitizing')
    ->where('turnaround_code', 'superrush')
    ->where('is_active', 1)
    ->update([
        'per_thousand_rate' => 0.80,
        'minimum_charge' => 12.00,
        'config_json' => '{"flat_upcharge":10}',
        'updated_at' => $now,
    ]);

// 2. Fix Vector profiles: config must have BOTH flat_upcharge AND minimum_hours
DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('work_type', 'vector')
    ->where('turnaround_code', 'standard')
    ->where('is_active', 1)
    ->update([
        'overage_rate' => 6.00,
        'minimum_charge' => 12.00,
        'config_json' => '{"flat_upcharge":0,"minimum_hours":2}',
        'updated_at' => $now,
    ]);

DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('work_type', 'vector')
    ->where('turnaround_code', 'priority')
    ->where('is_active', 1)
    ->update([
        'overage_rate' => 6.00,
        'minimum_charge' => 12.00,
        'config_json' => '{"flat_upcharge":5,"minimum_hours":2}',
        'updated_at' => $now,
    ]);

DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('work_type', 'vector')
    ->where('turnaround_code', 'superrush')
    ->where('is_active', 1)
    ->update([
        'overage_rate' => 6.00,
        'minimum_charge' => 12.00,
        'config_json' => '{"flat_upcharge":10,"minimum_hours":2}',
        'updated_at' => $now,
    ]);

// Show result
echo "=== FIXED PRICING PROFILES ===\n";
$rows = DB::table('site_pricing_profiles')->where('site_id', $siteId)->where('is_active', 1)->orderBy('id')->get();
foreach ($rows as $r) {
    $cfg = json_decode($r->config_json ?? '{}', true);
    $upcharge = $cfg['flat_upcharge'] ?? 0;
    $minHours = $cfg['minimum_hours'] ?? 0;
    $rate = $r->per_thousand_rate ? "\${$r->per_thousand_rate}/1k stitches" : "\${$r->overage_rate}/hour";
    echo "  ID {$r->id}: {$r->profile_name} | rate=$rate | min=\${$r->minimum_charge} | upcharge=+$" . number_format($upcharge, 2);
    if ($minHours > 0) echo " | min_hours={$minHours}";
    echo "\n";
}

echo "\nDone! Clear cache and test the forms.\n";
