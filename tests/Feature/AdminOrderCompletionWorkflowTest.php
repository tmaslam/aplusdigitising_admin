<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Http\Controllers\AdminOrderDetailController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOrderCompletionWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::spy();

        Schema::dropIfExists('users');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('site_pricing_profiles');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('site_promotion_claims');
        Schema::dropIfExists('site_promotions');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('quote_negotiations');

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_key', 30)->unique();
            $table->string('slug', 100)->unique();
            $table->string('name', 150);
            $table->string('brand_name', 150);
            $table->string('primary_domain')->nullable();
            $table->string('website_address')->nullable();
            $table->string('support_email')->nullable();
            $table->string('from_email')->nullable();
            $table->string('timezone', 100)->default('UTC');
            $table->string('pricing_strategy', 50)->default('customer_rate');
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('settings_json')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('site_pricing_profiles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id');
            $table->string('profile_name', 150);
            $table->string('work_type', 50)->nullable();
            $table->string('turnaround_code', 50)->nullable();
            $table->string('pricing_mode', 50)->default('per_thousand');
            $table->decimal('fixed_price', 12, 2)->nullable();
            $table->decimal('per_thousand_rate', 12, 4)->nullable();
            $table->decimal('minimum_charge', 12, 2)->nullable();
            $table->decimal('included_units', 12, 2)->nullable();
            $table->decimal('overage_rate', 12, 4)->nullable();
            $table->string('package_name', 50)->nullable();
            $table->text('config_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('website', 30)->nullable();
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('urgent_fee')->nullable();
            $table->string('normal_fee')->nullable();
            $table->string('middle_fee')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('customer_approval_limit')->nullable();
            $table->string('customer_pending_order_limit')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('website', 30)->nullable();
            $table->string('stitches', 255)->nullable();
            $table->string('stitches_price', 255)->nullable();
            $table->string('total_amount', 255)->nullable();
            $table->string('turn_around_time', 30)->nullable();
            $table->string('completion_date', 30)->nullable();
            $table->string('submit_date', 30)->nullable();
            $table->string('assigned_date', 30)->nullable();
            $table->string('vender_complete_date', 30)->nullable();
            $table->string('working', 30)->nullable();
            $table->string('modified_date', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('site_promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
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

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->string('amount', 255)->nullable();
            $table->integer('is_paid')->default(0);
            $table->integer('is_advance')->default(0);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->text('comments')->nullable();
            $table->string('comment_source')->nullable();
            $table->string('source_page')->nullable();
            $table->string('date_added')->nullable();
            $table->string('date_modified')->nullable();
        });

        Schema::create('advancepayment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('status')->default(0);
            $table->string('advance_pay')->nullable();
        });

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id');
            $table->string('file_source', 30)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('file_name_with_date', 255)->nullable();
            $table->string('file_name_with_order_id', 255)->nullable();
            $table->string('date_added', 30)->nullable();
        });

        Schema::create('quote_negotiations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('customer_user_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->string('customer_reason_code', 100)->nullable();
            $table->text('customer_reason_text')->nullable();
            $table->decimal('customer_target_amount', 10, 2)->nullable();
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->decimal('admin_counter_amount', 10, 2)->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->string('resolved_by_name', 150)->nullable();
            $table->string('resolved_at', 30)->nullable();
            $table->string('created_at', 30)->nullable();
            $table->string('updated_at', 30)->nullable();
        });

        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'site_id' => 1,
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'website' => '1dollar',
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'first_name' => null,
                'last_name' => null,
                'urgent_fee' => null,
                'normal_fee' => null,
                'middle_fee' => null,
                'payment_terms' => null,
                'customer_approval_limit' => null,
                'customer_pending_order_limit' => null,
                'is_active' => 1,
            ],
            [
                'user_id' => 2,
                'site_id' => 1,
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'website' => '1dollar',
                'user_name' => 'team-user',
                'user_email' => 'team@example.com',
                'first_name' => null,
                'last_name' => null,
                'urgent_fee' => null,
                'normal_fee' => null,
                'middle_fee' => null,
                'payment_terms' => null,
                'customer_approval_limit' => null,
                'customer_pending_order_limit' => null,
                'is_active' => 1,
            ],
            [
                'user_id' => 3,
                'site_id' => 1,
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'website' => '1dollar',
                'user_name' => 'customer-user',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'User',
                'urgent_fee' => null,
                'normal_fee' => null,
                'middle_fee' => null,
                'payment_terms' => null,
                'customer_approval_limit' => null,
                'customer_pending_order_limit' => null,
                'is_active' => 1,
            ],
        ]);

        \DB::table('site_promotions')->insert([
            'id' => 1,
            'site_id' => 1,
            'promotion_name' => 'Welcome Offer',
        ]);

        \DB::table('sites')->insert([
            'id' => 1,
            'legacy_key' => '1dollar',
            'slug' => '1dollar',
            'name' => 'APlus',
            'brand_name' => 'A Plus Digitizing',
            'primary_domain' => 'localhost',
            'website_address' => 'https://localhost',
            'support_email' => 'support@example.com',
            'from_email' => 'support@example.com',
            'timezone' => 'UTC',
            'pricing_strategy' => 'customer_rate',
            'is_primary' => 1,
            'is_active' => 1,
        ]);

        \DB::table('site_pricing_profiles')->insert([
            [
                'site_id' => 1,
                'profile_name' => 'Digitizing Standard',
                'work_type' => 'digitizing',
                'turnaround_code' => 'standard',
                'pricing_mode' => 'per_thousand',
                'per_thousand_rate' => 1.00,
                'minimum_charge' => 6.00,
                'is_active' => 1,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Digitizing Priority',
                'work_type' => 'digitizing',
                'turnaround_code' => 'priority',
                'pricing_mode' => 'per_thousand',
                'per_thousand_rate' => 1.50,
                'minimum_charge' => 9.00,
                'is_active' => 1,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Digitizing Super Rush',
                'work_type' => 'digitizing',
                'turnaround_code' => 'superrush',
                'pricing_mode' => 'per_thousand',
                'per_thousand_rate' => 2.00,
                'minimum_charge' => 12.00,
                'is_active' => 1,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Vector Standard',
                'work_type' => 'vector',
                'turnaround_code' => 'standard',
                'pricing_mode' => 'fixed_price',
                'fixed_price' => 6.00,
                'overage_rate' => 6.00,
                'is_active' => 1,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Vector Priority',
                'work_type' => 'vector',
                'turnaround_code' => 'priority',
                'pricing_mode' => 'fixed_price',
                'fixed_price' => 9.00,
                'overage_rate' => 9.00,
                'is_active' => 1,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Vector Super Rush',
                'work_type' => 'vector',
                'turnaround_code' => 'superrush',
                'pricing_mode' => 'fixed_price',
                'fixed_price' => 12.00,
                'overage_rate' => 12.00,
                'is_active' => 1,
            ],
        ]);
    }

    public function test_admin_can_complete_assigned_order_without_waiting_for_ready_status(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 101,
            'user_id' => 1,
            'assign_to' => 2,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Underprocess',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 10:00:00',
        ]);

        \DB::table('attach_files')->insert([
            'order_id' => 101,
            'file_source' => 'sewout',
            'file_name' => 'proof.pdf',
            'file_name_with_date' => 'proof.pdf',
            'file_name_with_order_id' => '(101) proof.pdf',
            'date_added' => '2026-03-25 10:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/complete', [
            'order_id' => 101,
            'page' => 'order',
            'back' => 'Designer Orders',
            'stitches' => '5000',
            'stamount' => '12.00',
            'ddlStatus' => 'done',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 101,
            'status' => 'done',
            'stitches' => '5000',
            'stitches_price' => '12.00',
            'total_amount' => '12.00',
        ]);
    }

    public function test_admin_can_complete_new_unassigned_order_without_team_assignment(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 111,
            'user_id' => 1,
            'assign_to' => 0,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Underprocess',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 10:00:00',
        ]);

        \DB::table('attach_files')->insert([
            'order_id' => 111,
            'file_source' => 'sewout',
            'file_name' => 'proof.pdf',
            'file_name_with_date' => 'proof.pdf',
            'file_name_with_order_id' => '(111) proof.pdf',
            'date_added' => '2026-03-25 10:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/complete', [
            'order_id' => 111,
            'page' => 'order',
            'back' => 'New Orders',
            'stitches' => '4200',
            'stamount' => '10.00',
            'ddlStatus' => 'done',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 111,
            'status' => 'done',
            'stitches' => '4200',
            'stitches_price' => '10.00',
            'total_amount' => '10.00',
        ]);
    }

    public function test_admin_price_preview_returns_free_amount_for_manual_signup_offer_under_10k_stitches(): void
    {
        \DB::table('site_promotion_claims')->insert([
            'id' => 1,
            'site_id' => 1,
            'site_promotion_id' => 1,
            'user_id' => 3,
            'website' => '1dollar',
            'status' => 'paid',
            'verification_required' => 0,
            'verified_at' => '2026-04-12 10:00:00',
            'payment_required' => 0,
            'required_payment_amount' => 1.00,
            'credit_amount' => 0.00,
            'first_order_flat_amount' => 0.00,
            'offer_snapshot_json' => json_encode([
                'first_order_free_under_stitches' => 10000,
                'first_order_flat_amount' => 0,
                'credit_amount' => 0,
            ]),
            'payment_transaction_id' => null,
            'payment_reference' => 'admin-approved-offer:test',
            'paid_at' => '2026-04-12 10:00:00',
            'redeemed_order_id' => null,
            'redeemed_at' => null,
            'created_at' => '2026-04-12 10:00:00',
            'updated_at' => '2026-04-12 10:00:00',
        ]);

        \DB::table('orders')->insert([
            'order_id' => 601,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Underprocess',
            'website' => '',
            'turn_around_time' => 'Standard',
            'submit_date' => '2026-04-12 10:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/price-preview', [
            'order_id' => 601,
            'stitches' => '5000',
        ]);

        $response->assertOk();
        $response->assertJson([
            'stitches' => '5000',
            'amount' => '0.00',
        ]);
    }

    public function test_admin_completion_requires_site_pricing_configuration(): void
    {
        \DB::table('sites')->insert([
            'id' => 2,
            'legacy_key' => 'site2',
            'slug' => 'site2',
            'name' => 'Site Two',
            'brand_name' => 'Site Two',
            'primary_domain' => 'site2.localhost',
            'website_address' => 'https://site2.localhost',
            'support_email' => 'support@site2.example.com',
            'from_email' => 'support@site2.example.com',
            'timezone' => 'UTC',
            'pricing_strategy' => 'customer_rate',
            'is_primary' => 0,
            'is_active' => 1,
        ]);

        \DB::table('users')->insert([
            'user_id' => 77,
            'site_id' => 2,
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'website' => 'site2',
            'user_name' => 'missing-pricing-customer',
            'user_email' => 'missing-pricing@example.com',
            'first_name' => 'Missing',
            'last_name' => 'Pricing',
            'urgent_fee' => null,
            'normal_fee' => null,
            'middle_fee' => null,
            'payment_terms' => null,
            'customer_approval_limit' => null,
            'customer_pending_order_limit' => null,
            'is_active' => 1,
        ]);

        \DB::table('orders')->insert([
            'order_id' => 602,
            'user_id' => 77,
            'site_id' => 2,
            'assign_to' => 0,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Underprocess',
            'website' => 'site2',
            'turn_around_time' => 'Standard',
            'submit_date' => '2026-04-12 10:05:00',
        ]);

        \DB::table('attach_files')->insert([
            'order_id' => 602,
            'file_source' => 'sewout',
            'file_name' => 'proof.pdf',
            'file_name_with_date' => 'proof.pdf',
            'file_name_with_order_id' => '(602) proof.pdf',
            'date_added' => '2026-04-12 10:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/complete', [
            'order_id' => 602,
            'page' => 'order',
            'back' => 'New Orders',
            'stitches' => '5000',
            'stamount' => '',
            'ddlStatus' => 'done',
        ]);

        $response->assertSessionHasErrors([
            'stamount' => 'No Embroidery Digitizing pricing profile is configured for Standard on Site Two. Add it in Site Pricing before continuing.',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 602,
            'status' => 'Underprocess',
        ]);
    }

    public function test_admin_order_detail_shows_direct_complete_and_view_as_customer_actions_for_new_unassigned_order(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 112,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Underprocess',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 10:00:00',
        ]);

        \DB::table('attach_files')->insert([
            'order_id' => 112,
            'file_source' => 'sewout',
            'file_name' => 'proof.pdf',
            'file_name_with_date' => 'proof.pdf',
            'file_name_with_order_id' => '(112) proof.pdf',
            'date_added' => '2026-03-25 10:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->get('/v/orders/112/detail/order');

        $response
            ->assertOk()
            ->assertSee('View As Customer')
            ->assertSee('/v/simulate-login/3', false)
            ->assertSee('Complete the Order')
            ->assertSee('This order is not assigned to team or supervisor yet, so admin can complete it directly here when needed.');
    }

    public function test_admin_can_complete_quote_without_customer_delivery_files(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 202,
            'user_id' => 1,
            'assign_to' => 0,
            'order_type' => 'digitzing',
            'type' => 'digitizing',
            'status' => 'Ready',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/complete', [
            'order_id' => 202,
            'page' => 'quote',
            'back' => 'Designer Completed Quotes',
            'stitches' => '3500',
            'stamount' => '9.50',
            'ddlStatus' => 'done',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 202,
            'status' => 'done',
            'stitches' => '3500',
            'stitches_price' => '9.50',
            'total_amount' => '9.50',
        ]);
    }

    public function test_admin_cannot_complete_order_with_zero_amount(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 2040,
            'user_id' => 1,
            'assign_to' => 2,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Ready',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('attach_files')->insert([
            'order_id' => 2040,
            'file_source' => 'sewout',
            'file_name' => 'proof.pdf',
            'file_name_with_date' => 'proof.pdf',
            'file_name_with_order_id' => '(2040) proof.pdf',
            'date_added' => '2026-03-25 11:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->from('/v/orders/2040/detail/order')->post('/v/order-detail/complete', [
            'order_id' => 2040,
            'page' => 'order',
            'back' => 'designer-completed',
            'stitches' => '3500',
            'stamount' => '0.00',
            'ddlStatus' => 'done',
        ]);

        $response->assertRedirect('/v/orders/2040/detail/order?back=designer-completed');
        $response->assertSessionHasErrors('stamount');

        $this->assertDatabaseHas('orders', [
            'order_id' => 2040,
            'status' => 'Ready',
        ]);
    }

    public function test_admin_can_disapprove_order_without_customer_delivery_files(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 203,
            'user_id' => 1,
            'assign_to' => 2,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Ready',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/complete', [
            'order_id' => 203,
            'page' => 'order',
            'back' => 'designer-completed',
            'stitches' => '3500',
            'stamount' => '9.50',
            'ddlStatus' => 'Disapproved',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 203,
            'status' => 'disapproved',
            'stitches' => '3500',
            'stitches_price' => '9.50',
            'total_amount' => '9.50',
        ]);
    }

    public function test_admin_quote_detail_shows_customer_price_response_summary(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 204,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'quote',
            'type' => 'digitizing',
            'status' => 'disapproved',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('quote_negotiations')->insert([
            'order_id' => 204,
            'customer_user_id' => 3,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Need a lower price to move forward.',
            'customer_target_amount' => 6.50,
            'created_at' => '2026-03-25 11:10:00',
            'updated_at' => '2026-03-25 11:10:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->get('/v/orders/204/detail/quote');

        $response
            ->assertOk()
            ->assertSee('Customer Price Response')
            ->assertSee('Pricing Too High')
            ->assertSee('Need a lower price to move forward.')
            ->assertSee('$6.50');
    }

    public function test_admin_can_accept_customer_requested_quote_price(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 205,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'quote',
            'type' => 'digitizing',
            'status' => 'disapproved',
            'website' => '1dollar',
            'total_amount' => '9.50',
            'stitches_price' => '9.50',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('quote_negotiations')->insert([
            'id' => 1,
            'order_id' => 205,
            'customer_user_id' => 3,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Need a lower price to move forward.',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 9.50,
            'created_at' => '2026-03-25 11:10:00',
            'updated_at' => '2026-03-25 11:10:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/respond-quote-negotiation', [
            'order_id' => 205,
            'negotiation_id' => 1,
            'page' => 'quote',
            'back' => 'completed-quotes',
            'action' => 'accept',
            'admin_note' => 'Approved as requested.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 205,
            'status' => 'Underprocess',
            'order_type' => 'order',
            'total_amount' => '6.50',
            'stitches_price' => '6.50',
        ]);

        $this->assertDatabaseHas('quote_negotiations', [
            'id' => 1,
            'status' => 'accepted_by_admin',
            'admin_counter_amount' => 6.50,
            'admin_note' => 'Approved as requested.',
        ]);

        $this->assertQuoteNegotiationMailSent('customer@example.com', 'approved');
    }

    public function test_admin_detail_quote_route_redirects_to_order_route_after_negotiation_conversion(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 208,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Underprocess',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/208/detail/quote?back=quote-negotiations');

        $response->assertRedirect('http://localhost/v/orders/208/detail/order?back=quote-negotiations');
    }

    public function test_converted_quote_completed_by_admin_stays_in_order_workflow(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 209,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'quote',
            'type' => 'digitizing',
            'status' => 'disapproved',
            'website' => '1dollar',
            'total_amount' => '9.50',
            'stitches_price' => '9.50',
            'turn_around_time' => 'Standard',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('quote_negotiations')->insert([
            'id' => 9,
            'order_id' => 209,
            'customer_user_id' => 3,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Need a lower price to move forward.',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 9.50,
            'created_at' => '2026-03-25 11:10:00',
            'updated_at' => '2026-03-25 11:10:00',
        ]);

        $acceptResponse = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/respond-quote-negotiation', [
            'order_id' => 209,
            'negotiation_id' => 9,
            'page' => 'quote',
            'back' => 'quote-negotiations',
            'action' => 'accept',
        ]);

        $acceptResponse->assertSessionHasNoErrors();

        \DB::table('attach_files')->insert([
            'order_id' => 209,
            'file_source' => 'sewout',
            'file_name' => 'proof.pdf',
            'file_name_with_date' => 'proof.pdf',
            'file_name_with_order_id' => '(209) proof.pdf',
            'date_added' => '2026-03-25 10:05:00',
        ]);

        $completeResponse = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/complete', [
            'order_id' => 209,
            'page' => 'order',
            'back' => 'new-orders',
            'stitches' => '4500',
            'stamount' => '6.50',
            'ddlStatus' => 'done',
        ]);

        $completeResponse->assertSessionHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'order_id' => 209,
            'order_type' => 'order',
            'status' => 'done',
            'total_amount' => '6.50',
        ]);

        $approvalWaitingResponse = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/approval-waiting');

        $approvalWaitingResponse->assertOk();
        $approvalWaitingResponse->assertSee('209');
    }

    public function test_admin_approval_waiting_keeps_negotiated_converted_order_visible_when_pending_limit_runs(): void
    {
        \DB::table('users')->where('user_id', 3)->update([
            'customer_pending_order_limit' => '1',
        ]);

        \DB::table('orders')->insert([
            [
                'order_id' => 210,
                'user_id' => 3,
                'assign_to' => 0,
                'order_type' => 'order',
                'type' => 'digitizing',
                'status' => 'done',
                'website' => '1dollar',
                'total_amount' => '6.50',
                'stitches_price' => '6.50',
                'submit_date' => '2026-03-25 11:00:00',
            ],
            [
                'order_id' => 211,
                'user_id' => 3,
                'assign_to' => 0,
                'order_type' => 'order',
                'type' => 'digitizing',
                'status' => 'done',
                'website' => '1dollar',
                'total_amount' => '8.00',
                'stitches_price' => '8.00',
                'submit_date' => '2026-03-25 11:30:00',
            ],
        ]);

        \DB::table('quote_negotiations')->insert([
            'id' => 10,
            'order_id' => 210,
            'customer_user_id' => 3,
            'status' => 'accepted_by_admin',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Approved after customer negotiation.',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 9.50,
            'admin_counter_amount' => 6.50,
            'resolved_by_user_id' => 1,
            'resolved_by_name' => 'admin',
            'resolved_at' => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->get('/v/orders/approval-waiting');

        $response->assertOk();
        $response->assertSee('210');

        $this->assertDatabaseHas('orders', [
            'order_id' => 210,
            'status' => 'done',
        ]);

        $this->assertDatabaseMissing('billing', [
            'order_id' => 210,
            'approved' => 'yes',
        ]);
    }

    public function test_preview_price_uses_accepted_negotiated_amount_for_converted_vector_order(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 2110,
            'user_id' => 3,
            'assign_to' => 0,
            'site_id' => 1,
            'order_type' => 'vector',
            'type' => 'vector',
            'status' => 'Underprocess',
            'website' => '1dollar',
            'total_amount' => '20.00',
            'stitches_price' => '20.00',
            'stitches' => '5:30',
            'turn_around_time' => 'Standard',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('quote_negotiations')->insert([
            'id' => 110,
            'order_id' => 2110,
            'customer_user_id' => 3,
            'status' => 'accepted_by_admin',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Approved after negotiation.',
            'customer_target_amount' => 20.00,
            'quoted_amount' => 33.00,
            'admin_counter_amount' => 20.00,
            'resolved_by_user_id' => 1,
            'resolved_by_name' => 'admin',
            'resolved_at' => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->postJson('/v/order-detail/price-preview', [
                'order_id' => 2110,
                'stitches' => '5:30',
            ]);

        $response->assertOk()
            ->assertJson([
                'stitches' => '5:30',
                'amount' => '20.00',
            ]);
    }

    public function test_admin_completion_keeps_accepted_negotiated_amount_when_amount_is_blank(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 2111,
            'user_id' => 3,
            'assign_to' => 0,
            'site_id' => 1,
            'order_type' => 'vector',
            'type' => 'vector',
            'status' => 'Underprocess',
            'website' => '1dollar',
            'total_amount' => '20.00',
            'stitches_price' => '20.00',
            'stitches' => '5:30',
            'turn_around_time' => 'Standard',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('quote_negotiations')->insert([
            'id' => 111,
            'order_id' => 2111,
            'customer_user_id' => 3,
            'status' => 'accepted_by_admin',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Approved after negotiation.',
            'customer_target_amount' => 20.00,
            'quoted_amount' => 33.00,
            'admin_counter_amount' => 20.00,
            'resolved_by_user_id' => 1,
            'resolved_by_name' => 'admin',
            'resolved_at' => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        \DB::table('attach_files')->insert([
            'order_id' => 2111,
            'file_source' => 'sewout',
            'file_name' => 'proof.dst',
            'file_name_with_date' => 'proof.dst',
            'file_name_with_order_id' => '(2111) proof.dst',
            'date_added' => '2026-03-25 10:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/complete', [
            'order_id' => 2111,
            'page' => 'vector',
            'back' => 'new-orders',
            'stitches' => '5:30',
            'stamount' => '',
            'ddlStatus' => 'done',
        ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'order_id' => 2111,
            'status' => 'done',
            'total_amount' => '20.00',
            'stitches_price' => '20.00',
            'stitches' => '5:30',
        ]);
    }

    public function test_admin_all_orders_shows_paid_converted_order_after_completion(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 212,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'approved',
            'website' => '1dollar',
            'total_amount' => '5.00',
            'stitches_price' => '5.00',
            'submit_date' => '2026-03-25 12:00:00',
        ]);

        \DB::table('billing')->insert([
            'order_id' => 212,
            'user_id' => 3,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'yes',
            'amount' => '5.00',
            'is_paid' => 1,
            'end_date' => null,
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/all-orders');

        $response->assertOk();
        $response->assertSee('212');
    }

    public function test_admin_accepting_vector_quote_negotiation_converts_it_to_vector_order(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 207,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'q-vector',
            'type' => 'vector',
            'status' => 'disapproved',
            'website' => '1dollar',
            'turn_around_time' => 'Priority',
            'total_amount' => '14.00',
            'stitches_price' => '14.00',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('quote_negotiations')->insert([
            'id' => 3,
            'order_id' => 207,
            'customer_user_id' => 3,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Need a lower price to move forward.',
            'customer_target_amount' => 11.00,
            'quoted_amount' => 14.00,
            'created_at' => '2026-03-25 11:10:00',
            'updated_at' => '2026-03-25 11:10:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/respond-quote-negotiation', [
            'order_id' => 207,
            'negotiation_id' => 3,
            'page' => 'quote',
            'back' => 'quote-negotiations',
            'action' => 'accept',
            'admin_note' => 'Approved and moving to production.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 207,
            'order_type' => 'vector',
            'type' => 'vector',
            'status' => 'Underprocess',
            'total_amount' => '11.00',
            'stitches_price' => '11.00',
        ]);
    }

    public function test_admin_can_send_counter_offer_on_quote_negotiation(): void
    {
        \DB::table('orders')->insert([
            'order_id' => 206,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'quote',
            'type' => 'digitizing',
            'status' => 'disapproved',
            'website' => '1dollar',
            'total_amount' => '9.50',
            'stitches_price' => '9.50',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        \DB::table('quote_negotiations')->insert([
            'id' => 2,
            'order_id' => 206,
            'customer_user_id' => 3,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Need a lower price to move forward.',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 9.50,
            'created_at' => '2026-03-25 11:10:00',
            'updated_at' => '2026-03-25 11:10:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/respond-quote-negotiation', [
            'order_id' => 206,
            'negotiation_id' => 2,
            'page' => 'quote',
            'back' => 'completed-quotes',
            'action' => 'reject',
            'admin_counter_amount' => '8.00',
            'admin_note' => 'We can meet you part way.',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 206,
            'status' => 'done',
            'total_amount' => '8.00',
            'stitches_price' => '8.00',
        ]);

        $this->assertDatabaseHas('quote_negotiations', [
            'id' => 2,
            'status' => 'counter_offered',
            'admin_counter_amount' => 8.00,
            'admin_note' => 'We can meet you part way.',
        ]);

        $this->assertQuoteNegotiationMailSent('customer@example.com', 'revised price');
    }

    private function assertQuoteNegotiationMailSent(string $recipient, string $subjectFragment): void
    {
        Mail::shouldHaveReceived('send')
            ->withArgs(function ($view, $data, $callback) use ($recipient, $subjectFragment) {
                if ($view !== [] || $data !== [] || ! is_callable($callback)) {
                    return false;
                }

                $message = new class
                {
                    public array $to = [];
                    public string $subject = '';

                    public function to($recipients)
                    {
                        $this->to = array_map('strtolower', (array) $recipients);

                        return $this;
                    }

                    public function subject($subject)
                    {
                        $this->subject = (string) $subject;

                        return $this;
                    }

                    public function html($html)
                    {
                        return $this;
                    }

                    public function from(...$arguments)
                    {
                        return $this;
                    }

                    public function replyTo(...$arguments)
                    {
                        return $this;
                    }

                    public function sender(...$arguments)
                    {
                        return $this;
                    }
                };

                $callback($message);

                return in_array(strtolower($recipient), $message->to, true)
                    && str_contains(strtolower($message->subject), strtolower($subjectFragment));
            })
            ->atLeast()->once();
    }

    public function test_completion_email_uses_configured_app_url_for_review_link_and_host_label(): void
    {
        config()->set('app.url', 'https://staging.aplusdigitizing.com');
        config()->set('app.force_url', 'https://staging.aplusdigitizing.com');

        \DB::table('orders')->insert([
            'order_id' => 303,
            'user_id' => 3,
            'assign_to' => 0,
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'done',
            'website' => '1dollar',
            'submit_date' => '2026-03-25 11:00:00',
        ]);

        $controller = app(AdminOrderDetailController::class);
        $method = new \ReflectionMethod($controller, 'completionEmailContext');
        $method->setAccessible(true);

        $context = $method->invoke(
            $controller,
            \App\Models\Order::query()->findOrFail(303),
            'order',
            '3500',
            '9.50'
        );

        $this->assertSame('staging.aplusdigitizing.com', $context['websiteAddress']);
        $this->assertSame('A Plus Digitizing', $context['companyName']);
        $this->assertSame(
            'https://staging.aplusdigitizing.com/view-order-detail.php?order_id=303',
            $context['reviewUrl']
        );

        $body = view('admin.orders.mail-complete', $context)->render();

        $this->assertStringContainsString('Your order with A Plus Digitizing has been completed.', $body);
        $this->assertStringContainsString('Kind regards,<br>A Plus Digitizing', $body);
        $this->assertStringContainsString('https://staging.aplusdigitizing.com/view-order-detail.php?order_id=303', $body);
        $this->assertStringNotContainsString('https://aplusdigitizing.com/view-order-detail.php?order_id=303', $body);
    }
}
