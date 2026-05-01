<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== CLEAR REPORTS AND HISTORY ===\n";
echo "Type 'CLEAR-ALL' to confirm: ";

$handle = fopen('php://stdin', 'r');
$confirm = trim(fgets($handle));

if ($confirm !== 'CLEAR-ALL') {
    echo "Aborted.\n";
    exit(1);
}

// Payment due / received reports
if (Schema::hasTable('billing')) {
    $count = DB::table('billing')->count();
    DB::table('billing')->delete();
    echo "Cleared {$count} billing record(s)\n";
}

if (Schema::hasTable('payment_transactions')) {
    $count = DB::table('payment_transactions')->count();
    DB::table('payment_transactions')->delete();
    echo "Cleared {$count} payment transaction(s)\n";
}

if (Schema::hasTable('payment_transaction_items')) {
    $count = DB::table('payment_transaction_items')->count();
    DB::table('payment_transaction_items')->delete();
    echo "Cleared {$count} payment transaction item(s)\n";
}

if (Schema::hasTable('customerpayments')) {
    $count = DB::table('customerpayments')->count();
    DB::table('customerpayments')->delete();
    echo "Cleared {$count} customer payment(s)\n";
}

// Login history
if (Schema::hasTable('login_history')) {
    $count = DB::table('login_history')->count();
    DB::table('login_history')->delete();
    echo "Cleared {$count} login history record(s)\n";
}

// Security events
if (Schema::hasTable('security_audit_events')) {
    $count = DB::table('security_audit_events')->count();
    DB::table('security_audit_events')->delete();
    echo "Cleared {$count} security audit event(s)\n";
}

// Block IP list (optional)
if (Schema::hasTable('block_ip')) {
    $count = DB::table('block_ip')->count();
    DB::table('block_ip')->delete();
    echo "Cleared {$count} blocked IP record(s)\n";
}

echo "\n=== DONE ===\n";
echo "Run: php artisan cache:clear\n";
