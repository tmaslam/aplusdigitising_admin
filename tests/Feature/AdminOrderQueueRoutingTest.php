<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\AdminNavigation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminOrderQueueRoutingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('billing');
        Schema::dropIfExists('advancepayment');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('quote_negotiations');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable()->default(0);
            $table->string('order_type', 30)->nullable()->default('order');
            $table->string('status', 30)->nullable();
            $table->string('design_name', 255)->nullable();
            $table->string('subject', 255)->nullable();
            $table->string('order_num', 100)->nullable();
            $table->string('total_amount', 255)->nullable();
            $table->string('website', 30)->nullable();
            $table->string('submit_date', 30)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->integer('is_paid')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('advancepayment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('status')->default(0);
        });

        Schema::create('attach_files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('file_source', 50)->nullable();
            $table->string('file_name')->nullable();
            $table->string('file_name_with_date')->nullable();
            $table->string('file_name_with_order_id')->nullable();
            $table->string('date_added', 30)->nullable();
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->text('comments')->nullable();
            $table->string('source_page', 50)->nullable();
            $table->string('comment_source', 50)->nullable();
            $table->string('date_added', 30)->nullable();
            $table->string('date_modified', 30)->nullable();
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

        DB::table('users')->insert([
            'user_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'admin',
            'user_email' => 'admin@example.com',
            'is_active' => 1,
        ]);

        DB::table('users')->insert([
            'user_id' => 101,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-one',
            'user_email' => 'customer@example.com',
            'first_name' => 'Customer',
            'last_name' => 'One',
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 777,
            'user_id' => 101,
            'assign_to' => 0,
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Queue Test Order',
            'subject' => 'Queue Test Order',
            'order_num' => 'ADM-777',
            'total_amount' => '5.00',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 10:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);
    }

    public function test_modern_queue_route_renders_orders_page(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/new-orders');

        $response->assertOk();
        $response->assertSee('New Orders');
        $response->assertSee('Queue Test Order');
    }

    public function test_legacy_queue_link_redirects_to_modern_queue_route(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/new-orders.php?txt_orderid=777');

        $response->assertRedirect('/v/orders/new-orders?txt_orderid=777');
    }

    public function test_legacy_detail_link_redirects_to_modern_detail_route(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/view-order-detail-2.php?oid=777&page=order&back=new-orders');

        $response->assertRedirect('/v/orders/777/detail/order?back=new-orders');
    }

    public function test_modern_queue_route_can_export_csv(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/new-orders?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Order ID', $response->streamedContent());
        $this->assertStringContainsString('Queue Test Order', $response->streamedContent());
    }

    public function test_completed_quotes_queue_includes_customer_rejected_quotes(): void
    {
        DB::table('orders')->insert([
            'order_id' => 778,
            'user_id' => 101,
            'assign_to' => 0,
            'order_type' => 'quote',
            'status' => 'disapproved',
            'design_name' => 'Negotiated Quote',
            'subject' => 'Negotiated Quote',
            'order_num' => 'Q-778',
            'total_amount' => '9.50',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 11:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/completed-quotes');

        $response->assertOk();
        $response->assertSee('Negotiated Quote');
        $response->assertSee('disapproved');
    }

    public function test_assigned_and_completed_quotes_show_delete_action(): void
    {
        DB::table('orders')->insert([
            'order_id' => 780,
            'user_id' => 101,
            'assign_to' => 55,
            'order_type' => 'quote',
            'status' => 'Underprocess',
            'design_name' => 'Assigned Quote Delete',
            'subject' => 'Assigned Quote Delete',
            'order_num' => 'Q-780',
            'total_amount' => '12.00',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 12:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);

        DB::table('orders')->insert([
            'order_id' => 781,
            'user_id' => 101,
            'assign_to' => 55,
            'order_type' => 'quote',
            'status' => 'done',
            'design_name' => 'Completed Quote Delete',
            'subject' => 'Completed Quote Delete',
            'order_num' => 'Q-781',
            'total_amount' => '12.00',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 13:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);

        $assignedResponse = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/assigned-quotes');

        $assignedResponse->assertOk();
        $assignedResponse->assertSee('Assigned Quote Delete');
        $assignedResponse->assertSee('/v/orders/780/delete', false);

        $completedResponse = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/completed-quotes');

        $completedResponse->assertOk();
        $completedResponse->assertSee('Completed Quote Delete');
        $completedResponse->assertSee('/v/orders/781/delete', false);
    }

    public function test_quote_negotiations_queue_surfaces_quotes_waiting_on_admin_review(): void
    {
        DB::table('orders')->insert([
            'order_id' => 779,
            'user_id' => 101,
            'assign_to' => 0,
            'order_type' => 'quote',
            'status' => 'disapproved',
            'design_name' => 'Negotiation Needed',
            'subject' => 'Negotiation Needed',
            'order_num' => 'Q-779',
            'total_amount' => '15.00',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 12:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);

        DB::table('quote_negotiations')->insert([
            'order_id' => 779,
            'customer_user_id' => 101,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'price_high',
            'customer_reason_text' => 'Need lower price.',
            'customer_target_amount' => 12.00,
            'quoted_amount' => 15.00,
            'created_at' => '2026-03-28 12:05:00',
            'updated_at' => '2026-03-28 12:05:00',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/quote-negotiations');

        $response->assertOk();
        $response->assertSee('Quote Negotiations');
        $response->assertSee('Negotiation Needed');
        $response->assertSee('Awaiting Admin Review');
        $response->assertDontSee('Past Due');
    }

    public function test_converted_quote_leaves_quote_queue_and_enters_order_queue(): void
    {
        DB::table('orders')->insert([
            'order_id' => 782,
            'user_id' => 101,
            'assign_to' => 0,
            'order_type' => 'quote',
            'status' => 'Underprocess',
            'design_name' => 'Converted Queue Item',
            'subject' => 'Converted Queue Item',
            'order_num' => 'Q-782',
            'total_amount' => '12.00',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 14:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);

        $before = AdminNavigation::counts();

        $this->assertSame(1, $before['new_quotes']);
        $this->assertSame(1, $before['new_orders']);

        $quotesResponse = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/new-quotes');

        $quotesResponse->assertOk();
        $quotesResponse->assertSee('Converted Queue Item');

        DB::table('orders')->where('order_id', 782)->update([
            'order_type' => 'order',
            'status' => 'Underprocess',
            'assign_to' => 0,
        ]);

        $quotesAfterConversion = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/new-quotes');

        $quotesAfterConversion->assertOk();
        $quotesAfterConversion->assertDontSee('Converted Queue Item');

        $ordersAfterConversion = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/new-orders');

        $ordersAfterConversion->assertOk();
        $ordersAfterConversion->assertSee('Converted Queue Item');

        $after = AdminNavigation::counts();

        $this->assertSame(0, $after['new_quotes']);
        $this->assertSame(2, $after['new_orders']);
    }

    public function test_admin_quote_conversion_route_moves_counts_from_quotes_to_orders(): void
    {
        DB::table('orders')->insert([
            'order_id' => 783,
            'user_id' => 101,
            'assign_to' => 0,
            'order_type' => 'quote',
            'status' => 'done',
            'design_name' => 'Admin Converted Quote',
            'subject' => 'Admin Converted Quote',
            'order_num' => 'Q-783',
            'total_amount' => '12.00',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 15:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);

        $before = AdminNavigation::counts();

        $this->assertSame(1, $before['completed_quotes']);
        $this->assertSame(1, $before['new_orders']);

        $response = $this->withSession(['admin_user_id' => 1])->post('/v/order-detail/convert-quote', [
            'order_id' => 783,
            'back' => 'new-orders',
        ]);

        $response
            ->assertRedirect('/v/orders/783/detail/order?back=new-orders')
            ->assertSessionHas('success', 'Quote converted to order successfully.');

        $this->assertDatabaseHas('orders', [
            'order_id' => 783,
            'order_type' => 'order',
            'status' => 'Underprocess',
            'assign_to' => 0,
        ]);

        $after = AdminNavigation::counts();

        $this->assertSame(0, $after['completed_quotes']);
        $this->assertSame(2, $after['new_orders']);
    }

    public function test_order_detail_from_quote_negotiations_highlights_negotiations_queue(): void
    {
        $redirect = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/777/detail/quote?back=quote-negotiations');

        $redirect->assertRedirect('/v/orders/777/detail/order?back=quote-negotiations');

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/777/detail/order?back=quote-negotiations');

        $response->assertOk();
        $response->assertSee('/v/orders/quote-negotiations" class="active"', false);
        $response->assertDontSee('/v/orders/new-quotes" class="active"', false);
    }

    public function test_order_detail_highlights_origin_queue_from_back_parameter(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/777/detail/order?back=designer-completed');

        $response->assertOk();
        $response->assertSee('/v/orders/designer-completed" class="active"', false);
        $response->assertDontSee('/v/orders/new-orders" class="active"', false);
    }

    public function test_order_detail_from_due_payment_highlights_due_payment_menu(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/777/detail/order?back=all-payment-due');

        $response->assertOk();
        $response->assertSee('/v/all-payment-due.php" class="active"', false);
        $response->assertDontSee('/v/orders/new-orders" class="active"', false);
        $response->assertSee('>Back to Due Payment<', false);
        $response->assertSee('href="http://localhost/v/all-payment-due.php"', false);
        $response->assertDontSee('>Assign Workflow<', false);
    }

    public function test_order_detail_from_received_payment_highlights_received_payment_menu(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/777/detail/order?back=payment-recieved');

        $response->assertOk();
        $response->assertSee('/v/payment-recieved.php" class="active"', false);
        $response->assertDontSee('/v/orders/new-orders" class="active"', false);
        $response->assertSee('>Back to Received Payment<', false);
        $response->assertSee('href="http://localhost/v/payment-recieved.php"', false);
        $response->assertDontSee('>Assign Workflow<', false);
    }

    public function test_order_detail_from_payment_due_report_highlights_report_menu(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/777/detail/order?back=payment-due-report');

        $response->assertOk();
        $response->assertSee('/v/payment-due-report.php" class="active"', false);
        $response->assertDontSee('/v/orders/new-orders" class="active"', false);
        $response->assertSee('>Back to Payment Due<', false);
        $response->assertSee('href="http://localhost/v/payment-due-report.php"', false);
        $response->assertDontSee('>Assign Workflow<', false);
    }

    public function test_order_detail_from_payment_received_report_highlights_report_menu(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/orders/777/detail/order?back=payment-recieved-report');

        $response->assertOk();
        $response->assertSee('/v/payment-recieved-report.php" class="active"', false);
        $response->assertDontSee('/v/orders/new-orders" class="active"', false);
        $response->assertSee('>Back to Payment Received<', false);
        $response->assertSee('href="http://localhost/v/payment-recieved-report.php"', false);
        $response->assertDontSee('>Assign Workflow<', false);
    }
}
