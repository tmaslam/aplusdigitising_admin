<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\CustomerRememberLogin;
use App\Support\PasswordManager;
use App\Support\SiteResolver;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CustomerRememberLoginTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('customer_remember_tokens');
        Schema::dropIfExists('login_history');
        Schema::dropIfExists('users');

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

        Schema::create('customer_remember_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('site_legacy_key', 100);
            $table->unsignedBigInteger('customer_user_id');
            $table->string('selector', 32)->unique();
            $table->string('token_hash', 64);
            $table->dateTime('expires_at');
            $table->dateTime('last_used_at')->nullable();
            $table->dateTime('created_at');
        });

        PasswordManager::refreshColumnAvailability();
    }

    public function test_login_with_remember_me_creates_persistent_token(): void
    {
        AdminUser::query()->create(array_merge([
            'user_id' => 100,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'remember-customer',
            'user_email' => 'remember@example.com',
            'is_active' => 1,
            'exist_customer' => '1',
        ], PasswordManager::payload('secret123')));

        $response = $this->post('/login.php', [
            'user_id' => 'remember@example.com',
            'user_psw' => 'secret123',
            'remember_me' => '1',
        ]);

        $response->assertRedirect('/dashboard.php');
        $response->assertCookie(CustomerRememberLogin::COOKIE_NAME);
        $this->assertSame(1, DB::table('customer_remember_tokens')->count());

        $cookie = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === CustomerRememberLogin::COOKIE_NAME);

        $this->assertNotNull($cookie);

        $this->assertNotNull($cookie);
    }

    public function test_remember_me_restore_and_clear_work_with_plain_cookie_payload(): void
    {
        $customer = AdminUser::query()->create(array_merge([
            'user_id' => 101,
            'website' => '1dollar',
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'remember-logout',
            'user_email' => 'logout@example.com',
            'is_active' => 1,
            'exist_customer' => '1',
        ], PasswordManager::payload('secret123')));

        $site = SiteResolver::fromHost('localhost');
        $issueRequest = Request::create('/login.php', 'POST');
        $issueSession = new Store('testing', app('session')->driver()->getHandler());
        $issueSession->start();
        $issueRequest->setLaravelSession($issueSession);

        CustomerRememberLogin::issue($issueRequest, $site, $customer);

        $cookie = app('cookie')->queued(CustomerRememberLogin::COOKIE_NAME);
        $this->assertNotNull($cookie);
        $this->assertSame(1, DB::table('customer_remember_tokens')->count());

        $restoreRequest = Request::create('/login.php', 'GET', [], [
            CustomerRememberLogin::COOKIE_NAME => $cookie->getValue(),
        ]);
        $restoreSession = new Store('testing', app('session')->driver()->getHandler());
        $restoreSession->start();
        $restoreRequest->setLaravelSession($restoreSession);

        $rememberedUser = CustomerRememberLogin::restore($restoreRequest, $site);

        $this->assertNotNull($rememberedUser);
        $this->assertSame(101, $rememberedUser->user_id);
        $this->assertSame(101, $restoreSession->get('customer_user_id'));

        $clearRequest = Request::create('/logout.php', 'GET', [], [
            CustomerRememberLogin::COOKIE_NAME => app('cookie')->queued(CustomerRememberLogin::COOKIE_NAME)?->getValue() ?? $cookie->getValue(),
        ]);

        CustomerRememberLogin::clearCurrent($clearRequest);

        $this->assertSame(0, DB::table('customer_remember_tokens')->count());
    }
}
