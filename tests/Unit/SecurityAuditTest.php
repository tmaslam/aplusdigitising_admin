<?php

namespace Tests\Unit;

use App\Models\SecurityAuditEvent;
use App\Support\SecurityAudit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SecurityAuditTest extends TestCase
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

    public function test_record_persists_structured_event_when_table_exists(): void
    {
        SecurityAudit::record(
            null,
            'files.public_asset_denied',
            'A non-public upload asset path was requested.',
            ['requested_path' => 'admin/secret.env'],
            'warning',
            [
                'portal' => 'public',
                'request_path' => '/uploads/admin/secret.env',
                'request_method' => 'GET',
            ]
        );

        $event = SecurityAuditEvent::query()->firstOrFail();

        $this->assertSame('files.public_asset_denied', $event->event_type);
        $this->assertSame('warning', $event->severity);
        $this->assertSame('public', $event->portal);
        $this->assertSame('/uploads/admin/secret.env', $event->request_path);
        $this->assertSame('admin/secret.env', $event->details_json['requested_path']);
    }
}
