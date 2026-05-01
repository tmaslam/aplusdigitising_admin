<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== DELETE ALL REMAINING ORDERS ===\n";
echo "Type 'DELETE-ALL' to confirm: ";

$handle = fopen('php://stdin', 'r');
$confirm = trim(fgets($handle));

if ($confirm !== 'DELETE-ALL') {
    echo "Aborted.\n";
    exit(1);
}

$customerIds = DB::table('users')->where('usre_type_id', 1)->pluck('user_id')->toArray();
$remainingOrderIds = DB::table('orders')
    ->whereNotIn('user_id', $customerIds)
    ->pluck('order_id')
    ->toArray();

echo "Found " . count($remainingOrderIds) . " remaining order(s) to delete.\n";

if (empty($remainingOrderIds)) {
    echo "Nothing to delete.\n";
    exit(0);
}

DB::beginTransaction();

try {
    // Delete related data
    $deleted = DB::table('attach_files')->whereIn('order_id', $remainingOrderIds)->delete();
    echo "Deleted {$deleted} attachment(s).\n";

    $deleted = DB::table('comments')->whereIn('order_id', $remainingOrderIds)->delete();
    echo "Deleted {$deleted} comment(s).\n";

    if (Schema::hasTable('quote_negotiations')) {
        $deleted = DB::table('quote_negotiations')->whereIn('order_id', $remainingOrderIds)->delete();
        echo "Deleted {$deleted} quote negotiation(s).\n";
    }

    if (Schema::hasTable('billing')) {
        $deleted = DB::table('billing')->whereIn('order_id', $remainingOrderIds)->delete();
        echo "Deleted {$deleted} billing record(s).\n";
    }

    if (Schema::hasTable('advancepayment')) {
        $deleted = DB::table('advancepayment')->whereIn('order_id', $remainingOrderIds)->delete();
        echo "Deleted {$deleted} advance payment(s).\n";
    }

    if (Schema::hasTable('order_workflow_meta')) {
        $deleted = DB::table('order_workflow_meta')->whereIn('order_id', $remainingOrderIds)->delete();
        echo "Deleted {$deleted} workflow meta record(s).\n";
    }

    // Delete orders
    $deleted = DB::table('orders')->whereIn('order_id', $remainingOrderIds)->delete();
    echo "Deleted {$deleted} order(s).\n";

    DB::commit();
    echo "\n=== COMPLETE ===\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
