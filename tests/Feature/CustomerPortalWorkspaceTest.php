<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\AdminNavigation;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerPortalWorkspaceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('quote_negotiations');
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
            $table->string('company_type')->nullable();
            $table->text('company_address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('user_city')->nullable();
            $table->string('user_country')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('user_fax')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('package_type')->nullable();
            $table->string('customer_approval_limit')->nullable();
            $table->string('single_approval_limit')->nullable();
            $table->string('customer_pending_order_limit')->nullable();
            $table->string('digitzing_format')->nullable();
            $table->string('vertor_format')->nullable();
            $table->string('end_date')->nullable();
            $table->integer('is_active')->default(1);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_num')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('order_status')->nullable();
            $table->string('status', 30)->nullable();
            $table->string('design_name')->nullable();
            $table->string('fabric_type')->nullable();
            $table->string('sew_out')->nullable();
            $table->string('width')->nullable();
            $table->string('height')->nullable();
            $table->string('measurement')->nullable();
            $table->integer('no_of_colors')->nullable();
            $table->text('color_names')->nullable();
            $table->string('appliques')->nullable();
            $table->integer('no_of_appliques')->nullable();
            $table->string('applique_colors')->nullable();
            $table->string('starting_point')->nullable();
            $table->text('comments1')->nullable();
            $table->text('comments2')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->string('assigned_date')->nullable();
            $table->string('submit_date')->nullable();
            $table->string('completion_date')->nullable();
            $table->string('modified_date')->nullable();
            $table->string('turn_around_time')->nullable();
            $table->string('format')->nullable();
            $table->string('total_amount')->nullable();
            $table->string('stitches_price')->nullable();
            $table->string('stitches')->nullable();
            $table->unsignedTinyInteger('advance_pay')->nullable();
            $table->string('subject')->nullable();
            $table->string('sent')->nullable();
            $table->string('working')->nullable();
            $table->unsignedTinyInteger('del_attachment')->nullable();
            $table->string('type')->nullable();
            $table->unsignedTinyInteger('notes_by_user')->nullable();
            $table->unsignedTinyInteger('notes_by_admin')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('is_active')->default(1);
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->string('amount')->nullable();
            $table->string('earned_amount')->nullable();
            $table->string('transid')->nullable();
            $table->string('comments')->nullable();
            $table->string('trandtime')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('payer_id')->nullable();
            $table->integer('is_advance')->default(0);
            $table->string('approve_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('is_paid')->default(0);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->text('comments')->nullable();
            $table->string('source_page')->nullable();
            $table->string('comment_source')->nullable();
            $table->string('date_added')->nullable();
            $table->string('date_modified')->nullable();
        });

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_name_with_date')->nullable();
            $table->string('file_name_with_order_id')->nullable();
            $table->string('file_source')->nullable();
            $table->string('date_added')->nullable();
        });

        Schema::create('quote_negotiations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('customer_user_id')->nullable();
            $table->string('legacy_website', 30)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('customer_reason_code')->nullable();
            $table->text('customer_reason_text')->nullable();
            $table->decimal('customer_target_amount', 12, 2)->nullable();
            $table->decimal('quoted_amount', 12, 2)->nullable();
            $table->decimal('admin_counter_amount', 12, 2)->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('resolved_by_user_id')->nullable();
            $table->string('resolved_by_name', 150)->nullable();
            $table->string('resolved_at')->nullable();
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
            'user_id' => 4366,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'workspace-user',
            'user_email' => 'workspace@example.com',
            'first_name' => 'Workspace',
            'last_name' => 'User',
            'company' => 'Workspace Studio',
            'company_type' => 'Digitizing Company',
            'company_address' => '123 Main Street',
            'zip_code' => '48001',
            'user_city' => 'Detroit',
            'user_country' => 'United States',
            'user_phone' => '1234567890',
            'user_fax' => '',
            'contact_person' => 'Workspace User',
            'payment_terms' => '0',
            'package_type' => 'Standard',
            'customer_approval_limit' => '15',
            'single_approval_limit' => '10',
            'customer_pending_order_limit' => '2',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            [
                'order_id' => 9001,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'done',
                'design_name' => 'Awaiting Approval Design',
                'submit_date' => '2026-03-29 10:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '15.00',
                'stitches_price' => '15.00',
                'advance_pay' => 0,
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'order_id' => 9002,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'Underprocess',
                'design_name' => 'Production Design',
                'submit_date' => '2026-03-29 11:00:00',
                'turn_around_time' => 'Priority',
                'format' => 'DST',
                'total_amount' => '20.00',
                'stitches_price' => '20.00',
                'advance_pay' => 0,
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'order_id' => 9003,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'quote',
                'status' => 'done',
                'design_name' => 'Quote Ready Design',
                'submit_date' => '2026-03-29 12:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'PDF',
                'total_amount' => '8.00',
                'stitches_price' => '8.00',
                'advance_pay' => 0,
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        DB::table('billing')->insert([
            'bill_id' => 4001,
            'order_id' => 9001,
            'user_id' => 4366,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '15.00',
            'approve_date' => '2026-03-29 14:00:00',
            'end_date' => null,
            'is_paid' => 0,
        ]);

        DB::table('attach_files')->insert([
            'id' => 7001,
            'order_id' => 9001,
            'file_name' => 'preview-image.jpg',
            'file_name_with_date' => 'preview-image-20260330.jpg',
            'file_source' => 'order',
            'date_added' => '2026-03-30 08:00:00',
        ]);

        DB::table('orders')->insert([
            'order_id' => 9010,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'approved',
            'design_name' => 'Zero Dollar Welcome Order',
            'submit_date' => '2026-03-29 15:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
            'total_amount' => '0.00',
            'stitches_price' => '0.00',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 4010,
            'order_id' => 9010,
            'user_id' => 4366,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '0.00',
            'approve_date' => '2026-03-29 16:00:00',
            'end_date' => null,
            'is_paid' => 0,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 4011,
            'order_id' => 9002,
            'user_id' => 4366,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'yes',
            'amount' => '15.00',
            'earned_amount' => '15.00',
            'transid' => 'OLD-YEAR-4011',
            'comments' => 'Historical invoice',
            'trandtime' => '2025-12-28 08:00:00',
            'site_id' => 1,
            'payer_id' => 'payer-old-year',
            'is_advance' => 0,
            'approve_date' => '2025-12-28 08:00:00',
            'end_date' => null,
            'is_paid' => 1,
        ]);
    }

    private function customerSession(): array
    {
        return [
            'customer_user_id' => 4366,
            'customer_user_name' => 'Workspace User',
            'customer_site_key' => '1dollar',
        ];
    }

    public function test_dashboard_surfaces_modern_workspace_actions_and_recent_activity(): void
    {
        $response = $this->withSession($this->customerSession())->get('/dashboard.php');

        $response
            ->assertOk()
            ->assertSee('Our Services')
            ->assertSee('My Orders')
            ->assertSee('My Quotes')
            ->assertSee('Payment Due')
            ->assertSee('Paid Orders')
            ->assertSee('Quick Actions')
            ->assertSee('Place Vector Order')
            ->assertSee('Recent Activity')
            ->assertSee('Awaiting Your Approval')
            ->assertSee('Ready For Response')
            ->assertDontSee('Start Something New')
            ->assertDontSee('Workflow Focus');
    }

    public function test_orders_page_uses_customer_friendly_status_language(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9910,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'assign_to' => 0,
            'design_name' => 'Fresh Intake Order',
            'submit_date' => '2026-04-01 09:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
        ]);

        $response = $this->withSession($this->customerSession())->get('/view-orders.php');

        $response
            ->assertOk()
            ->assertSee('Action Needed')
            ->assertSee('New')
            ->assertSee('Awaiting Your Approval')
            ->assertSee('In Production');
    }

    public function test_customer_orders_page_can_export_csv(): void
    {
        $response = $this->withSession($this->customerSession())->get('/view-orders.php?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Order ID', $response->streamedContent());
        $this->assertStringContainsString('Awaiting Approval Design', $response->streamedContent());
    }

    public function test_customer_quotes_page_can_export_csv(): void
    {
        $response = $this->withSession($this->customerSession())->get('/view-quotes.php?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Quote ID', $response->streamedContent());
        $this->assertStringContainsString('Quote Ready Design', $response->streamedContent());
    }

    public function test_quote_detail_uses_accept_and_reject_actions_without_always_visible_price_feedback_section(): void
    {
        $response = $this->withSession($this->customerSession())->get('/view-quote-detail.php?order_id=9003');

        $response
            ->assertOk()
            ->assertSee('Quote Decision')
            ->assertSee('Accept Quote')
            ->assertSee('Reject Quote')
            ->assertSee('Target Amount')
            ->assertDontSee('Price Feedback');
    }

    public function test_quote_rejection_records_feedback_and_marks_quote_disapproved(): void
    {
        config()->set('mail.admin_alert_address', 'admin@example.com');
        Mail::spy();

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/9003/feedback', [
                'reason_code' => 'pricing_too_high',
                'reason_text' => 'This quote is above my budget.',
                'target_amount' => '6.50',
            ]);

        $response
            ->assertRedirect('/view-quotes.php')
            ->assertSessionHas('success', 'Your quote feedback has been sent for admin review.');

        $this->assertDatabaseHas('quote_negotiations', [
            'order_id' => 9003,
            'customer_user_id' => 4366,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 9003,
            'status' => 'disapproved',
        ]);

        $this->assertAdminAlertSent('Customer Submitted Quote Feedback');
    }

    public function test_quote_waiting_on_admin_review_does_not_show_accept_action(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9004,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'quote',
            'status' => 'disapproved',
            'design_name' => 'Pending Review Quote',
            'submit_date' => '2026-03-29 12:30:00',
            'turn_around_time' => 'Standard',
            'format' => 'PDF',
            'total_amount' => '8.00',
            'stitches_price' => '8.00',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('quote_negotiations')->insert([
            'order_id' => 9004,
            'customer_user_id' => 4366,
            'legacy_website' => '1dollar',
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Please review this request.',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 8.00,
            'created_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->customerSession())->get('/view-quote-detail.php?order_id=9004');

        $response
            ->assertOk()
            ->assertDontSee('Accept Quote')
            ->assertSee('Quote Response Sent');
    }

    public function test_customer_quote_detail_shows_admin_counter_offer_response(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9051,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'quote',
            'status' => 'done',
            'design_name' => 'Counter Offer Quote',
            'submit_date' => '2026-03-29 13:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
            'total_amount' => '8.00',
            'stitches_price' => '8.00',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('quote_negotiations')->insert([
            'order_id' => 9051,
            'customer_user_id' => 4366,
            'legacy_website' => '1dollar',
            'status' => 'counter_offered',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Can you do better?',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 9.50,
            'admin_counter_amount' => 8.00,
            'admin_note' => 'We can meet you part way.',
            'resolved_by_user_id' => 1,
            'resolved_by_name' => 'admin',
            'resolved_at' => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->customerSession())->get('/view-quote-detail.php?order_id=9051');

        $response
            ->assertOk()
            ->assertSee('Revised Quote Available')
            ->assertSee('Revised price: $8.00.')
            ->assertSee('Admin note: We can meet you part way.')
            ->assertSee('Accept Quote');
    }

    public function test_customer_quote_detail_shows_size_customer_comments_and_latest_customer_quote_request(): void
    {
        DB::table('orders')
            ->where('order_id', 9003)
            ->update([
                'width' => '3.5',
                'height' => '2.25',
                'measurement' => 'inch',
                'no_of_colors' => 5,
                'appliques' => 'yes',
                'completion_date' => '2026-03-18 14:00:00',
                'comments1' => 'Please keep the underlay light.',
                'comments2' => 'Match the sample colors closely.',
            ]);

        DB::table('comments')->insert([
            'order_id' => 9003,
            'comments' => 'Please convert this quickly.',
            'source_page' => 'customerComments',
            'comment_source' => 'customerComments',
            'date_added' => now()->subMinute()->format('Y-m-d H:i:s'),
            'date_modified' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('quote_negotiations')->insert([
            'order_id' => 9003,
            'customer_user_id' => 4366,
            'legacy_website' => '1dollar',
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Need this closer to my target.',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 8.00,
            'created_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->customerSession())->get('/view-quote-detail.php?order_id=9003');

        $response
            ->assertOk()
            ->assertSee('Size')
            ->assertSee('3.5 x 2.25 inch')
            ->assertSee('Colors')
            ->assertSee('5')
            ->assertSee('Appliques')
            ->assertSee('yes')
            ->assertSee('Completion Date')
            ->assertSee('2026-03-18 14:00:00')
            ->assertSee('Your Comments')
            ->assertSee('Please keep the underlay light.')
            ->assertSee('Match the sample colors closely.')
            ->assertSee('Please convert this quickly.')
            ->assertSee('Your Latest Quote Request')
            ->assertSee('Requested price: $6.50.')
            ->assertSee('Your note: Need this closer to my target.');

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertLessThan(
            strpos($content, '<dt>Quoted Price</dt>'),
            strpos($content, '<dt>Format</dt>')
        );
    }

    public function test_customer_quote_detail_redirects_to_order_detail_after_quote_is_converted(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9052,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Converted From Quote',
            'submit_date' => '2026-03-29 13:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->get('/view-quote-detail.php?order_id=9052&origin=quotes');

        $response->assertRedirect('/view-order-detail.php?order_id=9052&origin=orders');
    }

    public function test_converted_quote_completed_by_admin_still_shows_on_customer_orders_page(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9053,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'done',
            'design_name' => 'Converted Completed Order',
            'submit_date' => '2026-03-29 13:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
            'total_amount' => '6.50',
            'stitches_price' => '6.50',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        $ordersResponse = $this->withSession($this->customerSession())->get('/view-orders.php');
        $ordersResponse->assertOk();
        $ordersResponse->assertSee('Converted Completed Order');

        $quotesResponse = $this->withSession($this->customerSession())->get('/view-quotes.php');
        $quotesResponse->assertOk();
        $quotesResponse->assertDontSee('Converted Completed Order');
    }

    public function test_negotiated_quote_conversion_stays_visible_in_orders_when_pending_limit_runs(): void
    {
        DB::table('users')->insert([
            'user_id' => 5500,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'converted-order-user',
            'user_email' => 'converted-order@example.com',
            'first_name' => 'Converted',
            'last_name' => 'Order',
            'payment_terms' => '0',
            'customer_approval_limit' => '0',
            'single_approval_limit' => '25',
            'customer_pending_order_limit' => '1',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            [
                'order_id' => 9500,
                'user_id' => 5500,
                'website' => '1dollar',
                'order_type' => 'order',
                'type' => 'digitizing',
                'status' => 'done',
                'design_name' => 'Negotiated Converted Order',
                'submit_date' => '2026-03-29 13:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '6.50',
                'stitches_price' => '6.50',
                'advance_pay' => 0,
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'order_id' => 9501,
                'user_id' => 5500,
                'website' => '1dollar',
                'order_type' => 'order',
                'type' => 'digitizing',
                'status' => 'done',
                'design_name' => 'Normal Approval Waiting Order',
                'submit_date' => '2026-03-29 13:30:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '9.00',
                'stitches_price' => '9.00',
                'advance_pay' => 0,
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        DB::table('quote_negotiations')->insert([
            'order_id' => 9500,
            'customer_user_id' => 5500,
            'legacy_website' => '1dollar',
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

        $response = $this->withSession([
            'customer_user_id' => 5500,
            'customer_user_name' => 'Converted Order',
            'customer_site_key' => '1dollar',
        ])->get('/view-orders.php');

        $response->assertOk();
        $response->assertSee('Negotiated Converted Order');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9500,
            'status' => 'done',
        ]);

        $this->assertDatabaseMissing('billing', [
            'order_id' => 9500,
            'approved' => 'yes',
        ]);
    }

    public function test_pending_limit_approves_oldest_completed_order_not_newly_completed_converted_order(): void
    {
        DB::table('users')->insert([
            'user_id' => 5600,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'pending-limit-user',
            'user_email' => 'pending-limit@example.com',
            'first_name' => 'Pending',
            'last_name' => 'Limit',
            'payment_terms' => '0',
            'customer_approval_limit' => '0',
            'single_approval_limit' => '25',
            'customer_pending_order_limit' => '1',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            [
                'order_id' => 9400,
                'user_id' => 5600,
                'website' => '1dollar',
                'order_type' => 'order',
                'type' => 'digitizing',
                'status' => 'done',
                'design_name' => 'Freshly Completed Converted Order',
                'submit_date' => '2026-03-20 10:00:00',
                'completion_date' => '2026-03-30 10:00:00',
                'modified_date' => '2026-03-30 10:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '6.50',
                'stitches_price' => '6.50',
                'advance_pay' => 0,
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'order_id' => 9600,
                'user_id' => 5600,
                'website' => '1dollar',
                'order_type' => 'order',
                'type' => 'digitizing',
                'status' => 'done',
                'design_name' => 'Older Approval Waiting Order',
                'submit_date' => '2026-03-29 09:00:00',
                'completion_date' => '2026-03-29 09:00:00',
                'modified_date' => '2026-03-29 09:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '9.00',
                'stitches_price' => '9.00',
                'advance_pay' => 0,
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        DB::table('quote_negotiations')->insert([
            'order_id' => 9400,
            'customer_user_id' => 5600,
            'legacy_website' => '1dollar',
            'status' => 'accepted_by_admin',
            'customer_reason_code' => 'pricing_too_high',
            'customer_reason_text' => 'Approved after negotiation.',
            'customer_target_amount' => 6.50,
            'quoted_amount' => 9.50,
            'admin_counter_amount' => 6.50,
            'resolved_by_user_id' => 1,
            'resolved_by_name' => 'admin',
            'resolved_at' => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->subMinute()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession([
            'customer_user_id' => 5600,
            'customer_user_name' => 'Pending Limit',
            'customer_site_key' => '1dollar',
        ])->get('/view-orders.php');

        $response->assertOk();
        $response->assertSee('Freshly Completed Converted Order');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9400,
            'status' => 'done',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 9600,
            'status' => 'approved',
        ]);
    }

    public function test_credit_limit_does_not_auto_approve_the_only_freshly_completed_order(): void
    {
        DB::table('users')->insert([
            'user_id' => 5700,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'fresh-credit-user',
            'user_email' => 'fresh-credit@example.com',
            'first_name' => 'Fresh',
            'last_name' => 'Credit',
            'payment_terms' => '0',
            'customer_approval_limit' => '15',
            'single_approval_limit' => '100',
            'customer_pending_order_limit' => '3',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 9700,
            'user_id' => 5700,
            'website' => '1dollar',
            'order_type' => 'vector',
            'type' => 'vector',
            'status' => 'done',
            'design_name' => 'Fresh High Value Vector Order',
            'submit_date' => '2026-03-30 12:00:00',
            'completion_date' => '2026-03-30 12:05:00',
            'modified_date' => '2026-03-30 12:05:00',
            'turn_around_time' => 'Priority',
            'format' => 'AI',
            'total_amount' => '90.00',
            'stitches_price' => '90.00',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        $response = $this->withSession([
            'customer_user_id' => 5700,
            'customer_user_name' => 'Fresh Credit',
            'customer_site_key' => '1dollar',
        ])->get('/view-orders.php');

        $response->assertOk();
        $response->assertSee('Fresh High Value Vector Order');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9700,
            'status' => 'done',
        ]);

        $this->assertDatabaseMissing('billing', [
            'order_id' => 9700,
            'approved' => 'yes',
        ]);
    }

    public function test_customer_navigation_does_not_auto_pay_non_zero_invoice_from_available_balance(): void
    {
        Schema::create('customer_credit_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('billing_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('entry_type', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference_no')->nullable();
            $table->string('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('date_added')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('deleted_by')->nullable();
        });

        DB::table('users')->insert([
            'user_id' => 5800,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'balance-user',
            'user_email' => 'balance-user@example.com',
            'first_name' => 'Balance',
            'last_name' => 'User',
            'payment_terms' => '0',
            'customer_approval_limit' => '15',
            'single_approval_limit' => '100',
            'customer_pending_order_limit' => '3',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 9800,
            'user_id' => 5800,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'approved',
            'design_name' => 'Approved Invoice With Balance',
            'submit_date' => '2026-03-30 13:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
            'total_amount' => '25.00',
            'stitches_price' => '25.00',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 9800,
            'order_id' => 9800,
            'user_id' => 5800,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '25.00',
            'approve_date' => '2026-03-30 13:05:00',
            'end_date' => null,
            'is_paid' => 0,
        ]);

        DB::table('customer_credit_ledger')->insert([
            'user_id' => 5800,
            'site_id' => 1,
            'website' => '1dollar',
            'entry_type' => 'payment',
            'amount' => 50.00,
            'reference_no' => 'TEST-BAL-5800',
            'notes' => 'Available balance for testing.',
            'created_by' => 'test',
            'date_added' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
        ]);

        $response = $this->withSession([
            'customer_user_id' => 5800,
            'customer_user_name' => 'Balance User',
            'customer_site_key' => '1dollar',
        ])->get('/dashboard.php');

        $response->assertOk();

        $this->assertDatabaseHas('billing', [
            'bill_id' => 9800,
            'payment' => 'no',
            'is_paid' => 0,
        ]);
    }

    public function test_customer_approval_uses_available_balance_when_invoice_is_fully_covered(): void
    {
        Schema::create('customer_credit_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('billing_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('entry_type', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference_no')->nullable();
            $table->string('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('date_added')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('deleted_by')->nullable();
        });

        DB::table('users')->insert([
            'user_id' => 5900,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'approval-balance-user',
            'user_email' => 'approval-balance@example.com',
            'first_name' => 'Approval',
            'last_name' => 'Balance',
            'payment_terms' => '0',
            'customer_approval_limit' => '50',
            'single_approval_limit' => '100',
            'customer_pending_order_limit' => '3',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 9900,
            'user_id' => 5900,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'done',
            'design_name' => 'Customer Approved With Balance',
            'submit_date' => '2026-03-30 14:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'DST',
            'total_amount' => '25.00',
            'stitches_price' => '25.00',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('customer_credit_ledger')->insert([
            'user_id' => 5900,
            'site_id' => 1,
            'website' => '1dollar',
            'entry_type' => 'payment',
            'amount' => 30.00,
            'reference_no' => 'TEST-BAL-5900',
            'notes' => 'Available balance for approval.',
            'created_by' => 'test',
            'date_added' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
        ]);

        $response = $this->withSession([
            'customer_user_id' => 5900,
            'customer_user_name' => 'Approval Balance',
            'customer_site_key' => '1dollar',
        ])->get('/approved-order.php?order_id=9900');

        $response->assertRedirect('/view-archive-orders.php');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9900,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('billing', [
            'order_id' => 9900,
            'payment' => 'yes',
            'is_paid' => 1,
            'transid' => 'stored-funds',
        ]);
    }

    public function test_zero_amount_invoice_is_auto_settled_and_moves_into_history(): void
    {
        Carbon::setTestNow('2026-04-03 12:00:00');

        try {
            $billingResponse = $this->withSession($this->customerSession())->get('/view-billing.php');

            $billingResponse
                ->assertOk()
                ->assertDontSee('Zero Dollar Welcome Order');

            $this->assertSame('yes', DB::table('billing')->where('bill_id', 4010)->value('payment'));
            $this->assertSame('1', (string) DB::table('billing')->where('bill_id', 4010)->value('is_paid'));
            $this->assertSame('NO-CHARGE-4010', DB::table('billing')->where('bill_id', 4010)->value('transid'));

            $archiveResponse = $this->withSession($this->customerSession())->get('/view-archive-orders.php');
            $archiveResponse
                ->assertOk()
                ->assertSee('Zero Dollar Welcome Order');

            $invoiceResponse = $this->withSession($this->customerSession())->get('/view-invoices.php');
            $invoiceResponse
                ->assertOk()
                ->assertSee('NO-CHARGE-4010')
                ->assertDontSee('OLD-YEAR-4011')
                ->assertSee('Showing 2026 invoices by default. Change the range as needed.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_invoice_history_defaults_to_last_ninety_days_early_in_the_year(): void
    {
        Carbon::setTestNow('2026-01-10 09:00:00');

        try {
            $invoiceResponse = $this->withSession($this->customerSession())->get('/view-invoices.php');

            $invoiceResponse
                ->assertOk()
                ->assertSee('NO-CHARGE-4010')
                ->assertSee('OLD-YEAR-4011')
                ->assertSee('Showing the last 90 days by default for a better new-year view. Change the range as needed.');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_paid_orders_search_and_date_filters_target_specific_history_entries(): void
    {
        DB::table('orders')->insert([
            [
                'order_id' => 9401,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Alpha Crest',
                'completion_date' => '2026-02-14 09:00:00',
                'total_amount' => '11.00',
                'stitches_price' => '11.00',
                'stitches' => '4400',
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'order_id' => 9402,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Bravo Patch',
                'completion_date' => '2026-03-22 14:30:00',
                'total_amount' => '14.00',
                'stitches_price' => '14.00',
                'stitches' => '5100',
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        DB::table('billing')->insert([
            [
                'bill_id' => 9501,
                'order_id' => 9401,
                'user_id' => 4366,
                'website' => '1dollar',
                'approved' => 'yes',
                'payment' => 'yes',
                'amount' => '11.00',
                'approve_date' => '2026-02-14 09:05:00',
                'trandtime' => '2026-02-14 09:10:00',
                'transid' => 'PAID-9401',
                'site_id' => 1,
                'is_advance' => 0,
                'end_date' => null,
                'is_paid' => 1,
            ],
            [
                'bill_id' => 9502,
                'order_id' => 9402,
                'user_id' => 4366,
                'website' => '1dollar',
                'approved' => 'yes',
                'payment' => 'yes',
                'amount' => '14.00',
                'approve_date' => '2026-03-22 14:35:00',
                'trandtime' => '2026-03-22 14:40:00',
                'transid' => 'PAID-9402',
                'site_id' => 1,
                'is_advance' => 0,
                'end_date' => null,
                'is_paid' => 1,
            ],
        ]);

        $response = $this->withSession($this->customerSession())
            ->get('/view-archive-orders.php?search=Bravo&date_from=2026-03-01&date_to=2026-03-31');

        $response->assertOk();
        $response->assertSee('Bravo Patch');
        $response->assertDontSee('Alpha Crest');
    }

    public function test_paid_orders_sort_by_completion_date_ascending_uses_oldest_first(): void
    {
        DB::table('orders')->insert([
            [
                'order_id' => 9411,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Earlier Completion',
                'completion_date' => '2026-02-01 08:00:00',
                'total_amount' => '8.00',
                'stitches_price' => '8.00',
                'stitches' => '3000',
                'end_date' => null,
                'is_active' => 1,
            ],
            [
                'order_id' => 9412,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Later Completion',
                'completion_date' => '2026-04-01 08:00:00',
                'total_amount' => '9.00',
                'stitches_price' => '9.00',
                'stitches' => '3600',
                'end_date' => null,
                'is_active' => 1,
            ],
        ]);

        DB::table('billing')->insert([
            [
                'bill_id' => 9511,
                'order_id' => 9411,
                'user_id' => 4366,
                'website' => '1dollar',
                'approved' => 'yes',
                'payment' => 'yes',
                'amount' => '8.00',
                'approve_date' => '2026-02-01 08:05:00',
                'trandtime' => '2026-02-01 08:10:00',
                'transid' => 'PAID-9411',
                'site_id' => 1,
                'is_advance' => 0,
                'end_date' => null,
                'is_paid' => 1,
            ],
            [
                'bill_id' => 9512,
                'order_id' => 9412,
                'user_id' => 4366,
                'website' => '1dollar',
                'approved' => 'yes',
                'payment' => 'yes',
                'amount' => '9.00',
                'approve_date' => '2026-04-01 08:05:00',
                'trandtime' => '2026-04-01 08:10:00',
                'transid' => 'PAID-9412',
                'site_id' => 1,
                'is_advance' => 0,
                'end_date' => null,
                'is_paid' => 1,
            ],
        ]);

        $response = $this->withSession($this->customerSession())
            ->get('/view-archive-orders.php?sort=completion_date&dir=asc');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertNotFalse($content);
        $this->assertLessThan(
            strpos($content, 'Later Completion'),
            strpos($content, 'Earlier Completion')
        );
    }

    public function test_paid_order_detail_shows_paid_status_when_opened_from_archive(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9413,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'approved',
            'design_name' => 'Archive Paid Detail',
            'completion_date' => '2026-04-05 08:00:00',
            'total_amount' => '10.00',
            'stitches_price' => '10.00',
            'stitches' => '4200',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 9513,
            'order_id' => 9413,
            'user_id' => 4366,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'yes',
            'amount' => '10.00',
            'approve_date' => '2026-04-05 08:05:00',
            'trandtime' => '2026-04-05 08:10:00',
            'transid' => 'PAID-9413',
            'site_id' => 1,
            'is_advance' => 0,
            'end_date' => null,
            'is_paid' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->get('/view-order-detail.php?order_id=9413&origin=archive');

        $response->assertOk();
        $response->assertSee('Current status: <span class="status success">Paid</span>', false);
    }

    public function test_paid_orders_page_can_export_csv(): void
    {
        $response = $this->withSession($this->customerSession())
            ->get('/view-archive-orders.php?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = $response->streamedContent();
        $this->assertStringContainsString('Order ID', $content);
        $this->assertStringContainsString('Design Name', $content);
        $this->assertStringContainsString('Completion Date', $content);
    }

    public function test_customer_portal_mobile_layout_no_longer_forces_old_table_min_width(): void
    {
        $response = $this->withSession($this->customerSession())->get('/view-orders.php');

        $response->assertOk();
        $response->assertDontSee('min-width: 620px', false);
        $response->assertSee('min-width: 100%', false);
    }

    public function test_invoice_detail_downloads_as_pdf_instead_of_html(): void
    {
        $response = $this->withSession($this->customerSession())
            ->get('/view-invoice-detail.php?transid=NO-CHARGE-4010&download=pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader('content-disposition', 'attachment; filename="aplus-digitising-invoice-NO-CHARGE-4010.pdf"');
        $this->assertStringStartsWith('%PDF-', (string) $response->getContent());
    }

    public function test_customer_can_send_completed_order_to_billing_with_legacy_billing_columns_present(): void
    {
        config()->set('mail.admin_alert_address', 'admin@example.com');
        Mail::spy();

        $response = $this->withSession($this->customerSession())
            ->post('/orders/9001/approve');

        $response
            ->assertRedirect('/view-billing.php')
            ->assertSessionHas('success', 'Your order has been approved and sent to billing.');

        $this->assertDatabaseHas('billing', [
            'order_id' => 9001,
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '15.00',
            'earned_amount' => '',
            'comments' => 'Order approved.',
            'website' => '1dollar',
            'is_paid' => 0,
            'is_advance' => 0,
        ]);

        $this->assertAdminAlertSent('Customer Approved Order');
    }

    public function test_customer_can_convert_quote_to_order_and_admin_gets_alert(): void
    {
        config()->set('mail.admin_alert_address', 'admin@example.com');
        Mail::spy();

        DB::table('users')->where('user_id', 4366)->update([
            'customer_pending_order_limit' => '5',
            'customer_approval_limit' => '50',
        ]);

        $before = AdminNavigation::counts();

        $this->assertGreaterThanOrEqual(1, $before['completed_quotes']);

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/9003/switch-to-order', [
                'response_comment' => 'Please start this as an order.',
            ]);

        $response
            ->assertRedirect('/view-quotes.php')
            ->assertSessionHas('success', 'Your quote has been converted to an order successfully.');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9003,
            'order_type' => 'order',
            'status' => 'Underprocess',
        ]);

        $after = AdminNavigation::counts();

        $this->assertSame($before['completed_quotes'] - 1, $after['completed_quotes']);
        $this->assertSame($before['new_orders'] + 1, $after['new_orders']);

        $this->assertAdminAlertSent('Customer Converted Quote To Order');
    }

    public function test_customer_can_update_order_and_admin_gets_alert(): void
    {
        config()->set('mail.admin_alert_address', 'admin@example.com');
        Mail::spy();

        DB::table('orders')->where('order_id', 9002)->update([
            'status' => 'Underprocess',
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/edit-order.php?order_id=9002', [
                'design_name' => 'Production Design Updated',
                'format' => 'PES',
                'fabric_type' => 'Beanie',
                'sew_out' => 'no',
                'width' => '3',
                'height' => '3',
                'measurement' => 'Inches',
                'no_of_colors' => '3',
                'color_names' => 'black, white, silver',
                'appliques' => 'no',
                'turn_around_time' => 'Priority',
                'comments' => 'Please update the cap layout.',
            ]);

        $response
            ->assertRedirect('/view-orders.php')
            ->assertSessionHas('success', 'Your order has been updated successfully.');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9002,
            'design_name' => 'Production Design Updated',
            'format' => 'PES',
        ]);

        $this->assertAdminAlertSent('Customer Order Updated');
    }

    public function test_customer_can_request_revision_and_admin_gets_alert(): void
    {
        config()->set('mail.admin_alert_address', 'admin@example.com');
        Mail::spy();

        $response = $this->withSession($this->customerSession())
            ->post('/disapprove-order.php?order_id=9001', [
                'subject' => 'Need a few changes',
                'comments' => 'Please adjust the lettering and center alignment.',
            ]);

        $response
            ->assertRedirect('/view-orders.php')
            ->assertSessionHas('success', 'Your edit request has been sent successfully.');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9001,
            'status' => 'disapproved',
            'subject' => 'Need a few changes',
        ]);

        $this->assertAdminAlertSent('Customer Revision Requested');
    }

    public function test_profile_page_marks_required_fields_and_uses_front_end_validation(): void
    {
        $response = $this->withSession($this->customerSession())->get('/my-profile.php');

        $response
            ->assertOk()
            ->assertSee('First Name <span class="field-meta required"', false)
            ->assertSee('Last Name <span class="field-meta required"', false)
            ->assertSee('Country <span class="field-meta required"', false)
            ->assertSee('Phone <span class="field-meta required"', false)
            ->assertSee('data-form-validation', false)
            ->assertSee('name="first_name"', false)
            ->assertSee('required', false);
    }

    public function test_profile_update_rejects_blank_required_fields(): void
    {
        $response = $this->from('/my-profile.php')
            ->withSession($this->customerSession())
            ->post('/my-profile.php', [
                'first_name' => '',
                'last_name' => '',
                'company' => 'Updated Company',
                'user_country' => '',
                'user_phone' => '',
            ]);

        $response
            ->assertRedirect('/my-profile.php')
            ->assertSessionHasErrors(['first_name', 'last_name', 'user_country', 'user_phone']);
    }

    public function test_vector_order_route_renders_customer_order_form(): void
    {
        $response = $this->withSession($this->customerSession())->get('/vector-order.php');

        $response
            ->assertOk()
            ->assertSee('New Vector Order')
            ->assertSee('Upload Files')
            ->assertSee('Priority (12 Hrs.) - $9.00 / hour');
    }

    public function test_vector_orders_do_not_appear_in_quotes_listing(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9050,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'vector',
            'status' => 'Underprocess',
            'design_name' => 'Vector Order Only',
            'submit_date' => '2026-03-29 13:00:00',
            'turn_around_time' => 'Standard',
            'format' => 'AI',
            'total_amount' => '12.00',
            'stitches_price' => '12.00',
            'advance_pay' => 0,
            'end_date' => null,
            'is_active' => 1,
        ]);

        $quotesResponse = $this->withSession($this->customerSession())->get('/view-quotes.php');
        $quotesResponse
            ->assertOk()
            ->assertDontSee('Vector Order Only');

        $ordersResponse = $this->withSession($this->customerSession())->get('/view-orders.php');
        $ordersResponse
            ->assertOk()
            ->assertSee('Vector Order Only');
    }

    public function test_digitizing_order_route_uses_updated_measurement_and_format_fields(): void
    {
        $response = $this->withSession($this->customerSession())->get('/new-order.php');

        $response
            ->assertOk()
            ->assertSee('New Digitizing Order')
            ->assertSee('Fabric/Garment Type')
            ->assertSee('Pique Polo')
            ->assertSee('Jersey')
            ->assertSee('Twil')
            ->assertSee('Fleece')
            ->assertSee('Leather')
            ->assertSee('Towel')
            ->assertSee('Canvas')
            ->assertSee('Format')
            ->assertSee('DST')
            ->assertSee('PES')
            ->assertSee('EXP')
            ->assertSee('EMB')
            ->assertSee('PXF')
            ->assertSee('NGS')
            ->assertSee('OTHER')
            ->assertSee('Measurement')
            ->assertSee('Inches')
            ->assertSee('CM')
            ->assertSee('MM')
            ->assertDontSee('Starting Point')
            ->assertDontSee('Express')
            ->assertSee('Priority (12 Hrs.) - $1.50/1k stitches, (Min. charge $9.00)')
            ->assertDontSee('placeholder="Inches, mm, cm"', false);
    }

    public function test_order_detail_uses_inline_preview_modal_trigger_instead_of_new_tab_links(): void
    {
        $response = $this->withSession($this->customerSession())->get('/view-order-detail.php?order_id=9001');

        $response
            ->assertOk()
            ->assertSee('data-preview-link', false)
            ->assertSee('data-preview-kind="image"', false)
            ->assertSee('data-preview-url="http://localhost/preview.php?attachment_id=7001"', false)
            ->assertDontSee('target="_blank"', false);
    }

    public function test_digitizing_order_can_be_submitted_successfully(): void
    {
        config()->set('mail.admin_alert_address', 'admin@example.com');
        Mail::spy();

        DB::table('users')->where('user_id', 4366)->update([
            'customer_pending_order_limit' => '5',
            'customer_approval_limit' => '50',
        ]);

        $response = $this->withSession($this->customerSession())->post('/new-order.php', [
            'design_name' => 'Order 1',
            'format' => 'EMB',
            'fabric_type' => 'Pique Polo',
            'sew_out' => 'no',
            'width' => '2',
            'height' => '2',
            'measurement' => 'Inches',
            'no_of_colors' => '2',
            'color_names' => 'color name',
            'appliques' => 'no',
            'no_of_appliques' => '11',
            'applique_colors' => 'green',
            'starting_point' => 'BottomCenter',
            'turn_around_time' => 'Standard',
            'comments' => 'Add order instruction',
            'source_files' => [UploadedFile::fake()->create('order1.dst', 100, 'application/octet-stream')],
        ]);

        $this->assertSame(302, $response->baseResponse->getStatusCode());
        $this->assertSame('http://localhost/view-orders.php', $response->baseResponse->getTargetUrl());
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success', 'Your order has been submitted successfully.');

        $this->assertDatabaseHas('orders', [
            'user_id' => 4366,
            'design_name' => 'Order 1',
            'order_type' => 'order',
            'order_status' => '',
            'sent' => 'Normal',
            'working' => '',
            'del_attachment' => 0,
            'type' => 'digitizing',
            'appliques' => 'no',
            'no_of_appliques' => 0,
            'applique_colors' => '',
        ]);

        $createdOrderId = (int) DB::table('orders')
            ->where('user_id', 4366)
            ->where('design_name', 'Order 1')
            ->value('order_id');

        $this->assertDatabaseHas('comments', [
            'order_id' => $createdOrderId,
            'source_page' => 'customerComments',
            'comment_source' => 'customerComments',
        ]);

        $this->assertMailSent('admin@example.com', 'New Digitizing Order Submitted');
        $this->assertMailSent('workspace@example.com', 'Your digitizing order has been received - A Plus Digitizing');

        $storedAttachmentPath = (string) DB::table('attach_files')
            ->where('order_id', $createdOrderId)
            ->value('file_name_with_date');

        $this->assertMatchesRegularExpression('#^\d{4}-\d{2}/#', $storedAttachmentPath);
    }

    public function test_customer_format_preference_is_remembered_for_next_order(): void
    {
        DB::table('users')->where('user_id', 4366)->update([
            'customer_pending_order_limit' => '5',
            'customer_approval_limit' => '50',
            'digitzing_format' => '',
        ]);

        $response = $this->withSession($this->customerSession())->post('/new-order.php', [
            'design_name' => 'Remember My Format',
            'format' => 'PXF',
            'fabric_type' => 'Pique Polo',
            'sew_out' => 'no',
            'width' => '2',
            'height' => '2',
            'measurement' => 'Inches',
            'no_of_colors' => '2',
            'color_names' => 'color name',
            'appliques' => 'no',
            'turn_around_time' => 'Standard',
            'comments' => 'Remember this format',
            'source_files' => [UploadedFile::fake()->create('remember.pxf', 100, 'application/octet-stream')],
        ]);

        $response->assertRedirect('http://localhost/view-orders.php');

        $this->assertSame('PXF', DB::table('users')->where('user_id', 4366)->value('digitzing_format'));

        $formResponse = $this->withSession($this->customerSession())->get('/new-order.php');
        $formResponse
            ->assertOk()
            ->assertSee('option value="PXF" selected', false);
    }

    public function test_digitizing_order_can_be_submitted_without_instructions(): void
    {
        DB::table('users')->where('user_id', 4366)->update([
            'customer_pending_order_limit' => '5',
            'customer_approval_limit' => '50',
        ]);

        $response = $this->withSession($this->customerSession())->post('/new-order.php', [
            'design_name' => 'Order Without Instructions',
            'format' => 'DST',
            'fabric_type' => 'Pique Polo',
            'sew_out' => 'no',
            'width' => '2',
            'height' => '2',
            'measurement' => 'Inches',
            'no_of_colors' => '2',
            'color_names' => 'navy, white',
            'appliques' => 'no',
            'turn_around_time' => 'Standard',
            'source_files' => [UploadedFile::fake()->create('no-instructions.dst', 100, 'application/octet-stream')],
        ]);

        $response->assertRedirect('http://localhost/view-orders.php');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'user_id' => 4366,
            'design_name' => 'Order Without Instructions',
            'comments1' => '',
        ]);

        $createdOrderId = (int) DB::table('orders')
            ->where('user_id', 4366)
            ->where('design_name', 'Order Without Instructions')
            ->value('order_id');

        $this->assertDatabaseMissing('comments', [
            'order_id' => $createdOrderId,
            'comment_source' => 'customerComments',
        ]);
    }

    public function test_digitizing_order_requires_at_least_one_source_file(): void
    {
        DB::table('users')->where('user_id', 4366)->update([
            'customer_pending_order_limit' => '5',
            'customer_approval_limit' => '50',
        ]);

        $response = $this->from('/new-order.php')
            ->withSession($this->customerSession())
            ->post('/new-order.php', [
                'design_name' => 'Missing File Order',
                'format' => 'DST',
                'fabric_type' => 'Pique Polo',
                'sew_out' => 'no',
                'width' => '2',
                'height' => '2',
                'measurement' => 'Inches',
                'no_of_colors' => '2',
                'color_names' => 'navy, white',
                'appliques' => 'no',
                'turn_around_time' => 'Standard',
            ]);

        $response
            ->assertRedirect('/new-order.php')
            ->assertSessionHasErrors(['source_files']);

        $this->assertDatabaseMissing('orders', [
            'user_id' => 4366,
            'design_name' => 'Missing File Order',
        ]);
    }

    public function test_customer_navigation_auto_approves_overdue_order_based_on_payment_terms(): void
    {
        DB::table('users')->where('user_id', 4366)->update([
            'payment_terms' => '7',
        ]);

        DB::table('orders')->insert([
            'order_id' => 9101,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'vector',
            'status' => 'done',
            'design_name' => 'Auto Approval Order',
            'submit_date' => '2026-03-20 10:00:00',
            'completion_date' => '2026-03-20 10:00:00',
            'modified_date' => '2026-03-20 10:00:00',
            'turn_around_time' => 'Priority',
            'total_amount' => '18.00',
            'stitches_price' => '18.00',
            'stitches' => '01:30',
            'advance_pay' => 0,
            'type' => 'vector',
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())->get('/dashboard.php');

        $response->assertOk();

        $this->assertDatabaseHas('orders', [
            'order_id' => 9101,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('billing', [
            'order_id' => 9101,
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '18.00',
        ]);
    }

    public function test_customer_navigation_auto_approves_oldest_done_orders_when_credit_limit_is_reached(): void
    {
        DB::table('users')->where('user_id', 4366)->update([
            'payment_terms' => '0',
            'customer_pending_order_limit' => '0',
            'customer_approval_limit' => '15',
        ]);

        DB::table('comments')->where('order_id', 9001)->delete();
        DB::table('attach_files')->where('order_id', 9001)->delete();
        DB::table('orders')->where('user_id', 4366)->delete();
        DB::table('billing')->where('user_id', 4366)->delete();

        DB::table('orders')->insert([
            [
                'order_id' => 9201,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'done',
                'design_name' => 'Credit Limit 1',
                'submit_date' => '2026-03-20 09:00:00',
                'completion_date' => '2026-03-20 09:00:00',
                'modified_date' => '2026-03-20 09:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '15.00',
                'stitches_price' => '15.00',
                'advance_pay' => 0,
                'is_active' => 1,
            ],
            [
                'order_id' => 9202,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'done',
                'design_name' => 'Credit Limit 2',
                'submit_date' => '2026-03-20 10:00:00',
                'completion_date' => '2026-03-20 10:00:00',
                'modified_date' => '2026-03-20 10:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '15.00',
                'stitches_price' => '15.00',
                'advance_pay' => 0,
                'is_active' => 1,
            ],
            [
                'order_id' => 9203,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'done',
                'design_name' => 'Credit Limit 3',
                'submit_date' => '2026-03-20 11:00:00',
                'completion_date' => '2026-03-20 11:00:00',
                'modified_date' => '2026-03-20 11:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '15.00',
                'stitches_price' => '15.00',
                'advance_pay' => 0,
                'is_active' => 1,
            ],
            [
                'order_id' => 9204,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'done',
                'design_name' => 'Credit Limit 4',
                'submit_date' => '2026-03-20 12:00:00',
                'completion_date' => '2026-03-20 12:00:00',
                'modified_date' => '2026-03-20 12:00:00',
                'turn_around_time' => 'Standard',
                'format' => 'DST',
                'total_amount' => '15.00',
                'stitches_price' => '15.00',
                'advance_pay' => 0,
                'is_active' => 1,
            ],
        ]);

        $response = $this->withSession($this->customerSession())->get('/dashboard.php');

        $response
            ->assertOk()
            ->assertSee('You have exceeded your credit limit of US$15.00. Please clear billing or contact support to continue.');

        $this->assertDatabaseHas('orders', [
            'order_id' => 9201,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('billing', [
            'order_id' => 9201,
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '15.00',
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 9202,
            'status' => 'done',
        ]);

        $newOrderPage = $this->withSession($this->customerSession())->get('/new-order.php');
        $newOrderPage
            ->assertOk()
            ->assertSee('You have exceeded your credit limit of US$15.00. Please clear billing or contact support to continue.');
    }

    public function test_customer_can_cancel_unassigned_order_from_listing(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9102,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Cancelable Order',
            'submit_date' => '2026-04-01 10:00:00',
            'turn_around_time' => 'Standard',
            'assign_to' => 0,
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())->post('/orders/9102/cancel');

        $response
            ->assertRedirect('/view-orders.php')
            ->assertSessionHas('success', 'Your order has been cancelled successfully.');

        $this->assertDatabaseMissing('orders', [
            'order_id' => 9102,
            'is_active' => 1,
        ]);
    }

    public function test_customer_can_delete_quote_from_listing(): void
    {
        DB::table('orders')->insert([
            'order_id' => 9103,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'q-vector',
            'status' => 'done',
            'design_name' => 'Delete Me Quote',
            'submit_date' => '2026-04-01 10:00:00',
            'turn_around_time' => 'Priority',
            'assign_to' => 2,
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())->post('/quotes/9103/delete');

        $response
            ->assertRedirect('/view-quotes.php')
            ->assertSessionHas('success', 'Your quote has been deleted successfully.');

        $this->assertDatabaseMissing('orders', [
            'order_id' => 9103,
            'is_active' => 1,
        ]);
    }

    public function test_customer_quote_listing_shows_delete_action_for_assigned_and_completed_quotes(): void
    {
        DB::table('orders')->insert([
            [
                'order_id' => 9104,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'quote',
                'status' => 'Underprocess',
                'design_name' => 'Assigned Quote',
                'submit_date' => '2026-04-01 11:00:00',
                'turn_around_time' => 'Standard',
                'assign_to' => 2,
                'is_active' => 1,
            ],
            [
                'order_id' => 9105,
                'user_id' => 4366,
                'website' => '1dollar',
                'order_type' => 'q-vector',
                'status' => 'done',
                'design_name' => 'Completed Quote',
                'submit_date' => '2026-04-01 12:00:00',
                'turn_around_time' => 'Priority',
                'assign_to' => 2,
                'is_active' => 1,
            ],
        ]);

        DB::table('billing')->insert([
            'bill_id' => 9105,
            'order_id' => 9105,
            'user_id' => 4366,
            'website' => '1dollar',
            'approved' => 'no',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => '9.00',
            'end_date' => null,
        ]);

        $response = $this->withSession($this->customerSession())->get('/view-quotes.php');

        $response->assertOk();
        $response->assertSee('/quotes/9104/delete', false);
        $response->assertSee('/quotes/9105/delete', false);
    }

    private function assertAdminAlertSent(string $expectedSubject): void
    {
        $this->assertMailSent('admin@example.com', $expectedSubject);
    }

    private function assertMailSent(string $recipient, string $expectedSubject): void
    {
        Mail::shouldHaveReceived('send')
            ->withArgs(function ($view, $data, $callback) use ($recipient, $expectedSubject) {
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

                return $message->subject === $expectedSubject
                    && in_array(strtolower($recipient), $message->to, true);
            });
    }
}
