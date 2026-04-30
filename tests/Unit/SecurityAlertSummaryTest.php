<?php

namespace Tests\Unit;

use App\Support\SecurityAlertSummary;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityAlertSummaryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('security_audit_events');
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
    }

    public function test_summary_returns_actionable_counts_recent_alerts_and_top_ips(): void
    {
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
                'details_json' => json_encode(['attempts' => 1]),
                'created_at' => now()->subHours(2)->format('Y-m-d H:i:s'),
            ],
            [
                'event_type' => 'files.upload_rejected',
                'severity' => 'warning',
                'portal' => 'admin',
                'site_legacy_key' => '1dollar',
                'actor_user_id' => 3,
                'actor_login' => 'main-admin',
                'ip_address' => '10.1.1.1',
                'user_agent' => 'TestAgent',
                'request_path' => '/v/create-order.php',
                'request_method' => 'POST',
                'message' => 'Rejected upload',
                'details_json' => json_encode(['upload_profile' => 'source']),
                'created_at' => now()->subHour()->format('Y-m-d H:i:s'),
            ],
            [
                'event_type' => 'auth.unauthorized_access',
                'severity' => 'critical',
                'portal' => 'admin',
                'site_legacy_key' => '1dollar',
                'actor_user_id' => null,
                'actor_login' => null,
                'ip_address' => '10.2.2.2',
                'user_agent' => 'TestAgent',
                'request_path' => '/v/welcome.php',
                'request_method' => 'GET',
                'message' => 'Unauthorized admin access',
                'details_json' => json_encode(['guard' => 'admin']),
                'created_at' => now()->subMinutes(30)->format('Y-m-d H:i:s'),
            ],
            [
                'event_type' => 'bot.turnstile_failed',
                'severity' => 'warning',
                'portal' => 'public',
                'site_legacy_key' => '1dollar',
                'actor_user_id' => null,
                'actor_login' => null,
                'ip_address' => '10.3.3.3',
                'user_agent' => 'TestAgent',
                'request_path' => '/sign-up.php',
                'request_method' => 'POST',
                'message' => 'Turnstile failed',
                'details_json' => json_encode(['code' => 'missing']),
                'created_at' => now()->subMinutes(10)->format('Y-m-d H:i:s'),
            ],
            [
                'event_type' => 'auth.login_succeeded',
                'severity' => 'info',
                'portal' => 'admin',
                'site_legacy_key' => '1dollar',
                'actor_user_id' => 1,
                'actor_login' => 'main-admin',
                'ip_address' => '10.9.9.9',
                'user_agent' => 'TestAgent',
                'request_path' => '/v',
                'request_method' => 'POST',
                'message' => 'Success',
                'details_json' => json_encode([]),
                'created_at' => now()->subMinutes(5)->format('Y-m-d H:i:s'),
            ],
        ]);

        $summary = SecurityAlertSummary::summary();

        $this->assertTrue($summary['available']);
        $this->assertSame(5, $summary['total_events']);
        $this->assertSame(4, $summary['actionable_events']);
        $this->assertSame(1, $summary['critical_events']);
        $this->assertSame(1, $summary['failed_logins']);
        $this->assertSame(1, $summary['unauthorized_access']);
        $this->assertSame(1, $summary['upload_rejections']);
        $this->assertSame(1, $summary['turnstile_failures']);
        $this->assertCount(4, $summary['recent_events']);
        $repeatIp = $summary['top_ips']->firstWhere('ip_address', '10.1.1.1');
        $this->assertNotNull($repeatIp);
        $this->assertSame(2, $repeatIp->total_events);
    }
}
