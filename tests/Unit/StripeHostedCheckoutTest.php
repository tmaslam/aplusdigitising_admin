<?php

namespace Tests\Unit;

use App\Support\StripeHostedCheckout;
use Tests\TestCase;

class StripeHostedCheckoutTest extends TestCase
{
    public function test_it_verifies_valid_stripe_webhook_signatures(): void
    {
        $payload = json_encode([
            'id' => 'evt_test_123',
            'type' => 'checkout.session.completed',
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        config()->set('services.stripe.webhook_secret', 'whsec_test_secret');
        config()->set('services.stripe.webhook_tolerance', 300);

        $timestamp = time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$payload, 'whsec_test_secret');
        $header = 't='.$timestamp.',v1='.$signature;

        $this->assertTrue(StripeHostedCheckout::verifyWebhookSignature($payload, $header));
    }

    public function test_it_rejects_invalid_stripe_webhook_signatures(): void
    {
        config()->set('services.stripe.webhook_secret', 'whsec_test_secret');
        config()->set('services.stripe.webhook_tolerance', 300);

        $payload = '{"id":"evt_bad"}';
        $header = 't='.time().',v1=not-a-real-signature';

        $this->assertFalse(StripeHostedCheckout::verifyWebhookSignature($payload, $header));
    }
}
