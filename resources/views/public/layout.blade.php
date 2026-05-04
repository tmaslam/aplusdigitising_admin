<!DOCTYPE html>
<html lang="en">
<head>
    <script>
        (function() {
            var theme = localStorage.getItem('admin-theme');
            if (theme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @php
        $legacyAssetBase = rtrim(url('/'), '/');
        $seoTitle = html_entity_decode(trim($__env->yieldContent('title', $siteContext->displayLabel())), ENT_QUOTES, 'UTF-8');
        $seoDescription = trim(preg_replace('/\s+/', ' ', strip_tags($__env->yieldContent('meta_description', 'Professional embroidery digitizing and vector art services with fast turnaround, secure customer workflow, and site-specific account isolation.'))));
        $seoCanonical = trim($__env->yieldContent('canonical', url()->current()));
        $seoRobots = trim($__env->yieldContent('meta_robots', 'index,follow,max-image-preview:large'));
        $seoImage = trim($__env->yieldContent('meta_image', $legacyAssetBase.'/images/logo.webp'));
        $seoType = trim($__env->yieldContent('meta_og_type', 'website'));
        $seoTwitterCard = trim($__env->yieldContent('twitter_card', 'summary_large_image'));
        $siteBaseUrl = rtrim(url('/'), '/');
        $supportEmail = $siteContext->supportEmail !== '' ? $siteContext->supportEmail : (string) config('mail.admin_alert_address', '');

        if ($seoImage !== '' && ! \Illuminate\Support\Str::startsWith($seoImage, ['http://', 'https://'])) {
            $seoImage = url($seoImage);
        }

        $organizationSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => $siteBaseUrl.'/#organization',
            'name' => $siteContext->displayLabel(),
            'url' => $siteBaseUrl.'/',
            'logo' => $legacyAssetBase.$siteContext->logoPath(),
        ];

        if ($supportEmail !== '') {
            $organizationSchema['email'] = $supportEmail;
            $organizationSchema['contactPoint'] = [[
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'email' => $supportEmail,
            ]];
        }

        $websiteSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => $siteBaseUrl.'/#website',
            'url' => $siteBaseUrl.'/',
            'name' => $siteContext->displayLabel(),
            'publisher' => ['@id' => $siteBaseUrl.'/#organization'],
        ];

        $pageSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'url' => $seoCanonical,
            'name' => $seoTitle,
            'description' => $seoDescription,
            'isPartOf' => ['@id' => $siteBaseUrl.'/#website'],
        ];

        $publicMenu = [
            // Public navigation hidden — all traffic routes through login
        ];
        $serviceLinks = [
            ['label' => 'Embroidery Digitizing', 'href' => '/embroidery-digitizing.php'],
            ['label' => '3D / Puff Embroidery', 'href' => '/3d-puff-embroidery-digitizing.php'],
            ['label' => 'Applique Embroidery', 'href' => '/applique-embroidery-digitizing.php'],
            ['label' => 'Chain Stitch Embroidery', 'href' => '/chain-stitch-embroidery-digitizing.php'],
            ['label' => 'Photo Digitizing', 'href' => '/photo-digitizing.php'],
            ['label' => 'Vector Art', 'href' => '/vector-art.php'],
        ];
        $companyLinks = [
            ['label' => 'About Us', 'href' => url('/about-us.php')],
            ['label' => 'Pricing', 'href' => url('/price-plan.php')],
            ['label' => 'Contact', 'href' => url('/contact-us.php')],
        ];
    @endphp
    <title>{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDescription }}">
    <meta name="robots" content="{{ $seoRobots }}">
    <link rel="canonical" href="{{ $seoCanonical }}">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="{{ $seoType }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:url" content="{{ $seoCanonical }}">
    <meta property="og:site_name" content="{{ $siteContext->displayLabel() }}">
    <meta property="og:image" content="{{ $seoImage }}">
    <meta name="twitter:card" content="{{ $seoTwitterCard }}">
    <meta name="twitter:title" content="{{ $seoTitle }}">
    <meta name="twitter:description" content="{{ $seoDescription }}">
    <meta name="twitter:image" content="{{ $seoImage }}">
    <script type="application/ld+json">@json($organizationSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)</script>
    <script type="application/ld+json">@json($websiteSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)</script>
    <script type="application/ld+json">@json($pageSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)</script>
    @hasSection('structured_data')
        <script type="application/ld+json">{!! trim($__env->yieldContent('structured_data')) !!}</script>
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('/css/front-theme-overrides.css') }}">
    <style>
        body.front-theme.public-theme *,
        body.front-theme.public-theme *::before,
        body.front-theme.public-theme *::after {
            box-sizing: border-box;
        }

        body.front-theme.public-theme .container {
            width: min(1220px, calc(100% - 28px)) !important;
            margin-left: auto !important;
            margin-right: auto !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        body.front-theme.public-theme .marketing-header {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid rgba(203, 213, 225, 0.72);
            backdrop-filter: blur(14px);
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
        }

        body.front-theme.public-theme .marketing-header-shell {
            min-height: 82px;
            display: grid;
            grid-template-columns: auto minmax(0, 1fr) auto;
            grid-template-areas: "brand nav actions";
            align-items: center;
            gap: 18px;
            padding-top: 12px;
            padding-bottom: 12px;
        }

        body.front-theme.public-theme .marketing-brand {
            grid-area: brand;
            display: inline-flex;
            align-items: center;
            min-width: 0;
        }

        body.front-theme.public-theme .marketing-brand img {
            height: 84px;
            width: auto;
            max-width: 100%;
            display: block;
        }

        body.front-theme.public-theme .marketing-toggle {
            display: none;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border: 0;
            border-radius: 12px;
            background: linear-gradient(135deg, #169fe6 0%, #0d6ea3 100%);
            color: #ffffff;
            font-family: "Inter", "Segoe UI", sans-serif;
            font-size: 0.92rem;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(22, 159, 230, 0.24);
        }

        body.front-theme.public-theme .marketing-nav {
            grid-area: nav;
            display: flex;
            align-items: center;
            min-width: 0;
            margin-left: 24px;
        }

        body.front-theme.public-theme .marketing-nav-list {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
            width: 100%;
            min-width: 0;
            margin: 0;
            padding: 0.4rem;
            border-radius: 999px;
            background: linear-gradient(180deg, rgba(248, 250, 252, 0.95), rgba(241, 245, 249, 0.92));
            border: 1px solid rgba(203, 213, 225, 0.8);
            box-shadow:
                inset 0 1px 0 rgba(255, 255, 255, 0.95),
                0 10px 26px rgba(15, 23, 42, 0.05);
        }

        body.front-theme.public-theme .marketing-nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.72rem 1rem;
            border-radius: 999px;
            color: #334155;
            text-decoration: none;
            font-family: "Inter", "Segoe UI", sans-serif;
            font-size: 0.94rem;
            font-weight: 700;
            line-height: 1;
            transition: color 0.2s ease, background 0.2s ease, box-shadow 0.2s ease;
        }

        body.front-theme.public-theme .marketing-nav-link:hover,
        body.front-theme.public-theme .marketing-nav-link.active {
            background: #ffffff;
            color: #0d6ea3;
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
        }

        body.front-theme.public-theme .marketing-actions {
            grid-area: actions;
            display: flex;
            align-items: center;
            gap: 0.55rem;
            justify-content: flex-end;
            margin-left: 68px;
            position: relative;
            top: -10px;
        }

        body.front-theme.public-theme .marketing-action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 0;
            min-height: 44px !important;
            padding: 0 18px !important;
            border-radius: 10px !important;
            font-size: 0.94rem !important;
            font-weight: 700 !important;
            line-height: 1 !important;
            white-space: nowrap;
        }

        @media (max-width: 900px) {
            body.front-theme.public-theme .marketing-header-shell {
                grid-template-columns: minmax(0, 1fr) auto;
                grid-template-areas:
                    "brand toggle"
                    "actions actions"
                    "nav nav";
                gap: 12px;
                min-height: auto;
            }

            body.front-theme.public-theme .marketing-brand {
                grid-area: brand;
            }

            body.front-theme.public-theme .marketing-brand img {
                max-width: 150px;
                height: auto;
            }

            body.front-theme.public-theme .marketing-toggle {
                grid-area: toggle;
                display: inline-flex;
                justify-self: end;
            }

            body.front-theme.public-theme .marketing-actions {
                grid-area: actions;
                margin-left: 0;
                position: static;
                top: auto;
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                width: 100%;
                gap: 10px;
            }

            body.front-theme.public-theme .marketing-action-button {
                min-height: 42px !important;
                width: 100%;
                justify-content: center;
                white-space: normal;
                padding: 10px 12px !important;
            }

            body.front-theme.public-theme .marketing-nav {
                grid-area: nav;
                margin-left: 0;
                display: none;
                width: 100%;
            }

            body.front-theme.public-theme .marketing-nav.open {
                display: block;
            }

            body.front-theme.public-theme .marketing-nav-list {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 0.75rem;
                padding: 1rem;
                border-radius: 22px;
            }

            body.front-theme.public-theme .marketing-nav-link {
                width: 100%;
                justify-content: flex-start;
                text-align: left;
                white-space: normal;
            }
        }

        @media (max-width: 640px) {
            body.front-theme.public-theme .marketing-header-shell {
                gap: 8px;
            }

            body.front-theme.public-theme .marketing-brand img {
                max-width: 122px;
            }

            body.front-theme.public-theme .marketing-actions {
                grid-template-columns: 1fr;
            }

            body.front-theme.public-theme .marketing-toggle {
                padding-left: 14px;
                padding-right: 14px;
            }
        }
    </style>
    <style>
        .theme-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 999px;
            border: 1px solid rgba(148,163,184,0.35);
            background: rgba(255,255,255,0.7);
            color: #334155;
            font-size: 0.84rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
            backdrop-filter: blur(4px);
        }
        .theme-toggle:hover {
            background: #ffffff;
            border-color: rgba(148,163,184,0.6);
        }
        .theme-toggle-light, .theme-toggle-dark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
        .theme-toggle .theme-toggle-dark { display: none; }
        .captcha-question { margin-bottom: 8px; font-weight: 600; color: #17212a; }
        [data-theme="dark"] .theme-toggle {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.15);
            color: #F1F5F9;
        }
        [data-theme="dark"] .theme-toggle:hover {
            background: rgba(255,255,255,0.15);
        }
        [data-theme="dark"] .theme-toggle .theme-toggle-light { display: none; }
        [data-theme="dark"] .theme-toggle .theme-toggle-dark { display: inline-flex; }

        [data-theme="dark"] body.front-theme.public-theme .marketing-header { background: rgba(15,23,42,0.96); border-bottom-color: #334155; }
        [data-theme="dark"] body.front-theme.public-theme .marketing-nav-list { background: linear-gradient(180deg, rgba(30,41,59,0.95), rgba(15,23,42,0.92)); border-color: #334155; box-shadow: inset 0 1px 0 rgba(255,255,255,0.05), 0 10px 26px rgba(0,0,0,0.2); }
        [data-theme="dark"] body.front-theme.public-theme .marketing-nav-link { color: #94A3B8; }
        [data-theme="dark"] body.front-theme.public-theme .marketing-nav-link:hover,
        [data-theme="dark"] body.front-theme.public-theme .marketing-nav-link.active { background: #111827; color: #F1F5F9; box-shadow: 0 10px 20px rgba(0,0,0,0.15); }
        [data-theme="dark"] body.front-theme.public-theme .marketing-toggle { background: linear-gradient(135deg, #0d6ea3 0%, #094c74 100%); color: #ffffff; }

        [data-theme="dark"] body.front-theme { background: radial-gradient(circle at top left, rgba(242,101,34,0.12), transparent 28%), linear-gradient(180deg, #0B1120 0%, #0B1120 100%) !important; color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .site-frame { background: rgba(17,24,39,0.96) !important; box-shadow: 0 28px 80px rgba(0,0,0,0.35) !important; }
        [data-theme="dark"] body.front-theme .site-header { background: rgba(15,23,42,0.97) !important; border-bottom-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .topbar { background: linear-gradient(135deg, #F26522 0%, #D94E0F 100%) !important; }
        [data-theme="dark"] body.front-theme .button.secondary,
        [data-theme="dark"] body.front-theme button.secondary,
        [data-theme="dark"] body.front-theme .customer-action.secondary,
        [data-theme="dark"] body.front-theme .button.ghost,
        [data-theme="dark"] body.front-theme .customer-tab,
        [data-theme="dark"] body.front-theme .account-chip-link { background: #111827 !important; color: #F1F5F9 !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .section-card,
        [data-theme="dark"] body.front-theme .content-card,
        [data-theme="dark"] body.front-theme .pricing-panel,
        [data-theme="dark"] body.front-theme .contact-panel,
        [data-theme="dark"] body.front-theme .service-showcase-card,
        [data-theme="dark"] body.front-theme .marketing-service-card,
        [data-theme="dark"] body.front-theme .marketing-feature-card,
        [data-theme="dark"] body.front-theme .marketing-testimonial-card,
        [data-theme="dark"] body.front-theme .marketing-faq-item,
        [data-theme="dark"] body.front-theme .activity-card,
        [data-theme="dark"] body.front-theme .action-card,
        [data-theme="dark"] body.front-theme .portal-stat,
        [data-theme="dark"] body.front-theme .info-card,
        [data-theme="dark"] body.front-theme .metric,
        [data-theme="dark"] body.front-theme .customer-nav,
        [data-theme="dark"] body.front-theme .detail-card,
        [data-theme="dark"] body.front-theme .list-card,
        [data-theme="dark"] body.front-theme .card,
        [data-theme="dark"] body.front-theme .panel { background: rgba(17,24,39,0.96) !important; border-color: #334155 !important; box-shadow: 0 4px 12px rgba(0,0,0,0.25) !important; }
        [data-theme="dark"] body.front-theme table { background: rgba(17,24,39,0.98) !important; }
        [data-theme="dark"] body.front-theme th { background: linear-gradient(180deg, rgba(242,101,34,0.1) 0%, rgba(242,101,34,0.05) 100%) !important; color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme input,
        [data-theme="dark"] body.front-theme select,
        [data-theme="dark"] body.front-theme textarea { background: #1E293B !important; border-color: rgba(148,163,184,0.3) !important; color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .footer { background: radial-gradient(circle at top left, rgba(78,192,239,0.08), transparent 28%), linear-gradient(180deg, #0B1120 0%, #0B1120 100%) !important; }
        [data-theme="dark"] body.front-theme .footer-card { background: rgba(255,255,255,0.04) !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .footer-bottom { border-top-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .page-header { background: linear-gradient(135deg, #0B1120 0%, #111827 100%) !important; }
        [data-theme="dark"] body.front-theme .page-header h1 { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .page-header p { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .contact-info h2 { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .faq-item h3 { color: #FFE4D6 !important; }
        [data-theme="dark"] body.front-theme .footer h4 { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .footer a { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .professional-prose,
        [data-theme="dark"] body.front-theme .prose { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .professional-prose h2,
        [data-theme="dark"] body.front-theme .professional-prose h3,
        [data-theme="dark"] body.front-theme .prose h2,
        [data-theme="dark"] body.front-theme .prose h3 { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .service-card h3,
        [data-theme="dark"] body.front-theme .service-showcase-copy h3,
        [data-theme="dark"] body.front-theme .marketing-feature-card h3,
        [data-theme="dark"] body.front-theme .action-card strong,
        [data-theme="dark"] body.front-theme .activity-card strong { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .service-card p,
        [data-theme="dark"] body.front-theme .feature-item p,
        [data-theme="dark"] body.front-theme .pricing-card p,
        [data-theme="dark"] body.front-theme .extra-card p,
        [data-theme="dark"] body.front-theme .faq-item p,
        [data-theme="dark"] body.front-theme .contact-method p { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .service-features li,
        [data-theme="dark"] body.front-theme .pricing-features li { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .pricing-price { border-bottom-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .pricing-price .unit,
        [data-theme="dark"] body.front-theme .pricing-price .minimum { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .contact-form { background: #111827 !important; }
        [data-theme="dark"] body.front-theme .form-label { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .form-input,
        [data-theme="dark"] body.front-theme .form-select,
        [data-theme="dark"] body.front-theme .form-textarea { background: #1E293B !important; border-color: #475569 !important; color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .faq-item { background: #111827 !important; }
        [data-theme="dark"] body.front-theme .page-intro-card,
        [data-theme="dark"] body.front-theme .card { background: rgba(17,24,39,0.96) !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .page-intro h1 { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .page-intro p { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .about-reason-card,
        [data-theme="dark"] body.front-theme .timeline-step-card,
        [data-theme="dark"] body.front-theme .service-banner-frame,
        [data-theme="dark"] body.front-theme .service-gallery-frame,
        [data-theme="dark"] body.front-theme .service-offers-block { background: rgba(17,24,39,0.96) !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .about-reason-card h3,
        [data-theme="dark"] body.front-theme .timeline-step-card h3,
        [data-theme="dark"] body.front-theme .service-offers-block h3 { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .about-reason-card p,
        [data-theme="dark"] body.front-theme .timeline-step-card p,
        [data-theme="dark"] body.front-theme .service-page-copy p,
        [data-theme="dark"] body.front-theme .service-offers-block p { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .service-offers-list { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .marketing-service-card img,
        [data-theme="dark"] body.front-theme .service-showcase-card img { background: linear-gradient(180deg, #0F172A 0%, #0B1120 100%) !important; }
        [data-theme="dark"] body.front-theme .info-card .info-card-contact { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .info-card .info-card-contact .inline-link { color: #FFE4D6 !important; }
        [data-theme="dark"] body.front-theme .order-detail-summary .info-card strong { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .section-head h2,
        [data-theme="dark"] body.front-theme .section-head h3 { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .marketing-stat-card span,
        [data-theme="dark"] body.front-theme .metric span,
        [data-theme="dark"] body.front-theme .info-card span,
        [data-theme="dark"] body.front-theme .portal-stat span { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .marketing-service-copy h3,
        [data-theme="dark"] body.front-theme .service-showcase-copy h3,
        [data-theme="dark"] body.front-theme .marketing-feature-card h3,
        [data-theme="dark"] body.front-theme .action-card strong,
        [data-theme="dark"] body.front-theme .activity-card strong { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .stat-card { background: rgba(17,24,39,0.96) !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .stat-label { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .hero-feature { background: rgba(17,24,39,0.9) !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .hero-feature-text { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .hero-feature-text span { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .marketing-hero-grid { background: linear-gradient(135deg, #9a3a0f 0%, #F26522 62%, #f06e38 100%) !important; }
        [data-theme="dark"] body.front-theme .template-cta-card { background: linear-gradient(135deg, #9a3a0f 0%, #D94E0F 100%) !important; }
        [data-theme="dark"] body.front-theme .captcha-question { color: #F1F5F9 !important; }
    </style>
</head>
<body class="front-theme public-theme">
    <div class="site-frame">
        <div class="top-bar">
            <div class="container topbar-inner">
                <span class="template-topbar-message">
                    Trusted Since 2005 | Custom Embroidery Digitizing &amp; Vector Art
                    <span class="template-topbar-separator">—</span>
                    <a href="tel:+12063126446">Call Us: +1 (206) 312-6446</a>
                </span>
            </div>
        </div>

        <header class="marketing-header">
            <div class="container marketing-header-shell">
                <a href="/login.php" class="marketing-brand">
                    <img class="site-logo" src="{{ $legacyAssetBase }}{{ $siteContext->logoPath() }}" alt="{{ $siteContext->displayLabel() }}">
                </a>

                <button class="marketing-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="public-navigation">Menu</button>

                <div class="marketing-actions">
                    @if (session()->has('customer_user_id'))
                        <a class="button secondary marketing-action-button" href="/dashboard.php">Dashboard</a>
                        <a class="button secondary marketing-action-button" href="/logout.php">Logout</a>
                        <a class="button primary marketing-action-button" href="/quote.php">Get Quote</a>
                    @else
                        <a class="button secondary marketing-action-button" href="/login.php">Login</a>
                        <a class="button secondary marketing-action-button" href="/sign-up.php">Sign Up</a>
                        <a class="button primary marketing-action-button" href="/sign-up.php">Get Quote</a>
                    @endif
                </div>

                <button class="theme-toggle" type="button" aria-label="Toggle dark mode" style="margin-left:8px;">
                    <span class="theme-toggle-light">🌙</span>
                    <span class="theme-toggle-dark">☀️</span>
                </button>

                <nav class="marketing-nav" id="public-navigation">
                    <div class="marketing-nav-list">
                        @foreach ($publicMenu as $item)
                            @php
                                $currentPath = request()->path();
                                $active = $currentPath === ltrim($item['href'], '/') || ($item['href'] === '/' && ($currentPath === '/' || $currentPath === ''));
                            @endphp
                            <a class="marketing-nav-link {{ $active ? 'active' : '' }}" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                        @endforeach
                    </div>
                </nav>
            </div>
         </header>

        <main class="page-content">
            @yield('content')
        </main>

        <footer class="footer">
            <div class="container">
                <div class="footer-grid">
                    <div class="footer-brand-block">
                        <img class="footer-logo" src="{{ $legacyAssetBase }}{{ $siteContext->logoPath() }}" alt="{{ $siteContext->displayLabel() }}">
                        <p>Professional embroidery digitizing services at affordable prices. Quality you can count on.</p>
                        <div class="footer-brand-pills">
                            <span>24 Hour Standard Turnaround</span>
                            <span>$1 per 1K Stitches</span>
                            <span>All Major Formats</span>
                        </div>
                    </div>

                    <div class="footer-column">
                        <h4>Services</h4>
                        <ul class="footer-links">
                            @foreach ($serviceLinks as $item)
                                <li><a href="{{ $item['href'] }}">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="footer-column">
                        <h4>Company</h4>
                        <ul class="footer-links">
                            @foreach ($companyLinks as $item)
                                <li><a href="{{ $item['href'] }}">{{ $item['label'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="footer-column footer-contact-block">
                        <h4>Contact</h4>
                        <ul class="footer-links">
                            <li><a href="tel:+12063126446">+1 (206) 312-6446</a></li>
                            <li>46494 Mission Blvd<br>Fremont, CA 94539</li>
                            @if ($supportEmail !== '')
                                <li><a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a></li>
                            @endif
                        </ul>
                        <div class="footer-cta-group">
                            <a class="button secondary footer-button" href="/login.php">Login</a>
                            <a class="button primary footer-button" href="{{ session()->has('customer_user_id') ? '/quote.php' : '/sign-up.php' }}">Get Quote</a>
                        </div>
                    </div>
                </div>
            </div>

            <div aria-hidden="true" style="height:40px;"></div>
            <div class="footer-bottom-wrap">
                <div class="container">
                    <div class="footer-bottom" style="margin-top:0;padding-top:0;">
                        <p>&copy; {{ date('Y') }} A Plus Digitizing. All rights reserved.</p>
                        <div class="footer-bottom-links">
                            <a href="/login.php">Login</a>
                            <a href="/sign-up.php">Sign Up</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('[data-nav-toggle]');
            var navigation = document.getElementById('public-navigation');

            if (toggle && navigation) {
                toggle.addEventListener('click', function () {
                    var isOpen = navigation.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            document.querySelectorAll('form[data-validate-form]').forEach(function (form) {
                var controls = Array.prototype.slice.call(form.querySelectorAll('input, select, textarea')).filter(function (control) {
                    return control.type !== 'hidden' && control.type !== 'submit' && control.type !== 'button' && control.type !== 'reset';
                });

                function fieldContainer(control) {
                    return control.closest('[data-form-field]') || control.closest('label') || control.parentElement;
                }

                function fieldErrorNode(control) {
                    var container = fieldContainer(control);
                    return container ? container.querySelector('[data-field-error]') : null;
                }

                function renderError(control, isValid, message) {
                    var error = fieldErrorNode(control);
                    control.classList.toggle('is-invalid', !isValid);
                    control.setAttribute('aria-invalid', isValid ? 'false' : 'true');

                    if (error) {
                        error.textContent = isValid ? '' : message;
                    }
                }

                function validateControl(control) {
                    if (control.disabled) {
                        return true;
                    }

                    var valid = control.checkValidity();
                    renderError(control, valid, valid ? '' : control.validationMessage);
                    return valid;
                }

                controls.forEach(function (control) {
                    control.addEventListener('blur', function () {
                        validateControl(control);
                    });

                    control.addEventListener('input', function () {
                        if (control.classList.contains('is-invalid') || control.getAttribute('aria-invalid') === 'true') {
                            validateControl(control);
                        }
                    });
                });

                form.addEventListener('submit', function (event) {
                    var firstInvalid = null;

                    controls.forEach(function (control) {
                        if (!validateControl(control) && !firstInvalid) {
                            firstInvalid = control;
                        }
                    });

                    if (firstInvalid) {
                        event.preventDefault();
                        firstInvalid.focus();
                    }
                });
            });
        });

        // Theme toggle
        var themeToggle = document.querySelector('.theme-toggle');
        if (themeToggle) {
            themeToggle.addEventListener('click', function () {
                var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                if (isDark) {
                    localStorage.removeItem('admin-theme');
                    document.documentElement.removeAttribute('data-theme');
                } else {
                    localStorage.setItem('admin-theme', 'dark');
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            });
        }
    </script>
</body>
</html>
