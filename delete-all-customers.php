<?php

/**
 * DELETE ALL CUSTOMERS AND THEIR DATA
 * Run: cd /home/apluihej/user.aplusdigitising.com && php delete-all-customers.php
 *
 * WARNING: This is IRREVERSIBLE. All customer accounts, orders,
 * comments, billing records, and uploaded files will be permanently deleted.
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Support\SharedUploads;

echo "=== CUSTOMER & ORDER DATA DELETION ===\n";
echo "WARNING: This will permanently delete ALL customers and their data.\n";
echo "Type 'DELETE-ALL' to confirm: ";

$handle = fopen('php://stdin', 'r');
$confirm = trim(fgets($handle));

if ($confirm !== 'DELETE-ALL') {
    echo "Aborted. Confirmation not provided.\n";
    exit(1);
}

DB::beginTransaction();

try {
    // 1. Get all customer IDs (usre_type_id = 1)
    $customerIds = DB::table('users')
        ->where('usre_type_id', 1)
        ->pluck('user_id')
        ->toArray();

    echo "Found " . count($customerIds) . " customer(s) to delete.\n";

    if (empty($customerIds)) {
        echo "No customers found. Nothing to delete.\n";
        DB::rollBack();
        exit(0);
    }

    // 2. Get all order IDs for these customers
    $orderIds = DB::table('orders')
        ->whereIn('user_id', $customerIds)
        ->pluck('order_id')
        ->toArray();

    echo "Found " . count($orderIds) . " order(s) to delete.\n";

    // 3. Delete attachment files from disk
    $attachments = DB::table('attach_files')
        ->whereIn('order_id', $orderIds)
        ->get(['id', 'file_name_with_date', 'file_name_with_order_id']);

    $filesDeleted = 0;
    foreach ($attachments as $attachment) {
        $paths = [
            SharedUploads::path('order/' . ($attachment->file_name_with_date ?? '')),
            SharedUploads::path('order/' . ($attachment->file_name_with_order_id ?? '')),
        ];
        foreach ($paths as $path) {
            if ($path && is_file($path)) {
                @unlink($path);
                $filesDeleted++;
            }
        }
    }
    echo "Deleted {$filesDeleted} file(s) from disk.\n";

    // 4. Delete database records (child tables first)

    // Attachments metadata
    $deleted = DB::table('attach_files')->whereIn('order_id', $orderIds)->delete();
    echo "Deleted {$deleted} attachment record(s).\n";

    // Order comments
    $deleted = DB::table('comments')->whereIn('order_id', $orderIds)->delete();
    echo "Deleted {$deleted} order comment(s).\n";

    // Team comments
    if (Schema::hasTable('team_comments')) {
        $deleted = DB::table('team_comments')->whereIn('order_id', $orderIds)->delete();
        echo "Deleted {$deleted} team comment(s).\n";
    }

    // Quick quote comments
    if (Schema::hasTable('quick_order_comments')) {
        $deleted = DB::table('quick_order_comments')->whereIn('order_id', $orderIds)->delete();
        echo "Deleted {$deleted} quick quote comment(s).\n";
    }

    // Quote negotiations
    if (Schema::hasTable('quote_negotiations')) {
        $deleted = DB::table('quote_negotiations')->whereIn('order_id', $orderIds)->delete();
        echo "Deleted {$deleted} quote negotiation(s).\n";
    }

    // Billing
    if (Schema::hasTable('billing')) {
        $deleted = DB::table('billing')->whereIn('order_id', $orderIds)->delete();
        echo "Deleted {$deleted} billing record(s).\n";
    }

    // Advance payments
    if (Schema::hasTable('advancepayment')) {
        $deleted = DB::table('advancepayment')->whereIn('order_id', $orderIds)->delete();
        echo "Deleted {$deleted} advance payment record(s).\n";
    }

    // Customer topups
    if (Schema::hasTable('customer_topups')) {
        $deleted = DB::table('customer_topups')->whereIn('user_id', $customerIds)->delete();
        echo "Deleted {$deleted} customer topup record(s).\n";
    }

    // Site promotion claims
    if (Schema::hasTable('site_promotion_claims')) {
        $deleted = DB::table('site_promotion_claims')->whereIn('user_id', $customerIds)->delete();
        echo "Deleted {$deleted} promotion claim(s).\n";
    }

    // Customer approval queue
    if (Schema::hasTable('customer_approval_queue')) {
        $deleted = DB::table('customer_approval_queue')->whereIn('user_id', $customerIds)->delete();
        echo "Deleted {$deleted} approval queue record(s).\n";
    }

    // Password reset tokens
    if (Schema::hasTable('customer_password_reset_tokens')) {
        $deleted = DB::table('customer_password_reset_tokens')->whereIn('user_id', $customerIds)->delete();
        echo "Deleted {$deleted} password reset token(s).\n";
    }

    if (Schema::hasTable('admin_password_reset_tokens')) {
        $deleted = DB::table('admin_password_reset_tokens')->whereIn('user_id', $customerIds)->delete();
        echo "Deleted {$deleted} admin password reset token(s).\n";
    }

    // Remember login tokens
    if (Schema::hasTable('customer_remember_logins')) {
        $deleted = DB::table('customer_remember_logins')->whereIn('user_id', $customerIds)->delete();
        echo "Deleted {$deleted} remember login token(s).\n";
    }

    // Login history / security audit related to customers
    if (Schema::hasTable('login_history')) {
        $deleted = DB::table('login_history')->whereIn('user_id', $customerIds)->delete();
        echo "Deleted {$deleted} login history record(s).\n";
    }

    if (Schema::hasTable('security_audit_events')) {
        $deleted = DB::table('security_audit_events')
            ->whereIn('user_id', $customerIds)
            ->orWhereIn('target_user_id', $customerIds)
            ->delete();
        echo "Deleted {$deleted} security audit record(s).\n";
    }

    // Orders
    $deleted = DB::table('orders')->whereIn('user_id', $customerIds)->delete();
    echo "Deleted {$deleted} order(s).\n";

    // Finally, delete customers
    $deleted = DB::table('users')->whereIn('user_id', $customerIds)->delete();
    echo "Deleted {$deleted} customer account(s).\n";

    DB::commit();

    echo "\n=== DELETION COMPLETE ===\n";
    echo "All customers and their data have been permanently removed.\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\nERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
