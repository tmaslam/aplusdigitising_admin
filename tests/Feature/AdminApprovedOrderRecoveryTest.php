<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminApprovedOrderRecoveryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('billing');
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
            $table->string('stitches', 255)->nullable();
            $table->string('total_amount', 255)->nullable();
            $table->string('website', 30)->nullable();
            $table->string('submit_date', 30)->nullable();
            $table->string('completion_date', 30)->nullable();
            $table->string('modified_date', 30)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('amount', 255)->nullable();
            $table->string('payment', 30)->nullable();
            $table->string('approve_date', 30)->nullable();
            $table->string('transid', 255)->nullable();
            $table->string('website', 30)->nullable();
            $table->integer('is_paid')->default(0);
            $table->string('deleted_by', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        DB::table('users')->insert([
            'user_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'admin',
            'user_email' => 'admin@example.com',
            'first_name' => null,
            'last_name' => null,
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
            'status' => 'approved',
            'design_name' => 'Legacy Approved Order',
            'subject' => 'Legacy Approved Order',
            'order_num' => 'ADM-777',
            'stitches' => '4500',
            'total_amount' => '5.00',
            'website' => '1dollar',
            'submit_date' => '2026-03-28 10:00:00',
            'completion_date' => '2026-03-28 11:00:00',
            'modified_date' => '2026-03-28 11:00:00',
            'is_active' => 1,
            'end_date' => null,
        ]);
    }

    public function test_due_report_syncs_missing_billing_for_approved_orders(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/payment-due-report.php');

        $response->assertOk();
        $this->assertDatabaseHas('billing', [
            'order_id' => 777,
            'approved' => 'yes',
            'payment' => 'no',
            'website' => '1dollar',
        ]);
        $response->assertSee('101');
    }

    public function test_mark_paid_creates_or_updates_billing_even_when_missing_initially(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->from('/v/orders.php?page=Approved%20Orders')
            ->post('/v/orders/777/mark-paid?page=Approved%20Orders', [
                'transaction_id' => 'manual-paid-777',
            ]);

        $response->assertRedirect('/v/orders/approved-orders');
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('billing', [
            'order_id' => 777,
            'approved' => 'yes',
            'payment' => 'yes',
            'is_paid' => 1,
            'transid' => 'manual-paid-777',
            'website' => '1dollar',
        ]);
    }
}
