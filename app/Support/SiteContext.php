<?php

namespace App\Support;

class SiteContext
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $legacyKey,
        public readonly string $slug,
        public readonly string $name,
        public readonly string $brandName,
        public readonly string $host,
        public readonly string $supportEmail,
        public readonly string $fromEmail,
        public readonly string $websiteAddress,
        public readonly bool $isPrimary,
        public readonly string $activePaymentProvider = '',
        public readonly string $timezone = 'UTC',
        public readonly string $companyAddress = '',
        public readonly string $logoUrl = '',
        public readonly string $faviconUrl = '',
        public readonly string $primaryColor = '',
        public readonly string $accentColor = '',
        public readonly array $themeSettings = [],
    ) {
    }

    public function displayLabel(): string
    {
        return $this->brandName !== '' ? $this->brandName : $this->name;
    }

    public function matchesLegacyKey(?string $legacyKey): bool
    {
        return $legacyKey !== null && strcasecmp($this->legacyKey, trim($legacyKey)) === 0;
    }

    public function cssPrimaryColor(): string
    {
        return $this->primaryColor !== '' ? $this->primaryColor : '#F26522';
    }

    public function cssPrimaryDarkColor(): string
    {
        if ($this->primaryColor !== '') {
            return $this->themeSettings['primary_dark'] ?? $this->primaryColor;
        }
        return '#D94E0F';
    }

    public function cssAccentColor(): string
    {
        return $this->accentColor !== '' ? $this->accentColor : '#f4b43a';
    }

    public function logoPath(): string
    {
        return $this->logoUrl !== '' ? $this->logoUrl : '/images/logo.png';
    }

    public function faviconPath(): string
    {
        return $this->faviconUrl !== '' ? $this->faviconUrl : '/images/favicon.png';
    }
}
