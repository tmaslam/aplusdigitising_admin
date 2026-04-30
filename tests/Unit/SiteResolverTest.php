<?php

namespace Tests\Unit;

use App\Support\SiteResolver;
use Tests\TestCase;

class SiteResolverTest extends TestCase
{
    public function test_it_uses_fallback_site_for_known_host(): void
    {
        config()->set('sites.fallback_sites', [
            '1dollar' => [
                'legacy_key' => '1dollar',
                'slug' => '1dollar',
                'name' => 'A Plus Digitizing',
                'brand_name' => 'A Plus Digitizing',
                'host' => 'aplusdigitizing.com',
                'support_email' => 'support@aplusdigitizing.com',
                'from_email' => 'support@aplusdigitizing.com',
                'website_address' => 'https://aplusdigitizing.com',
                'is_primary' => true,
            ],
        ]);

        $site = SiteResolver::fromHost('aplusdigitizing.com');

        $this->assertSame('1dollar', $site->legacyKey);
        $this->assertSame('A Plus Digitizing', $site->brandName);
        $this->assertSame('aplusdigitizing.com', $site->host);
    }

    public function test_it_falls_back_to_primary_site_when_host_is_unknown(): void
    {
        config()->set('sites.primary_legacy_key', '1dollar');
        config()->set('sites.fallback_sites', [
            '1dollar' => [
                'legacy_key' => '1dollar',
                'slug' => '1dollar',
                'name' => 'A Plus Digitizing',
                'brand_name' => 'A Plus Digitizing',
                'host' => 'aplusdigitizing.com',
                'support_email' => 'support@aplusdigitizing.com',
                'from_email' => 'support@aplusdigitizing.com',
                'website_address' => 'https://aplusdigitizing.com',
                'is_primary' => true,
            ],
        ]);

        $site = SiteResolver::fromHost('unknown-site.test');

        $this->assertSame('1dollar', $site->legacyKey);
        $this->assertSame('aplusdigitizing.com', $site->host);
    }
}
