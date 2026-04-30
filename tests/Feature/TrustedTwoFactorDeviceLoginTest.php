<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\PasswordManager;
use App\Support\TrustedTwoFactorDevice;
use App\Support\TwoFactorAuth;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TrustedTwoFactorDeviceLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach ([
            TrustedTwoFactorDevice::TABLE,
            'admin_password_reset_tokens',
            'customer_password_reset_tokens',
            'admin_login_attempts',
            'login_history',
            'users',
        ] as $table) {
            Schema::dropIfExists($table);
        }

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('website', 30)->nullable();
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('alternate_email')->nullable();
            $table->string('user_password')->nullable();
            $table->string('password_hash')->nullable();
            $table->dateTime('password_migrated_at')->nullable();
            $table->tinyInteger('two_factor_enabled')->default(0);
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('exist_customer')->nullable();
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('login_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('IP_Address', 45);
            $table->string('Login_Name')->nullable();
            $table->string('Password')->nullable();
            $table->string('Status')->nullable();
            $table->string('Date_Added')->nullable();
        });

        Schema::create('admin_login_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('login_name')->nullable();
            $table->unsignedBigInteger('matched_user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('request_path', 255)->nullable();
            $table->string('attempt_outcome', 40)->nullable();
            $table->string('status', 255)->nullable();
            $table->tinyInteger('is_rate_limited')->default(0);
            $table->dateTime('attempted_at');
        });

        Schema::create('admin_password_reset_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_user_id');
            $table->string('selector', 32)->unique();
            $table->string('token_hash', 64);
            $table->string('token_type', 20)->default('password_reset');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('expires_at');
            $table->dateTime('created_at');
        });

        Schema::create('customer_password_reset_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('customer_user_id');
            $table->string('site_legacy_key', 100);
            $table->string('selector', 32)->unique();
            $table->string('token_hash', 64);
            $table->string('token_type', 20)->default('password_reset');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('expires_at');
            $table->dateTime('created_at');
        });

        Schema::create(TrustedTwoFactorDevice::TABLE, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('portal', 20);
            $table->string('site_legacy_key', 100)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('selector', 32)->unique();
            $table->string('token_hash', 64);
            $table->string('user_agent_hash', 64);
            $table->string('password_signature', 64);
            $table->dateTime('expires_at');
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('created_at');
        });

        PasswordManager::refreshColumnAvailability();
    }

    public function test_customer_can_trust_browser_and_skip_two_factor_for_thirty_days(): void
    {
        AdminUser::query()->create(array_merge([
            'user_id' => 100,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'trusted-customer',
            'user_email' => 'trusted-customer@example.com',
            'is_active' => 1,
            'exist_customer' => '1',
            'two_factor_enabled' => 1,
        ], PasswordManager::payload('secret123')));

        $this->post('/login.php', [
            'user_id' => 'trusted-customer@example.com',
            'user_psw' => 'secret123',
        ])->assertRedirect(route('customer.2fa.show'));

        $code = TwoFactorAuth::issueCode('customer', 100, '1dollar');

        $verifyResponse = $this->post('/login-verify.php', [
            'code' => $code,
            'trust_device' => '1',
        ]);

        $verifyResponse->assertRedirect('/dashboard.php');
        $verifyResponse->assertCookie(TrustedTwoFactorDevice::cookieName('customer', '1dollar'));
        $this->assertSame(1, DB::table(TrustedTwoFactorDevice::TABLE)->count());

        $cookieName = TrustedTwoFactorDevice::cookieName('customer', '1dollar');
        $cookie = app('cookie')->queued($cookieName);

        $this->assertNotNull($cookie, 'Expected trusted-device cookie to be queued for customer 2FA.');

        $this->get('/logout.php')->assertRedirect('/login.php');

        $this->withCookie($cookieName, $cookie->getValue())
            ->post('/login.php', [
                'user_id' => 'trusted-customer@example.com',
                'user_psw' => 'secret123',
            ])->assertRedirect('/dashboard.php');

        $this->assertNull(session('customer_pending_2fa'));
    }

    public function test_admin_can_trust_browser_and_skip_two_factor_for_thirty_days(): void
    {
        AdminUser::query()->create(array_merge([
            'user_id' => 200,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'trusted-admin',
            'user_email' => 'trusted-admin@example.com',
            'is_active' => 1,
        ], PasswordManager::payload('secret123')));

        $this->post('/v/login', [
            'txtLogin' => 'trusted-admin',
            'txtPassword' => 'secret123',
        ])->assertRedirect(route('admin.2fa.show'));

        $code = TwoFactorAuth::issueCode('admin', 200);

        $verifyResponse = $this->post('/v/login-2fa', [
            'code' => $code,
            'trust_device' => '1',
        ]);

        $verifyResponse->assertRedirect('/welcome.php');
        $verifyResponse->assertCookie(TrustedTwoFactorDevice::cookieName('admin'));
        $this->assertSame(1, DB::table(TrustedTwoFactorDevice::TABLE)->count());

        $cookieName = TrustedTwoFactorDevice::cookieName('admin');
        $cookie = app('cookie')->queued($cookieName);

        $this->assertNotNull($cookie, 'Expected trusted-device cookie to be queued for admin 2FA.');

        $this->get('/v/logout.php')->assertRedirect('/v');

        $this->withCookie($cookieName, $cookie->getValue())
            ->post('/v/login', [
                'txtLogin' => 'trusted-admin',
                'txtPassword' => 'secret123',
            ])->assertRedirect('/welcome.php');

        $this->assertNull(session('admin_pending_2fa_user_id'));
    }

    public function test_trusted_customer_browser_is_revoked_after_password_change(): void
    {
        $customer = AdminUser::query()->create(array_merge([
            'user_id' => 300,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'password-rotate',
            'user_email' => 'password-rotate@example.com',
            'is_active' => 1,
            'exist_customer' => '1',
            'two_factor_enabled' => 1,
        ], PasswordManager::payload('secret123')));

        $this->post('/login.php', [
            'user_id' => 'password-rotate@example.com',
            'user_psw' => 'secret123',
        ])->assertRedirect(route('customer.2fa.show'));

        $code = TwoFactorAuth::issueCode('customer', 300, '1dollar');

        $verifyResponse = $this->post('/login-verify.php', [
            'code' => $code,
            'trust_device' => '1',
        ]);

        $cookieName = TrustedTwoFactorDevice::cookieName('customer', '1dollar');
        $cookie = app('cookie')->queued($cookieName);

        $this->assertNotNull($cookie, 'Expected trusted-device cookie to be queued for customer 2FA.');

        $this->get('/logout.php');

        $customer->forceFill(PasswordManager::payload('newsecret456'))->save();

        $this->withCookie($cookieName, $cookie->getValue())
            ->post('/login.php', [
                'user_id' => 'password-rotate@example.com',
                'user_psw' => 'newsecret456',
            ])->assertRedirect(route('customer.2fa.show'));

        $this->assertSame(0, DB::table(TrustedTwoFactorDevice::TABLE)->count());
        $this->assertNotNull(session('customer_pending_2fa'));
    }
}
