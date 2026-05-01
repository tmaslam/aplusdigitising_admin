<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$siteId = 1;

// Update vector profiles to add minimum_hours: 2
DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('work_type', 'vector')
    ->where('is_active', 1)
    ->update([
        'config_json' => DB::raw('JSON_SET(COALESCE(config_json, \'{}\'), \'$.minimum_hours\', 2)'),
        'updated_at' => now(),
    ]);

echo "Vector profiles updated with minimum_hours=2.\n";

// Show current state
$profiles = DB::table('site_pricing_profiles')
    ->where('site_id', $siteId)
    ->where('is_active', 1)
    ->orderBy('id')
    ->get();

foreach ($profiles as $p) {
    $cfg = json_decode($p->config_json ?? '{}', true);
    $upcharge = $cfg['flat_upcharge'] ?? 0;
    $minHours = $cfg['minimum_hours'] ?? 0;
    $rate = $p->per_thousand_rate ? "\${$p->per_thousand_rate}/1k stitches" : "\${$p->overage_rate}/hour";
    echo "  {$p->profile_name}: rate=$rate | min=\${$p->minimum_charge} | upcharge=+$" . number_format($upcharge, 2);
    if ($minHours > 0) {
        echo " | min_hours={$minHours}";
    }
    echo "\n";
}
