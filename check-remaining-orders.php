<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$totalOrders = DB::table('orders')->count();
$customerIds = DB::table('users')->where('usre_type_id', 1)->pluck('user_id')->toArray();
$ordersWithCustomer = DB::table('orders')->whereIn('user_id', $customerIds)->count();
$ordersWithNullUser = DB::table('orders')->whereNull('user_id')->count();
$ordersWithoutCustomer = DB::table('orders')->whereNotIn('user_id', $customerIds)->whereNotNull('user_id')->count();

echo "Total orders: {$totalOrders}\n";
echo "Orders with valid customer: {$ordersWithCustomer}\n";
echo "Orders with NULL user_id: {$ordersWithNullUser}\n";
echo "Orders with non-customer user_id: {$ordersWithoutCustomer}\n\n";

if ($ordersWithoutCustomer > 0) {
    $userIds = DB::table('orders')
        ->whereNotIn('user_id', $customerIds)
        ->whereNotNull('user_id')
        ->select('user_id', DB::raw('COUNT(*) as count'))
        ->groupBy('user_id')
        ->orderByDesc('count')
        ->limit(10)
        ->get();

    echo "Top non-customer user_ids:\n";
    foreach ($userIds as $row) {
        $user = DB::table('users')->where('user_id', $row->user_id)->first();
        $type = $user ? ($user->usre_type_id ?? 'unknown') : 'NOT FOUND';
        echo "  user_id={$row->user_id}, type={$type}, orders={$row->count}\n";
    }
}
