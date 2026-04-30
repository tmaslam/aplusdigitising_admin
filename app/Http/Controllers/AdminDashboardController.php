<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\Billing;
use App\Models\CustomerTopup;
use App\Support\AdminNavigation;
use App\Support\CustomerBalance;
use App\Support\SecurityAlertSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $navCounts = AdminNavigation::counts();
        $hasCreditLedger = Schema::hasTable('customer_credit_ledger');
        $customerCreditInventory = $hasCreditLedger ? CustomerBalance::balances() : collect();

        $subscriptionAmounts = [79.99, 199.99, 399.99, 799.99];

        $activeSubscriptionUserIds = AdminUser::query()
            ->customers()
            ->active()
            ->where('is_active', 1)
            ->whereExists(function ($query) use ($subscriptionAmounts) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('customer_topups')
                    ->whereColumn('customer_topups.user_id', 'users.user_id')
                    ->where('customer_topups.status', 'completed')
                    ->whereIn('customer_topups.amount', $subscriptionAmounts);
            })
            ->pluck('users.user_id');

        $subscriptionsTotal = (float) CustomerTopup::query()
            ->where('status', 'completed')
            ->whereIn('amount', $subscriptionAmounts)
            ->whereIn('user_id', $activeSubscriptionUserIds)
            ->sum('amount');

        $subscriptionCustomersCount = $activeSubscriptionUserIds->count();

        $financialSnapshot = [
            'due_invoices' => Billing::query()->active()->where('approved', 'yes')->where('payment', 'no')->count(),
            'due_amount' => (float) Billing::query()->active()->where('approved', 'yes')->where('payment', 'no')->sum(\Illuminate\Support\Facades\DB::raw('CAST(amount AS DECIMAL(12,2))')),
            'customer_balance' => $hasCreditLedger
                ? (float) $customerCreditInventory->sum(fn ($row) => (float) $row->balance_total)
                : null,
            'customers_with_credit' => $hasCreditLedger ? $customerCreditInventory->count() : 0,
            'subscriptions_total' => $subscriptionsTotal,
            'subscription_customers_count' => $subscriptionCustomersCount,
        ];

        $operationsSnapshot = [
            'active_customers' => $navCounts['customers'],
            'blocked_customers' => $navCounts['blocked_customers'],
            'team_accounts' => AdminUser::query()->teams()->active()->where('is_active', 1)->count(),
            'supervisors' => AdminUser::query()->supervisors()->active()->where('is_active', 1)->count(),
            'all_open_work' => $navCounts['all_orders'],
        ];

        $workflowFocus = [
            'review_ready' => ($navCounts['designer_completed_orders'] ?? 0) + ($navCounts['designer_completed_quotes'] ?? 0),
            'approval_waiting' => $navCounts['approval_waiting_orders'] ?? 0,
            'new_work' => ($navCounts['new_orders'] ?? 0) + ($navCounts['new_quotes'] ?? 0),
            'assigned_work' => ($navCounts['designer_orders'] ?? 0) + ($navCounts['assigned_quotes'] ?? 0),
        ];

        $securityWatch = SecurityAlertSummary::summary();

        return view('admin.dashboard', [
            'adminUser' => $request->attributes->get('adminUser'),
            'navCounts' => $navCounts,
            'financialSnapshot' => $financialSnapshot,
            'operationsSnapshot' => $operationsSnapshot,
            'workflowFocus' => $workflowFocus,
            'securityWatch' => $securityWatch,
            'hasCreditLedger' => $hasCreditLedger,
        ]);
    }
}
