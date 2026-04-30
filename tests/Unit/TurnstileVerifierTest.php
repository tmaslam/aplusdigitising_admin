<?php

namespace Tests\Unit;

use App\Support\TurnstileVerifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileVerifierTest extends TestCase
{
    public function test_it_returns_true_when_turnstile_is_disabled(): void
    {
        config()->set('services.turnstile.enabled', false);

        $request = Request::create('/login.php', 'POST');

        $this->assertTrue(TurnstileVerifier::verify($request, 'login'));
    }

    public function test_it_validates_turnstile_response_server_side(): void
    {
        config()->set('services.turnstile.enabled', true);
        config()->set('services.turnstile.site_key', 'site-key');
        config()->set('services.turnstile.secret_key', 'secret-key');

        Http::fake([
            'https://challenges.cloudflare.com/turnstile/v0/siteverify' => Http::response([
                'success' => true,
            ], 200),
        ]);

        $request = Request::create('/sign-up.php', 'POST', [
            'cf-turnstile-response' => 'valid-token',
        ]);
        $request->server->set('REMOTE_ADDR', '127.0.0.1');

        $this->assertTrue(TurnstileVerifier::verify($request, 'customer-signup'));
    }
}
