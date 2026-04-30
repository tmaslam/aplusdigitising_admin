<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\PasswordManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerSignupOfferFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('orders');
        Schema::dropIfExists('site_promotion_claims');
        Schema::dropIfExists('site_promotions');
        Schema::dropIfExists('site_pricing_profiles');
        Schema::dropIfExists('customer_activation_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('site_domains');
        Schema::dropIfExists('sites');
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
            $table->string('pricing_strategy', 50)->default('customer_rate');
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('settings_json')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('site_domains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('host')->unique();
            $table->boolean('is_primary')->default(true);
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
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
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name', 100)->nullable();
            $table->string('user_password', 100)->nullable();
            $table->string('security_key', 100);
            $table->decimal('normal_fee', 12, 2)->nullable();
            $table->decimal('middle_fee', 12, 2)->nullable();
            $table->decimal('urgent_fee', 12, 2)->nullable();
            $table->decimal('super_fee', 12, 2)->nullable();
            $table->string('payment_terms', 30)->nullable();
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('user_email', 190)->nullable();
            $table->string('alternate_email', 190);
            $table->string('company', 150)->nullable();
            $table->string('company_type', 100)->nullable();
            $table->string('company_address', 500)->nullable();
            $table->string('zip_code', 50)->nullable();
            $table->string('user_city', 100)->nullable();
            $table->string('user_country', 100)->nullable();
            $table->string('user_phone', 100)->nullable();
            $table->integer('is_active')->default(0);
            $table->string('exist_customer', 10)->nullable();
            $table->string('date_added', 30)->nullable();
            $table->string('customer_approval_limit', 30)->nullable();
            $table->string('single_approval_limit', 30)->nullable();
            $table->string('customer_pending_order_limit', 30)->nullable();
            $table->string('userip_addrs', 100)->nullable();
            $table->string('user_term', 10)->nullable();
            $table->string('package_type', 30)->nullable();
            $table->string('real_user', 10)->nullable();
            $table->string('ref_code', 100)->nullable();
            $table->string('ref_code_other', 150)->nullable();
            $table->string('digitzing_format', 100);
            $table->string('vertor_format', 100);
            $table->string('topup', 30);
            $table->string('register_by', 100);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('order_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assign_to')->nullable();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('order_type', 30)->nullable();
            $table->string('type', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->string('turn_around_time', 30)->nullable();
            $table->string('stitches', 255)->nullable();
            $table->string('stitches_price', 255)->nullable();
            $table->string('total_amount', 255)->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
            $table->string('submit_date', 30)->nullable();
        });

        PasswordManager::refreshColumnAvailability();

        Schema::create('customer_activation_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('site_legacy_key', 30);
            $table->unsignedBigInteger('customer_user_id');
            $table->string('selector', 64);
            $table->string('token_hash', 64);
            $table->dateTime('expires_at');
            $table->dateTime('created_at');
        });

        Schema::create('site_promotions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->string('promotion_name', 150);
            $table->string('promotion_code', 100)->nullable();
            $table->string('work_type', 50)->nullable();
            $table->string('discount_type', 50)->default('fixed');
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('minimum_order_amount', 12, 2)->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->text('config_json')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('site_promotion_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('site_promotion_id');
            $table->unsignedBigInteger('user_id');
            $table->string('website', 30)->default('1dollar');
            $table->string('status', 50)->default('pending_verification');
            $table->boolean('verification_required')->default(true);
            $table->dateTime('verified_at')->nullable();
            $table->boolean('payment_required')->default(false);
            $table->decimal('required_payment_amount', 12, 2)->default(0);
            $table->decimal('credit_amount', 12, 2)->default(0);
            $table->decimal('first_order_flat_amount', 12, 2)->nullable();
            $table->text('offer_snapshot_json')->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->string('payment_reference')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->unsignedBigInteger('redeemed_order_id')->nullable();
            $table->dateTime('redeemed_at')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
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
            'pricing_strategy' => 'customer_rate',
            'is_primary' => 1,
            'is_active' => 1,
            'settings_json' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('site_domains')->insert([
            'site_id' => 1,
            'host' => 'localhost',
            'is_primary' => 1,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('site_promotions')->insert([
            'id' => 1,
            'site_id' => 1,
            'promotion_name' => 'New member welcome offer',
            'promotion_code' => 'WELCOME5',
            'work_type' => 'signup',
            'discount_type' => 'signup_offer',
            'discount_value' => 5.00,
            'minimum_order_amount' => null,
            'starts_at' => now()->subDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'config_json' => json_encode([
                'headline' => 'Pay $1 and your first order is free under 10k stitches',
                'summary' => 'Verify your email and complete the secure $1 welcome payment before you start ordering.',
                'verification_message' => 'If the verification email is not in your inbox, please check spam or junk.',
                'onboarding_payment_amount' => 1,
                'credit_amount' => 0,
                'first_order_flat_amount' => 0,
                'first_order_free_under_stitches' => 10000,
            ]),
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('site_pricing_profiles')->insert([
            [
                'site_id' => 1,
                'profile_name' => 'Digitizing Standard',
                'work_type' => 'digitizing',
                'turnaround_code' => 'standard',
                'pricing_mode' => 'per_thousand',
                'per_thousand_rate' => 1.00,
                'minimum_charge' => 6.00,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Digitizing Priority',
                'work_type' => 'digitizing',
                'turnaround_code' => 'priority',
                'pricing_mode' => 'per_thousand',
                'per_thousand_rate' => 1.50,
                'minimum_charge' => 9.00,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'site_id' => 1,
                'profile_name' => 'Digitizing Super Rush',
                'work_type' => 'digitizing',
                'turnaround_code' => 'superrush',
                'pricing_mode' => 'per_thousand',
                'per_thousand_rate' => 2.00,
                'minimum_charge' => 12.00,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        DB::table('users')->insert([
            'user_id' => 1,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'main-admin',
            'user_password' => '',
            'security_key' => 'admin-security-key',
            'normal_fee' => 0,
            'middle_fee' => 0,
            'urgent_fee' => 0,
            'super_fee' => 0,
            'payment_terms' => '7',
            'first_name' => 'Main',
            'last_name' => 'Admin',
            'user_email' => 'admin@example.com',
            'alternate_email' => '',
            'company' => '',
            'company_type' => '',
            'company_address' => '',
            'zip_code' => '',
            'user_city' => '',
            'user_country' => 'United States',
            'user_phone' => '',
            'is_active' => 1,
            'exist_customer' => '1',
            'date_added' => $now,
            'customer_approval_limit' => '0',
            'single_approval_limit' => '0',
            'customer_pending_order_limit' => '0',
            'userip_addrs' => '127.0.0.1',
            'user_term' => '',
            'package_type' => '',
            'real_user' => '1',
            'ref_code' => '',
            'ref_code_other' => '',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ]);
    }

    public function test_signup_page_shows_active_welcome_offer(): void
    {
        $this->get('/sign-up.php')
            ->assertOk()
            ->assertSee('Secure welcome payment', false)
            ->assertSee('new customer offer', false)
            ->assertSee('check spam or junk', false)
            ->assertSee('name="confirmuseremail"', false)
            ->assertSee('autocomplete="off"', false)
            ->assertSee('Latvia')
            ->assertSee('Full-Service Apparel Decorator (Embroidery &amp; Printing)', false);
    }

    public function test_signup_page_clears_active_customer_session_before_rendering(): void
    {
        $response = $this->withSession([
            'customer_user_id' => 9001,
            'customer_user_name' => 'Existing Customer',
            'customer_site_key' => '1dollar',
        ])->get('/sign-up.php');

        $response
            ->assertOk()
            ->assertSessionMissing('customer_user_id')
            ->assertSessionMissing('customer_user_name')
            ->assertSessionMissing('customer_site_key');
    }

    public function test_signup_submission_creates_inactive_customer_activation_token_and_offer_claim_for_payment_path(): void
    {
        $response = $this->post('/sign-up.php', [
            'first_name' => 'Fresh',
            'last_name' => 'Customer',
            'selCountry' => 'United States',
            'telephone_num' => '555-0100',
            'package_type' => 'BASIC',
            'useremail' => 'fresh.customer@gmail.com',
            'confirmuseremail' => 'fresh.customer@gmail.com',
            'user_psw' => 'secret123',
            'confirm_psw' => 'secret123',
            'term' => 'ip',
            'selCompanyTypes' => 'Home Business',
            'company_name' => 'Fresh Customer Co',
            'company_address' => '123 Main Street',
            'refraloptions' => 'Google Search',
            'refralcode' => '',
            'terms' => '1',
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(url('/login.php'), $response->headers->get('Location'));
        $response->assertSessionHas('success');

        $customer = DB::table('users')
            ->where('user_email', 'fresh.customer@gmail.com')
            ->first();

        $this->assertNotNull($customer);
        $this->assertSame(0, (int) $customer->is_active);
        $this->assertSame('1dollar', $customer->website);
        $this->assertNotSame('', (string) $customer->security_key);
        $this->assertSame('', (string) $customer->alternate_email);
        $this->assertSame('', (string) $customer->digitzing_format);
        $this->assertSame('', (string) $customer->vertor_format);
        $this->assertSame('', (string) $customer->topup);
        $this->assertSame('1dollar', (string) $customer->register_by);
        $this->assertSame('1.00', number_format((float) $customer->normal_fee, 2, '.', ''));
        $this->assertSame('1.00', number_format((float) $customer->middle_fee, 2, '.', ''));
        $this->assertSame('1.50', number_format((float) $customer->urgent_fee, 2, '.', ''));
        $this->assertSame('2.00', number_format((float) $customer->super_fee, 2, '.', ''));

        $this->assertSame(1, DB::table('customer_activation_tokens')->where('customer_user_id', $customer->user_id)->count());
        $this->assertSame('pending_verification', DB::table('site_promotion_claims')->where('user_id', $customer->user_id)->value('status'));
        $this->assertSame('1.00', number_format((float) DB::table('site_promotion_claims')->where('user_id', $customer->user_id)->value('required_payment_amount'), 2, '.', ''));
    }

    public function test_signup_offer_page_shows_verified_copy_after_admin_or_email_verification(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        config()->set('app.env', 'local');
        config()->set('services.twocheckout.simulation_enabled', true);

        DB::table('users')->insert([
            'user_id' => 5001,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'verified-offer-user',
            'user_password' => '',
            'security_key' => 'abc123',
            'normal_fee' => 1,
            'urgent_fee' => 1.5,
            'super_fee' => 3,
            'payment_terms' => '7',
            'first_name' => 'Verified',
            'last_name' => 'Offer',
            'user_email' => 'verified.offer@example.com',
            'alternate_email' => '',
            'company' => 'Verified Offer Co',
            'company_type' => 'Home Business',
            'company_address' => '123 Main',
            'zip_code' => '',
            'user_city' => '',
            'user_country' => 'United States',
            'user_phone' => '555-0100',
            'is_active' => 1,
            'exist_customer' => '1',
            'date_added' => $now,
            'customer_approval_limit' => '15',
            'single_approval_limit' => '10',
            'customer_pending_order_limit' => '0',
            'userip_addrs' => '127.0.0.1',
            'user_term' => 'ip',
            'package_type' => 'BASIC',
            'real_user' => '1',
            'ref_code' => 'verified-offer',
            'ref_code_other' => 'Google',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ]);

        DB::table('site_promotion_claims')->insert([
            'site_id' => 1,
            'site_promotion_id' => 1,
            'user_id' => 5001,
            'website' => '1dollar',
            'status' => 'pending_payment',
            'verification_required' => 1,
            'verified_at' => $now,
            'payment_required' => 1,
            'required_payment_amount' => 1.00,
            'credit_amount' => 0.00,
            'first_order_flat_amount' => 0.00,
            'offer_snapshot_json' => '{}',
            'payment_transaction_id' => null,
            'payment_reference' => null,
            'paid_at' => null,
            'redeemed_order_id' => null,
            'redeemed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->withSession([
            'customer_user_id' => 5001,
            'customer_user_name' => 'Verified Offer',
            'customer_site_key' => '1dollar',
        ])->get('/member-offer.php');

        $response->assertOk();
        $response->assertSeeText('Email Verified');
        $response->assertSeeText('Your email is already verified. Complete the secure onboarding payment');
        $response->assertDontSeeText('Verification Required');
        $response->assertDontSeeText('Why this step exists:');
        $response->assertSee('name="provider" value="2checkout_hosted"', false);
        $response->assertSee('signup-offer-payment-form', false);
    }

    public function test_signup_submission_for_admin_approval_path_creates_pending_offer_claim_without_payment_gate(): void
    {
        $response = $this->post('/sign-up.php', [
            'first_name' => 'Manual',
            'last_name' => 'Review',
            'selCountry' => 'United States',
            'telephone_num' => '555-0101',
            'package_type' => 'BASIC',
            'useremail' => 'manual.review@gmail.com',
            'confirmuseremail' => 'manual.review@gmail.com',
            'user_psw' => 'secret123',
            'confirm_psw' => 'secret123',
            'term' => 'dc',
            'selCompanyTypes' => 'Home Business',
            'company_name' => 'Manual Review Co',
            'company_address' => '123 Main Street',
            'refraloptions' => 'Google Search',
            'refralcode' => '',
            'terms' => '1',
        ]);

        $response->assertRedirect(url('/login.php'));
        $response->assertSessionHas('success');

        $customer = DB::table('users')
            ->where('user_email', 'manual.review@gmail.com')
            ->first();

        $this->assertNotNull($customer);
        $this->assertSame(0, (int) $customer->is_active);
        $this->assertSame('dc', (string) $customer->user_term);
        $this->assertSame(1, DB::table('customer_activation_tokens')->where('customer_user_id', $customer->user_id)->count());
        $this->assertSame(1, DB::table('site_promotion_claims')->where('user_id', $customer->user_id)->count());
        $this->assertSame('pending_verification', DB::table('site_promotion_claims')->where('user_id', $customer->user_id)->value('status'));
    }

    public function test_signup_allows_reuse_of_email_after_soft_deleted_customer_record(): void
    {
        DB::table('users')->insert([
            'user_id' => 57,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'deletedmember',
            'user_password' => 'legacy-password',
            'security_key' => 'fixture-security-key-57',
            'normal_fee' => 1,
            'urgent_fee' => 1.5,
            'super_fee' => 3,
            'payment_terms' => '7',
            'first_name' => 'Deleted',
            'last_name' => 'Member',
            'user_email' => 'deleted.member@example.com',
            'alternate_email' => '',
            'company' => 'Deleted Member Co',
            'company_type' => 'Home Business',
            'company_address' => '123 Main',
            'zip_code' => '',
            'user_city' => '',
            'user_country' => 'United States',
            'user_phone' => '555-0100',
            'is_active' => 0,
            'exist_customer' => '1',
            'date_added' => now()->subDay()->format('Y-m-d H:i:s'),
            'customer_approval_limit' => '15',
            'single_approval_limit' => '10',
            'customer_pending_order_limit' => '0',
            'userip_addrs' => '127.0.0.1',
            'user_term' => 'ip',
            'package_type' => 'BASIC',
            'real_user' => '1',
            'ref_code' => 'deleted-member',
            'ref_code_other' => 'Google',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => now()->subHour()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->post('/sign-up.php', [
            'first_name' => 'Fresh',
            'last_name' => 'Again',
            'selCountry' => 'United States',
            'telephone_num' => '555-0100',
            'package_type' => 'BASIC',
            'useremail' => 'deleted.member@example.com',
            'confirmuseremail' => 'deleted.member@example.com',
            'user_psw' => 'secret123',
            'confirm_psw' => 'secret123',
            'term' => 'ip',
            'selCompanyTypes' => 'Home Business',
            'company_name' => 'Fresh Again Co',
            'company_address' => '123 Main Street',
            'refraloptions' => 'Google Search',
            'refralcode' => '',
            'terms' => '1',
        ]);

        $response->assertRedirect(url('/login.php'));
        $response->assertSessionHas('success');

        $activeCustomers = DB::table('users')
            ->where('user_email', 'deleted.member@example.com')
            ->orderBy('user_id')
            ->get();

        $this->assertCount(2, $activeCustomers);
        $this->assertSame(57, (int) $activeCustomers[0]->user_id);
        $this->assertNotSame(57, (int) $activeCustomers[1]->user_id);
        $this->assertSame(1, DB::table('customer_activation_tokens')->where('customer_user_id', $activeCustomers[1]->user_id)->count());
    }

    public function test_signup_same_ip_error_is_generic_and_does_not_reference_ip_address(): void
    {
        DB::table('users')->insert([
            'user_id' => 59,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'sameipcustomer',
            'user_password' => 'legacy-password',
            'security_key' => 'fixture-security-key-59',
            'normal_fee' => 1,
            'urgent_fee' => 1.5,
            'super_fee' => 3,
            'payment_terms' => '7',
            'first_name' => 'Same',
            'last_name' => 'IP',
            'user_email' => 'same.ip@example.com',
            'alternate_email' => '',
            'company' => 'Same IP Co',
            'company_type' => 'Home Business',
            'company_address' => '123 Main',
            'zip_code' => '',
            'user_city' => '',
            'user_country' => 'United States',
            'user_phone' => '555-0100',
            'is_active' => 1,
            'exist_customer' => '1',
            'date_added' => now()->subDay()->format('Y-m-d H:i:s'),
            'customer_approval_limit' => '15',
            'single_approval_limit' => '10',
            'customer_pending_order_limit' => '0',
            'userip_addrs' => '127.0.0.1',
            'user_term' => 'ip',
            'package_type' => 'BASIC',
            'real_user' => '1',
            'ref_code' => 'same-ip',
            'ref_code_other' => 'Google',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ]);

        $response = $this->from('/sign-up.php')->post('/sign-up.php', [
            'first_name' => 'Fresh',
            'last_name' => 'Customer',
            'selCountry' => 'United States',
            'telephone_num' => '555-0100',
            'package_type' => 'BASIC',
            'useremail' => 'fresh.sameip@gmail.com',
            'confirmuseremail' => 'fresh.sameip@gmail.com',
            'user_psw' => 'secret123',
            'confirm_psw' => 'secret123',
            'term' => 'ip',
            'selCompanyTypes' => 'Home Business',
            'company_name' => 'Fresh Customer Co',
            'company_address' => '123 Main Street',
            'refraloptions' => 'Google Search',
            'refralcode' => '',
            'terms' => '1',
        ]);

        $response
            ->assertRedirect('/sign-up.php')
            ->assertSessionHasErrors([
                'signup' => 'We are unable to process your registration at this time. If you need assistance, please contact our support team.',
            ]);

        $response->assertSessionDoesntHaveErrors(['country']);
        $this->assertSame(1, DB::table('users')->where('user_email', 'same.ip@example.com')->count());
        $this->assertSame(0, DB::table('users')->where('user_email', 'fresh.sameip@gmail.com')->count());
    }

    public function test_signup_reuses_base_username_when_only_deleted_customer_has_that_username(): void
    {
        DB::table('users')->insert([
            'user_id' => 58,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'deletedbase',
            'user_password' => 'legacy-password',
            'security_key' => 'fixture-security-key-58',
            'normal_fee' => 1,
            'urgent_fee' => 1.5,
            'super_fee' => 3,
            'payment_terms' => '7',
            'first_name' => 'Deleted',
            'last_name' => 'Base',
            'user_email' => 'deleted.base.old@example.com',
            'alternate_email' => '',
            'company' => 'Deleted Base Co',
            'company_type' => 'Home Business',
            'company_address' => '123 Main',
            'zip_code' => '',
            'user_city' => '',
            'user_country' => 'United States',
            'user_phone' => '555-0100',
            'is_active' => 0,
            'exist_customer' => '1',
            'date_added' => now()->subDay()->format('Y-m-d H:i:s'),
            'customer_approval_limit' => '15',
            'single_approval_limit' => '10',
            'customer_pending_order_limit' => '0',
            'userip_addrs' => '198.51.100.10',
            'user_term' => 'ip',
            'package_type' => 'BASIC',
            'real_user' => '1',
            'ref_code' => 'deleted-base',
            'ref_code_other' => 'Google',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => now()->subHour()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->post('/sign-up.php', [
            'first_name' => 'Fresh',
            'last_name' => 'Base',
            'selCountry' => 'United States',
            'telephone_num' => '555-0110',
            'package_type' => 'BASIC',
            'useremail' => 'deletedbase@example.com',
            'confirmuseremail' => 'deletedbase@example.com',
            'user_psw' => 'secret123',
            'confirm_psw' => 'secret123',
            'term' => 'ip',
            'selCompanyTypes' => 'Home Business',
            'company_name' => 'Fresh Base Co',
            'company_address' => '123 Main Street',
            'refraloptions' => 'Google Search',
            'refralcode' => '',
            'terms' => '1',
        ]);

        $response->assertRedirect(url('/login.php'));

        $customer = DB::table('users')
            ->where('user_email', 'deletedbase@example.com')
            ->orderByDesc('user_id')
            ->first();

        $this->assertNotNull($customer);
        $this->assertSame('deletedbase', (string) $customer->user_name);
    }

    public function test_activation_redirects_customer_into_welcome_offer_payment_when_required(): void
    {
        $now = now()->format('Y-m-d H:i:s');

        DB::table('users')->insert([
            'user_id' => 55,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'newmember',
            'user_password' => 'secret123',
            'security_key' => 'fixture-security-key-55',
            'first_name' => 'New',
            'last_name' => 'Member',
            'user_email' => 'newmember@example.com',
            'alternate_email' => '',
            'is_active' => 0,
            'exist_customer' => '0',
            'userip_addrs' => '127.0.0.1',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ]);

        DB::table('customer_activation_tokens')->insert([
            'site_id' => 1,
            'site_legacy_key' => '1dollar',
            'customer_user_id' => 55,
            'selector' => 'selector123',
            'token_hash' => hash('sha256', 'validator123'),
            'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'created_at' => $now,
        ]);

        DB::table('site_promotion_claims')->insert([
            'site_id' => 1,
            'site_promotion_id' => 1,
            'user_id' => 55,
            'website' => '1dollar',
            'status' => 'pending_verification',
            'verification_required' => 1,
            'verified_at' => null,
            'payment_required' => 1,
            'required_payment_amount' => 1.00,
            'credit_amount' => 0.00,
            'first_order_flat_amount' => 0.00,
            'offer_snapshot_json' => null,
            'payment_transaction_id' => null,
            'payment_reference' => null,
            'paid_at' => null,
            'redeemed_order_id' => null,
            'redeemed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->get('/confirmation_registration.php?selector=selector123&token=validator123');

        $response->assertRedirect('/member-offer.php');
        $response->assertSessionHas('customer_user_id', 55);
        $this->assertSame(0, (int) DB::table('users')->where('user_id', 55)->value('is_active'));
        $this->assertSame('0', (string) DB::table('users')->where('user_id', 55)->value('exist_customer'));
        $this->assertSame('pending_payment', DB::table('site_promotion_claims')->where('user_id', 55)->value('status'));
    }

    public function test_activation_for_admin_approval_path_keeps_customer_pending_until_admin_approval(): void
    {
        $now = now()->format('Y-m-d H:i:s');

        DB::table('users')->insert([
            'user_id' => 56,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'approvalmember',
            'user_password' => 'secret123',
            'security_key' => 'fixture-security-key-56',
            'first_name' => 'Approval',
            'last_name' => 'Member',
            'user_email' => 'approvalmember@example.com',
            'alternate_email' => '',
            'is_active' => 0,
            'exist_customer' => '0',
            'user_term' => 'dc',
            'userip_addrs' => '127.0.0.1',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ]);

        DB::table('customer_activation_tokens')->insert([
            'site_id' => 1,
            'site_legacy_key' => '1dollar',
            'customer_user_id' => 56,
            'selector' => 'selector456',
            'token_hash' => hash('sha256', 'validator456'),
            'expires_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'created_at' => $now,
        ]);

        $response = $this->get('/confirmation_registration.php?selector=selector456&token=validator456');

        $response->assertOk()->assertSee('waiting for admin approval', false);
        $this->assertSame(0, (int) DB::table('users')->where('user_id', 56)->value('is_active'));
        $this->assertSame('0', DB::table('users')->where('user_id', 56)->value('exist_customer'));
        $this->assertSame(0, DB::table('customer_activation_tokens')->where('customer_user_id', 56)->count());
    }

    public function test_customer_with_pending_offer_payment_is_redirected_to_offer_gate(): void
    {
        $now = now()->format('Y-m-d H:i:s');

        DB::table('users')->insert([
            'user_id' => 72,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'pendingmember',
            'user_password' => 'secret123',
            'security_key' => 'fixture-security-key-72',
            'first_name' => 'Pending',
            'last_name' => 'Member',
            'user_email' => 'pending@example.com',
            'alternate_email' => '',
            'is_active' => 0,
            'exist_customer' => '0',
            'userip_addrs' => '127.0.0.1',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ]);

        DB::table('site_promotion_claims')->insert([
            'site_id' => 1,
            'site_promotion_id' => 1,
            'user_id' => 72,
            'website' => '1dollar',
            'status' => 'pending_payment',
            'verification_required' => 1,
            'verified_at' => $now,
            'payment_required' => 1,
            'required_payment_amount' => 1.00,
            'credit_amount' => 0.00,
            'first_order_flat_amount' => 0.00,
            'offer_snapshot_json' => null,
            'payment_transaction_id' => null,
            'payment_reference' => null,
            'paid_at' => null,
            'redeemed_order_id' => null,
            'redeemed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withSession([
            'customer_user_id' => 72,
            'customer_user_name' => 'Pending Member',
            'customer_site_key' => '1dollar',
        ])->get('/dashboard.php')->assertRedirect('/member-offer.php');
    }

    public function test_verified_unpaid_offer_customer_can_log_in_only_into_offer_gate(): void
    {
        $now = now()->format('Y-m-d H:i:s');
        config()->set('services.turnstile.enabled', false);

        DB::table('users')->insert(array_merge([
            'user_id' => 73,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'offerlogin',
            'security_key' => 'fixture-security-key-73',
            'first_name' => 'Offer',
            'last_name' => 'Login',
            'user_email' => 'offerlogin@example.com',
            'alternate_email' => '',
            'is_active' => 0,
            'exist_customer' => '0',
            'userip_addrs' => '127.0.0.1',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ], PasswordManager::payload('secret123')));

        DB::table('site_promotion_claims')->insert([
            'site_id' => 1,
            'site_promotion_id' => 1,
            'user_id' => 73,
            'website' => '1dollar',
            'status' => 'pending_payment',
            'verification_required' => 1,
            'verified_at' => $now,
            'payment_required' => 1,
            'required_payment_amount' => 1.00,
            'credit_amount' => 0.00,
            'first_order_flat_amount' => 0.00,
            'offer_snapshot_json' => null,
            'payment_transaction_id' => null,
            'payment_reference' => null,
            'paid_at' => null,
            'redeemed_order_id' => null,
            'redeemed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $response = $this->post('/login.php', [
            'user_id' => 'offerlogin@example.com',
            'user_psw' => 'secret123',
        ]);

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(url('/member-offer.php'), $response->headers->get('Location'));
        $response->assertSessionHas('customer_user_id', 73);
    }

    public function test_verified_unpaid_offer_customer_can_open_offer_page_but_not_dashboard(): void
    {
        $now = now()->format('Y-m-d H:i:s');

        DB::table('users')->insert([
            'user_id' => 74,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'offergate',
            'user_password' => '',
            'security_key' => 'fixture-security-key-74',
            'first_name' => 'Offer',
            'last_name' => 'Gate',
            'user_email' => 'offergate@example.com',
            'alternate_email' => '',
            'is_active' => 0,
            'exist_customer' => '0',
            'userip_addrs' => '127.0.0.1',
            'digitzing_format' => '',
            'vertor_format' => '',
            'topup' => '',
            'register_by' => '1dollar',
            'end_date' => null,
        ]);

        DB::table('site_promotion_claims')->insert([
            'site_id' => 1,
            'site_promotion_id' => 1,
            'user_id' => 74,
            'website' => '1dollar',
            'status' => 'pending_payment',
            'verification_required' => 1,
            'verified_at' => $now,
            'payment_required' => 1,
            'required_payment_amount' => 1.00,
            'credit_amount' => 0.00,
            'first_order_flat_amount' => 0.00,
            'offer_snapshot_json' => null,
            'payment_transaction_id' => null,
            'payment_reference' => null,
            'paid_at' => null,
            'redeemed_order_id' => null,
            'redeemed_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->withSession([
            'customer_user_id' => 74,
            'customer_user_name' => 'Offer Gate',
            'customer_site_key' => '1dollar',
        ])->get('/member-offer.php')->assertOk();

        $this->withSession([
            'customer_user_id' => 74,
            'customer_user_name' => 'Offer Gate',
            'customer_site_key' => '1dollar',
        ])->get('/dashboard.php')->assertRedirect('/member-offer.php');
    }

    public function test_manual_signup_offer_applies_to_admin_price_preview_for_first_new_order(): void
    {
        $response = $this->post('/sign-up.php', [
            'first_name' => 'Fresh',
            'last_name' => 'Manual',
            'selCountry' => 'United States',
            'telephone_num' => '555-0102',
            'package_type' => 'BASIC',
            'useremail' => 'fresh.manual@example.com',
            'confirmuseremail' => 'fresh.manual@example.com',
            'user_psw' => 'secret123',
            'confirm_psw' => 'secret123',
            'term' => 'dc',
            'selCompanyTypes' => 'Home Business',
            'company_name' => 'Fresh Manual Co',
            'company_address' => '123 Main Street',
            'refraloptions' => 'Google Search',
            'refralcode' => '',
            'terms' => '1',
        ]);

        $response->assertRedirect(url('/login.php'));

        $customer = AdminUser::query()
            ->where('user_email', 'fresh.manual@example.com')
            ->firstOrFail();

        $this->withSession(['admin_user_id' => 1])
            ->post('/v/customers/'.$customer->user_id.'/verify-email')
            ->assertRedirect('/v/customer-approvals.php');

        $this->withSession(['admin_user_id' => 1])
            ->post('/v/customers/'.$customer->user_id.'/approve')
            ->assertRedirect('/v/customer-approvals.php');

        DB::table('orders')->insert([
            'order_id' => 901,
            'user_id' => $customer->user_id,
            'assign_to' => 0,
            'site_id' => 1,
            'website' => '1dollar',
            'order_type' => 'order',
            'type' => 'digitizing',
            'status' => 'Underprocess',
            'turn_around_time' => 'Standard',
            'stitches' => '0',
            'stitches_price' => '0',
            'total_amount' => '0',
            'is_active' => 1,
            'end_date' => null,
            'submit_date' => now()->format('Y-m-d H:i:s'),
        ]);

        $preview = $this->withSession(['admin_user_id' => 1])
            ->post('/v/order-detail/price-preview', [
                'order_id' => 901,
                'stitches' => '5000',
            ]);

        $preview->assertOk();
        $preview->assertJson([
            'stitches' => '5000',
            'amount' => '0.00',
        ]);
    }
}
