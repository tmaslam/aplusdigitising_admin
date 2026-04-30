<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\HostedPaymentProviders;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StripeWebhookReconciliationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.primary_legacy_key', '1dollar');
        config()->set('services.stripe.webhook_secret', 'whsec_test_secret');
        config()->set('services.stripe.webhook_tolerance', 300);

        Schema::disableForeignKeyConstraints();
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

        \App\Models\AdminUser::query()->create([
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

        \App\Models\PaymentTransaction::query()->create([
            'id' => 500,
            'user_id' => 100,
            'order_id' => 200,
            'billing_id' => 300,
            'legacy_website' => '1dollar',
            'provider' => HostedPaymentProviders::STRIPE,
            'merchant_reference' => 'STRIPE-REF-001',
            'payment_scope' => 'single_invoice',
            'status' => 'initiated',
            'currency' => 'USD',
            'requested_amount' => 10.00,
            'return_url' => url('/successpay.php'),
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        \App\Models\PaymentTransactionItem::query()->create([
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

    public function test_valid_stripe_webhook_marks_invoice_paid_and_stores_overpayment_credit(): void
    {
        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'checkout.session.completed',
            'data' => [
                'object' => [
                    'id' => 'cs_test_123',
                    'client_reference_id' => 'STRIPE-REF-001',
                    'payment_status' => 'paid',
                    'amount_total' => 1200,
                    'payment_intent' => [
                        'id' => 'pi_test_123',
                    ],
                ],
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_secret');

        $this->call(
            'POST',
            '/webhooks/stripe',
            [],
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_STRIPE_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
            ],
            $payload
        )->assertOk()
            ->assertSee('OK');

        $this->assertSame('yes', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame(1, (int) \App\Models\Billing::query()->where('bill_id', 300)->value('is_paid'));
        $this->assertSame('pi_test_123', \App\Models\Billing::query()->where('bill_id', 300)->value('transid'));
        $this->assertSame('success', \App\Models\PaymentTransaction::query()->where('id', 500)->value('status'));
        $this->assertSame('2.00', number_format((float) DB::table('customer_credit_ledger')->where('reference_no', 'STRIPE-REF-001:overpayment')->value('amount'), 2, '.', ''));
        $this->assertSame(1, \App\Models\PaymentProviderEvent::query()->count());
    }
}
