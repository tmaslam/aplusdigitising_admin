<?php

namespace Tests\Unit;

use App\Support\HostedPaymentProviders;
use App\Support\SiteContext;
use Tests\TestCase;

class HostedPaymentProvidersTest extends TestCase
{
    public function test_default_provider_prefers_configured_option(): void
    {
        config()->set('services.stripe.secret_key', 'sk_test_123');
        config()->set('services.twocheckout.seller_id', 'seller');
        config()->set('services.twocheckout.secret_word', 'secret');
        config()->set('services.twocheckout.purchase_url', 'https://example.com/2co');
        config()->set('services.payments.default_provider', HostedPaymentProviders::TWOCHECKOUT);

        $options = HostedPaymentProviders::configuredOptions();

        $this->assertSame([
            HostedPaymentProviders::STRIPE,
            HostedPaymentProviders::TWOCHECKOUT,
        ], array_column($options, 'key'));
        $this->assertSame(HostedPaymentProviders::TWOCHECKOUT, HostedPaymentProviders::defaultProvider());
    }

    public function test_choose_falls_back_to_available_provider(): void
    {
        config()->set('services.stripe.secret_key', 'sk_test_123');
        config()->set('services.twocheckout.seller_id', '');
        config()->set('services.twocheckout.secret_word', '');
        config()->set('services.twocheckout.purchase_url', '');
        config()->set('services.payments.default_provider', HostedPaymentProviders::TWOCHECKOUT);

        $this->assertSame(HostedPaymentProviders::STRIPE, HostedPaymentProviders::choose(HostedPaymentProviders::TWOCHECKOUT));
        $this->assertSame(HostedPaymentProviders::STRIPE, HostedPaymentProviders::defaultProvider());
    }

    public function test_primary_site_defaults_to_twocheckout(): void
    {
        config()->set('sites.primary_legacy_key', '1dollar');
        config()->set('services.stripe.secret_key', 'sk_test_123');
        config()->set('services.twocheckout.seller_id', 'seller');
        config()->set('services.twocheckout.secret_word', 'secret');
        config()->set('services.twocheckout.purchase_url', 'https://example.com/2co');

        $site = new SiteContext(
            id: 1,
            legacyKey: '1dollar',
            slug: '1dollar',
            name: 'APlus',
            brandName: 'A Plus Digitizing',
            host: 'localhost',
            supportEmail: 'weborders@example.com',
            fromEmail: 'weborders@example.com',
            websiteAddress: 'localhost',
            isPrimary: true,
            activePaymentProvider: '',
            timezone: 'UTC',
        );

        $this->assertSame(HostedPaymentProviders::TWOCHECKOUT, HostedPaymentProviders::defaultProvider($site));
        $this->assertSame([HostedPaymentProviders::TWOCHECKOUT], array_column(HostedPaymentProviders::configuredOptions($site), 'key'));
    }

    public function test_site_specific_provider_overrides_global_default(): void
    {
        config()->set('services.stripe.secret_key', 'sk_test_123');
        config()->set('services.twocheckout.seller_id', 'seller');
        config()->set('services.twocheckout.secret_word', 'secret');
        config()->set('services.twocheckout.purchase_url', 'https://example.com/2co');
        config()->set('services.payments.default_provider', HostedPaymentProviders::TWOCHECKOUT);

        $site = new SiteContext(
            id: 2,
            legacyKey: 'brandx',
            slug: 'brandx',
            name: 'Brand X',
            brandName: 'Brand X',
            host: 'brandx.local',
            supportEmail: 'support@example.com',
            fromEmail: 'support@example.com',
            websiteAddress: 'brandx.local',
            isPrimary: false,
            activePaymentProvider: HostedPaymentProviders::STRIPE,
            timezone: 'UTC',
        );

        $this->assertSame(HostedPaymentProviders::STRIPE, HostedPaymentProviders::defaultProvider($site));
        $this->assertSame(HostedPaymentProviders::STRIPE, HostedPaymentProviders::choose(HostedPaymentProviders::TWOCHECKOUT, $site));
    }
}
