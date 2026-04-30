<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\HostedPaymentProviders;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerPaymentReconciliationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.primary_legacy_key', '1dollar');
        config()->set('services.twocheckout.seller_id', '123456');
        config()->set('services.twocheckout.secret_word', 'secretword');

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
            $table->string('topup')->nullable();
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
            'topup' => '0',
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
            'provider' => HostedPaymentProviders::TWOCHECKOUT,
            'merchant_reference' => 'PAY-REF-001',
            'payment_scope' => 'single_invoice',
            'status' => 'initiated',
            'currency' => 'USD',
            'requested_amount' => 10.00,
            'return_url' => url('/payment-proceed.php'),
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

    public function test_verified_hosted_return_marks_invoice_paid_and_stores_overpayment_as_credit(): void
    {
        $total = '12.00';
        $orderNumber = '987654';
        $hash = strtoupper(md5('secretword'.'123456'.$orderNumber.$total));

        $this->get('/successpay.php?'.http_build_query([
            'cart_order_id' => 'PAY-REF-001',
            'sid' => '123456',
            'total' => $total,
            'order_number' => $orderNumber,
            'key' => $hash,
        ]))->assertOk()
            ->assertSee('Payment was recorded successfully and the extra amount was stored as customer credit.');

        $this->assertSame('yes', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame(1, (int) \App\Models\Billing::query()->where('bill_id', 300)->value('is_paid'));
        $this->assertSame($orderNumber, \App\Models\Billing::query()->where('bill_id', 300)->value('transid'));
        $this->assertSame('success', \App\Models\PaymentTransaction::query()->where('id', 500)->value('status'));
        $this->assertSame('2.00', number_format((float) \Illuminate\Support\Facades\DB::table('customer_credit_ledger')->where('reference_no', 'PAY-REF-001:overpayment')->value('amount'), 2, '.', ''));
        $this->assertSame(1, \App\Models\PaymentProviderEvent::query()->count());
    }

    public function test_local_twocheckout_simulation_shows_intermediate_checkout_page_before_result(): void
    {
        config()->set('app.env', 'local');
        config()->set('services.twocheckout.simulation_enabled', true);

        $response = $this->withSession([
            'customer_user_id' => 100,
            'customer_user_name' => 'customer-one',
            'customer_site_key' => '1dollar',
        ])->get('/simulate-2checkout/500/checkout');

        $response->assertOk()
            ->assertSee('Hosted Payment Simulation')
            ->assertSee('Simulate Completed Payment')
            ->assertSee('Simulate Failed Payment');

        $this->assertSame('initiated', \App\Models\PaymentTransaction::query()->where('id', 500)->value('status'));
    }

    public function test_customer_advance_deposit_can_settle_invoice_without_checkout_payment(): void
    {
        \Illuminate\Support\Facades\DB::table('users')->where('user_id', 100)->update([
            'topup' => '15.00',
        ]);

        $billing = \App\Models\Billing::query()->where('bill_id', 300)->firstOrFail();

        $this->assertTrue(\App\Support\CustomerBalance::applyToBilling($billing, 'system-auto'));

        $this->assertSame('yes', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame(1, (int) \App\Models\Billing::query()->where('bill_id', 300)->value('is_paid'));
        $this->assertSame('stored-funds', \App\Models\Billing::query()->where('bill_id', 300)->value('transid'));
        $this->assertSame('5.00', number_format((float) \Illuminate\Support\Facades\DB::table('users')->where('user_id', 100)->value('topup'), 2, '.', ''));
    }

    public function test_forged_hosted_return_cannot_mark_invoice_paid(): void
    {
        $response = $this->get('/successpay.php?'.http_build_query([
            'cart_order_id' => 'PAY-REF-001',
            'sid' => '123456',
            'total' => '10.00',
            'order_number' => '987654',
            'key' => 'INVALIDHASH',
        ]));

        $response->assertOk()
            ->assertSee('still needs a quick review before your billing updates are confirmed');

        $this->assertSame('no', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame(0, (int) \App\Models\Billing::query()->where('bill_id', 300)->value('is_paid'));
        $this->assertSame('verification_failed', \App\Models\PaymentTransaction::query()->where('id', 500)->value('status'));
    }

    public function test_forged_notification_cannot_mark_invoice_paid(): void
    {
        $response = $this->post('/payment-notification.php', [
            'merchant_order_id' => 'PAY-REF-001',
            'vendor_id' => '123456',
            'sale_id' => 'SALE-001',
            'invoice_id' => 'INV-001',
            'md5_hash' => 'INVALIDHASH',
            'invoice_list_amount' => '10.00',
        ]);

        $response->assertOk();
        $this->assertSame('OK', $response->getContent());
        $this->assertSame('no', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame(0, (int) \App\Models\Billing::query()->where('bill_id', 300)->value('is_paid'));
        $this->assertSame('initiated', \App\Models\PaymentTransaction::query()->where('id', 500)->value('status'));
    }

    public function test_verified_notification_marks_invoice_paid(): void
    {
        $vendorId = '123456';
        $saleId = 'SALE-001';
        $invoiceId = 'INV-001';
        $hash = strtoupper(md5($saleId.$vendorId.$invoiceId.'secretword'));

        $response = $this->post('/payment-notification.php', [
            'merchant_order_id' => 'PAY-REF-001',
            'vendor_id' => $vendorId,
            'sale_id' => $saleId,
            'invoice_id' => $invoiceId,
            'md5_hash' => $hash,
            'invoice_list_amount' => '10.00',
        ]);

        $response->assertOk();
        $this->assertSame('OK', $response->getContent());
        $this->assertSame('yes', \App\Models\Billing::query()->where('bill_id', 300)->value('payment'));
        $this->assertSame(1, (int) \App\Models\Billing::query()->where('bill_id', 300)->value('is_paid'));
        $this->assertSame($saleId, \App\Models\Billing::query()->where('bill_id', 300)->value('transid'));
        $this->assertSame('success', \App\Models\PaymentTransaction::query()->where('id', 500)->value('status'));
        $this->assertDatabaseHas('payment_provider_events', [
            'payment_transaction_id' => 500,
            'event_type' => 'notification',
            'status' => 'verified',
        ]);
    }

    public function test_legacy_proceed_redirects_to_success_handler_with_expected_params(): void
    {
        $response = $this->post('/payment-proceed.php', [
            'sid' => '123456',
            'cart_order_id' => 'PAY-REF-001',
            'order_number' => 'ORDER-123',
            'total' => '10.00',
            'key' => 'RETURNKEY',
        ]);

        $response->assertRedirect('/successpay.php?sid=123456&cart_order_id=PAY-REF-001&order_number=ORDER-123&total=10.00&key=RETURNKEY');
    }

    public function test_legacy_proceed_handles_incomplete_return_without_customer_facing_error(): void
    {
        $response = $this->post('/payment-proceed.php');

        $response->assertRedirect('/view-billing.php');
        $response->assertSessionHas('success', 'Your payment return is still being checked. Please refresh your billing page in a moment, and contact support if the payment does not appear shortly.');
    }

    public function test_hosted_return_logs_sanitized_diagnostics_without_sensitive_fields(): void
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function (string $message, array $context): bool {
                return $message === '2checkout.return.received'
                    && ($context['merchant_reference'] ?? null) === 'PAY-REF-001'
                    && ($context['provider_reference'] ?? null) === '987654'
                    && ! array_key_exists('key', $context)
                    && ! array_key_exists('card_number', $context)
                    && ! array_key_exists('cvv', $context)
                    && in_array('cart_order_id', $context['input_keys'] ?? [], true)
                    && ! in_array('card_number', $context['input_keys'] ?? [], true)
                    && ! in_array('cvv', $context['input_keys'] ?? [], true);
            });

        Log::shouldReceive('info')->times(2);

        $total = '10.00';
        $orderNumber = '987654';
        $hash = strtoupper(md5('secretword'.'123456'.$orderNumber.$total));

        $this->get('/successpay.php?'.http_build_query([
            'cart_order_id' => 'PAY-REF-001',
            'sid' => '123456',
            'total' => $total,
            'order_number' => $orderNumber,
            'key' => $hash,
            'card_number' => '4111111111111111',
            'cvv' => '123',
        ]))->assertOk();
    }
}
