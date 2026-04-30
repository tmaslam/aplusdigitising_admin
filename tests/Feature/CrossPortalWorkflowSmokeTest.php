<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\AdminNavigation;
use App\Support\TeamNavigation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CrossPortalWorkflowSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('site_promotion_claims');
        Schema::dropIfExists('site_promotions');
        Schema::dropIfExists('supervisor_team_members');
        Schema::dropIfExists('advancepayment');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('quote_negotiations');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('site_domains');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_key', 30)->unique();
            $table->string('slug', 100)->nullable();
            $table->string('name', 150)->nullable();
            $table->string('brand_name', 150)->nullable();
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

        Schema::create('site_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('host')->unique();
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

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
            $table->string('payment_terms')->nullable();
            $table->string('customer_approval_limit')->nullable();
            $table->string('single_approval_limit')->nullable();
            $table->string('customer_pending_order_limit')->nullable();
            $table->string('register_by')->nullable();
            $table->string('user_term')->nullable();
            $table->string('exist_customer')->nullable();
            $table->string('real_user')->nullable();
            $table->string('topup')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date')->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_num')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable()->default(0);
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('order_status')->nullable();
            $table->string('status', 30)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('design_name')->nullable();
            $table->string('subject')->nullable();
            $table->string('order_num_legacy')->nullable();
            $table->string('turn_around_time', 30)->nullable();
            $table->string('format')->nullable();
            $table->string('submit_date')->nullable();
            $table->string('completion_date')->nullable();
            $table->string('assigned_date')->nullable();
            $table->string('vender_complete_date')->nullable();
            $table->string('modified_date')->nullable();
            $table->string('working')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('stitches_price')->nullable();
            $table->string('stitches')->nullable();
            $table->unsignedTinyInteger('advance_pay')->nullable();
            $table->string('sent')->nullable();
            $table->unsignedTinyInteger('del_attachment')->nullable();
            $table->unsignedTinyInteger('notes_by_user')->nullable();
            $table->unsignedTinyInteger('notes_by_admin')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date')->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->string('amount')->nullable();
            $table->string('earned_amount')->nullable();
            $table->string('approve_date')->nullable();
            $table->string('trandtime')->nullable();
            $table->string('transid')->nullable();
            $table->string('comments')->nullable();
            $table->string('payer_id')->nullable();
            $table->integer('is_paid')->default(0);
            $table->integer('is_advance')->default(0);
            $table->string('end_date')->nullable();
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

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('file_source', 50)->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_name_with_date')->nullable();
            $table->string('file_name_with_order_id')->nullable();
            $table->string('date_added')->nullable();
        });

        Schema::create('quote_negotiations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('customer_user_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->decimal('customer_target_amount', 10, 2)->nullable();
            $table->decimal('quoted_amount', 10, 2)->nullable();
            $table->decimal('admin_counter_amount', 10, 2)->nullable();
            $table->text('admin_note')->nullable();
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
        });

        Schema::create('advancepayment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('status')->default(0);
            $table->string('advance_pay')->nullable();
        });

        Schema::create('supervisor_team_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supervisor_user_id');
            $table->unsignedBigInteger('member_user_id');
            $table->string('date_added')->nullable();
            $table->string('end_date')->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('site_promotions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('promotion_name')->nullable();
            $table->string('promotion_code')->nullable();
            $table->string('work_type')->nullable();
            $table->string('discount_type')->nullable();
            $table->decimal('discount_value', 10, 2)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->text('config_json')->nullable();
            $table->integer('is_active')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('site_promotion_claims', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('site_promotion_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->integer('verification_required')->default(1);
            $table->string('verified_at')->nullable();
            $table->integer('payment_required')->default(1);
            $table->decimal('required_payment_amount', 10, 2)->nullable();
            $table->decimal('credit_amount', 10, 2)->nullable();
            $table->decimal('first_order_flat_amount', 10, 2)->nullable();
            $table->text('offer_snapshot_json')->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->string('payment_reference')->nullable();
            $table->string('paid_at')->nullable();
            $table->unsignedBigInteger('redeemed_order_id')->nullable();
            $table->string('redeemed_at')->nullable();
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
        });

        DB::table('sites')->insert([
            'id' => 1,
            'legacy_key' => '1dollar',
            'slug' => '1dollar',
            'name' => 'APlus',
            'brand_name' => 'A Plus Digitizing',
            'primary_domain' => 'localhost',
            'website_address' => 'http://localhost',
            'support_email' => 'support@example.com',
            'from_email' => 'support@example.com',
            'timezone' => 'UTC',
            'pricing_strategy' => 'customer_rate',
            'is_primary' => 1,
            'is_active' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('site_domains')->insert([
            'site_id' => 1,
            'host' => 'localhost',
            'is_primary' => 1,
            'is_active' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('users')->insert([
            [
                'user_id' => 10,
                'site_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'admin-user',
                'user_email' => 'admin@example.com',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'company' => null,
                'payment_terms' => null,
                'customer_approval_limit' => null,
                'single_approval_limit' => null,
                'customer_pending_order_limit' => null,
                'real_user' => '1',
                'register_by' => null,
                'user_term' => null,
                'exist_customer' => null,
                'topup' => null,
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 20,
                'site_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_TEAM,
                'user_name' => 'digitizer-one',
                'user_email' => 'team@example.com',
                'first_name' => 'Digitizer',
                'last_name' => 'One',
                'company' => null,
                'payment_terms' => null,
                'customer_approval_limit' => null,
                'single_approval_limit' => null,
                'customer_pending_order_limit' => null,
                'register_by' => 'supervisor-one',
                'user_term' => null,
                'exist_customer' => null,
                'real_user' => '1',
                'topup' => null,
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 21,
                'site_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_SUPERVISOR,
                'user_name' => 'supervisor-one',
                'user_email' => 'supervisor@example.com',
                'first_name' => 'Supervisor',
                'last_name' => 'One',
                'company' => null,
                'payment_terms' => null,
                'customer_approval_limit' => null,
                'single_approval_limit' => null,
                'customer_pending_order_limit' => null,
                'real_user' => '1',
                'register_by' => null,
                'user_term' => null,
                'exist_customer' => null,
                'topup' => null,
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 30,
                'site_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'One',
                'company' => null,
                'payment_terms' => '0',
                'customer_approval_limit' => '0',
                'single_approval_limit' => '0',
                'customer_pending_order_limit' => '5',
                'register_by' => null,
                'user_term' => null,
                'exist_customer' => null,
                'real_user' => '1',
                'topup' => null,
                'is_active' => 1,
                'end_date' => null,
            ],
        ]);

        DB::table('supervisor_team_members')->insert([
            'supervisor_user_id' => 21,
            'member_user_id' => 20,
            'date_added' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
            'deleted_by' => null,
        ]);

        DB::table('orders')->insert([
            'order_id' => 9800,
            'user_id' => 30,
            'order_num' => '1001',
            'assign_to' => 20,
            'website' => '1dollar',
            'order_type' => 'order',
            'order_status' => '',
            'status' => 'Underprocess',
            'type' => 'digitizing',
            'design_name' => 'Cross Portal Order',
            'subject' => 'Cross Portal Order',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
            'submit_date' => '2026-04-16 09:00:00',
            'completion_date' => '2026-04-17 09:00:00',
            'assigned_date' => '2026-04-16 09:05:00',
            'vender_complete_date' => null,
            'modified_date' => '2026-04-16 09:00:00',
            'working' => '',
            'total_amount' => '0.00',
            'stitches_price' => '0.00',
            'stitches' => '0',
            'advance_pay' => 0,
            'sent' => 'Normal',
            'del_attachment' => 0,
            'notes_by_user' => 0,
            'notes_by_admin' => 0,
            'is_active' => 1,
            'end_date' => null,
        ]);

        DB::table('attach_files')->insert([
            'order_id' => 9800,
            'file_source' => 'sewout',
            'file_name' => 'ready-proof.dst',
            'file_name_with_date' => 'ready-proof.dst',
            'file_name_with_order_id' => '(9800) ready-proof.dst',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_single_order_moves_cleanly_across_customer_team_supervisor_admin_and_back_to_customer(): void
    {
        $customerOrders = $this->withSession($this->customerSession())
            ->get('/view-orders.php');

        $customerOrders->assertOk();
        $customerOrders->assertSee('Cross Portal Order');

        $this->assertSame(1, AdminNavigation::counts()['designer_orders']);

        $teamNewOrders = $this->withSession($this->teamSession())
            ->get('/team/queues/new-orders');

        $teamNewOrders->assertOk();
        $teamNewOrders->assertSee('30-9800');
        $this->assertSame(1, TeamNavigation::counts(20, AdminUser::TYPE_TEAM)['new_orders']);

        $this->withSession($this->teamSession())
            ->post('/team/orders/9800/working', [
                'queue' => 'working-orders',
            ])
            ->assertRedirect('/team/queues/working-orders');

        $this->assertNotNull(DB::table('orders')->where('order_id', 9800)->value('working'));

        $teamWorkingOrders = $this->withSession($this->teamSession())
            ->get('/team/queues/working-orders');

        $teamWorkingOrders->assertOk();
        $teamWorkingOrders->assertSee('30-9800');

        $this->withSession($this->teamSession())
            ->post('/team/order-detail/complete', [
                'order_id' => 9800,
                'mode' => 'order',
                'queue' => 'working-orders',
                'stitches' => '4800',
            ])
            ->assertRedirect('/team/queues/working-orders');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9800,
            'status' => 'Ready',
            'stitches' => '4800',
        ]);

        $this->assertSame(1, TeamNavigation::counts(21, AdminUser::TYPE_SUPERVISOR)['ready_review']);

        $supervisorQueue = $this->withSession($this->supervisorSession())
            ->get('/team/review-queue.php');

        $supervisorQueue->assertOk();
        $supervisorQueue->assertSee('Cross Portal Order');

        $this->withSession($this->supervisorSession())
            ->post('/team/review-order.php', [
                'order_id' => 9800,
                'review_note' => 'Ready for admin review.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'order_id' => 9800,
            'comment_source' => 'supervisorReview',
            'source_page' => 'supervisorReview',
            'comments' => 'Ready for admin review.',
        ]);

        $this->assertSame(1, AdminNavigation::counts()['designer_completed_orders']);

        $adminQueue = $this->withSession($this->adminSession())
            ->get('/v/orders/designer-completed');

        $adminQueue->assertOk();
        $adminQueue->assertSee('Cross Portal Order');

        $this->withSession($this->adminSession())
            ->post('/v/order-detail/complete', [
                'order_id' => 9800,
                'page' => 'order',
                'back' => 'designer-completed',
                'stitches' => '4800',
                'stamount' => '12.00',
                'ddlStatus' => 'done',
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9800,
            'status' => 'done',
            'stitches' => '4800',
            'total_amount' => '12.00',
        ]);

        $this->assertSame(1, AdminNavigation::counts()['approval_waiting_orders']);

        $this->withSession($this->customerSession())
            ->get('/approved-order.php?order_id=9800')
            ->assertRedirect('/view-billing.php');

        $this->assertDatabaseHas('billing', [
            'order_id' => 9800,
            'user_id' => 30,
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '12.00',
            'is_paid' => 0,
        ]);

        $this->assertSame(1, AdminNavigation::counts()['approved_orders']);
        $this->assertSame(0, AdminNavigation::counts()['approval_waiting_orders']);

        $customerBilling = $this->withSession($this->customerSession())
            ->get('/view-billing.php');

        $customerBilling->assertOk();
        $customerBilling->assertSee('Cross Portal Order');
    }

    private function customerSession(): array
    {
        return [
            'customer_user_id' => 30,
            'customer_user_name' => 'Customer One',
            'customer_site_key' => '1dollar',
        ];
    }

    private function teamSession(): array
    {
        return [
            'team_user_id' => 20,
            'team_user_name' => 'digitizer-one',
            'team_user_type_id' => AdminUser::TYPE_TEAM,
        ];
    }

    private function supervisorSession(): array
    {
        return [
            'team_user_id' => 21,
            'team_user_name' => 'supervisor-one',
            'team_user_type_id' => AdminUser::TYPE_SUPERVISOR,
        ];
    }

    private function adminSession(): array
    {
        return [
            'admin_user_id' => 10,
        ];
    }
}
