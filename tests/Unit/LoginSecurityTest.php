<?php

namespace Tests\Unit;

use App\Models\AdminUser;
use App\Support\LoginSecurity;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Session\Store;
use Tests\TestCase;

class LoginSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Schema::dropIfExists('admin_login_attempts');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->integer('is_active')->default(1);
        });

        Schema::create('admin_login_attempts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('login_name');
            $table->unsignedBigInteger('matched_user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();
            $table->string('request_path', 255)->nullable();
            $table->string('attempt_outcome', 40)->nullable();
            $table->string('status', 255)->nullable();
            $table->unsignedTinyInteger('is_rate_limited')->default(0);
            $table->dateTime('attempted_at');
        });

        config()->set('mail.admin_alert_address', '');
    }

    public function test_lockouts_escalate_from_short_to_long_then_hard_block(): void
    {
        $user = AdminUser::query()->create([
            'user_id' => 501,
            'usre_type_id' => AdminUser::TYPE_CUSTOMER,
            'user_name' => 'customer-lockout',
            'user_email' => 'customer@example.com',
            'is_active' => 1,
        ]);

        $firstRequest = $this->requestFrom('10.0.0.10');
        $this->assertFalse(LoginSecurity::handleRateLimit($firstRequest, 'customer@example.com', 'customer', $user));
        $this->assertStringContainsString('15 minutes', (string) LoginSecurity::activeLockMessage($firstRequest, 'customer@example.com', 'customer', $user));
        $this->assertSame(1, (int) $user->fresh()->is_active);

        Cache::forget('login-security:temporary-lock:'.$user->user_id);

        $secondRequest = $this->requestFrom('10.0.0.11');
        $this->assertFalse(LoginSecurity::handleRateLimit($secondRequest, 'customer@example.com', 'customer', $user));
        $this->assertStringContainsString('1 hour', (string) LoginSecurity::activeLockMessage($secondRequest, 'customer@example.com', 'customer', $user));
        $this->assertSame(1, (int) $user->fresh()->is_active);

        Cache::forget('login-security:temporary-lock:'.$user->user_id);

        $thirdRequest = $this->requestFrom('10.0.0.12');
        $this->assertTrue(LoginSecurity::handleRateLimit($thirdRequest, 'customer@example.com', 'customer', $user));
        $this->assertSame(0, (int) $user->fresh()->is_active);
    }

    public function test_clearing_security_state_removes_temporary_lock(): void
    {
        $user = AdminUser::query()->create([
            'user_id' => 777,
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'admin-lockout',
            'user_email' => 'admin@example.com',
            'is_active' => 1,
        ]);

        $request = $this->requestFrom('10.2.0.20');
        LoginSecurity::handleRateLimit($request, 'admin-lockout', 'admin', $user);

        $this->assertNotNull(LoginSecurity::activeLockMessage($request, 'admin-lockout', 'admin', $user));

        LoginSecurity::clearSecurityState($user);

        $this->assertNull(LoginSecurity::activeLockMessage($request, 'admin-lockout', 'admin', $user));
    }

    private function requestFrom(string $ip): Request
    {
        $request = Request::create('/login.php', 'POST', [], [], [], [
            'REMOTE_ADDR' => $ip,
            'HTTP_USER_AGENT' => 'PHPUnit',
        ]);

        /** @var Store $session */
        $session = app('session.store');
        $session->start();
        $request->setLaravelSession($session);

        return $request;
    }
}
