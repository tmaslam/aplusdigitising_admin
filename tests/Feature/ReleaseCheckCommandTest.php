<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ReleaseCheckCommandTest extends TestCase
{
    private array $requiredTables = [
        'sites',
        'site_domains',
        'site_pricing_profiles',
        'site_promotions',
        'site_promotion_claims',
        'customer_activation_tokens',
        'customer_password_reset_tokens',
        'customer_remember_tokens',
        'two_factor_trusted_devices',
        'customer_credit_ledger',
        'payment_transactions',
        'payment_transaction_items',
        'payment_provider_events',
        'quote_negotiations',
        'order_workflow_meta',
        'email_templates',
        'security_audit_events',
        'admin_login_attempts',
        'supervisor_team_members',
    ];

    protected function tearDown(): void
    {
        foreach (array_reverse(array_merge(['users'], $this->requiredTables)) as $table) {
            Schema::dropIfExists($table);
        }

        putenv('SHARED_UPLOADS_PATH');

        parent::tearDown();
    }

    public function test_release_check_strict_fails_when_release_prerequisites_are_missing(): void
    {
        $this->artisan('release:check --strict')
            ->expectsOutputToContain('Blocking issues')
            ->expectsOutputToContain('Missing required table: sites')
            ->assertExitCode(1);
    }

    public function test_release_check_strict_passes_when_required_setup_is_present(): void
    {
        foreach ($this->requiredTables as $table) {
            Schema::create($table, function (Blueprint $table) {
                $table->bigIncrements('id');
            });
        }

        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('user_id');
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('password_hash')->nullable();
            $table->dateTime('password_migrated_at')->nullable();
        });

        config()->set('app.url', 'https://aplusdigitizing.com');
        config()->set('sites.primary_host', 'aplusdigitizing.com');
        config()->set('mail.default', 'smtp');
        config()->set('mail.mailers.smtp.host', 'premium349.web-hosting.com');
        config()->set('mail.mailers.smtp.username', 'contact@aplusdigitizing.com');
        config()->set('services.payments.default_provider', 'stripe_checkout');
        config()->set('services.stripe.secret_key', 'sk_test_123');
        config()->set('services.stripe.publishable_key', 'pk_test_123');
        config()->set('services.stripe.webhook_secret', 'whsec_123');

        putenv('SHARED_UPLOADS_PATH='.sys_get_temp_dir());

        $this->artisan('release:check --strict')
            ->expectsOutputToContain('No release issues detected.')
            ->assertExitCode(0);
    }
}
