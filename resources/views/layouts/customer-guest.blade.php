<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $siteContext->displayLabel())</title>
    <link rel="icon" type="image/png" href="{{ $siteContext->faviconPath() }}?v=2">
    @php
        $legacyAssetBase = rtrim(url('/'), '/');
        $publicMenu = [
            ['label' => 'Home', 'href' => 'https://aplusdigitizing.com/'],
            ['label' => 'Services', 'href' => 'https://aplusdigitizing.com/services'],
            ['label' => 'Pricing', 'href' => 'https://aplusdigitizing.com/pricing'],
            ['label' => 'Process', 'href' => 'https://aplusdigitizing.com/process'],
            ['label' => 'Blog', 'href' => 'https://aplusdigitizing.com/blog'],
            ['label' => 'About', 'href' => 'https://aplusdigitizing.com/about'],
            ['label' => 'Contact', 'href' => 'https://aplusdigitizing.com/contact'],
        ];
        $serviceLinks = [
            ['label' => 'All Services', 'href' => 'https://aplusdigitizing.com/services'],
            ['label' => '3D Puff Digitizing', 'href' => 'https://aplusdigitizing.com/services/3d-puff'],
            ['label' => 'Vector Art Conversion', 'href' => 'https://aplusdigitizing.com/services/vector-art'],
            ['label' => 'Corporate Embroidery', 'href' => 'https://aplusdigitizing.com/services/corporate'],
        ];
        $companyLinks = [
            ['label' => 'About Us', 'href' => 'https://aplusdigitizing.com/about'],
            ['label' => 'Blog', 'href' => 'https://aplusdigitizing.com/blog'],
            ['label' => 'Pricing', 'href' => 'https://aplusdigitizing.com/pricing'],
            ['label' => 'Our Process', 'href' => 'https://aplusdigitizing.com/process'],
            ['label' => 'Contact', 'href' => 'https://aplusdigitizing.com/contact'],
        ];
        $legalLinks = [
            ['label' => 'Terms & Conditions', 'href' => 'https://aplusdigitizing.com/terms'],
        ];
    @endphp
    <style>
        :root {
            color-scheme: light;
            --page-bg: #FAF7F2;
            --surface: #ffffff;
            --surface-soft: #FFF8F0;
            --ink: #1f252d;
            --muted: #5e6772;
            --brand: {{ $siteContext->cssPrimaryColor() }};
            --brand-dark: {{ $siteContext->cssPrimaryDarkColor() }};
            --line: #E8E0D8;
            --shadow: 0 18px 38px rgba(17, 31, 45, 0.12);
            --footer: #111821;
            --max: 1180px;
            --danger: #b8504d;
            --success: #2d7b53;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Roboto", "Segoe UI", sans-serif;
            color: var(--ink);
            background: #0d1117;
            line-height: 1.6;
        }

        a { color: inherit; text-decoration: none; }
        img { max-width: 100%; height: auto; display: block; }

        .container {
            width: min(var(--max), calc(100% - 28px));
            margin: 0 auto;
        }

        .site-frame {
            width: min(100%, 1280px);
            margin: 0 auto;
            background: #111821;
            box-shadow: 0 10px 32px rgba(0, 0, 0, 0.4);
            min-height: 100vh;
        }


        .site-header {
            position: sticky;
            top: 0;
            z-index: 50;
            background: rgba(17,24,33,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        .nav-shell {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            min-height: 76px;
        }

        .brand {
            display: inline-flex;
            align-items: center;
        }

        .brand img {
            height: 78px;
            width: auto;
            max-width: 48vw;
        }

        .nav-toggle {
            display: none;
            border: 1px solid rgba(255,255,255,0.45);
            background: rgba(255,255,255,0.12);
            color: #fff;
            border-radius: 12px;
            padding: 10px 14px;
            font-weight: 700;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .nav-links a {
            padding: 26px 14px;
            font-size: 15px;
            font-family: 'Roboto Slab', serif;
            color: rgba(255,255,255,0.88);
            transition: color 0.2s ease;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--brand);
        }

        .page-content {
            padding: 40px 0 56px;
        }

        .guest-shell {
            width: min(1120px, 100%);
            margin: 0 auto;
        }

        .panel {
            border-radius: 24px;
            background: #fff;
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        .intro-panel {
            padding: clamp(28px, 4vw, 42px);
            color: #fff;
            background:
                linear-gradient(rgba(0, 0, 0, 0.48), rgba(0, 0, 0, 0.48)),
                url('{{ $legacyAssetBase }}/images/aplus-digitizing-banner.webp') center/cover no-repeat;
        }

        .intro-panel span {
            display: inline-block;
            padding: 7px 12px;
            border-radius: 999px;
            background: rgba(255,255,255,0.14);
            font-size: 0.76rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .intro-panel h1 {
            margin: 18px 0 10px;
            font-size: clamp(2rem, 4.6vw, 3.5rem);
            line-height: 0.98;
            letter-spacing: -0.04em;
        }

        .intro-panel p {
            margin: 0;
            color: rgba(255,255,255,0.88);
            line-height: 1.8;
        }

        .intro-stack {
            display: grid;
            gap: 12px;
            margin-top: 24px;
        }

        .intro-card {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.12);
            border: 1px solid rgba(255,255,255,0.16);
        }

        .form-panel {
            padding: clamp(24px, 3vw, 36px);
        }

        .form-panel.auth-panel {
            border-top: 5px solid var(--brand);
        }

        .form-panel h2 {
            margin: 0 0 10px;
            font-size: 1.9rem;
            letter-spacing: -0.04em;
        }

        .muted {
            margin: 0 0 22px;
            color: var(--muted);
            line-height: 1.7;
        }

        .alert {
            margin-bottom: 16px;
            padding: 13px 15px;
            border-radius: 16px;
            border: 1px solid rgba(184,80,77,0.2);
            background: rgba(184,80,77,0.10);
            color: #7c2f2d;
        }

        .alert.success {
            background: rgba(45,123,83,0.10);
            color: #1d5639;
            border-color: rgba(45,123,83,0.18);
        }

        form {
            display: grid;
            gap: 16px;
        }

        .grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        label {
            display: grid;
            gap: 8px;
            font-weight: 700;
        }

        .form-field {
            display: grid;
            gap: 8px;
            position: relative;
        }

        .field-label {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            font-weight: 700;
            color: var(--ink);
        }

        .field-meta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 22px;
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .field-meta.required {
            min-height: auto;
            padding: 0;
            background: transparent;
            color: #d43f3a;
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0;
        }

        .field-meta.optional {
            display: none;
        }

        .form-section {
            display: grid;
            gap: 14px;
            margin-top: 6px;
            padding-top: 10px;
        }

        .form-section + .form-section {
            border-top: 1px solid var(--line);
            padding-top: 20px;
            margin-top: 4px;
        }

        .section-heading {
            display: grid;
            gap: 4px;
        }

        .section-heading h3 {
            margin: 0;
            font-size: 1rem;
            letter-spacing: -0.02em;
            color: var(--ink);
        }

        .section-heading p {
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
        }

        .field-help {
            margin-top: -2px;
            min-height: 20px;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.5;
        }

        .quick-picks {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .country-results {
            display: grid;
            gap: 6px;
            max-height: 240px;
            overflow-y: auto;
            padding: 10px;
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            z-index: 40;
            border: 1px solid rgba(13, 110, 163, 0.14);
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 16px 32px rgba(17, 31, 45, 0.08);
        }

        .country-results[hidden] {
            display: none;
        }

        .country-result {
            min-height: auto;
            width: 100%;
            justify-content: flex-start;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid transparent;
            background: #fff;
            color: var(--ink);
            font-weight: 600;
            text-align: left;
            box-shadow: none;
        }

        .country-result:hover,
        .country-result:focus,
        .country-result.is-selected {
            background: rgba(242, 101, 34, 0.10);
            border-color: rgba(242, 101, 34, 0.18);
        }

        .quick-pick {
            min-height: 36px;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid rgba(13, 110, 163, 0.18);
            background: rgba(242, 101, 34, 0.08);
            color: var(--brand-dark);
            font-size: 0.84rem;
            font-weight: 700;
            line-height: 1.2;
            box-shadow: none;
        }

        .quick-pick:hover,
        .quick-pick:focus {
            background: rgba(242, 101, 34, 0.14);
        }

        .field-error {
            min-height: 18px;
            color: var(--danger);
            font-size: 0.86rem;
            line-height: 1.4;
        }

        input, select, textarea {
            width: 100%;
            min-height: 48px;
            padding: 12px 14px;
            border-radius: 16px;
            border: 2px solid #8fa3b5;
            background: #fff;
            color: var(--ink);
            font: inherit;
            box-shadow: inset 0 1px 2px rgba(17, 31, 45, 0.04);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
        }

        textarea { min-height: 110px; resize: vertical; }

        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(242, 101, 34, 0.16);
        }

        input.is-invalid,
        select.is-invalid,
        textarea.is-invalid,
        .field-check.is-invalid,
        .radio-group.is-invalid {
            border-color: rgba(184, 80, 77, 0.65) !important;
            box-shadow: 0 0 0 4px rgba(184, 80, 77, 0.10);
        }

        .radio-group {
            display: grid;
            gap: 10px;
            padding: 4px;
            border-radius: 18px;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .radio-option {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255,255,255,0.88);
        }

        .radio-option input { width: auto; min-height: auto; margin-top: 4px; }

        .field-check,
        .terms-row {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 18px;
            background: rgba(255,255,255,0.88);
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        .field-check input,
        .terms-row input {
            width: auto;
            min-height: auto;
            margin-top: 4px;
        }

        .field-check-copy,
        .terms-copy {
            display: grid;
            gap: 6px;
        }

        .terms-copy a {
            color: var(--brand-dark);
            font-weight: 700;
        }

        .terms-line {
            display: inline-flex;
            align-items: center;
            flex-wrap: nowrap;
            gap: 6px;
            font-weight: 600;
            white-space: nowrap;
        }

        @media (max-width: 640px) {
            .terms-line {
                display: inline;
                white-space: normal;
            }
        }

        .actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .info-note {
            margin-bottom: 14px;
            padding: 14px 16px;
            border-radius: 16px;
            border: 1px solid rgba(242, 101, 34, 0.16);
            background: rgba(242, 101, 34, 0.06);
            color: #355061;
        }

        .info-note strong {
            color: var(--brand-dark);
        }

        button, .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 12px 18px;
            border-radius: 16px;
            border: 0;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: white;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }

        .button.secondary {
            background: white;
            color: var(--brand-dark);
            border: 1px solid var(--line);
        }

        .footer {
            margin-top: 48px;
            background: #111821;
            color: rgba(255, 255, 255, 0.78);
            padding: 44px 0 18px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr repeat(4, 1fr);
            gap: 24px;
        }

        .footer-card {
            padding: 22px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .footer-logo {
            width: auto;
            height: 40px;
            max-width: 100%;
            margin-bottom: 16px;
        }

        .footer-intro {
            margin: 0;
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.6;
        }

        .footer h3 {
            margin-top: 0;
            margin-bottom: 14px;
            color: #fff;
            font-size: 1rem;
            font-weight: 600;
            text-transform: none;
            letter-spacing: normal;
        }

        .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }

        .footer-link {
            color: rgba(255, 255, 255, 0.78);
            font-weight: 400;
            transition: color 0.2s ease;
        }

        .footer-link:hover {
            color: #FFE4D6;
        }

        .footer-bottom {
            margin-top: 28px;
            padding-top: 16px;
            border-top: 1px solid rgba(255,255,255,0.12);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 0.92rem;
            color: rgba(255, 255, 255, 0.78);
        }
        .social-links {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .social-links a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 999px;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.78);
            transition: background 0.2s ease, color 0.2s ease;
        }
        .social-links a:hover {
            background: var(--brand);
            color: #fff;
        }

        @media (max-width: 980px) {
            .guest-shell,
            .footer-grid,
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 860px) {
            .nav-toggle { display: inline-flex; }
            .nav-links {
                display: none;
                width: 100%;
                padding: 8px 0 16px;
            }
            .nav-links.open { display: flex; }
            .nav-links a {
                padding: 12px 14px;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ url('/css/front-theme-overrides.css') }}">
</head>
<body class="front-theme customer-guest-theme">
    <div class="site-frame">
        <header class="site-header">
            <div class="container nav-shell">
                <a class="brand" href="https://aplusdigitizing.com/">
                    <img src="{{ $legacyAssetBase }}{{ $siteContext->logoPath() }}" alt="{{ $siteContext->displayLabel() }}">
                </a>

                <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="public-navigation">Menu</button>

                <nav class="nav-links" id="public-navigation">
                    @foreach ($publicMenu as $item)
                        @php
                            $active = request()->path() === ltrim($item['href'], '/') || ($item['href'] === '/' && request()->path() === '/');
                        @endphp
                        <a class="{{ $active ? 'active' : '' }}" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                    @endforeach
                </nav>
            </div>
        </header>

        <main class="page-content">
            @yield('content')
        </main>

        @section('footer')
        <footer class="footer">
            <div class="container footer-grid">
                <div class="footer-card">
                    <img src="{{ $legacyAssetBase }}/images/logo.png" alt="Aplus Digitizing" class="footer-logo">
                    <p class="footer-intro">Professional embroidery digitizing & vector art services with guaranteed 24-hour turnaround.</p>
                </div>
                <div class="footer-card">
                    <h3>Services</h3>
                    <ul>
                        @foreach ($serviceLinks as $link)
                            <li><a href="{{ $link['href'] }}" class="footer-link">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="footer-card">
                    <h3>Company</h3>
                    <ul>
                        @foreach ($companyLinks as $link)
                            <li><a href="{{ $link['href'] }}" class="footer-link">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="footer-card">
                    <h3>Legal</h3>
                    <ul>
                        @foreach ($legalLinks as $link)
                            <li><a href="{{ $link['href'] }}" class="footer-link">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="footer-card">
                    <h3>Get in Touch</h3>
                    <ul>
                        <li>support@aplusdigitizing.com</li>
                        <li>24/7 Support Available</li>
                    </ul>
                    <a href="https://aplusdigitizing.com/bookameeting" class="button" style="margin-top: 14px; min-height: 36px; padding: 8px 18px; font-size: 0.9rem; border-radius: 999px;">Book a Meeting</a>
                </div>
            </div>
            <div class="container footer-bottom">
                <span>&copy; {{ date('Y') }} A Plus Digitizing. All rights reserved.</span>
                <div class="social-links">
                    <a href="https://www.facebook.com/APlusDigitizingUSA/" target="_blank" rel="noopener noreferrer" aria-label="Facebook">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                    </a>
                    <a href="https://www.instagram.com/aplusdigitizingworks/" target="_blank" rel="noopener noreferrer" aria-label="Instagram">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M7.75 2h8.5A5.75 5.75 0 0 1 22 7.75v8.5A5.75 5.75 0 0 1 16.25 22h-8.5A5.75 5.75 0 0 1 2 16.25v-8.5A5.75 5.75 0 0 1 7.75 2zm0 1.5A4.25 4.25 0 0 0 3.5 7.75v8.5A4.25 4.25 0 0 0 7.75 20.5h8.5a4.25 4.25 0 0 0 4.25-4.25v-8.5A4.25 4.25 0 0 0 16.25 3.5h-8.5zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 1.5a3.5 3.5 0 1 0 0 7 3.5 3.5 0 0 0 0-7zM17.75 6.5a.75.75 0 1 1 0 1.5.75.75 0 0 1 0-1.5z"/></svg>
                    </a>
                    <a href="https://www.pinterest.com/aplusdigitizingworks/" target="_blank" rel="noopener noreferrer" aria-label="Pinterest">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.477 2 12c0 4.236 2.636 7.855 6.356 9.312-.088-.791-.167-2.005.035-2.868.181-.78 1.172-4.97 1.172-4.97s-.299-.598-.299-1.482c0-1.388.806-2.425 1.808-2.425.853 0 1.265.64 1.265 1.408 0 .858-.546 2.14-.828 3.33-.236.995.5 1.807 1.48 1.807 1.778 0 3.144-1.874 3.144-4.58 0-2.393-1.72-4.068-4.177-4.068-2.845 0-4.515 2.135-4.515 4.34 0 .859.331 1.781.745 2.281a.3.3 0 0 1 .069.288l-.278 1.133c-.044.183-.145.223-.335.134-1.249-.581-2.03-2.407-2.03-3.874 0-3.154 2.292-6.052 6.608-6.052 3.469 0 6.165 2.473 6.165 5.776 0 3.445-2.173 6.22-5.19 6.22-1.013 0-1.965-.527-2.292-1.148l-.623 2.378c-.226.869-.835 1.958-1.244 2.621.937.29 1.931.446 2.962.446 5.523 0 10-4.477 10-10S17.523 2 12 2z"/></svg>
                    </a>
                </div>
            </div>
        </footer>
        @show

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('[data-nav-toggle]');
            var navigation = document.getElementById('public-navigation');

            if (!toggle || !navigation) {
                return;
            }

            toggle.addEventListener('click', function () {
                var isOpen = navigation.classList.toggle('open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.querySelectorAll('form[data-validate-form]').forEach(function (form) {
                var controls = Array.prototype.slice.call(form.querySelectorAll('input, select, textarea')).filter(function (control) {
                    return control.type !== 'hidden' && control.type !== 'submit' && control.type !== 'button' && control.type !== 'reset';
                });

                var radioNames = {};

                function fieldContainer(control) {
                    return control.closest('[data-form-field]') || control.closest('label') || control.parentElement;
                }

                function fieldErrorNode(control) {
                    var container = fieldContainer(control);

                    return container ? container.querySelector('[data-field-error]') : null;
                }

                function syncMatchValidity(control) {
                    var otherName = control.getAttribute('data-match');

                    if (!otherName) {
                        syncCountryValidity(control);
                        return;
                    }

                    var other = form.querySelector('[name="' + otherName + '"]');

                    if (!other) {
                        control.setCustomValidity('');
                        return;
                    }

                    if (control.value !== '' && other.value !== '' && control.value !== other.value) {
                        control.setCustomValidity(control.getAttribute('data-match-message') || 'This field must match.');
                    } else {
                        control.setCustomValidity('');
                    }

                    syncCountryValidity(control);
                }

                function syncCountryValidity(control) {
                    if (!control.hasAttribute('data-country-strict')) {
                        return;
                    }

                    var options = [];

                    try {
                        options = JSON.parse(control.getAttribute('data-country-options') || '[]');
                    } catch (error) {
                        options = [];
                    }

                    var value = (control.value || '').trim();

                    if (value === '' || options.indexOf(value) !== -1) {
                        control.setCustomValidity('');
                    } else {
                        control.setCustomValidity('Please choose a country from the suggested list.');
                    }
                }

                function renderError(control, isValid, message) {
                    var container = fieldContainer(control);
                    var error = fieldErrorNode(control);

                    control.classList.toggle('is-invalid', !isValid);
                    control.setAttribute('aria-invalid', isValid ? 'false' : 'true');

                    if (container && (control.type === 'checkbox' || control.type === 'radio')) {
                        container.classList.toggle('is-invalid', !isValid);
                    }

                    if (error) {
                        error.textContent = isValid ? '' : message;
                    }
                }

                function validateRadio(control) {
                    if (radioNames[control.name]) {
                        return radioNames[control.name];
                    }

                    var group = Array.prototype.slice.call(form.querySelectorAll('input[type="radio"][name="' + control.name + '"]'));
                    var required = group.some(function (item) { return item.required; });
                    var valid = !required || group.some(function (item) { return item.checked; });
                    var message = valid ? '' : (control.getAttribute('data-group-error') || 'Please select an option.');

                    group.forEach(function (item) {
                        renderError(item, valid, message);
                    });

                    radioNames[control.name] = valid;

                    return valid;
                }

                function validateControl(control) {
                    if (control.disabled) {
                        return true;
                    }

                    if (control.type === 'radio') {
                        return validateRadio(control);
                    }

                    syncMatchValidity(control);

                    var valid = control.checkValidity();
                    renderError(control, valid, valid ? '' : control.validationMessage);

                    return valid;
                }

                controls.forEach(function (control) {
                    control.addEventListener('blur', function () {
                        radioNames = {};
                        validateControl(control);
                    });

                    control.addEventListener('input', function () {
                        radioNames = {};
                        if (control.classList.contains('is-invalid') || control.getAttribute('aria-invalid') === 'true') {
                            validateControl(control);
                        } else if (control.hasAttribute('data-match')) {
                            validateControl(control);
                        }
                    });

                    control.addEventListener('change', function () {
                        radioNames = {};
                        validateControl(control);
                    });
                });

                form.addEventListener('submit', function (event) {
                    radioNames = {};
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

            document.querySelectorAll('[data-country-pick]').forEach(function (button) {
                button.addEventListener('click', function () {
                    var field = document.querySelector('[data-country-input]');

                    if (!field) {
                        return;
                    }

                    field.value = button.getAttribute('data-country-pick') || '';
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    field.focus();
                });
            });

            document.querySelectorAll('[data-country-input]').forEach(function (field) {
                var results = field.parentElement ? field.parentElement.querySelector('[data-country-results]') : null;
                var options = [];

                try {
                    options = JSON.parse(field.getAttribute('data-country-options') || '[]');
                } catch (error) {
                    options = [];
                }

                function renderCountryOptions(term) {
                    if (!results) {
                        return;
                    }

                    var query = (term || '').trim().toLowerCase();
                    var hasFocus = document.activeElement === field;

                    if (!hasFocus) {
                        results.hidden = true;
                        return;
                    }

                    var startsWith = options.filter(function (country) {
                        return query === '' || country.toLowerCase().indexOf(query) === 0;
                    });
                    var includes = options.filter(function (country) {
                        return query !== '' && country.toLowerCase().indexOf(query) > 0;
                    });
                    var matches = startsWith.concat(includes);

                    if (!matches.length) {
                        results.innerHTML = '';
                        results.hidden = true;
                        return;
                    }

                    function escapeHtml(str) {
                        var div = document.createElement('div');
                        div.textContent = str;
                        return div.innerHTML;
                    }
                    results.innerHTML = matches.map(function (country) {
                        var selected = field.value === country ? ' is-selected' : '';
                        return '<button type="button" class="country-result' + selected + '" data-country-value="' + escapeHtml(country) + '">' + escapeHtml(country) + '</button>';
                    }).join('');
                    results.hidden = false;
                }

                field.addEventListener('focus', function () {
                    renderCountryOptions('');
                });

                field.addEventListener('input', function () {
                    renderCountryOptions(field.value);
                });

                field.addEventListener('keydown', function (e) {
                    if (e.key === 'Tab' || e.key === 'Escape') {
                        if (results) {
                            results.hidden = true;
                        }
                    }
                });

                field.addEventListener('blur', function () {
                    window.setTimeout(function () {
                        if (results) {
                            results.hidden = true;
                        }
                    }, 140);
                });

                if (!results) {
                    return;
                }

                results.addEventListener('click', function (event) {
                    var option = event.target.closest('[data-country-value]');

                    if (!option) {
                        return;
                    }

                    field.value = option.getAttribute('data-country-value') || '';
                    field.dispatchEvent(new Event('input', { bubbles: true }));
                    field.dispatchEvent(new Event('change', { bubbles: true }));
                    results.hidden = true;
                    field.focus();
                });

                results.hidden = true;
            });
        });
    </script>
</body>
</html>
