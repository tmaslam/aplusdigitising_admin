<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\PaymentTransaction;
use App\Models\PaymentTransactionItem;
use App\Support\SecurityAudit;
use App\Support\HostedPaymentProviders;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TwocheckoutSimulationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.env', 'local');
        config()->set('sites.primary_legacy_key', '1dollar');
        config()->set('services.twocheckout.simulation_enabled', true);
        config()->set('services.twocheckout.simulation_outcome', 'success');
        config()->set('services.twocheckout.simulation_customer_id', null);
        config()->set('services.twocheckout.simulation_customer_email', '');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('security_audit_events');
        Schema::dropIfExists('payment_provider_events');
        Schema::dropIfExists('payment_transaction_items');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('customer_credit_ledger');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('total_amount')->nullable();
            $table->string('stitches_price')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('approved', 10)->nullable();
            $table->string('payment', 10)->nullable();
            $table->unsignedTinyInteger('is_paid')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('transid')->nullable();
            $table->string('comments')->nullable();
            $table->string('trandtime')->nullable();
            $table->string('approve_date')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('customer_credit_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('billing_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('entry_type', 50)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('date_added')->nullable();
            $table->string('end_date', 30)->nullable();
            $table->string('deleted_by')->nullable();
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('billing_id')->nullable();
            $table->string('legacy_website', 30)->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('merchant_reference')->unique();
            $table->string('payment_scope', 50)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('currency', 10)->nullable();
            $table->decimal('requested_amount', 12, 2)->default(0);
            $table->decimal('confirmed_amount', 12, 2)->nullable();
            $table->string('return_url')->nullable();
            $table->string('redirect_url')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->longText('provider_payload')->nullable();
            $table->string('failure_reason')->nullable();
            $table->string('reconciled_at')->nullable();
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
        });

        Schema::create('payment_transaction_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('payment_transaction_id');
            $table->unsignedBigInteger('billing_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('legacy_website', 30)->nullable();
            $table->decimal('requested_amount', 12, 2)->default(0);
            $table->decimal('confirmed_amount', 12, 2)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('created_at')->nullable();
            $table->string('updated_at')->nullable();
        });

        Schema::create('payment_provider_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('event_type', 50)->nullable();
            $table->string('event_reference')->nullable();
            $table->string('status', 30)->nullable();
            $table->longText('payload')->nullable();
            $table->string('received_at')->nullable();
            $table->string('processed_at')->nullable();
        });

        Schema::create('security_audit_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('event_type', 80);
            $table->string('severity', 20);
            $table->string('portal', 30);
            $table->string('site_legacy_key', 100)->nullable();
            $table->unsignedBigInteger('actor_user_id')->nullable();
            $table->string('actor_login', 150)->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 255)->nullable();
            $table->string('request_path', 255)->nullable();
            $table->string('request_method', 10);
            $table->string('message', 255);
            $table->json('details_json')->nullable();
            $table->dateTime('created_at');
        });

        SecurityAudit::refreshTableAvailability();

        AdminUser::query()->create([
            'user_id' => 100,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-one',
            'user_email' => 'customer@example.com',
            'is_active' => 1,
            'end_date' => null,
        ]);

        \App\Models\Order::query()->create([
            'order_id' => 200,
            'user_id' => 100,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'approved',
            'total_amount' => '10.00',
            'stitches_price' => '10.00',
            'end_date' => null,
        ]);

        \App\Models\Billing::query()->create([
            'bill_id' => 300,
            'user_id' => 100,
            'order_id' => 200,
            'website' => '1dollar',
            'approved' => 'yes',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => 10.00,
            'approve_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
        ]);

        PaymentTransaction::query()->create([
            'id' => 500,
            'user_id' => 100,
            'order_id' => 200,
            'billing_id' => 300,
            'legacy_website' => '1dollar',
            'provider' => HostedPaymentProviders::TWOCHECKOUT,
            'merchant_reference' => 'PAY-SIM-001',
            'payment_scope' => 'single_invoice',
            'status' => 'initiated',
            'currency' => 'USD',
            'requested_amount' => 10.00,
            'return_url' => url('/successpay.php'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        PaymentTransactionItem::query()->create([
            'payment_transaction_id' => 500,
            'billing_id' => 300,
            'order_id' => 200,
            'user_id' => 100,
            'legacy_website' => '1dollar',
            'requested_amount' => 10.00,
            'status' => 'initiated',
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    protected function tearDown(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('security_audit_events');
        Schema::dropIfExists('payment_provider_events');
        Schema::dropIfExists('payment_transaction_items');
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('customer_credit_ledger');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        parent::tearDown();
    }

    public function test_twocheckout_simulation_uses_requested_failed_outcome(): void
    {
        $response = $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/simulate-2checkout/500?outcome=failed');

        $response->assertOk()->assertSee('Simulated 2Checkout payment was marked as failed.');
        $this->assertSame('no', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame('failed', PaymentTransaction::query()->where('id', 500)->value('status'));
    }

    public function test_twocheckout_simulation_uses_requested_success_outcome_even_when_default_is_failed(): void
    {
        config()->set('services.twocheckout.simulation_outcome', 'failed');

        $response = $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/simulate-2checkout/500?outcome=success');

        $response->assertOk()->assertSee('Payment was recorded successfully.');
        $this->assertSame('yes', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame('success', PaymentTransaction::query()->where('id', 500)->value('status'));
    }

    public function test_twocheckout_simulation_is_blocked_for_other_customers_outside_local(): void
    {
        config()->set('app.env', 'production');
        config()->set('services.twocheckout.simulation_customer_id', 999);

        $response = $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/simulate-2checkout/500');

        $response->assertNotFound();
        $this->assertDatabaseHas('security_audit_events', [
            'event_type' => 'payments.simulation_denied',
            'portal' => 'customer',
            'request_path' => '/simulate-2checkout/500',
        ]);
    }

    public function test_twocheckout_simulation_is_allowed_for_configured_customer_outside_local(): void
    {
        config()->set('app.env', 'production');
        config()->set('services.twocheckout.simulation_customer_id', 100);

        $response = $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/simulate-2checkout/500');

        $response->assertOk()->assertSee('Payment was recorded successfully.');
    }
}
