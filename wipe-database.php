<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== WIPE DATABASE ===\n";
echo "WARNING: This will DELETE all data.\n";
echo "Type 'WIPE-ALL' to confirm: ";

$handle = fopen('php://stdin', 'r');
$confirm = trim(fgets($handle));

if ($confirm !== 'WIPE-ALL') {
    echo "Aborted.\n";
    exit(1);
}

$tablesToKeep = [
    'migrations',
    'personal_access_tokens',
    'password_reset_tokens',
    'failed_jobs',
    'cache',
    'cache_locks',
    'sessions',
    'jobs',
    'job_batches',
];

$tables = DB::select('SHOW TABLES');
$dbName = DB::getDatabaseName();
$key = 'Tables_in_' . $dbName;

echo "\nDropping all tables except system tables...\n";

foreach ($tables as $table) {
    $tableName = $table->$key;
    if (in_array($tableName, $tablesToKeep)) {
        echo "  Keeping: {$tableName}\n";
        continue;
    }
    
    Schema::dropIfExists($tableName);
    echo "  Dropped: {$tableName}\n";
}

echo "\n=== DATABASE WIPED ===\n";
echo "Ready for fresh import.\n";
echo "Run: mysql -u apluihej_aplusadmin -p apluihej_aplusadmin < vogueves_aplus.sql\n";
