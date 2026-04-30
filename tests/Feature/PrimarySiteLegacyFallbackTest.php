<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Models\Billing;
use App\Models\Order;
use App\Support\CustomerBalance;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrimarySiteLegacyFallbackTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sites.primary_legacy_key', '1dollar');

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('customer_credit_ledger');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_password')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id');
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('approved', 10)->nullable();
            $table->string('payment', 10)->nullable();
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
            $table->string('entry_type', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference_no')->nullable();
            $table->string('notes')->nullable();
            $table->string('created_by')->nullable();
            $table->string('date_added')->nullable();
            $table->string('end_date', 30)->nullable();
            $table->string('deleted_by')->nullable();
        });
    }

    public function test_primary_site_scope_includes_blank_legacy_rows(): void
    {
        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'primary-tagged',
                'user_email' => 'primary@example.com',
                'user_password' => 'secret',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 2,
                'website' => '',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'primary-blank',
                'user_email' => 'blank@example.com',
                'user_password' => 'secret',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 3,
                'website' => 'brandb',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'brand-b',
                'user_email' => 'brandb@example.com',
                'user_password' => 'secret',
                'is_active' => 1,
                'end_date' => null,
            ],
        ]);

        Order::query()->insert([
            ['order_id' => 10, 'user_id' => 1, 'website' => '1dollar', 'order_type' => 'order', 'status' => 'done', 'end_date' => null],
            ['order_id' => 11, 'user_id' => 2, 'website' => '', 'order_type' => 'order', 'status' => 'done', 'end_date' => null],
            ['order_id' => 12, 'user_id' => 3, 'website' => 'brandb', 'order_type' => 'order', 'status' => 'done', 'end_date' => null],
        ]);

        $this->assertSame([1, 2], AdminUser::query()->customers()->forWebsite('1dollar')->orderBy('user_id')->pluck('user_id')->all());
        $this->assertSame([10, 11], Order::query()->forWebsite('1dollar')->orderBy('order_id')->pluck('order_id')->all());
        $this->assertSame([3], AdminUser::query()->customers()->forWebsite('brandb')->pluck('user_id')->all());
        $this->assertSame([12], Order::query()->forWebsite('brandb')->pluck('order_id')->all());
    }

    public function test_customer_balance_is_scoped_to_site_with_primary_fallback(): void
    {
        Order::query()->insert([
            ['order_id' => 21, 'user_id' => 77, 'website' => '', 'order_type' => 'order', 'status' => 'approved', 'end_date' => null],
            ['order_id' => 22, 'user_id' => 77, 'website' => 'brandb', 'order_type' => 'order', 'status' => 'approved', 'end_date' => null],
        ]);

        Billing::query()->insert([
            [
                'bill_id' => 31,
                'user_id' => 77,
                'order_id' => 21,
                'website' => '',
                'approved' => 'yes',
                'payment' => 'no',
                'amount' => 5.00,
                'approve_date' => now()->format('Y-m-d H:i:s'),
                'end_date' => null,
            ],
            [
                'bill_id' => 32,
                'user_id' => 77,
                'order_id' => 22,
                'website' => 'brandb',
                'approved' => 'yes',
                'payment' => 'no',
                'amount' => 7.00,
                'approve_date' => now()->format('Y-m-d H:i:s'),
                'end_date' => null,
            ],
        ]);

        CustomerBalance::addPaymentCredit(77, '1dollar', 5.00, 'ref-primary', 'test');
        CustomerBalance::addPaymentCredit(77, 'brandb', 5.00, 'ref-brandb', 'test');

        $this->assertSame(5.0, CustomerBalance::available(77, '1dollar'));
        $this->assertSame(5.0, CustomerBalance::available(77, 'brandb'));

        $primaryBilling = Billing::query()->where('bill_id', 31)->firstOrFail();
        $brandBilling = Billing::query()->where('bill_id', 32)->firstOrFail();

        $this->assertTrue(CustomerBalance::applyToBilling($primaryBilling, 'admin'));
        $this->assertFalse(CustomerBalance::applyToBilling($brandBilling, 'admin'));

        $this->assertSame('yes', Billing::query()->where('bill_id', 31)->value('payment'));
        $this->assertSame('no', Billing::query()->where('bill_id', 32)->value('payment'));
        $this->assertSame(0.0, CustomerBalance::available(77, '1dollar'));
        $this->assertSame(5.0, CustomerBalance::available(77, 'brandb'));
    }

    public function test_incoming_payment_can_apply_to_primary_site_due_invoice_even_with_legacy_website_mismatch(): void
    {
        AdminUser::query()->create([
            'user_id' => 88,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'legacy-primary',
            'user_email' => 'legacy-primary@example.com',
            'is_active' => 1,
            'end_date' => null,
        ]);

        Order::query()->create([
            'order_id' => 41,
            'user_id' => 88,
            'website' => 'oned',
            'order_type' => 'order',
            'status' => 'approved',
            'end_date' => null,
        ]);

        Billing::query()->create([
            'bill_id' => 51,
            'user_id' => 88,
            'order_id' => 41,
            'website' => 'oned',
            'approved' => 'yes',
            'payment' => 'no',
            'amount' => 9.00,
            'approve_date' => now()->format('Y-m-d H:i:s'),
            'end_date' => null,
        ]);

        $result = CustomerBalance::recordIncomingPayment(
            88,
            9.00,
            'legacy-primary-payment',
            'admin',
            'manual apply',
            'TXN-LEGACY-1',
            true,
            '1dollar'
        );

        $this->assertSame('actual', $result['status']);
        $this->assertSame('yes', Billing::query()->where('bill_id', 51)->value('payment'));
        $this->assertSame('TXN-LEGACY-1', Billing::query()->where('bill_id', 51)->value('transid'));
    }

    public function test_legacy_oned_website_normalizes_to_primary_balance_scope(): void
    {
        CustomerBalance::addPaymentCredit(99, 'oned', 12.00, 'alias-credit', 'admin');

        $this->assertSame(12.0, CustomerBalance::available(99, '1dollar'));
        $this->assertSame(12.0, CustomerBalance::available(99, 'oned'));
    }
}
