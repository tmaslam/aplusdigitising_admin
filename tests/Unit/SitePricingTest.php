<?php

namespace Tests\Unit;

use App\Models\AdminUser;
use App\Support\SitePricing;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SitePricingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('site_pricing_profiles');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

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
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
        });

        Schema::create('site_pricing_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('profile_name', 150);
            $table->string('work_type', 50)->default('digitizing');
            $table->string('turnaround_code', 50)->nullable();
            $table->string('pricing_mode', 50)->default('per_thousand');
            $table->decimal('fixed_price', 12, 2)->nullable();
            $table->decimal('per_thousand_rate', 12, 4)->nullable();
            $table->decimal('minimum_charge', 12, 2)->nullable();
            $table->decimal('included_units', 12, 2)->nullable();
            $table->decimal('overage_rate', 12, 4)->nullable();
            $table->string('package_name', 150)->nullable();
            $table->text('config_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('normal_fee', 20)->nullable();
            $table->string('middle_fee', 20)->nullable();
            $table->string('urgent_fee', 20)->nullable();
            $table->string('super_fee', 20)->nullable();
            $table->string('max_num_stiches', 20)->nullable();
        });

        $now = now()->format('Y-m-d H:i:s');

        DB::table('sites')->insert([
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
            'is_primary' => 1,
            'is_active' => 1,
        ]);

        DB::table('site_pricing_profiles')->insert([
            [
                'site_id' => 1,
                'profile_name' => 'Standard digitizing',
                'work_type' => 'digitizing',
                'turnaround_code' => 'standard',
                'pricing_mode' => 'per_thousand',
                'fixed_price' => null,
                'per_thousand_rate' => 1.20,
                'minimum_charge' => 6.00,
                'included_units' => null,
                'overage_rate' => null,
                'package_name' => null,
                'config_json' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Vector rush',
                'work_type' => 'vector',
                'turnaround_code' => 'priority',
                'pricing_mode' => 'fixed_price',
                'fixed_price' => 8.00,
                'per_thousand_rate' => null,
                'minimum_charge' => null,
                'included_units' => null,
                'overage_rate' => null,
                'package_name' => null,
                'config_json' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Super Rush digitizing',
                'work_type' => 'digitizing',
                'turnaround_code' => 'superrush',
                'pricing_mode' => 'per_thousand',
                'fixed_price' => null,
                'per_thousand_rate' => 2.00,
                'minimum_charge' => 12.00,
                'included_units' => null,
                'overage_rate' => null,
                'package_name' => null,
                'config_json' => null,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function test_site_profile_is_used_when_customer_has_no_override(): void
    {
        $customer = new AdminUser([
            'site_id' => 1,
            'website' => '1dollar',
            'normal_fee' => '',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'max_num_stiches' => '',
        ]);

        $small = SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '5500');
        $large = SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '8000');

        // 5500 * 1.20/1000 = 6.60 which exceeds the $6 minimum
        $this->assertSame(6.6, $small);
        $this->assertSame(9.6, $large);
    }

    public function test_customer_override_takes_precedence_over_site_default(): void
    {
        $customer = new AdminUser([
            'site_id' => 1,
            'website' => '1dollar',
            'normal_fee' => '2.00',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'max_num_stiches' => '',
        ]);

        $small = SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '4000');
        $large = SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '8000');

        $this->assertSame(12.0, $small);
        $this->assertSame(16.0, $large);
    }

    public function test_customer_stitch_cap_does_not_bypass_minimum_charge(): void
    {
        $customer = new AdminUser([
            'site_id' => 1,
            'website' => '1dollar',
            'normal_fee' => '1.00',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'max_num_stiches' => '5000',
        ]);

        $small = SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '4500');

        $this->assertSame(6.0, $small);
    }

    public function test_super_rush_uses_customer_super_fee_when_present(): void
    {
        $customer = new AdminUser([
            'site_id' => 1,
            'website' => '1dollar',
            'normal_fee' => '1.00',
            'middle_fee' => '1.50',
            'urgent_fee' => '2.00',
            'super_fee' => '3.50',
            'max_num_stiches' => '',
        ]);

        $price = SitePricing::embroidery($customer, '1dollar', 1, 'Superrush', '4000');

        $this->assertSame(21.0, $price);
    }

    public function test_vector_profile_can_define_hourly_rate(): void
    {
        $price = SitePricing::vector('1dollar', 1, 'Priority', '1:30');

        $this->assertSame(12.0, $price);
    }

    public function test_site_pricing_matches_profiles_even_when_work_type_and_turnaround_are_stored_with_mixed_case(): void
    {
        DB::table('site_pricing_profiles')->insert([
            'site_id' => 1,
            'profile_name' => 'Mixed Case Color',
            'work_type' => ' Color ',
            'turnaround_code' => ' Priority ',
            'pricing_mode' => 'fixed_price',
            'fixed_price' => 10.00,
            'per_thousand_rate' => null,
            'minimum_charge' => null,
            'included_units' => null,
            'overage_rate' => null,
            'package_name' => null,
            'config_json' => null,
            'is_active' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $price = SitePricing::color('1dollar', 1, 'Priority', '1:00');

        $this->assertSame(10.0, $price);
    }

    public function test_minimum_charge_applies_regardless_of_stitch_count(): void
    {
        DB::table('site_pricing_profiles')->where('work_type', 'digitizing')->update([
            'per_thousand_rate' => 1.00,
            'minimum_charge' => 10.00,
        ]);

        $customer = new AdminUser([
            'site_id' => 1,
            'website' => '1dollar',
            'normal_fee' => '',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'max_num_stiches' => '',
        ]);

        $this->assertSame(10.0, SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '6000'));
        $this->assertSame(10.0, SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '8000'));
        $this->assertSame(11.0, SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '11000'));
    }

    public function test_customer_rate_override_cannot_bypass_site_minimum_charge(): void
    {
        DB::table('site_pricing_profiles')
            ->where('work_type', 'digitizing')
            ->where('turnaround_code', 'standard')
            ->update([
                'per_thousand_rate' => 1.00,
                'minimum_charge' => 15.00,
            ]);

        $customer = new AdminUser([
            'site_id' => 1,
            'website' => '1dollar',
            'normal_fee' => '1.00',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'max_num_stiches' => '',
        ]);

        $schedule = SitePricing::turnaroundFeeSchedule($customer, new \App\Support\SiteContext(
            id: 1,
            legacyKey: '1dollar',
            slug: '1dollar',
            name: 'A Plus Digitizing',
            brandName: 'A Plus Digitizing',
            host: 'localhost',
            supportEmail: 'support@example.com',
            fromEmail: 'support@example.com',
            websiteAddress: 'https://localhost',
            isPrimary: true,
            timezone: 'UTC',
        ), 'digitizing', true);

        $this->assertSame(15.0, $schedule['standard']['minimum']);
        $this->assertSame(15.0, SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '500'));
    }

    public function test_signup_package_fees_can_come_from_site_package_profile(): void
    {
        DB::table('site_pricing_profiles')->insert([
            'site_id' => 1,
            'profile_name' => 'BUSINESS Package',
            'work_type' => 'digitizing',
            'turnaround_code' => '',
            'pricing_mode' => 'package',
            'fixed_price' => null,
            'per_thousand_rate' => null,
            'minimum_charge' => null,
            'included_units' => null,
            'overage_rate' => null,
            'package_name' => 'BUSINESS',
            'config_json' => json_encode([
                'standard_rate' => 1.75,
                'priority_rate' => 2.25,
                'superrush_rate' => 3.25,
            ]),
            'is_active' => 1,
            'created_at' => now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $site = new \App\Support\SiteContext(
            id: 1,
            legacyKey: '1dollar',
            slug: '1dollar',
            name: 'A Plus Digitizing',
            brandName: 'A Plus Digitizing',
            host: 'localhost',
            supportEmail: 'support@example.com',
            fromEmail: 'support@example.com',
            websiteAddress: 'https://localhost',
            isPrimary: true,
            timezone: 'UTC',
        );

        $fees = SitePricing::signupPackageFees($site, 'BUSINESS');

        $this->assertSame([1.75, 2.25, 3.25], $fees);
    }

    public function test_signup_package_fees_do_not_fall_back_to_hard_coded_customer_overrides(): void
    {
        $site = new \App\Support\SiteContext(
            id: 1,
            legacyKey: '1dollar',
            slug: '1dollar',
            name: 'A Plus Digitizing',
            brandName: 'A Plus Digitizing',
            host: 'localhost',
            supportEmail: 'support@example.com',
            fromEmail: 'support@example.com',
            websiteAddress: 'https://localhost',
            isPrimary: true,
            timezone: 'UTC',
        );

        $fees = SitePricing::signupPackageFees($site, 'BASIC');

        $this->assertSame([null, null, null], $fees);
    }

    public function test_fee_schedule_with_customer_overrides_matches_actual_pricing(): void
    {
        $site = new \App\Support\SiteContext(
            id: 1,
            legacyKey: '1dollar',
            slug: '1dollar',
            name: 'A Plus Digitizing',
            brandName: 'A Plus Digitizing',
            host: 'localhost',
            supportEmail: 'support@example.com',
            fromEmail: 'support@example.com',
            websiteAddress: 'https://localhost',
            isPrimary: true,
            timezone: 'UTC',
        );

        $customer = new AdminUser([
            'site_id' => 1,
            'website' => '1dollar',
            'normal_fee' => '2.00',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'max_num_stiches' => '',
        ]);

        $withOverrides = SitePricing::turnaroundFeeSchedule($customer, $site, 'digitizing', true);
        $withoutOverrides = SitePricing::turnaroundFeeSchedule($customer, $site, 'digitizing', false);

        $this->assertSame(2.0, $withOverrides['standard']['amount']);
        $this->assertSame(1.2, $withoutOverrides['standard']['amount']);

        $actualPrice = SitePricing::embroidery($customer, '1dollar', 1, 'Standard', '8000');
        $this->assertSame(16.0, $actualPrice);
        $this->assertSame($withOverrides['standard']['amount'], 2.0);
    }
}
