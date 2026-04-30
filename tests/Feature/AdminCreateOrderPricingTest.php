<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminCreateOrderPricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('site_pricing_profiles');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('users');

        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_key', 30)->unique();
            $table->string('slug', 100)->unique();
            $table->string('name', 150);
            $table->string('brand_name', 150);
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

        Schema::create('site_pricing_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('profile_name', 150);
            $table->string('work_type', 50)->nullable();
            $table->string('turnaround_code', 50)->nullable();
            $table->string('pricing_mode', 50)->default('per_thousand');
            $table->decimal('fixed_price', 12, 2)->nullable();
            $table->decimal('per_thousand_rate', 12, 4)->nullable();
            $table->decimal('minimum_charge', 12, 2)->nullable();
            $table->decimal('included_units', 12, 2)->nullable();
            $table->decimal('overage_rate', 12, 4)->nullable();
            $table->string('package_name', 50)->nullable();
            $table->text('config_json')->nullable();
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
            $table->decimal('normal_fee', 12, 2)->nullable();
            $table->decimal('middle_fee', 12, 2)->nullable();
            $table->decimal('urgent_fee', 12, 2)->nullable();
            $table->decimal('super_fee', 12, 2)->nullable();
            $table->string('max_num_stiches', 30)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        DB::table('users')->insert([
            'user_id' => 1,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'admin',
            'user_email' => 'admin@example.com',
            'normal_fee' => null,
            'middle_fee' => null,
            'urgent_fee' => null,
            'super_fee' => null,
            'max_num_stiches' => null,
            'is_active' => 1,
            'end_date' => null,
        ]);

        DB::table('users')->insert([
            'user_id' => 77,
            'site_id' => 2,
            'website' => 'site2',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-two',
            'user_email' => 'customer-two@example.com',
            'normal_fee' => 1.00,
            'middle_fee' => 1.50,
            'urgent_fee' => 1.50,
            'super_fee' => 3.00,
            'max_num_stiches' => '5000',
            'is_active' => 1,
            'end_date' => null,
        ]);

        DB::table('sites')->insert([
            [
                'id' => 1,
                'legacy_key' => '1dollar',
                'slug' => '1dollar',
                'name' => 'APlus',
                'brand_name' => 'A Plus Digitizing',
                'primary_domain' => 'localhost',
                'website_address' => 'https://localhost',
                'support_email' => 'support@example.com',
                'from_email' => 'support@example.com',
                'timezone' => 'UTC',
                'pricing_strategy' => 'customer_rate',
                'is_primary' => 1,
                'is_active' => 1,
            ],
            [
                'id' => 2,
                'legacy_key' => 'site2',
                'slug' => 'site2',
                'name' => 'Site 2',
                'brand_name' => 'Site Two',
                'primary_domain' => 'site2.localhost',
                'website_address' => 'https://site2.localhost',
                'support_email' => 'support@example.com',
                'from_email' => 'support@example.com',
                'timezone' => 'UTC',
                'pricing_strategy' => 'customer_rate',
                'is_primary' => 0,
                'is_active' => 1,
            ],
        ]);
    }

    public function test_price_preview_requires_site_pricing_configuration(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->postJson('/v/create-order/price-preview', [
                'flow_context' => 'order',
                'work_type' => 'digitizing',
                'website' => 'site2',
                'customer_user_id' => 77,
                'turn_around_time' => 'Standard',
                'stitches' => '4500',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'No Embroidery Digitizing pricing profile is configured for Standard on Site Two. Add it in Site Pricing before continuing.',
            ]);
    }

    public function test_price_preview_uses_configured_site_pricing_when_profile_exists(): void
    {
        DB::table('site_pricing_profiles')->insert([
            'site_id' => 2,
            'profile_name' => 'Digitizing Standard',
            'work_type' => 'digitizing',
            'turnaround_code' => 'standard',
            'pricing_mode' => 'per_thousand',
            'per_thousand_rate' => 1.00,
            'minimum_charge' => 15.00,
            'is_active' => 1,
        ]);

        $response = $this->withSession(['admin_user_id' => 1])
            ->postJson('/v/create-order/price-preview', [
                'flow_context' => 'order',
                'work_type' => 'digitizing',
                'website' => 'site2',
                'customer_user_id' => 77,
                'turn_around_time' => 'Standard',
                'stitches' => '4500',
            ]);

        $response->assertOk()
            ->assertJson([
                'stitches' => '4500',
                'amount' => '15.00',
            ]);
    }

    public function test_price_preview_rejects_customer_from_wrong_website_scope(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])
            ->postJson('/v/create-order/price-preview', [
                'flow_context' => 'order',
                'work_type' => 'digitizing',
                'website' => '1dollar',
                'customer_user_id' => 77,
                'turn_around_time' => 'Standard',
                'stitches' => '4500',
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The selected customer user ID was not found.',
            ]);
    }
}
