<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use App\Support\LoginSecurity;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminPasswordResetUnlockTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        Mail::fake();

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('admin_password_reset_tokens');
        Schema::dropIfExists('admin_login_attempts');
        Schema::dropIfExists('users');
        Schema::enableForeignKeyConstraints();

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedTinyInteger('usre_type_id')->default(AdminUser::TYPE_CUSTOMER);
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_password')->nullable();
            $table->integer('is_active')->default(1);
            $table->string('end_date', 30)->nullable();
        });

        Schema::create('admin_password_reset_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_user_id');
            $table->string('selector', 64);
            $table->string('token_hash', 64);
            $table->dateTime('expires_at');
            $table->dateTime('created_at');
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

        config()->set('services.turnstile.enabled', false);
        config()->set('mail.admin_alert_address', '');
    }

    public function test_admin_forgot_password_can_issue_reset_link_for_permanently_locked_account(): void
    {
        AdminUser::query()->create([
            'user_id' => 901,
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'locked-admin',
            'user_email' => 'locked-admin@example.com',
            'user_password' => 'old-secret',
            'is_active' => 0,
            'end_date' => null,
        ]);

        $response = $this->post('/v/forgot-password', [
            'identity' => 'locked-admin',
        ]);

        $response->assertRedirect(url('/v'));
        $response->assertSessionHas('success');
        $this->assertSame(1, Schema::hasTable('admin_password_reset_tokens') ? \Illuminate\Support\Facades\DB::table('admin_password_reset_tokens')->where('admin_user_id', 901)->count() : 0);
    }

    public function test_admin_password_reset_clears_temporary_lock_and_reactivates_locked_account(): void
    {
        $admin = AdminUser::query()->create([
            'user_id' => 902,
            'usre_type_id' => AdminUser::TYPE_ADMIN,
            'user_name' => 'reset-admin',
            'user_email' => 'reset-admin@example.com',
            'user_password' => 'old-secret',
            'is_active' => 1,
            'end_date' => null,
        ]);

        LoginSecurity::handleRateLimit($this->requestFrom('10.5.0.10'), 'reset-admin', 'admin', $admin);
        $this->assertNotNull(Cache::get('login-security:temporary-lock:'.$admin->user_id));

        $admin->update(['is_active' => 0]);

        \Illuminate\Support\Facades\DB::table('admin_password_reset_tokens')->insert([
            'admin_user_id' => $admin->user_id,
            'selector' => 'selector-902',
            'token_hash' => hash('sha256', 'validator-902'),
            'expires_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);

        $response = $this->post('/v/reset-password', [
            'selector' => 'selector-902',
            'token' => 'validator-902',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ]);

        $response->assertRedirect(url('/v'));
        $response->assertSessionHas('success', 'Your password has been reset. Please sign in with your new password.');
        $this->assertSame(1, (int) $admin->fresh()->is_active);
        $this->assertNull(Cache::get('login-security:temporary-lock:'.$admin->user_id));
        $this->assertNull(Cache::get('login-security:escalation:'.$admin->user_id));
    }

    private function requestFrom(string $ip): Request
    {
        $request = Request::create('/v/login', 'POST', [], [], [], [
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
