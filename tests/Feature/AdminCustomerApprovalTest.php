<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Order;
use App\Support\SignupOfferService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminCustomerApprovalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('site_promotion_claims');
        Schema::dropIfExists('site_promotions');
        Schema::dropIfExists('customer_activation_tokens');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('user_country')->nullable();
            $table->string('date_added')->nullable();
            $table->string('user_term', 20)->nullable();
            $table->string('exist_customer', 40)->nullable();
            $table->string('real_user', 10)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('is_paid')->default(0);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('site_promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('promotion_name')->nullable();
        });

        Schema::create('site_promotion_claims', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('site_promotion_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->integer('verification_required')->default(1);
            $table->string('verified_at', 30)->nullable();
            $table->integer('payment_required')->default(1);
            $table->decimal('required_payment_amount', 10, 2)->nullable();
            $table->decimal('credit_amount', 10, 2)->nullable();
            $table->decimal('first_order_flat_amount', 10, 2)->nullable();
            $table->text('offer_snapshot_json')->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->string('payment_reference', 120)->nullable();
            $table->string('paid_at', 30)->nullable();
            $table->unsignedBigInteger('redeemed_order_id')->nullable();
            $table->string('redeemed_at', 30)->nullable();
            $table->string('created_at', 30)->nullable();
            $table->string('updated_at', 30)->nullable();
        });

        Schema::create('customer_activation_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('site_legacy_key', 30)->nullable();
            $table->unsignedBigInteger('customer_user_id');
            $table->string('selector', 32);
            $table->string('token_hash', 64);
            $table->string('expires_at', 30)->nullable();
            $table->string('created_at', 30)->nullable();
        });

        foreach ([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'first_name' => null,
                'last_name' => null,
                'company' => null,
                'user_country' => null,
                'date_added' => null,
                'user_term' => null,
                'exist_customer' => null,
                'real_user' => '1',
                'is_active' => 1,
            ],
            [
                'user_id' => 11,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'awaiting-approval',
                'user_email' => 'awaiting@example.com',
                'first_name' => 'Awaiting',
                'last_name' => 'Approval',
                'company' => null,
                'user_country' => 'United States',
                'date_added' => '2026-03-29 10:00:00',
                'user_term' => 'dc',
                'exist_customer' => 'pending_admin_approval',
                'real_user' => '1',
                'is_active' => 0,
            ],
            [
                'user_id' => 12,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'not-verified-yet',
                'user_email' => 'notverified@example.com',
                'first_name' => 'Not',
                'last_name' => 'Verified',
                'company' => null,
                'user_country' => 'United States',
                'date_added' => '2026-03-29 10:30:00',
                'user_term' => 'dc',
                'exist_customer' => '0',
                'real_user' => '1',
                'is_active' => 0,
            ],
            [
                'user_id' => 13,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'blocked-customer',
                'user_email' => 'blocked@example.com',
                'first_name' => 'Blocked',
                'last_name' => 'Customer',
                'company' => null,
                'user_country' => 'United States',
                'date_added' => '2026-03-29 11:00:00',
                'user_term' => 'ip',
                'exist_customer' => '1',
                'real_user' => '1',
                'is_active' => 0,
            ],
            [
                'user_id' => 14,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'pending-payment-verification',
                'user_email' => 'pendingpay@example.com',
                'first_name' => 'Pending',
                'last_name' => 'Payment',
                'company' => null,
                'user_country' => 'United States',
                'date_added' => '2026-03-29 11:30:00',
                'user_term' => 'ip',
                'exist_customer' => '0',
                'real_user' => '1',
                'is_active' => 0,
            ],
            [
                'user_id' => 15,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'payment-email-pending',
                'user_email' => 'verifyfirst@example.com',
                'first_name' => 'Verify',
                'last_name' => 'First',
                'company' => null,
                'user_country' => 'United States',
                'date_added' => '2026-03-29 11:20:00',
                'user_term' => 'ip',
                'exist_customer' => '0',
                'real_user' => '1',
                'is_active' => 0,
            ],
            [
                'user_id' => 16,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'legacy-admin-approved-payment',
                'user_email' => 'legacyadminpay@example.com',
                'first_name' => 'Legacy',
                'last_name' => 'Approval',
                'company' => null,
                'user_country' => 'United States',
                'date_added' => '2026-03-29 11:50:00',
                'user_term' => 'ip',
                'exist_customer' => '1',
                'real_user' => '1',
                'is_active' => 1,
            ],
        ] as $user) {
            AdminUser::query()->insert($user);
        }

        \Illuminate\Support\Facades\DB::table('site_promotions')->insert([
            'id' => 1,
            'promotion_name' => 'Welcome Offer',
        ]);

        \Illuminate\Support\Facades\DB::table('site_promotion_claims')->insert([
            [
                'id' => 4,
                'site_id' => 1,
                'site_promotion_id' => 1,
                'user_id' => 12,
                'website' => '1dollar',
                'status' => 'pending_verification',
                'verification_required' => 1,
                'verified_at' => null,
                'payment_required' => 1,
                'required_payment_amount' => 1.00,
                'credit_amount' => 0.00,
                'first_order_flat_amount' => 1.00,
                'offer_snapshot_json' => '{}',
                'payment_transaction_id' => null,
                'payment_reference' => null,
                'paid_at' => null,
                'redeemed_order_id' => null,
                'redeemed_at' => null,
                'created_at' => '2026-03-29 10:30:00',
                'updated_at' => '2026-03-29 10:30:00',
            ],
            [
                'id' => 1,
                'site_id' => 1,
                'site_promotion_id' => 1,
                'user_id' => 14,
                'website' => '1dollar',
                'status' => 'pending_payment',
                'verification_required' => 1,
                'verified_at' => '2026-03-29 11:40:00',
                'payment_required' => 1,
                'required_payment_amount' => 1.00,
                'credit_amount' => 0.00,
                'first_order_flat_amount' => null,
                'offer_snapshot_json' => '{}',
                'payment_transaction_id' => null,
                'payment_reference' => null,
                'paid_at' => null,
                'redeemed_order_id' => null,
                'redeemed_at' => null,
                'created_at' => '2026-03-29 11:30:00',
                'updated_at' => '2026-03-29 11:40:00',
            ],
            [
                'id' => 2,
                'site_id' => 1,
                'site_promotion_id' => 1,
                'user_id' => 15,
                'website' => '1dollar',
                'status' => 'pending_verification',
                'verification_required' => 1,
                'verified_at' => null,
                'payment_required' => 1,
                'required_payment_amount' => 1.00,
                'credit_amount' => 0.00,
                'first_order_flat_amount' => null,
                'offer_snapshot_json' => '{}',
                'payment_transaction_id' => null,
                'payment_reference' => null,
                'paid_at' => null,
                'redeemed_order_id' => null,
                'redeemed_at' => null,
                'created_at' => '2026-03-29 11:20:00',
                'updated_at' => '2026-03-29 11:20:00',
            ],
            [
                'id' => 3,
                'site_id' => 1,
                'site_promotion_id' => 1,
                'user_id' => 16,
                'website' => '1dollar',
                'status' => 'paid',
                'verification_required' => 1,
                'verified_at' => '2026-03-29 11:52:00',
                'payment_required' => 0,
                'required_payment_amount' => 1.00,
                'credit_amount' => 0.00,
                'first_order_flat_amount' => null,
                'offer_snapshot_json' => '{}',
                'payment_transaction_id' => null,
                'payment_reference' => 'admin-approved:legacy-admin',
                'paid_at' => '2026-03-29 11:52:00',
                'redeemed_order_id' => null,
                'redeemed_at' => null,
                'created_at' => '2026-03-29 11:50:00',
                'updated_at' => '2026-03-29 11:52:00',
            ],
        ]);

        \Illuminate\Support\Facades\DB::table('customer_activation_tokens')->insert([
            [
                'id' => 1,
                'site_id' => 1,
                'site_legacy_key' => '1dollar',
                'customer_user_id' => 12,
                'selector' => 'selector-12',
                'token_hash' => 'hash-12',
                'expires_at' => '2026-04-01 10:30:00',
                'created_at' => '2026-03-29 10:30:00',
            ],
            [
                'id' => 2,
                'site_id' => 1,
                'site_legacy_key' => '1dollar',
                'customer_user_id' => 15,
                'selector' => 'selector-15',
                'token_hash' => 'hash-15',
                'expires_at' => '2026-04-01 11:20:00',
                'created_at' => '2026-03-29 11:20:00',
            ],
        ]);
    }

    public function test_pending_customer_approvals_page_shows_signup_accounts_across_states(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/customer-approvals.php');

        $response->assertOk();
        $response->assertSee('awaiting@example.com');
        $response->assertSee('notverified@example.com');
        $response->assertSee('pendingpay@example.com');
        $response->assertSee('verifyfirst@example.com');
        $response->assertDontSee('blocked@example.com');
    }

    public function test_pending_customer_approvals_page_can_filter_pending_verification_accounts(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/customer-approvals.php?approval_state=pending_verification');

        $response->assertOk();
        $response->assertSeeText('notverified@example.com');
        $response->assertSeeText('verifyfirst@example.com');
        $response->assertDontSeeText('awaiting@example.com');
        $response->assertDontSeeText('pendingpay@example.com');
        $response->assertDontSeeText('blocked@example.com');
    }

    public function test_pending_customer_approvals_page_excludes_legacy_admin_approved_claim_from_payment_pending_queue(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/customer-approvals.php?approval_state=pending_welcome_payment');

        $response->assertOk();
        $response->assertSeeText('pendingpay@example.com');
        $response->assertSeeText('Waiting On $1 Payment');
        $response->assertDontSeeText('Approve this customer account?');
        $response->assertDontSeeText('legacyadminpay@example.com');
    }

    public function test_admin_can_approve_pending_customer_account(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/customer-approvals.php')
            ->post('/v/customers/11/approve');

        $response->assertRedirect('/v/customer-approvals.php');
        $response->assertSessionHas('success');

        $this->assertSame(1, (int) AdminUser::query()->whereKey(11)->value('is_active'));
        $this->assertSame('1', (string) AdminUser::query()->whereKey(11)->value('exist_customer'));
    }

    public function test_admin_can_approve_verified_payment_pending_customer_without_waiving_welcome_payment(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/customer-approvals.php')
            ->post('/v/customers/14/approve');

        $response->assertRedirect('/v/customer-approvals.php');
        $response->assertSessionHas('success');

        $this->assertSame(1, (int) AdminUser::query()->whereKey(14)->value('is_active'));
        $this->assertSame('1', (string) AdminUser::query()->whereKey(14)->value('exist_customer'));
        $this->assertSame('pending_payment', (string) \Illuminate\Support\Facades\DB::table('site_promotion_claims')->where('user_id', 14)->value('status'));
        $this->assertSame(1, (int) \Illuminate\Support\Facades\DB::table('site_promotion_claims')->where('user_id', 14)->value('payment_required'));
        $this->assertSame('admin-approved-account:main-admin', (string) \Illuminate\Support\Facades\DB::table('site_promotion_claims')->where('user_id', 14)->value('payment_reference'));
    }

    public function test_admin_can_verify_email_for_admin_approval_signup(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/customer-approvals.php')
            ->post('/v/customers/12/verify-email');

        $response->assertRedirect('/v/customer-approvals.php');
        $response->assertSessionHas('success');

        $this->assertSame(0, (int) AdminUser::query()->whereKey(12)->value('is_active'));
        $this->assertSame('0', (string) AdminUser::query()->whereKey(12)->value('exist_customer'));
        $this->assertNull(\Illuminate\Support\Facades\DB::table('customer_activation_tokens')->where('customer_user_id', 12)->first());
    }

    public function test_admin_approval_signup_marks_offer_complete_and_applies_first_order_bonus(): void
    {
        $this->withSession(['admin_user_id' => 1])
            ->post('/v/customers/12/verify-email');

        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/customer-approvals.php')
            ->post('/v/customers/12/approve');

        $response->assertRedirect('/v/customer-approvals.php');
        $response->assertSessionHas('success');

        $claim = \Illuminate\Support\Facades\DB::table('site_promotion_claims')
            ->where('user_id', 12)
            ->orderByDesc('id')
            ->first();

        $this->assertSame('paid', (string) $claim->status);
        $this->assertSame(0, (int) $claim->payment_required);
        $this->assertNotNull($claim->verified_at);
        $this->assertNotNull($claim->paid_at);
        $this->assertStringStartsWith('admin-approved-offer:', (string) $claim->payment_reference);

        Order::query()->insert([
            'order_id' => 501,
            'user_id' => 12,
            'website' => '',
            'order_type' => 'order',
            'status' => 'new',
            'is_active' => 1,
            'end_date' => null,
        ]);

        $order = Order::query()->findOrFail(501);

        $this->assertSame(1.00, SignupOfferService::applyEligibleFirstOrderAmount($order, 25.00));
    }

    public function test_admin_can_verify_email_for_welcome_payment_signup(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/customer-approvals.php')
            ->post('/v/customers/15/verify-email');

        $response->assertRedirect('/v/customer-approvals.php');
        $response->assertSessionHas('success', 'Customer email has been marked verified. The account is now waiting for the customer welcome payment.');

        $this->assertSame(0, (int) AdminUser::query()->whereKey(15)->value('is_active'));
        $this->assertSame('0', (string) AdminUser::query()->whereKey(15)->value('exist_customer'));
        $this->assertSame('pending_payment', (string) \Illuminate\Support\Facades\DB::table('site_promotion_claims')->where('user_id', 15)->value('status'));
        $this->assertNotNull(\Illuminate\Support\Facades\DB::table('site_promotion_claims')->where('user_id', 15)->value('verified_at'));
        $this->assertNull(\Illuminate\Support\Facades\DB::table('customer_activation_tokens')->where('customer_user_id', 15)->first());
    }

    public function test_blocked_customers_page_only_shows_truly_blocked_existing_accounts(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/block-customer_list.php');

        $response->assertOk();
        $response->assertSee('blocked@example.com');
        $response->assertDontSee('awaiting@example.com');
        $response->assertDontSee('notverified@example.com');
        $response->assertDontSee('pendingpay@example.com');
    }

    public function test_unblock_route_rejects_pending_signup_accounts(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/block-customer_list.php')
            ->post('/v/block-customer_list/14/unblock');

        $response->assertRedirect('/v/customer-approvals.php');
        $response->assertSessionHas('error');

        $this->assertSame(0, (int) AdminUser::query()->whereKey(14)->value('is_active'));
        $this->assertSame('0', (string) AdminUser::query()->whereKey(14)->value('exist_customer'));
    }
}
