<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Order;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerOrderCancelDeleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('billing');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('attach_files');
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
            $table->integer('is_active')->default(1);
            $table->dateTime('end_date')->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
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
            $table->decimal('amount', 12, 2)->default(0);
            $table->integer('is_paid')->default(0);
            $table->dateTime('end_date')->nullable();
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

        DB::table('users')->insert([
            'user_id' => 200,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-two',
            'user_email' => 'other@example.com',
            'first_name' => 'Customer',
            'last_name' => 'Two',
            'real_user' => '1',
            'is_active' => 1,
        ]);
    }

    private function customerSession(): array
    {
        return [
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ];
    }

    // ─── Order cancellation ───────────────────────────────────────────

    public function test_customer_can_cancel_unassigned_underprocess_order(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5001,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Cancel Me',
            'assign_to' => 0,
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/orders/5001/cancel');

        $response->assertRedirect('/view-orders.php');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 5001,
            'is_active' => 0,
        ]);
        $this->assertNotNull(DB::table('orders')->where('order_id', 5001)->value('end_date'));
    }

    public function test_customer_cannot_cancel_assigned_order(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5002,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Assigned Order',
            'assign_to' => 10,
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/orders/5002/cancel');

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'order_id' => 5002,
            'is_active' => 1,
        ]);
    }

    public function test_customer_cannot_cancel_order_with_billing(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5003,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Billed Order',
            'assign_to' => 0,
            'is_active' => 1,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 6001,
            'order_id' => 5003,
            'user_id' => 100,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => 10.00,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/orders/5003/cancel');

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'order_id' => 5003,
            'is_active' => 1,
        ]);
    }

    public function test_customer_cannot_cancel_completed_order(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5004,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'done',
            'design_name' => 'Done Order',
            'assign_to' => 0,
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/orders/5004/cancel');

        $response->assertStatus(404);
    }

    public function test_customer_cannot_cancel_another_customers_order(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5005,
            'user_id' => 200,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Other Customer Order',
            'assign_to' => 0,
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/orders/5005/cancel');

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'order_id' => 5005,
            'is_active' => 1,
        ]);
    }

    public function test_cancel_sets_end_date_and_marks_inactive(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5006,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'vector',
            'status' => 'Underprocess',
            'design_name' => 'Vector Cancel',
            'assign_to' => 0,
            'is_active' => 1,
        ]);

        DB::table('comments')->insert([
            'order_id' => 5006,
            'comments' => 'Test note',
            'source_page' => 'customerComments',
            'comment_source' => 'customerComments',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);

        $this->withSession($this->customerSession())->post('/orders/5006/cancel');

        $this->assertDatabaseHas('orders', [
            'order_id' => 5006,
            'is_active' => 0,
        ]);
        $this->assertNotNull(DB::table('orders')->where('order_id', 5006)->value('end_date'));
        $this->assertNotNull(DB::table('comments')->where('order_id', 5006)->value('end_date'));
    }

    // ─── Quote deletion ───────────────────────────────────────────────

    public function test_customer_can_delete_own_quote(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5010,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'quote',
            'status' => 'Underprocess',
            'design_name' => 'Delete Me Quote',
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/5010/delete');

        $response->assertRedirect('/view-quotes.php');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'order_id' => 5010,
            'is_active' => 0,
        ]);
    }

    public function test_customer_can_delete_digitzing_quote(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5011,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'digitzing',
            'status' => 'done',
            'design_name' => 'Digitzing Quote',
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/5011/delete');

        $response->assertRedirect('/view-quotes.php');

        $this->assertDatabaseHas('orders', [
            'order_id' => 5011,
            'is_active' => 0,
        ]);
    }

    public function test_customer_can_delete_vector_quote(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5012,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'q-vector',
            'status' => 'Underprocess',
            'design_name' => 'Vector Quote',
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/5012/delete');

        $response->assertRedirect('/view-quotes.php');

        $this->assertDatabaseHas('orders', [
            'order_id' => 5012,
            'is_active' => 0,
        ]);
    }

    public function test_customer_cannot_delete_another_customers_quote(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5013,
            'user_id' => 200,
            'website' => '1dollar',
            'order_type' => 'quote',
            'status' => 'Underprocess',
            'design_name' => 'Other Quote',
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/5013/delete');

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'order_id' => 5013,
            'is_active' => 1,
        ]);
    }

    public function test_customer_cannot_delete_regular_order_via_quote_route(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5014,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Regular Order',
            'is_active' => 1,
        ]);

        $response = $this->withSession($this->customerSession())
            ->post('/quotes/5014/delete');

        $response->assertStatus(404);

        $this->assertDatabaseHas('orders', [
            'order_id' => 5014,
            'is_active' => 1,
        ]);
    }

    public function test_guest_cannot_cancel_order(): void
    {
        DB::table('orders')->insert([
            'order_id' => 5015,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'design_name' => 'Guest Test',
            'assign_to' => 0,
            'is_active' => 1,
        ]);

        $response = $this->post('/orders/5015/cancel');

        $response->assertRedirect('/login.php');

        $this->assertDatabaseHas('orders', [
            'order_id' => 5015,
            'is_active' => 1,
        ]);
    }
}
