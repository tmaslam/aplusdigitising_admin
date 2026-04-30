<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminCustomerProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('customer_activation_tokens');
        Schema::dropIfExists('site_promotion_claims');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company')->nullable();
            $table->string('company_type')->nullable();
            $table->string('company_address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('user_city')->nullable();
            $table->string('user_country')->nullable();
            $table->string('user_phone')->nullable();
            $table->string('user_fax')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('userip_addrs', 100)->nullable();
            $table->string('user_term', 20)->nullable();
            $table->string('exist_customer', 40)->nullable();
            $table->string('normal_fee')->nullable();
            $table->string('middle_fee')->nullable();
            $table->string('urgent_fee')->nullable();
            $table->string('super_fee')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('customer_pending_order_limit')->nullable();
            $table->string('customer_approval_limit')->nullable();
            $table->string('single_approval_limit')->nullable();
            $table->string('topup')->nullable();
            $table->string('max_num_stiches')->nullable();
            $table->integer('is_active')->default(1);
        });

        Schema::create('site_promotion_claims', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->unsignedBigInteger('site_promotion_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->string('status', 30)->nullable();
            $table->integer('verification_required')->default(1);
            $table->string('verified_at', 30)->nullable();
            $table->integer('payment_required')->default(1);
            $table->decimal('required_payment_amount', 10, 2)->nullable();
            $table->decimal('credit_amount', 10, 2)->nullable();
            $table->decimal('first_order_flat_amount', 10, 2)->nullable();
            $table->text('offer_snapshot_json')->nullable();
            $table->unsignedBigInteger('payment_transaction_id')->nullable();
            $table->string('payment_reference', 120)->nullable();
            $table->string('paid_at', 30)->nullable();
            $table->unsignedBigInteger('redeemed_order_id')->nullable();
            $table->string('redeemed_at', 30)->nullable();
            $table->string('created_at', 30)->nullable();
            $table->string('updated_at', 30)->nullable();
        });

        Schema::create('customer_activation_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('site_legacy_key', 30)->nullable();
            $table->unsignedBigInteger('customer_user_id');
            $table->string('selector', 32);
            $table->string('token_hash', 64);
            $table->string('expires_at', 30)->nullable();
            $table->string('created_at', 30)->nullable();
        });

        AdminUser::query()->insert([
            'user_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'admin',
            'user_email' => 'admin@example.com',
            'is_active' => 1,
        ]);

        AdminUser::query()->insert([
            'user_id' => 602,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-602',
            'user_email' => 'customer602@example.com',
            'userip_addrs' => '198.51.100.10',
            'user_term' => '',
            'exist_customer' => '1',
            'payment_terms' => '7',
            'is_active' => 1,
        ]);

        AdminUser::query()->insert([
            'user_id' => 603,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'verify-manual',
            'user_email' => 'verify-manual@example.com',
            'user_term' => 'dc',
            'exist_customer' => '0',
            'is_active' => 0,
        ]);

        AdminUser::query()->insert([
            'user_id' => 604,
            'site_id' => 1,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'verify-payment',
            'user_email' => 'verify-payment@example.com',
            'user_term' => 'ip',
            'exist_customer' => '0',
            'is_active' => 0,
        ]);

        DB::table('customer_activation_tokens')->insert([
            'id' => 1,
            'site_id' => 1,
            'site_legacy_key' => '1dollar',
            'customer_user_id' => 603,
            'selector' => 'selector-603',
            'token_hash' => 'hash-603',
            'expires_at' => '2026-04-30 10:00:00',
            'created_at' => '2026-04-25 10:00:00',
        ]);

        DB::table('site_promotion_claims')->insert([
            [
                'id' => 1,
                'site_id' => 1,
                'site_promotion_id' => 1,
                'user_id' => 603,
                'website' => '1dollar',
                'status' => 'pending_verification',
                'verification_required' => 1,
                'verified_at' => null,
                'payment_required' => 1,
                'required_payment_amount' => 1.00,
                'credit_amount' => 0.00,
                'first_order_flat_amount' => 1.00,
                'offer_snapshot_json' => '{}',
                'created_at' => '2026-04-25 10:00:00',
                'updated_at' => '2026-04-25 10:00:00',
            ],
            [
                'id' => 2,
                'site_id' => 1,
                'site_promotion_id' => 1,
                'user_id' => 604,
                'website' => '1dollar',
                'status' => 'pending_payment',
                'verification_required' => 1,
                'verified_at' => '2026-04-25 11:00:00',
                'payment_required' => 1,
                'required_payment_amount' => 1.00,
                'credit_amount' => 0.00,
                'first_order_flat_amount' => 1.00,
                'offer_snapshot_json' => '{}',
                'created_at' => '2026-04-25 10:00:00',
                'updated_at' => '2026-04-25 11:00:00',
            ],
        ]);
    }

    public function test_admin_can_update_customer_signup_ip(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])->post('/v/edit-customer-detail.php', [
            'uid' => 602,
            'user_name' => 'customer-602',
            'txtFirstName' => '',
            'txtLastName' => '',
            'txtCompany' => '',
            'selCompanyTypes' => '',
            'txtEmail' => 'customer602@example.com',
            'txtCompanyAddress' => '',
            'txtZipCode' => '',
            'txtCity' => '',
            'selCountry' => '',
            'txtTelephone' => '',
            'txtFax' => '',
            'txtContactPerson' => '',
            'txtSignupIp' => '203.0.113.25',
            'user_type' => '1',
            'is_active' => '1',
            'normal_fee' => '',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'payment_terms' => '7',
            'customer_pending_order_limit' => '',
            'customer_approval_limit' => '',
            'single_approval_limit' => '',
            'topup' => '',
            'max_num_stiches' => '',
        ]);

        $response->assertRedirect('/v/customer_list.php');
        $this->assertSame('203.0.113.25', AdminUser::query()->find(602)?->userip_addrs);
    }

    public function test_admin_can_clear_customer_signup_ip(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])->post('/v/edit-customer-detail.php', [
            'uid' => 602,
            'user_name' => 'customer-602',
            'txtFirstName' => '',
            'txtLastName' => '',
            'txtCompany' => '',
            'selCompanyTypes' => '',
            'txtEmail' => 'customer602@example.com',
            'txtCompanyAddress' => '',
            'txtZipCode' => '',
            'txtCity' => '',
            'selCountry' => '',
            'txtTelephone' => '',
            'txtFax' => '',
            'txtContactPerson' => '',
            'txtSignupIp' => '',
            'user_type' => '1',
            'is_active' => '1',
            'normal_fee' => '',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'payment_terms' => '7',
            'customer_pending_order_limit' => '',
            'customer_approval_limit' => '',
            'single_approval_limit' => '',
            'topup' => '',
            'max_num_stiches' => '',
        ]);

        $response->assertRedirect('/v/customer_list.php');
        $this->assertSame('', AdminUser::query()->find(602)?->userip_addrs);
    }

    public function test_admin_edit_preserves_customer_type_regardless_of_submitted_value(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])->post('/v/edit-customer-detail.php', [
            'uid' => 602,
            'user_name' => 'customer-602',
            'txtFirstName' => '',
            'txtLastName' => '',
            'txtCompany' => '',
            'selCompanyTypes' => '',
            'txtEmail' => 'customer602@example.com',
            'txtCompanyAddress' => '',
            'txtZipCode' => '',
            'txtCity' => '',
            'selCountry' => '',
            'txtTelephone' => '',
            'txtFax' => '',
            'txtContactPerson' => '',
            'txtSignupIp' => '',
            'user_type' => '3',
            'is_active' => '1',
            'normal_fee' => '',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'payment_terms' => '7',
            'customer_pending_order_limit' => '',
            'customer_approval_limit' => '',
            'single_approval_limit' => '',
            'topup' => '',
            'max_num_stiches' => '',
        ]);

        $response->assertRedirect('/v/customer_list.php');
        $this->assertSame(AdminUser::TYPE_CUSTOMER, (int) AdminUser::query()->find(602)?->usre_type_id);
    }

    public function test_admin_edit_activating_manual_verification_customer_clears_pending_signup_state(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])->post('/v/edit-customer-detail.php', [
            'uid' => 603,
            'user_name' => 'verify-manual',
            'txtFirstName' => '',
            'txtLastName' => '',
            'txtCompany' => '',
            'selCompanyTypes' => '',
            'txtEmail' => 'verify-manual@example.com',
            'txtCompanyAddress' => '',
            'txtZipCode' => '',
            'txtCity' => '',
            'selCountry' => '',
            'txtTelephone' => '',
            'txtFax' => '',
            'txtContactPerson' => '',
            'txtSignupIp' => '',
            'is_active' => '1',
            'normal_fee' => '',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'payment_terms' => '7',
            'customer_pending_order_limit' => '',
            'customer_approval_limit' => '',
            'single_approval_limit' => '',
            'topup' => '',
            'max_num_stiches' => '',
        ]);

        $response->assertRedirect('/v/customer_list.php');
        $this->assertSame(1, (int) AdminUser::query()->find(603)?->is_active);
        $this->assertSame('1', (string) AdminUser::query()->find(603)?->exist_customer);
        $this->assertSame(0, DB::table('customer_activation_tokens')->where('customer_user_id', 603)->count());
        $this->assertSame('paid', (string) DB::table('site_promotion_claims')->where('user_id', 603)->value('status'));
        $this->assertSame(0, (int) DB::table('site_promotion_claims')->where('user_id', 603)->value('payment_required'));
        $this->assertStringStartsWith('admin-approved-offer:', (string) DB::table('site_promotion_claims')->where('user_id', 603)->value('payment_reference'));
    }

    public function test_admin_edit_activating_welcome_payment_customer_waives_payment_gate(): void
    {
        $response = $this->withSession(['admin_user_id' => 1])->post('/v/edit-customer-detail.php', [
            'uid' => 604,
            'user_name' => 'verify-payment',
            'txtFirstName' => '',
            'txtLastName' => '',
            'txtCompany' => '',
            'selCompanyTypes' => '',
            'txtEmail' => 'verify-payment@example.com',
            'txtCompanyAddress' => '',
            'txtZipCode' => '',
            'txtCity' => '',
            'selCountry' => '',
            'txtTelephone' => '',
            'txtFax' => '',
            'txtContactPerson' => '',
            'txtSignupIp' => '',
            'is_active' => '1',
            'normal_fee' => '',
            'middle_fee' => '',
            'urgent_fee' => '',
            'super_fee' => '',
            'payment_terms' => '7',
            'customer_pending_order_limit' => '',
            'customer_approval_limit' => '',
            'single_approval_limit' => '',
            'topup' => '',
            'max_num_stiches' => '',
        ]);

        $response->assertRedirect('/v/customer_list.php');
        $this->assertSame(1, (int) AdminUser::query()->find(604)?->is_active);
        $this->assertSame('1', (string) AdminUser::query()->find(604)?->exist_customer);
        $this->assertSame('paid', (string) DB::table('site_promotion_claims')->where('user_id', 604)->value('status'));
        $this->assertSame(0, (int) DB::table('site_promotion_claims')->where('user_id', 604)->value('payment_required'));
        $this->assertStringStartsWith('admin-approved-offer:', (string) DB::table('site_promotion_claims')->where('user_id', 604)->value('payment_reference'));
    }
}
