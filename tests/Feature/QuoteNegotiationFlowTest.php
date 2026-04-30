<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Order;
use App\Models\QuoteNegotiation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuoteNegotiationFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('system_email_templates');
        Schema::dropIfExists('quote_negotiations');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('attach_files');
        Schema::dropIfExists('advancepayment');
        Schema::dropIfExists('orders');
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
            $table->string('real_user', 10)->nullable();
            $table->string('customer_approval_limit')->nullable();
            $table->string('single_approval_limit')->nullable();
            $table->string('customer_pending_order_limit')->nullable();
            $table->string('topup')->nullable();
            $table->string('urgent_fee')->nullable();
            $table->string('normal_fee')->nullable();
            $table->string('middle_fee')->nullable();
            $table->integer('is_active')->default(1);
            $table->dateTime('end_date')->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_num')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('design_name')->nullable();
            $table->string('subject')->nullable();
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
            $table->text('comments1')->nullable();
            $table->text('comments2')->nullable();
            $table->unsignedTinyInteger('advance_pay')->nullable();
            $table->string('sent')->nullable();
            $table->string('working')->nullable();
            $table->unsignedTinyInteger('notes_by_user')->nullable();
            $table->unsignedTinyInteger('notes_by_admin')->nullable();
            $table->string('deleted_by')->nullable();
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
            $table->string('trandtime')->nullable();
            $table->string('comments')->nullable();
            $table->string('approve_date')->nullable();
            $table->decimal('amount_decimal', 12, 2)->default(0);
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
            $table->string('deleted_by')->nullable();
            $table->dateTime('end_date')->nullable();
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

        Schema::create('advancepayment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->integer('status')->default(0);
        });

        Schema::create('quote_negotiations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('customer_user_id')->nullable();
            $table->string('legacy_website', 30)->nullable();
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
            'user_name' => 'main-admin',
            'user_email' => 'admin@example.com',
            'is_active' => 1,
        ]);

        DB::table('users')->insert([
            'user_id' => 100,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-one',
            'user_email' => 'customer@example.com',
            'first_name' => 'Customer',
            'last_name' => 'One',
            'real_user' => '1',
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 7001,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'quote',
            'status' => 'done',
            'design_name' => 'Negotiation Quote',
            'total_amount' => '25.00',
            'stitches_price' => '25.00',
            'submit_date' => '2026-04-01 10:00:00',
            'completion_date' => '2026-04-02 12:00:00',
            'is_active' => 1,
        ]);

        Mail::fake();
    }

    private function customerSession(): array
    {
        return [
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ];
    }

    private function adminSession(): array
    {
        return [
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ];
    }

    // ─── Customer submits quote feedback (rejection with target price) ──

    public function test_customer_can_reject_quote_with_target_price(): void
    {
        $response = $this->withSession($this->customerSession())
            ->post('/quotes/7001/feedback', [
                'reason_code' => 'pricing_too_high',
                'reason_text' => 'I need a lower price for bulk work.',
                'target_amount' => '15.00',
            ]);

        $response->assertRedirect('/view-quotes.php');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 7001,
            'status' => 'disapproved',
        ]);

        $this->assertDatabaseHas('quote_negotiations', [
            'order_id' => 7001,
            'customer_user_id' => 100,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_target_amount' => 15.00,
            'quoted_amount' => 25.00,
        ]);

        $this->assertDatabaseHas('comments', [
            'order_id' => 7001,
            'comment_source' => 'customerComments',
        ]);
    }

    public function test_customer_cannot_reject_quote_that_is_not_done(): void
    {
        DB::table('orders')->where('order_id', 7001)->update(['status' => 'Underprocess']);

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/7001/feedback', [
                'reason_code' => 'pricing_too_high',
                'reason_text' => 'Too high.',
                'target_amount' => '10.00',
            ]);

        $response->assertStatus(404);
    }

    public function test_quote_feedback_requires_target_amount(): void
    {
        $response = $this->withSession($this->customerSession())
            ->post('/quotes/7001/feedback', [
                'reason_code' => 'pricing_too_high',
                'reason_text' => 'I want it cheaper.',
            ]);

        $response->assertSessionHasErrors('target_amount');
    }

    public function test_quote_feedback_requires_positive_target_amount(): void
    {
        $response = $this->withSession($this->customerSession())
            ->post('/quotes/7001/feedback', [
                'reason_code' => 'pricing_too_high',
                'reason_text' => 'Zero price please.',
                'target_amount' => '0',
            ]);

        $response->assertSessionHasErrors('target_amount');
    }

    // ─── Admin accepts customer's requested price ───────────────────────

    public function test_admin_can_accept_customer_requested_price(): void
    {
        DB::table('quote_negotiations')->insert([
            'id' => 1,
            'order_id' => 7001,
            'customer_user_id' => 100,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_target_amount' => 15.00,
            'quoted_amount' => 25.00,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('orders')->where('order_id', 7001)->update(['status' => 'done']);

        $response = $this->withSession($this->adminSession())
            ->post('/v/order-detail/respond-quote-negotiation', [
                'order_id' => 7001,
                'negotiation_id' => 1,
                'page' => 'quote',
                'action' => 'accept',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quote_negotiations', [
            'id' => 1,
            'status' => 'accepted_by_admin',
            'admin_counter_amount' => 15.00,
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 7001,
            'order_type' => 'order',
            'total_amount' => '15.00',
            'stitches_price' => '15.00',
        ]);

        $this->assertDatabaseHas('comments', [
            'order_id' => 7001,
            'comment_source' => 'admin',
        ]);
    }

    // ─── Admin sends counter offer ──────────────────────────────────────

    public function test_admin_can_send_counter_offer(): void
    {
        DB::table('quote_negotiations')->insert([
            'id' => 2,
            'order_id' => 7001,
            'customer_user_id' => 100,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'budget_limit',
            'customer_target_amount' => 10.00,
            'quoted_amount' => 25.00,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->adminSession())
            ->post('/v/order-detail/respond-quote-negotiation', [
                'order_id' => 7001,
                'negotiation_id' => 2,
                'page' => 'quote',
                'action' => 'reject',
                'admin_counter_amount' => '18.00',
                'admin_note' => 'We can do 18 for this design.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quote_negotiations', [
            'id' => 2,
            'status' => 'counter_offered',
            'admin_counter_amount' => 18.00,
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 7001,
            'status' => 'done',
            'total_amount' => '18.00',
        ]);
    }

    // ─── Admin declines without counter ─────────────────────────────────

    public function test_admin_can_decline_request_without_counter(): void
    {
        DB::table('quote_negotiations')->insert([
            'id' => 3,
            'order_id' => 7001,
            'customer_user_id' => 100,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'other',
            'customer_target_amount' => 5.00,
            'quoted_amount' => 25.00,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->adminSession())
            ->post('/v/order-detail/respond-quote-negotiation', [
                'order_id' => 7001,
                'negotiation_id' => 3,
                'page' => 'quote',
                'action' => 'reject',
                'admin_note' => 'The price reflects complexity.',
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quote_negotiations', [
            'id' => 3,
            'status' => 'request_declined',
            'admin_counter_amount' => null,
        ]);

        $this->assertDatabaseHas('orders', [
            'order_id' => 7001,
            'status' => 'done',
            'total_amount' => '25.00',
        ]);
    }

    // ─── Guard: admin cannot respond to already-resolved negotiation ────

    public function test_admin_cannot_respond_to_already_resolved_negotiation(): void
    {
        DB::table('quote_negotiations')->insert([
            'id' => 4,
            'order_id' => 7001,
            'customer_user_id' => 100,
            'status' => 'accepted_by_admin',
            'customer_reason_code' => 'pricing_too_high',
            'customer_target_amount' => 15.00,
            'quoted_amount' => 25.00,
            'resolved_at' => now()->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->adminSession())
            ->post('/v/order-detail/respond-quote-negotiation', [
                'order_id' => 7001,
                'negotiation_id' => 4,
                'page' => 'quote',
                'action' => 'accept',
            ]);

        $response->assertStatus(404);
    }

    // ─── Guard: admin cannot respond to non-quote order type ────────────

    public function test_admin_cannot_respond_negotiation_for_regular_order(): void
    {
        DB::table('orders')->where('order_id', 7001)->update(['order_type' => 'order']);

        DB::table('quote_negotiations')->insert([
            'id' => 5,
            'order_id' => 7001,
            'customer_user_id' => 100,
            'status' => 'pending_admin_review',
            'customer_reason_code' => 'pricing_too_high',
            'customer_target_amount' => 10.00,
            'quoted_amount' => 25.00,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->withSession($this->adminSession())
            ->post('/v/order-detail/respond-quote-negotiation', [
                'order_id' => 7001,
                'negotiation_id' => 5,
                'page' => 'quote',
                'action' => 'accept',
            ]);

        $response->assertStatus(404);
    }

    // ─── Full round-trip: customer rejects → admin accepts → verify ─────

    public function test_full_negotiation_round_trip(): void
    {
        $this->withSession($this->customerSession())
            ->post('/quotes/7001/feedback', [
                'reason_code' => 'pricing_too_high',
                'reason_text' => 'My budget is 18.',
                'target_amount' => '18.00',
            ]);

        $this->assertDatabaseHas('orders', ['order_id' => 7001, 'status' => 'disapproved']);
        $negotiation = QuoteNegotiation::query()->where('order_id', 7001)->firstOrFail();
        $this->assertSame('pending_admin_review', $negotiation->status);

        DB::table('orders')->where('order_id', 7001)->update(['status' => 'done']);

        $this->withSession($this->adminSession())
            ->post('/v/order-detail/respond-quote-negotiation', [
                'order_id' => 7001,
                'negotiation_id' => $negotiation->id,
                'page' => 'quote',
                'action' => 'accept',
            ]);

        $negotiation->refresh();
        $this->assertSame('accepted_by_admin', $negotiation->status);
        $this->assertEquals(18.00, (float) $negotiation->admin_counter_amount);

        $order = Order::query()->findOrFail(7001);
        $this->assertSame('order', $order->order_type);
        $this->assertSame('18.00', $order->total_amount);
    }
}
