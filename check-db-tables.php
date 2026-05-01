<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DATABASE TABLES CHECK ===\n\n";

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$key = 'Tables_in_' . $dbName;

foreach ($tables as $table) {
    $tableName = $table->$key;
    $count = DB::table($tableName)->count();
    echo sprintf("%-35s %10d\n", $tableName, $count);
}

echo "\n=== DONE ===\n";
