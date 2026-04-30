<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminSecurityDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('security_audit_events');
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
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('user_term', 20)->nullable();
            $table->string('exist_customer', 40)->nullable();
            $table->string('real_user', 10)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('approved', 10)->nullable();
            $table->string('payment', 10)->nullable();
            $table->integer('is_paid')->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('end_date', 30)->nullable();
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

        Schema::create('customer_credit_ledger', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->string('reference_no', 100)->nullable();
            $table->string('entry_type', 40)->nullable();
            $table->string('end_date', 30)->nullable();
            $table->dateTime('date_added')->nullable();
        });

        AdminUser::query()->insert([
            [
                'user_id' => 1,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_ADMIN,
                'user_name' => 'main-admin',
                'user_email' => 'admin@example.com',
                'first_name' => null,
                'last_name' => null,
                'user_term' => null,
                'exist_customer' => null,
                'real_user' => '1',
                'is_active' => 1,
                'end_date' => null,
            ],
            [
                'user_id' => 2,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'blocked-customer',
                'user_email' => 'blocked@example.com',
                'first_name' => null,
                'last_name' => null,
                'user_term' => 'ip',
                'exist_customer' => '1',
                'real_user' => '1',
                'is_active' => 0,
                'end_date' => null,
            ],
            [
                'user_id' => 3,
                'website' => '1dollar',
                'usre_type_id' => AdminUser::TYPE_CUSTOMER,
                'user_name' => 'credit-customer',
                'user_email' => 'credit@example.com',
                'first_name' => 'Credit',
                'last_name' => 'Customer',
                'user_term' => 'ip',
                'exist_customer' => '1',
                'real_user' => '1',
                'is_active' => 1,
                'end_date' => null,
            ],
        ]);

        DB::table('security_audit_events')->insert([
            [
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
                'created_at' => now()->subMinutes(20)->format('Y-m-d H:i:s'),
            ],
            [
                'event_type' => 'files.upload_rejected',
                'severity' => 'warning',
                'portal' => 'admin',
                'site_legacy_key' => '1dollar',
                'actor_user_id' => 1,
                'actor_login' => 'main-admin',
                'ip_address' => '10.1.1.1',
                'user_agent' => 'TestAgent',
                'request_path' => '/v/create-order.php',
                'request_method' => 'POST',
                'message' => 'Rejected upload',
                'details_json' => json_encode([]),
                'created_at' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
            ],
        ]);

        DB::table('customer_credit_ledger')->insert([
            [
                'user_id' => 3,
                'website' => '1dollar',
                'amount' => 20.00,
                'reference_no' => 'credit-1',
                'entry_type' => 'payment',
                'end_date' => null,
                'date_added' => now()->format('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 2,
                'website' => '1dollar',
                'amount' => -25.00,
                'reference_no' => 'credit-2',
                'entry_type' => 'applied',
                'end_date' => null,
                'date_added' => now()->format('Y-m-d H:i:s'),
            ],
        ]);
    }

    public function test_admin_dashboard_surfaces_security_watch_summary(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/welcome.php');

        $response
            ->assertOk()
            ->assertSee('Security Watch')
            ->assertSee('Action Required')
            ->assertSee('Failed Logins')
            ->assertSee('Upload Rejections')
            ->assertSee('10.1.1.1');
    }

    public function test_security_events_page_surfaces_summary_cards(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/security-events.php');

        $response
            ->assertOk()
            ->assertSee('Critical Events')
            ->assertSee('Recent Alerts')
            ->assertSee('Top Source IPs')
            ->assertSee('Rejected upload');
    }

    public function test_admin_dashboard_customer_credit_card_matches_inventory_purpose(): void
    {
        $response = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/welcome.php');

        $response
            ->assertOk()
            ->assertSee('Available Customer Credit')
            ->assertSee('20.00')
            ->assertSee('Across 1 active customer with credit ready to apply to future invoices.')
            ->assertDontSee('-5.00');

        $inventory = $this->withSession([
            'admin_user_id' => 1,
            'admin_user_name' => 'main-admin',
        ])->get('/v/customer-payment-inventory.php');

        $inventory
            ->assertOk()
            ->assertSee('Customer Credit Inventory')
            ->assertSee('credit@example.com')
            ->assertSee('20.00')
            ->assertDontSee('blocked@example.com')
            ->assertDontSee('-25.00');
    }
}
