<?php

namespace Tests\Feature;

use App\Support\SecurityAudit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityAuditSurfaceTest extends TestCase
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

        SecurityAudit::refreshTableAvailability();
    }

    public function test_customer_auth_middleware_records_unauthorized_access_event(): void
    {
        $this->get('/dashboard.php')->assertRedirect('/login.php');

        $this->assertDatabaseHas('security_audit_events', [
            'event_type' => 'auth.unauthorized_access',
            'portal' => 'customer',
            'request_path' => '/dashboard.php',
        ]);
    }
}
