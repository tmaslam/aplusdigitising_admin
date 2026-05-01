<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Show how many customers have overrides before
echo "=== BEFORE ===\n";
$before = DB::table('users')
    ->where('usre_type_id', 1)
    ->where(function ($q) {
        $q->where('normal_fee', '>', 0)
          ->orWhere('middle_fee', '>', 0)
          ->orWhere('urgent_fee', '>', 0)
          ->orWhere('super_fee', '>', 0);
    })
    ->count();
echo "Customers with rate overrides: $before\n";

// Clear ALL customer rate overrides
DB::table('users')
    ->where('usre_type_id', 1)
    ->update([
        'normal_fee' => 0.00,
        'middle_fee' => 0.00,
        'urgent_fee' => 0.00,
        'super_fee' => 0.00,
    ]);

// Show after
echo "\n=== AFTER ===\n";
$after = DB::table('users')
    ->where('usre_type_id', 1)
    ->where(function ($q) {
        $q->where('normal_fee', '>', 0)
          ->orWhere('middle_fee', '>', 0)
          ->orWhere('urgent_fee', '>', 0)
          ->orWhere('super_fee', '>', 0);
    })
    ->count();
echo "Customers with rate overrides: $after\n";

echo "\n=== DONE ===\n";
echo "All customer rate overrides cleared.\n";
echo "Everyone now uses the new site pricing profiles.\n";
