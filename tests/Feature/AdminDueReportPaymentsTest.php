<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminDueReportPaymentsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('billing');
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

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->integer('is_paid')->default(0);
            $table->string('amount', 255)->nullable();
            $table->string('transid', 255)->nullable();
            $table->string('trandtime', 30)->nullable();
            $table->string('comments', 255)->nullable();
            $table->string('website', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        DB::table('users')->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'admin',
                'user_email' => 'admin@example.com',
                'is_active' => 1,
            ],
            [
                'user_id' => 100,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'is_active' => 1,
            ],
        ]);
    }

    public function test_due_report_pay_customer_only_marks_approved_unpaid_invoices_paid(): void
    {
        DB::table('billing')->insert([
            [
                'bill_id' => 10,
                'user_id' => 100,
                'order_id' => 501,
                'approved' => 'yes',
                'payment' => 'no',
                'is_paid' => 0,
                'amount' => '10.00',
                'website' => '1dollar',
            ],
            [
                'bill_id' => 11,
                'user_id' => 100,
                'order_id' => 502,
                'approved' => 'no',
                'payment' => 'no',
                'is_paid' => 0,
                'amount' => '5.00',
                'website' => '1dollar',
            ],
            [
                'bill_id' => 12,
                'user_id' => 100,
                'order_id' => 503,
                'approved' => 'yes',
                'payment' => 'yes',
                'is_paid' => 1,
                'amount' => '7.00',
                'website' => '1dollar',
            ],
        ]);

        $this->withSession(['admin_user_id' => 1])
            ->post('/v/payment-due-report/customer-pay', [
                'uid' => 100,
                'transaction_id' => 'TXN-CUSTOMER-1',
            ])
            ->assertRedirect('/v/payment-recieved-report.php');

        $this->assertDatabaseHas('billing', [
            'bill_id' => 10,
            'payment' => 'yes',
            'is_paid' => 1,
            'transid' => 'TXN-CUSTOMER-1',
        ]);

        $this->assertDatabaseHas('billing', [
            'bill_id' => 11,
            'payment' => 'no',
            'is_paid' => 0,
        ]);

        $this->assertDatabaseHas('billing', [
            'bill_id' => 12,
            'payment' => 'yes',
            'is_paid' => 1,
        ]);
    }

    public function test_due_report_pay_invoice_marks_single_invoice_paid_and_sets_paid_flag(): void
    {
        DB::table('billing')->insert([
            'bill_id' => 20,
            'user_id' => 100,
            'order_id' => 601,
            'approved' => 'yes',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => '12.00',
            'website' => '1dollar',
        ]);

        $this->withSession(['admin_user_id' => 1])
            ->post('/v/payment-due-report/invoice/20/pay', [
                'transaction_id' => 'TXN-INVOICE-1',
            ])
            ->assertRedirect('/v/payment-due-detail.php?uid=100');

        $this->assertDatabaseHas('billing', [
            'bill_id' => 20,
            'approved' => 'yes',
            'payment' => 'yes',
            'is_paid' => 1,
            'transid' => 'TXN-INVOICE-1',
        ]);
    }

    public function test_due_report_pay_customer_ignores_soft_deleted_billings(): void
    {
        DB::table('billing')->insert([
            [
                'bill_id' => 40,
                'user_id' => 100,
                'order_id' => 801,
                'approved' => 'yes',
                'payment' => 'no',
                'is_paid' => 0,
                'amount' => '18.00',
                'website' => '1dollar',
                'end_date' => null,
            ],
            [
                'bill_id' => 41,
                'user_id' => 100,
                'order_id' => 802,
                'approved' => 'yes',
                'payment' => 'no',
                'is_paid' => 0,
                'amount' => '19.00',
                'website' => '1dollar',
                'end_date' => '2026-04-01 10:00:00',
            ],
        ]);

        $this->withSession(['admin_user_id' => 1])
            ->post('/v/payment-due-report/customer-pay', [
                'uid' => 100,
                'transaction_id' => 'TXN-CUSTOMER-SAFE',
            ])
            ->assertRedirect('/v/payment-recieved-report.php');

        $this->assertDatabaseHas('billing', [
            'bill_id' => 40,
            'payment' => 'yes',
            'is_paid' => 1,
            'transid' => 'TXN-CUSTOMER-SAFE',
        ]);

        $this->assertDatabaseHas('billing', [
            'bill_id' => 41,
            'payment' => 'no',
            'is_paid' => 0,
            'end_date' => '2026-04-01 10:00:00',
        ]);
    }

    public function test_due_report_can_export_csv_without_server_error(): void
    {
        DB::table('billing')->insert([
            'bill_id' => 30,
            'user_id' => 100,
            'order_id' => 701,
            'approved' => 'yes',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => '22.50',
            'website' => '1dollar',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/payment-due-report.php?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = (string) $response->getContent();
        $this->assertStringContainsString('"Invoice Ref","User ID",Customer,"Total Design",Amount,"Available Balance"', $content);
        $this->assertStringContainsString('#30,100,customer-one,1,22.50', $content);
    }

    public function test_received_report_can_export_csv_without_server_error(): void
    {
        DB::table('billing')->insert([
            'bill_id' => 31,
            'user_id' => 100,
            'order_id' => 702,
            'approved' => 'yes',
            'payment' => 'yes',
            'is_paid' => 1,
            'amount' => '11.00',
            'transid' => 'TXN-31',
            'trandtime' => now()->format('Y-m-d H:i:s'),
            'website' => '1dollar',
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->get('/v/payment-recieved-report.php?export=csv');

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $content = (string) $response->getContent();
        $this->assertStringContainsString('"Invoice Ref","User ID",Customer,"Total Design",Amount', $content);
        $this->assertStringContainsString('#31,100,customer-one,1,11.00', $content);
    }
}
