<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check if users table has rate override columns
$cols = DB::select("SHOW COLUMNS FROM users LIKE '%fee%'");
echo "Fee columns in users table:\n";
foreach ($cols as $c) {
    echo "  {$c->Field} ({$c->Type})\n";
}

// Check how many customers have rate overrides set
$count = DB::table('users')
    ->where('usre_type_id', 1)
    ->where(function ($q) {
        $q->where('normal_fee', '>', 0)
          ->orWhere('middle_fee', '>', 0)
          ->orWhere('urgent_fee', '>', 0)
          ->orWhere('super_fee', '>', 0);
    })
    ->count();

echo "\nCustomers with rate overrides: $count\n";

// Show a few examples
$examples = DB::table('users')
    ->where('usre_type_id', 1)
    ->where(function ($q) {
        $q->where('normal_fee', '>', 0)
          ->orWhere('middle_fee', '>', 0)
          ->orWhere('urgent_fee', '>', 0)
          ->orWhere('super_fee', '>', 0);
    })
    ->select('user_id', 'user_name', 'normal_fee', 'middle_fee', 'urgent_fee', 'super_fee')
    ->limit(5)
    ->get();

foreach ($examples as $e) {
    echo "  {$e->user_name}: normal={$e->normal_fee}, middle={$e->middle_fee}, urgent={$e->urgent_fee}, super={$e->super_fee}\n";
}
