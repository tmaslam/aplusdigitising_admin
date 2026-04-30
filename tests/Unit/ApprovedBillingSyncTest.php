<?php

namespace Tests\Unit;

use App\Models\Billing;
use App\Models\Order;
use App\Support\ApprovedBillingSync;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ApprovedBillingSyncTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('website', 30)->nullable();
            $table->string('total_amount', 255)->nullable();
            $table->string('completion_date', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('amount', 255)->nullable();
            $table->string('earned_amount', 255)->nullable();
            $table->string('payment', 30)->nullable();
            $table->string('approve_date', 30)->nullable();
            $table->string('comments', 255)->nullable();
            $table->string('transid', 255)->nullable();
            $table->string('trandtime', 30)->nullable();
            $table->string('website', 30)->nullable();
            $table->integer('is_paid')->default(0);
            $table->integer('is_advance')->default(0);
            $table->string('deleted_by', 255)->nullable();
            $table->string('end_date', 30)->nullable();
        });
    }

    public function test_sync_creates_missing_approved_billing_for_approved_order(): void
    {
        Order::query()->create([
            'order_id' => 501,
            'user_id' => 7,
            'order_type' => 'order',
            'status' => 'approved',
            'website' => '1dollar',
            'total_amount' => '18.50',
            'completion_date' => '2026-03-29 10:00:00',
            'end_date' => null,
        ]);

        $synced = ApprovedBillingSync::syncMissingApprovedBillings();

        $this->assertSame(1, $synced);
        $this->assertDatabaseHas('billing', [
            'order_id' => 501,
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => '18.50',
            'website' => '1dollar',
        ]);
    }

    public function test_sync_upgrades_existing_billing_without_losing_paid_state(): void
    {
        Order::query()->create([
            'order_id' => 777,
            'user_id' => 9,
            'order_type' => 'vector',
            'status' => 'approved',
            'website' => '1dollar',
            'total_amount' => '22.00',
            'completion_date' => '2026-03-29 12:00:00',
            'end_date' => null,
        ]);

        Billing::query()->create([
            'bill_id' => 1,
            'user_id' => 9,
            'order_id' => 777,
            'approved' => 'no',
            'amount' => '22.00',
            'payment' => 'yes',
            'approve_date' => null,
            'comments' => 'Legacy payment row',
            'website' => '1dollar',
            'is_paid' => 1,
            'is_advance' => 0,
            'end_date' => null,
        ]);

        $billing = ApprovedBillingSync::ensureForOrder(Order::query()->findOrFail(777));

        $this->assertInstanceOf(Billing::class, $billing);
        $this->assertDatabaseHas('billing', [
            'order_id' => 777,
            'approved' => 'yes',
            'payment' => 'yes',
            'is_paid' => 1,
        ]);
    }
}
