<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\CustomerTopup;
use App\Support\AdminNavigation;
use App\Support\AdminReferenceData;
use App\Support\CustomerApprovalQueue;
use App\Support\CustomerPricing;
use App\Support\PasswordManager;
use App\Support\SignupOfferService;
use App\Support\SiteContext;
use App\Support\SiteResolver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminPeopleController extends Controller
{
    public function createCustomer(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('admin.people.customer-create', [
                'adminUser' => $request->attributes->get('adminUser'),
                'navCounts' => AdminNavigation::counts(),
                'countries' => AdminReferenceData::countriesForCustomerForms(),
                'companyTypes' => AdminReferenceData::companyTypes(),
            ]);
        }

        $site = SiteResolver::forRequest($request);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'selCountry' => ['required', 'string', 'max:150', Rule::in(AdminReferenceData::countries())],
            'telephone_num' => ['required', 'string', 'max:50'],
            'package_type' => ['nullable', 'string', 'in:,Starter,Plus,Pro,Enterprise'],
            'useremail' => ['required', 'email', 'max:190'],
            'user_psw' => ['required', 'string', 'min:6', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'company_address' => ['nullable', 'string', 'max:500'],
            'selCompanyTypes' => ['nullable', 'string', 'max:100'],
        ]);

        $email = strtolower(trim((string) $validated['useremail']));
        $username = $this->deriveUsername($email, $site);

        $existingAccount = AdminUser::query()
            ->customers()
            ->active()
            ->forWebsite($site->legacyKey)
            ->where(function ($query) use ($email, $username) {
                $query->where('user_email', $email)
                    ->orWhere('user_name', $username);
            })
            ->exists();

        if ($existingAccount) {
            return back()->withErrors(['useremail' => 'A customer with this email or a matching username already exists.'])->withInput();
        }

        $now = now()->format('Y-m-d H:i:s');
        $ipAddress = (string) ($request->ip() ?? '127.0.0.1');

        $customer = AdminUser::query()->create(array_merge([
            'site_id' => $site->id,
            'website' => $site->legacyKey,
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => $username,
            'first_name' => trim((string) $validated['first_name']),
            'last_name' => trim((string) $validated['last_name']),
            'company' => trim((string) ($validated['company_name'] ?? '')),
            'company_type' => trim((string) ($validated['selCompanyTypes'] ?? '')),
            'user_email' => $email,
            'company_address' => trim((string) ($validated['company_address'] ?? '')),
            'zip_code' => '',
            'user_city' => '',
            'user_country' => trim((string) $validated['selCountry']),
            'user_phone' => trim((string) $validated['telephone_num']),
            'is_active' => 1,
            'payment_terms' => 7,
            'date_added' => $now,
            'customer_approval_limit' => 0,
            'single_approval_limit' => 0,
            'customer_pending_order_limit' => 0,
            'userip_addrs' => $ipAddress,
            'user_term' => 'dc',
            'package_type' => (string) ($validated['package_type'] ?? ''),
            'real_user' => '1',
            'ref_code' => '',
            'ref_code_other' => '',
            'exist_customer' => '1',
        ], CustomerPricing::sitePricingPayload($site), $this->legacyRegistrationDefaults($site), PasswordManager::payload((string) $validated['user_psw'])));

        return redirect(url('/v/customer_list.php'))
            ->with('success', 'Customer account created successfully. Customer ID: '.$customer->user_id);
    }

    public function customers(Request $request)
    {
        $status = $request->input('status', 'active');
        $customers = AdminUser::query()
            ->customers()
            ->active()
            ->when($status === 'active', function (Builder $query) {
                $query->where('is_active', 1);
            })
            ->when($status === 'inactive', function (Builder $query) {
                $query->where('is_active', 0);
            })
            ->when($status === 'blocked', function (Builder $query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('is_active', 0)
                        ->orWhere('user_term', 'blocked');
                });
            })
            ->when($request->filled('txtUserID'), function (Builder $query) use ($request) {
                $query->where('user_id', 'like', '%'.trim((string) $request->string('txtUserID')).'%');
            })
            ->when($request->filled('txtUserName'), function (Builder $query) use ($request) {
                $term = '%'.$request->string('txtUserName')->trim().'%';
                $query->where(function (Builder $searchQuery) use ($term) {
                    $searchQuery
                        ->where('user_name', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", [$term]);
                });
            })
            ->when($request->filled('txtEmail'), function (Builder $query) use ($request) {
                $query->where('user_email', 'like', '%'.$request->string('txtEmail')->trim().'%');
            })
            ->orderBy($this->sortColumn((string) $request->input('column_name'), 'user_id', ['user_id', 'user_name', 'user_email', 'user_country', 'userip_addrs', 'date_added']), $this->sortDirection((string) $request->input('sort'), 'desc'))
            ->paginate(30)
            ->withQueryString();

        return view('admin.people.customers', [
            'adminUser' => $request->attributes->get('adminUser'),
            'navCounts' => AdminNavigation::counts(),
            'customers' => $customers,
        ]);
    }

    public function subscriptionCustomers(Request $request)
    {
        $subscriptionAmounts = [79.99, 199.99, 399.99, 799.99];

        $latestSubscriptionIds = DB::table('customer_topups')
            ->select('user_id', DB::raw('MAX(id) as latest_id'))
            ->where('status', 'completed')
            ->whereIn('amount', $subscriptionAmounts)
            ->groupBy('user_id');

        $customers = AdminUser::query()
            ->customers()
            ->active()
            ->where('is_active', 1)
            ->joinSub($latestSubscriptionIds, 'latest_sub', function ($join) {
                $join->on('users.user_id', '=', 'latest_sub.user_id');
            })
            ->join('customer_topups as ct', 'ct.id', '=', 'latest_sub.latest_id')
            ->select('users.*', 'ct.amount as subscription_amount', 'ct.plan_option as subscription_plan', 'ct.completed_at as subscription_date')
            ->when($request->filled('txtUserID'), function (Builder $query) use ($request) {
                $query->where('users.user_id', 'like', '%'.trim((string) $request->string('txtUserID')).'%');
            })
            ->when($request->filled('txtUserName'), function (Builder $query) use ($request) {
                $term = '%'.$request->string('txtUserName')->trim().'%';
                $query->where(function (Builder $searchQuery) use ($term) {
                    $searchQuery
                        ->where('users.user_name', 'like', $term)
                        ->orWhere('users.first_name', 'like', $term)
                        ->orWhere('users.last_name', 'like', $term)
                        ->orWhere('users.company', 'like', $term)
                        ->orWhereRaw("CONCAT(COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) like ?", [$term]);
                });
            })
            ->when($request->filled('txtEmail'), function (Builder $query) use ($request) {
                $query->where('users.user_email', 'like', '%'.$request->string('txtEmail')->trim().'%');
            })
            ->orderBy($this->sortColumn((string) $request->input('column_name'), 'user_id', ['user_id', 'user_name', 'user_email', 'user_country', 'userip_addrs', 'date_added']), $this->sortDirection((string) $request->input('sort'), 'desc'))
            ->paginate(30)
            ->withQueryString();

        $customers->getCollection()->transform(function (AdminUser $customer) {
            $customer->subscription_plan_label = $customer->subscription_plan
                ? ucfirst((string) $customer->subscription_plan)
                : '-';
            return $customer;
        });

        return view('admin.people.subscription-customers', [
            'adminUser' => $request->attributes->get('adminUser'),
            'navCounts' => AdminNavigation::counts(),
            'customers' => $customers,
        ]);
    }

    public function pendingApprovals(Request $request)
    {
        $approvalState = trim((string) $request->input('approval_state'));
        $queueUserIds = CustomerApprovalQueue::userIds(null, $approvalState !== '' ? $approvalState : null);
        $claimStatuses = CustomerApprovalQueue::claimStatusMap($queueUserIds);

        $customers = AdminUser::query()
            ->customers()
            ->active()
            ->whereIn('user_id', $queueUserIds === [] ? [0] : $queueUserIds)
            ->when($request->filled('txtUserID'), function (Builder $query) use ($request) {
                $query->where('user_id', 'like', '%'.trim((string) $request->string('txtUserID')).'%');
            })
            ->when($request->filled('txtUserName'), function (Builder $query) use ($request) {
                $term = '%'.$request->string('txtUserName')->trim().'%';
                $query->where(function (Builder $searchQuery) use ($term) {
                    $searchQuery
                        ->where('user_name', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhere('company', 'like', $term)
                        ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", [$term]);
                });
            })
            ->when($request->filled('txtEmail'), function (Builder $query) use ($request) {
                $query->where('user_email', 'like', '%'.$request->string('txtEmail')->trim().'%');
            })
            ->orderBy($this->sortColumn((string) $request->input('column_name'), 'user_id', ['user_id', 'user_name', 'user_email', 'website', 'user_country', 'date_added']), $this->sortDirection((string) $request->input('sort'), 'desc'))
            ->paginate(30)
            ->withQueryString();

        $customers->getCollection()->transform(function (AdminUser $customer) use ($claimStatuses) {
            $approvalState = CustomerApprovalQueue::stateForCustomer(
                $customer,
                $claimStatuses[(int) $customer->user_id] ?? null
            );

            $customer->approval_state = $approvalState;
            $customer->approval_state_label = CustomerApprovalQueue::stateLabel($approvalState);
            $customer->signup_path_label = trim((string) $customer->user_term) === 'ip'
                ? 'Welcome Payment'
                : 'Admin Approval';

            return $customer;
        });

        return view('admin.people.pending-approvals', [
            'adminUser' => $request->attributes->get('adminUser'),
            'navCounts' => AdminNavigation::counts(),
            'customers' => $customers,
            'approvalState' => $approvalState,
            'approvalStateOptions' => CustomerApprovalQueue::stateFilterOptions(),
        ]);
    }

    public function verifyCustomerEmail(Request $request, AdminUser $customer)
    {
        abort_unless((int) $customer->usre_type_id === AdminUser::TYPE_CUSTOMER, 404);

        $approvalState = CustomerApprovalQueue::stateForCustomer(
            $customer,
            CustomerApprovalQueue::claimStatusMap([(int) $customer->user_id], trim((string) $customer->website))[(int) $customer->user_id] ?? null
        );

        if ($approvalState !== CustomerApprovalQueue::STATE_PENDING_VERIFICATION) {
            return redirect()->to($this->withQuery('/v/customer-approvals.php', $request->except('_token')))
                ->with('error', 'This customer account is not waiting for email verification.');
        }

        $this->clearActivationToken($customer);

        if (trim(strtolower((string) ($customer->user_term ?? ''))) === 'dc') {
            $customer->update([
                'is_active' => 0,
                'exist_customer' => '0',
            ]);

            $message = 'Customer email has been marked verified and the account is now waiting for admin approval.';
        } else {
            if (! SignupOfferService::adminVerifyPendingClaim($customer)) {
                return redirect()->to($this->withQuery('/v/customer-approvals.php', $request->except('_token')))
                    ->with('error', 'No pending verification record was found for this customer account.');
            }

            $customer->update([
                'is_active' => 0,
                'exist_customer' => '0',
            ]);

            $message = 'Customer email has been marked verified. The account is now waiting for the customer welcome payment.';
        }

        return redirect()->to($this->withQuery('/v/customer-approvals.php', $request->except('_token')))
            ->with('success', $message);
    }

    public function approveCustomer(Request $request, AdminUser $customer)
    {
        abort_unless((int) $customer->usre_type_id === AdminUser::TYPE_CUSTOMER, 404);

        $adminName = $request->attributes->get('adminUser')?->user_name ?: 'admin';
        $isManualApprovalSignup = trim(strtolower((string) ($customer->user_term ?? ''))) === 'dc';

        $customer->update([
            'is_active' => 1,
            'exist_customer' => '1',
        ]);

        $welcomePaymentPending = $isManualApprovalSignup
            ? false
            : SignupOfferService::adminApprovePendingPayment($customer, $adminName);

        if ($isManualApprovalSignup) {
            SignupOfferService::completeManualApprovalClaim($customer, $adminName);
        }

        return redirect()->to($this->withQuery('/v/customer-approvals.php', $request->except('_token')))
            ->with('success', $welcomePaymentPending
                ? 'Customer has been approved successfully. The welcome payment offer still remains pending until the $1 payment is completed.'
                : 'Customer has been approved successfully.');
    }

    public function blockCustomer(Request $request, AdminUser $customer)
    {
        abort_unless((int) $customer->usre_type_id === 1, 404);

        $returnTo = trim((string) $request->input('return_to', ''));

        $updateData = ['is_active' => 0];

        if ($returnTo === 'customer-approvals') {
            // Pre-approval block: set user_term='blocked' so the account
            // disappears from all approval-queue queries (which check for
            // user_term='dc' or user_term='ip') but remains visible in the
            // Inactive Customers report via the widened scopeBlockedCustomerAccounts.
            $updateData['user_term'] = 'blocked';

            // Cancel any pending promotion claim so this user_id is no longer
            // returned by verifiedPendingPaymentUserIds() queue lookups.
            if (Schema::hasTable('site_promotion_claims')) {
                DB::table('site_promotion_claims')
                    ->where('user_id', $customer->user_id)
                    ->whereIn('status', [
                        SignupOfferService::STATUS_PENDING_VERIFICATION,
                        SignupOfferService::STATUS_PENDING_PAYMENT,
                    ])
                    ->update(['status' => 'rejected', 'updated_at' => now()->format('Y-m-d H:i:s')]);
            }
        }

        $customer->update($updateData);

        $redirectBase = $returnTo === 'customer-approvals'
            ? url('/v/customer-approvals.php')
            : url('/v/customer_list.php');

        return redirect()->to($redirectBase.'?'.http_build_query($request->except(['_token', 'return_to'])))
            ->with('success', 'Customer has been blocked successfully.');
    }

    public function deleteCustomer(Request $request, AdminUser $customer)
    {
        abort_unless((int) $customer->usre_type_id === 1, 404);

        $adminUser = $request->attributes->get('adminUser');

        $customer->update([
            'end_date' => now()->format('Y-m-d H:i:s'),
            'deleted_by' => $adminUser?->user_name ?: 'admin',
        ]);

        return redirect()->to(url('/v/customer_list.php').'?'.http_build_query($request->except('_token')))
            ->with('success', 'Customer has been deleted successfully.');
    }

    public function teams(Request $request)
    {
        $statusFilter = trim((string) $request->input('status', 'all'));

        $teams = AdminUser::query()
            ->teamPortalUsers()
            ->when($statusFilter === 'active', fn (Builder $q) => $q->where('is_active', 1))
            ->when($statusFilter === 'locked', fn (Builder $q) => $q->where('is_active', 0))
            ->when($request->filled('txtUserID'), function (Builder $query) use ($request) {
                $query->where('user_id', 'like', '%'.trim((string) $request->string('txtUserID')).'%');
            })
            ->when($request->filled('txtUserName'), function (Builder $query) use ($request) {
                $term = '%'.trim((string) $request->string('txtUserName')).'%';
                $query->where(function (Builder $searchQuery) use ($term) {
                    $searchQuery
                        ->where('user_name', 'like', $term)
                        ->orWhere('user_email', 'like', $term)
                        ->orWhere('first_name', 'like', $term)
                        ->orWhere('last_name', 'like', $term)
                        ->orWhereRaw("CONCAT(COALESCE(first_name, ''), ' ', COALESCE(last_name, '')) like ?", [$term]);
                });
            })
            ->when($request->filled('account_type'), function (Builder $query) use ($request) {
                $type = trim((string) $request->string('account_type'));

                if ($type === 'team') {
                    $query->where('usre_type_id', AdminUser::TYPE_TEAM);
                } elseif ($type === 'supervisor') {
                    $query->where('usre_type_id', AdminUser::TYPE_SUPERVISOR);
                }
            })
            ->orderBy($this->sortColumn((string) $request->input('column_name'), 'user_id', ['user_id', 'user_name', 'date_added']), $this->sortDirection((string) $request->input('sort'), 'desc'))
            ->paginate(50)
            ->withQueryString();

        return view('admin.people.teams', [
            'adminUser' => $request->attributes->get('adminUser'),
            'navCounts' => AdminNavigation::counts(),
            'teams' => $teams,
        ]);
    }

    public function disableTeam(Request $request, AdminUser $team)
    {
        abort_unless(in_array((int) $team->usre_type_id, [AdminUser::TYPE_TEAM, AdminUser::TYPE_SUPERVISOR], true), 404);

        $team->update(['is_active' => 0]);

        return redirect()->to(url('/v/show-all-teams.php').'?'.http_build_query($request->except('_token')))
            ->with('success', 'Team account has been removed successfully.');
    }

    public function unlockTeam(Request $request, AdminUser $team)
    {
        abort_unless(in_array((int) $team->usre_type_id, [AdminUser::TYPE_TEAM, AdminUser::TYPE_SUPERVISOR], true), 404);

        $team->update(['is_active' => 1]);

        return redirect()->to(url('/v/show-all-teams.php').'?'.http_build_query($request->except('_token')))
            ->with('success', 'Team account has been unlocked and is now active.');
    }

    private function sortColumn(string $column, string $default, array $allowed): string
    {
        return in_array($column, $allowed, true) ? $column : $default;
    }

    private function sortDirection(string $direction, string $default = 'desc'): string
    {
        $direction = strtolower($direction);

        return in_array($direction, ['asc', 'desc'], true) ? $direction : $default;
    }

    private function deriveUsername(string $email, SiteContext $site): string
    {
        $base = strtolower(trim((string) explode('@', $email)[0]));
        $base = preg_replace('/[^a-z0-9._-]/', '', $base) ?: 'customer';

        $username = $base;
        $suffix = 1;

        while (AdminUser::query()->customers()->active()->forWebsite($site->legacyKey)->where('user_name', $username)->exists()) {
            $suffix++;
            $username = $base.$suffix;
        }

        return $username;
    }

    private function legacyRegistrationDefaults(SiteContext $site): array
    {
        static $userColumns = null;

        if ($userColumns === null) {
            $userColumns = collect(Schema::getColumns('users'))
                ->pluck('name')
                ->flip()
                ->all();
        }

        $defaults = [];

        $legacyValues = [
            'security_key' => Str::random(40),
            'alternate_email' => '',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => $site->legacyKey,
        ];

        foreach ($legacyValues as $column => $value) {
            if (isset($userColumns[$column])) {
                $defaults[$column] = $value;
            }
        }

        return $defaults;
    }

    private function withQuery(string $path, array $query): string
    {
        $query = array_filter($query, static fn ($value) => $value !== null && $value !== '');

        return $query === [] ? url($path) : url($path).'?'.http_build_query($query);
    }

    private function clearActivationToken(AdminUser $customer): void
    {
        if (! Schema::hasTable('customer_activation_tokens')) {
            return;
        }

        DB::table('customer_activation_tokens')
            ->where('customer_user_id', $customer->user_id)
            ->when(trim((string) $customer->website) !== '', function ($query) use ($customer) {
                $query->where('site_legacy_key', trim((string) $customer->website));
            })
            ->delete();
    }
}
