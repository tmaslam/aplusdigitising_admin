<?php

namespace Tests\Feature;

use Tests\TestCase;

class InternalTurnstileProtectionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.turnstile.enabled', true);
        config()->set('services.turnstile.site_key', 'site-key');
        config()->set('services.turnstile.secret_key', 'secret-key');
    }

    public function test_admin_and_team_login_pages_render_turnstile_when_enabled(): void
    {
        $this->get('/v')
            ->assertOk()
            ->assertSee('cf-turnstile', false)
            ->assertSee('site-key', false);

        $this->get('/team')
            ->assertOk()
            ->assertSee('cf-turnstile', false)
            ->assertSee('site-key', false);
    }

    public function test_admin_and_team_login_require_turnstile_when_enabled(): void
    {
        $this->from('/v')
            ->post('/v/login', [
                'txtLogin' => 'admin-user',
                'txtPassword' => 'secret123',
            ])
            ->assertRedirect('/v')
            ->assertSessionHasErrors(['auth']);

        $this->from('/team')
            ->post('/team/login', [
                'txtLogin' => 'team-user',
                'txtPassword' => 'secret123',
            ])
            ->assertRedirect('/team')
            ->assertSessionHasErrors(['auth']);
    }
}
