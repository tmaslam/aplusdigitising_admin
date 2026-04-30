<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerDashboardCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('site_promotion_claims');
        Schema::dropIfExists('site_promotions');
        Schema::dropIfExists('billing');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('site_domains');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('users');

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_key', 30)->unique();
            $table->string('slug', 100)->nullable();
            $table->string('name', 150)->nullable();
            $table->string('brand_name', 150)->nullable();
            $table->string('primary_domain')->nullable();
            $table->string('website_address')->nullable();
            $table->string('support_email')->nullable();
            $table->string('from_email')->nullable();
            $table->string('timezone', 100)->default('UTC');
            $table->string('pricing_strategy', 50)->default('customer_rate');
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('settings_json')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('site_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('host')->unique();
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('alternate_email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('customer_approval_limit')->nullable();
            $table->string('single_approval_limit')->nullable();
            $table->string('end_date')->nullable();
            $table->integer('is_active')->default(1);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->unsignedTinyInteger('advance_pay')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('total_amount')->nullable();
            $table->integer('is_active')->default(1);
        });

        Schema::create('billing', function (Blueprint $table) {
            $table->bigIncrements('bill_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('approved', 30)->nullable();
            $table->string('payment', 30)->nullable();
            $table->string('amount')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->integer('is_paid')->default(0);
        });

        Schema::create('site_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('promotion_name', 150);
            $table->string('promotion_code', 100)->nullable();
            $table->string('work_type', 50)->nullable();
            $table->string('discount_type', 50)->default('fixed');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->text('config_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('site_promotion_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('site_promotion_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('website', 30)->default('1dollar');
            $table->string('status', 50)->default('pending_verification');
            $table->boolean('verification_required')->default(true);
            $table->boolean('payment_required')->default(false);
            $table->decimal('required_payment_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->decimal('first_order_flat_amount', 12, 2)->nullable();
            $table->text('offer_snapshot_json')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        DB::table('sites')->insert([
            'id' => 1,
            'legacy_key' => '1dollar',
            'slug' => '1dollar',
            'name' => 'APlus',
            'brand_name' => 'A Plus Digitizing',
            'primary_domain' => 'localhost',
            'website_address' => 'http://localhost',
            'support_email' => 'support@example.com',
            'from_email' => 'support@example.com',
            'timezone' => 'UTC',
            'pricing_strategy' => 'customer_rate',
            'is_primary' => 1,
            'is_active' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('site_domains')->insert([
            'site_id' => 1,
            'host' => 'localhost',
            'is_primary' => 1,
            'is_active' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        DB::table('users')->insert([
            'user_id' => 4366,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'compat-user',
            'user_email' => 'compat@example.com',
            'first_name' => 'Compat',
            'last_name' => 'User',
            'customer_approval_limit' => '15',
            'single_approval_limit' => '10',
            'end_date' => null,
            'is_active' => 1,
        ]);

        DB::table('orders')->insert([
            'order_id' => 9001,
            'user_id' => 4366,
            'website' => '1dollar',
            'order_type' => 'order',
            'status' => 'Underprocess',
            'advance_pay' => 0,
            'end_date' => null,
            'total_amount' => '0.00',
            'is_active' => 1,
        ]);
    }

    public function test_dashboard_handles_datetime_end_date_and_numeric_advance_pay_columns(): void
    {
        $response = $this->withSession([
            'customer_user_id' => 4366,
            'customer_user_name' => 'Compat User',
            'customer_site_key' => '1dollar',
        ])->get('/dashboard.php');

        $response->assertOk()->assertSee('Dashboard');
    }
}
