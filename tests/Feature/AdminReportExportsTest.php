<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminReportExportsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('security_audit_events');
        Schema::dropIfExists('login_history');
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
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('user_term', 20)->nullable();
            $table->string('exist_customer', 40)->nullable();
            $table->string('real_user', 10)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('approved', 10)->nullable();
            $table->string('payment', 10)->nullable();
            $table->integer('is_paid')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('transid')->nullable();
            $table->string('trandtime')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('design_name')->nullable();
            $table->string('completion_date')->nullable();
            $table->string('stitches')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('customer_credit_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('website', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('entry_type', 50)->nullable();
            $table->string('reference_no')->nullable();
            $table->string('date_added')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('login_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('IP_Address')->nullable();
            $table->string('Login_Name')->nullable();
            $table->string('Date_Added')->nullable();
            $table->string('Status')->nullable();
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

        DB::table('users')->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'first_name' => null,
                'last_name' => null,
                'real_user' => '1',
                'is_active' => 1,
            ],
            [
                'user_id' => 100,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'customer-one',
                'user_email' => 'customer@example.com',
                'first_name' => 'Customer',
                'last_name' => 'One',
                'real_user' => '1',
                'is_active' => 1,
            ],
            [
                'user_id' => 101,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'zebra-customer',
                'user_email' => 'zebra@example.com',
                'first_name' => 'Zebra',
                'last_name' => 'Customer',
                'real_user' => '1',
                'is_active' => 1,
            ],
        ]);

        DB::table('billing')->insert([
            [
                'bill_id' => 10,
                'user_id' => 100,
                'order_id' => 501,
                'approved' => 'yes',
                'payment' => 'no',
                'is_paid' => 0,
                'amount' => 10.00,
                'transid' => null,
                'trandtime' => null,
                'website' => '1dollar',
            ],
            [
                'bill_id' => 11,
                'user_id' => 100,
                'order_id' => 502,
                'approved' => 'yes',
                'payment' => 'yes',
                'is_paid' => 1,
                'amount' => 12.00,
                'transid' => 'TXN-11',
                'trandtime' => now()->format('Y-m-d H:i:s'),
                'website' => '1dollar',
            ],
            [
                'bill_id' => 12,
                'user_id' => 100,
                'order_id' => 503,
                'approved' => 'yes',
                'payment' => 'no',
                'is_paid' => 0,
                'amount' => 21.00,
                'transid' => null,
                'trandtime' => null,
                'website' => 'alpha',
            ],
            [
                'bill_id' => 13,
                'user_id' => 100,
                'order_id' => 504,
                'approved' => 'yes',
                'payment' => 'yes',
                'is_paid' => 1,
                'amount' => 8.00,
                'transid' => 'TXN-13',
                'trandtime' => now()->subDay()->format('Y-m-d H:i:s'),
                'website' => 'zeta',
            ],
            [
                'bill_id' => 14,
                'user_id' => 101,
                'order_id' => 505,
                'approved' => 'yes',
                'payment' => 'no',
                'is_paid' => 0,
                'amount' => 15.00,
                'transid' => null,
                'trandtime' => null,
                'website' => '1dollar',
            ],
            [
                'bill_id' => 15,
                'user_id' => 101,
                'order_id' => 506,
                'approved' => 'yes',
                'payment' => 'yes',
                'is_paid' => 1,
                'amount' => 18.00,
                'transid' => 'TXN-15',
                'trandtime' => now()->addMinute()->format('Y-m-d H:i:s'),
                'website' => '1dollar',
            ],
        ]);

        DB::table('orders')->insert([
            [
                'order_id' => 501,
                'user_id' => 100,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Cap Logo',
                'completion_date' => now()->format('Y-m-d H:i:s'),
                'stitches' => '8500',
                'total_amount' => 10.00,
            ],
            [
                'order_id' => 502,
                'user_id' => 100,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Shirt Logo',
                'completion_date' => now()->format('Y-m-d H:i:s'),
                'stitches' => '12000',
                'total_amount' => 12.00,
            ],
            [
                'order_id' => 503,
                'user_id' => 100,
                'website' => 'alpha',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Left Chest',
                'completion_date' => now()->format('Y-m-d H:i:s'),
                'stitches' => '6000',
                'total_amount' => 21.00,
            ],
            [
                'order_id' => 504,
                'user_id' => 100,
                'website' => 'zeta',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Back Jacket',
                'completion_date' => now()->format('Y-m-d H:i:s'),
                'stitches' => '16000',
                'total_amount' => 8.00,
            ],
            [
                'order_id' => 505,
                'user_id' => 101,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Applique Crest',
                'completion_date' => now()->format('Y-m-d H:i:s'),
                'stitches' => '9000',
                'total_amount' => 15.00,
            ],
            [
                'order_id' => 506,
                'user_id' => 101,
                'website' => '1dollar',
                'order_type' => 'order',
                'status' => 'approved',
                'design_name' => 'Chenille Patch',
                'completion_date' => now()->format('Y-m-d H:i:s'),
                'stitches' => '11000',
                'total_amount' => 18.00,
            ],
        ]);

        DB::table('customer_credit_ledger')->insert([
            'user_id' => 100,
            'website' => '1dollar',
            'amount' => 3.50,
            'entry_type' => 'payment',
            'reference_no' => 'credit-1',
            'date_added' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('login_history')->insert([
            'IP_Address' => '10.9.9.9',
            'Login_Name' => 'customer@example.com',
            'Date_Added' => now()->format('Y-m-d H:i:s'),
            'Status' => 'success',
        ]);

        DB::table('security_audit_events')->insert([
            'event_type' => 'auth.login_failed',
            'severity' => 'warning',
            'portal' => 'customer',
            'site_legacy_key' => '1dollar',
            'actor_user_id' => null,
            'actor_login' => 'wrong@example.com',
            'ip_address' => '10.1.1.1',
            'user_agent' => 'TestAgent',
            'request_path' => '/login.php',
            'request_method' => 'POST',
            'message' => 'Failed login',
            'details_json' => json_encode([]),
            'created_at' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_due_report_can_export_csv(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-due-report.php?export=csv');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $rows = $this->csvRows((string) $response->getContent());

        $this->assertSame(['Invoice Ref', 'User ID', 'Customer', 'Total Design', 'Amount', 'Available Balance'], $rows[0]);
        $this->assertSame('#14', $rows[1][0]);
        $this->assertSame('101', $rows[1][1]);
        $this->assertSame('Zebra Customer', $rows[1][2]);
        $this->assertSame('1', $rows[1][3]);
        $this->assertSame('15.00', $rows[1][4]);
        $this->assertSame('0.00', $rows[1][5]);
    }

    public function test_due_payment_page_renders_order_wise_billing_view(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/all-payment-due.php?txtorderID=501');

        $response->assertOk();
        $response->assertSee('Due Payment');
        $response->assertSee('Open Detail');
        $response->assertSee('501');
        $response->assertSee('Customer One');
    }

    public function test_received_payment_page_renders_order_wise_billing_view_with_detail_action(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-recieved.php?txtorderID=502');

        $response->assertOk();
        $response->assertSee('Received Payment');
        $response->assertSee('Open Detail');
        $response->assertSee('502');
        $response->assertSee('Customer One');
    }

    public function test_due_payment_page_honors_requested_sort_column_and_direction(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/all-payment-due.php?column_name=amount&sort=asc');

        $response->assertOk();

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $smallerAmountPos = strpos($content, '>10.00<');
        $largerAmountPos = strpos($content, '>21.00<');

        $this->assertIsInt($smallerAmountPos);
        $this->assertIsInt($largerAmountPos);
        $this->assertLessThan($largerAmountPos, $smallerAmountPos);
    }

    public function test_received_payment_page_honors_requested_sort_column_and_direction(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-recieved.php?column_name=trandtime&sort=asc');

        $response->assertOk();

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $olderTxnPos = strpos($content, 'TXN-13');
        $newerTxnPos = strpos($content, 'TXN-11');

        $this->assertIsInt($olderTxnPos);
        $this->assertIsInt($newerTxnPos);
        $this->assertLessThan($newerTxnPos, $olderTxnPos);
    }

    public function test_due_payment_page_supports_design_name_sorting(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/all-payment-due.php?column_name=design_name&sort=asc');

        $response->assertOk();

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $appliquePos = strpos($content, 'Applique Crest');
        $capPos = strpos($content, 'Cap Logo');

        $this->assertIsInt($appliquePos);
        $this->assertIsInt($capPos);
        $this->assertLessThan($capPos, $appliquePos);
    }

    public function test_received_payment_page_supports_customer_name_sorting(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-recieved.php?column_name=customer_name&sort=desc');

        $response->assertOk();

        $content = $response->getContent();
        $this->assertNotFalse($content);
        $zebraPos = strpos($content, 'Zebra Customer');
        $customerOnePos = strpos($content, 'Customer One');

        $this->assertIsInt($zebraPos);
        $this->assertIsInt($customerOnePos);
        $this->assertLessThan($customerOnePos, $zebraPos);
    }

    public function test_due_report_supports_legacy_order_name_filter(): void
    {
        DB::table('users')->insert([
            'user_id' => 201,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-two',
            'user_email' => 'other@example.com',
            'first_name' => 'Other',
            'last_name' => 'Customer',
            'real_user' => '1',
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 603,
            'user_id' => 201,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'approved',
            'design_name' => 'Badge Patch',
            'completion_date' => now()->format('Y-m-d H:i:s'),
            'stitches' => '4000',
            'total_amount' => 5.00,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 16,
            'user_id' => 201,
            'order_id' => 603,
            'approved' => 'yes',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => 5.00,
            'website' => '1dollar',
        ]);

        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-due-report.php?txt_ordername=Cap');

        $response->assertOk();
        $response->assertSee('Customer One');
        $response->assertDontSee('Other Customer');
    }

    public function test_security_events_can_export_csv(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/security-events.php?export=csv');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $rows = $this->csvRows((string) $response->getContent());

        $this->assertSame(['Time', 'Severity', 'Portal', 'Event', 'Actor', 'Actor User ID', 'IP', 'Method', 'Path', 'Message'], $rows[0]);
        $this->assertSame('warning', $rows[1][1]);
        $this->assertSame('customer', $rows[1][2]);
        $this->assertSame('auth.login_failed', $rows[1][3]);
        $this->assertSame('wrong@example.com', $rows[1][4]);
    }

    public function test_received_report_can_export_csv(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-recieved-report.php?export=csv');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $rows = $this->csvRows((string) $response->getContent());

        $this->assertSame(['Invoice Ref', 'User ID', 'Customer', 'Total Design', 'Amount'], $rows[0]);
        $this->assertSame('#15', $rows[1][0]);
        $this->assertSame('101', $rows[1][1]);
        $this->assertSame('Zebra Customer', $rows[1][2]);
        $this->assertSame('1', $rows[1][3]);
        $this->assertSame('18.00', $rows[1][4]);
    }

    public function test_received_report_supports_legacy_transaction_filter(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-recieved-report.php?txt_transid=TXN-11');

        $response->assertOk();
        $response->assertSee('Customer One');
    }

    public function test_due_report_total_uses_filtered_group_results(): void
    {
        DB::table('users')->insert([
            'user_id' => 102,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-three',
            'user_email' => 'third@example.com',
            'first_name' => 'Third',
            'last_name' => 'Customer',
            'real_user' => '1',
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 604,
            'user_id' => 102,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'approved',
            'design_name' => 'Jacket Back',
            'completion_date' => now()->format('Y-m-d H:i:s'),
            'stitches' => '7000',
            'total_amount' => 25.00,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 17,
            'user_id' => 102,
            'order_id' => 604,
            'approved' => 'yes',
            'payment' => 'no',
            'is_paid' => 0,
            'amount' => 25.00,
            'website' => '1dollar',
        ]);

        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-due-report.php?txtorderID=501');

        $response->assertOk();
        $response->assertSee('#10');
        $response->assertSee('Customer One');
        $response->assertDontSee('604');
        $response->assertDontSee('Third Customer');
    }

    public function test_received_report_total_uses_filtered_group_results(): void
    {
        DB::table('users')->insert([
            'user_id' => 103,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-four',
            'user_email' => 'fourth@example.com',
            'first_name' => 'Fourth',
            'last_name' => 'Customer',
            'real_user' => '1',
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 605,
            'user_id' => 103,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'approved',
            'design_name' => 'Sleeve Mark',
            'completion_date' => now()->format('Y-m-d H:i:s'),
            'stitches' => '3000',
            'total_amount' => 20.00,
        ]);

        DB::table('billing')->insert([
            'bill_id' => 18,
            'user_id' => 103,
            'order_id' => 605,
            'approved' => 'yes',
            'payment' => 'yes',
            'is_paid' => 1,
            'amount' => 20.00,
            'transid' => 'TXN-14',
            'trandtime' => now()->format('Y-m-d H:i:s'),
            'website' => '1dollar',
        ]);

        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-recieved-report.php?txt_transid=TXN-11');

        $response->assertOk();
        $response->assertSee('#11');
        $response->assertDontSee('605');
        $response->assertDontSee('Fourth Customer');
    }

    public function test_login_history_can_export_csv(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/login_history.php?export=csv');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $rows = $this->csvRows((string) $response->getContent());

        $this->assertSame(['IP Address', 'Login Name', 'Date Added', 'Reason'], $rows[0]);
        $this->assertSame('10.9.9.9', $rows[1][0]);
        $this->assertSame('customer@example.com', $rows[1][1]);
        $this->assertSame('success', $rows[1][3]);
    }

    public function test_due_report_detail_can_export_csv(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-due-detail.php?uid=100&export=csv');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $rows = $this->csvRows((string) $response->getContent());

        $this->assertSame(['Bill ID', 'Order ID', 'Design Name', 'Completion Date', 'Stitches', 'Amount', 'Payment'], $rows[0]);
        $this->assertSame('10', $rows[1][0]);
        $this->assertSame('501', $rows[1][1]);
        $this->assertSame('Cap Logo', $rows[1][2]);
        $this->assertSame('8500', $rows[1][4]);
        $this->assertSame('10.00', $rows[1][5]);
    }

    public function test_received_report_detail_can_export_csv(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/payment-recieved-detail.php?uid=100&export=csv');

        $response
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $rows = $this->csvRows((string) $response->getContent());

        $this->assertSame(['Bill ID', 'Order ID', 'Design Name', 'Completion Date', 'Stitches', 'Amount', 'Transaction ID', 'Paid At'], $rows[0]);
        $this->assertSame('11', $rows[1][0]);
        $this->assertSame('502', $rows[1][1]);
        $this->assertSame('Shirt Logo', $rows[1][2]);
        $this->assertSame('12.00', $rows[1][5]);
        $this->assertSame('TXN-11', $rows[1][6]);
    }

    /**
     * @return array<int, array<int, string|null>>
     */
    private function csvRows(string $content): array
    {
        return collect(preg_split("/\r\n|\n|\r/", trim($content)))
            ->filter()
            ->map(fn (string $line) => str_getcsv($line))
            ->values()
            ->all();
    }
}
