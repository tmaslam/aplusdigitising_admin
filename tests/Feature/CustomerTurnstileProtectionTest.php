<?php

namespace Tests\Feature;

use Tests\TestCase;

class CustomerTurnstileProtectionTest extends TestCase
{
    public function test_register_page_renders_turnstile_when_enabled(): void
    {
        config()->set('services.turnstile.enabled', true);
        config()->set('services.turnstile.site_key', 'site-key');
        config()->set('services.turnstile.secret_key', 'secret-key');

        $this->get('/sign-up.php')
            ->assertOk()
            ->assertSee('cf-turnstile', false)
            ->assertSee('site-key', false);
    }

    public function test_login_requires_turnstile_when_enabled(): void
    {
        config()->set('services.turnstile.enabled', true);
        config()->set('services.turnstile.site_key', 'site-key');
        config()->set('services.turnstile.secret_key', 'secret-key');

        $this->from('/login.php')
            ->post('/login.php', [
                'user_id' => 'customer@example.com',
                'user_psw' => 'secret123',
            ])
            ->assertRedirect('/login.php')
            ->assertSessionHasErrors(['auth']);
    }
}
