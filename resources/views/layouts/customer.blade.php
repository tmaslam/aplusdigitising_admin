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
    <title>@yield('title', $siteContext->displayLabel())</title>
    <link rel="icon" type="image/png" href="{{ $siteContext->faviconPath() }}?v=2">
    @php
        $legacyAssetBase = rtrim(url('/'), '/');
        $publicMenu = [
            // Public navigation hidden — all traffic routes through login
        ];
        $serviceLinks = [
            ['label' => 'Embroidery Digitizing', 'href' => url('/embroidery-digitizing.php')],
            ['label' => '3D / Puff Embroidery', 'href' => url('/3d-puff-embroidery-digitizing.php')],
            ['label' => 'Applique Embroidery', 'href' => url('/applique-embroidery-digitizing.php')],
            ['label' => 'Chain Stitch Embroidery', 'href' => url('/chain-stitch-embroidery-digitizing.php')],
            ['label' => 'Photo Digitizing', 'href' => url('/photo-digitizing.php')],
            ['label' => 'Vector Art', 'href' => url('/vector-art.php')],
        ];
        $companyLinks = [];
        $resourceLinks = [];
    @endphp
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
            --page-bg: #F8FAFC;
            --surface: #ffffff;
            --surface-soft: #FFF7ED;
            --ink: #0F172A;
            --muted: #64748B;
            --brand: {{ $siteContext->cssPrimaryColor() }};
            --brand-dark: {{ $siteContext->cssPrimaryDarkColor() }};
            --accent: {{ $siteContext->cssAccentColor() }};
            --line: #E2E8F0;
            --line-strong: #CBD5E1;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            --footer: #f8fafc;
            --max: 1180px;
        }

@include('shared.file-preview-styles')

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; font-size: 15px; }
        body {
            margin: 0;
            font-family: "Inter", "Roboto", "Segoe UI", sans-serif;
            color: var(--ink);
            background: #F8FAFC;
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
            background: #F8FAFC;
        }

        .topbar {
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            color: rgba(255,255,255,0.96);
            font-size: 0.92rem;
        }

        .topbar-inner {
            min-height: 46px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding: 10px 0;
        }

        .topbar a { color: #ffffff; }
        .topbar a:hover { color: rgba(255,255,255,0.86); }

        .topbar-links {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
        }

        .account-chip {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 8px 12px;
            border-radius: 999px;
            background: rgba(242, 101, 34, 0.08);
            border: 1px solid rgba(242, 101, 34, 0.12);
            color: var(--ink);
        }

        .account-chip-meta {
            display: block;
            line-height: 1.2;
        }

        .account-chip-meta strong {
            font-size: 0.92rem;
            color: var(--ink);
        }

        .account-chip-actions {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .account-chip-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 34px;
            padding: 7px 12px;
            border-radius: 999px;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--ink) !important;
            background: rgba(255,255,255,0.94);
            border: 1px solid rgba(17, 31, 45, 0.10);
            transition: background .18s ease, border-color .18s ease, color .18s ease;
        }

        .topbar .account-chip-link {
            color: var(--ink) !important;
        }

        .account-chip-link:hover {
            color: var(--brand-dark) !important;
            border-color: rgba(242, 101, 34, 0.18);
            background: rgba(242, 101, 34, 0.10);
        }

        .site-header {
            position: relative;
            z-index: 50;
            background: #ffffff;
            border-bottom: 1px solid #E2E8F0;
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
            gap: 0;
            min-width: 0;
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

        .nav-item {
            position: relative;
        }

        .nav-links > a,
        .nav-item > a,
        .nav-parent-toggle {
            padding: 26px 14px;
            border-radius: 0;
            font-size: 15px;
            font-family: 'Roboto Slab', serif;
            color: var(--ink);
            display: block;
            transition: color 0.2s ease;
        }

        .nav-parent-toggle {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: 0;
            cursor: pointer;
            width: 100%;
        }

        .nav-parent-toggle::after {
            content: "";
            width: 8px;
            height: 8px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg) translateY(-2px);
            transition: transform 0.2s ease;
            opacity: 0.95;
        }

        .nav-links > a:hover,
        .nav-links > a.active,
        .nav-item > a:hover,
        .nav-item > a.active,
        .nav-parent-toggle:hover,
        .nav-parent-toggle.active,
        .nav-item:hover > a {
            color: var(--brand);
        }

        .sub-nav {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 260px;
            background: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
            z-index: 60;
        }

        .sub-nav a {
            display: block;
            padding: 12px 16px;
            color: var(--ink);
            font-family: "Roboto", "Segoe UI", sans-serif;
            font-size: 0.98rem;
            border-bottom: 1px solid #e8eef3;
        }

        .sub-nav a:hover,
        .sub-nav a.active {
            background: #F8FAFC;
            color: var(--brand-dark);
        }

        .nav-item:hover .sub-nav {
            display: block;
        }

        .nav-item:hover .nav-parent-toggle::after,
        .nav-item:focus-within .nav-parent-toggle::after,
        .nav-item.open .nav-parent-toggle::after {
            transform: rotate(225deg) translateY(2px);
        }

        .nav-item:focus-within .sub-nav {
            display: block;
        }

        .nav-cta {
            background: transparent;
            color: #fff !important;
            font-weight: 400;
            box-shadow: none;
        }

        .nav-cta:hover {
            background: #fff !important;
            color: var(--brand) !important;
        }

        .page-content {
            padding: 28px 0 48px;
        }

        .customer-shell {
            display: grid;
            gap: 18px;
        }

        .customer-hero,
        .customer-nav,
        .content-card {
            background: #FFFFFF;
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        }

        .customer-hero {
            padding: clamp(18px, 3vw, 26px);
            background:
                linear-gradient(180deg, rgba(242, 101, 34, 0.06) 0%, rgba(255, 255, 255, 0.96) 100%),
                #fff;
        }

        .customer-hero.hero-compact {
            padding: 12px clamp(20px, 3vw, 28px);
        }

        .customer-hero.hero-compact .eyebrow {
            display: none;
        }

        .customer-hero.hero-compact .hero-grid {
            margin-top: 0;
            align-items: center;
        }

        .customer-hero.hero-compact h2 {
            font-size: 1.05rem;
            margin: 0;
            letter-spacing: -0.01em;
            line-height: 1.4;
        }

        .customer-hero.hero-compact p {
            font-size: 0.85rem;
            margin-top: 2px;
        }

        .eyebrow {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 999px;
            background: rgba(242, 101, 34, 0.12);
            color: #333333;
            font-size: 0.72rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .hero-grid {
            margin-top: 12px;
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .customer-hero h2 {
            margin: 12px 0 8px;
            font-size: clamp(1.6rem, 2.8vw, 2.2rem);
            letter-spacing: -0.04em;
            line-height: 1.1;
        }

        .customer-hero p {
            margin: 0;
            max-width: 760px;
            color: var(--muted);
            line-height: 1.7;
        }

        .hero-meta {
            display: grid;
            gap: 6px;
            justify-items: end;
            text-align: right;
        }

        .hero-meta strong {
            font-size: 1rem;
        }

        .hero-meta .status {
            justify-self: end;
        }

        .customer-nav {
            padding: 16px 18px;
        }

        .customer-nav-shell {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .customer-nav-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .customer-nav-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .customer-action-menu {
            position: relative;
        }

        .customer-tab {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: #666666;
            font-weight: 600;
            font-size: 0.92rem;
            transition: color .18s ease, background .18s ease;
            position: relative;
        }

        .customer-tab:hover {
            color: #333333;
            background: rgba(0, 0, 0, 0.04);
        }

        .customer-tab.active {
            color: var(--brand);
            background: transparent;
            font-weight: 700;
        }

        .customer-tab.active::after {
            content: "";
            position: absolute;
            bottom: 2px;
            left: 16px;
            right: 16px;
            height: 2px;
            background: var(--brand);
            border-radius: 2px;
        }

        .customer-tab.account {
            background: rgba(242, 101, 34, 0.06);
        }

        .customer-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 10px 20px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-weight: 700;
            transition: background .18s ease, border-color .18s ease, color .18s ease, transform .18s ease;
        }

        .customer-action.primary {
            background: linear-gradient(135deg, {{ $siteContext->cssPrimaryColor() }}, {{ $siteContext->cssPrimaryDarkColor() }});
            color: #fff;
            box-shadow: 0 12px 24px rgba(242, 101, 34, 0.18);
        }

        /* New Order = green (matches admin team Unlock button) */
        .customer-action.primary.new-order {
            background: linear-gradient(135deg, #22C55E, #16A34A);
            box-shadow: 0 12px 24px rgba(34, 197, 94, 0.18);
        }

        /* Add Funds = blue */
        .customer-action.primary.add-funds {
            background: linear-gradient(135deg, #2563EB, #1D4ED8);
            box-shadow: 0 12px 24px rgba(37, 99, 235, 0.18);
        }

        .customer-action.secondary {
            background: rgba(242, 101, 34, 0.08);
            color: var(--brand-dark);
            border-color: rgba(242, 101, 34, 0.16);
        }

        .customer-action:hover {
            transform: translateY(-1px);
        }

        .customer-action-menu summary {
            list-style: none;
            cursor: pointer;
        }

        .customer-action-menu summary::-webkit-details-marker {
            display: none;
        }

        .customer-action-menu .customer-action {
            gap: 8px;
        }

        .customer-action-menu .customer-action::after {
            content: "";
            width: 8px;
            height: 8px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg) translateY(-2px);
            transition: transform 0.18s ease;
        }

        .customer-action-menu[open] .customer-action::after {
            transform: rotate(225deg) translateY(2px);
        }

        .customer-action-list {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            min-width: 240px;
            padding: 10px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.98);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
            display: grid;
            gap: 6px;
            z-index: 30;
        }

        .customer-action-list a {
            display: grid;
            gap: 2px;
            padding: 12px 14px;
            border-radius: 14px;
            color: var(--ink);
            transition: background .18s ease, color .18s ease;
        }

        .customer-action-list a:hover {
            background: rgba(242, 101, 34, 0.08);
            color: var(--brand-dark);
        }

        .customer-action-list strong {
            font-size: 0.98rem;
            letter-spacing: -0.02em;
        }

        .customer-action-list span {
            color: var(--muted);
            font-size: 0.84rem;
        }

        .customer-action-list form {
            display: grid;
            gap: 2px;
            padding: 0;
            margin: 0;
        }

        .customer-action-list button {
            display: grid;
            gap: 2px;
            padding: 12px 14px;
            border-radius: 14px;
            color: var(--ink);
            background: none;
            border: none;
            text-align: left;
            cursor: pointer;
            font: inherit;
            transition: background .18s ease, color .18s ease;
        }

        .customer-action-list button:hover {
            background: rgba(242, 101, 34, 0.08);
            color: var(--brand-dark);
        }

        .customer-action-list .benefits-link {
            display: block;
            padding: 2px 0 0;
            font-size: 0.78rem;
            color: var(--brand);
            text-decoration: underline;
            cursor: pointer;
        }

        .customer-action-list .benefits-link:hover {
            color: var(--brand-dark);
        }

        .content-card {
            padding: clamp(18px, 2.4vw, 28px);
        }

        .flash {
            display: grid;
            gap: 10px;
        }

        .alert {
            padding: 14px 16px;
            border-radius: 18px;
            border: 1px solid transparent;
            font-size: 0.96rem;
            line-height: 1.6;
            background: rgba(184,80,77,0.10);
            color: #7c2f2d;
            border-color: rgba(184,80,77,0.18);
        }

        .alert-success {
            background: rgba(45,123,83,0.10);
            color: #1d5639;
            border-color: rgba(45,123,83,0.18);
        }

        .alert-error {
            background: rgba(184,80,77,0.10);
            color: #7c2f2d;
            border-color: rgba(184,80,77,0.18);
        }

        /* Allow class="alert success" as well as "alert alert-success" */
        .alert.success {
            background: rgba(45,123,83,0.10);
            color: #1d5639;
            border-color: rgba(45,123,83,0.18);
        }

        .metric-grid {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: thin;
        }
        .metric-grid::-webkit-scrollbar {
            height: 6px;
        }
        .metric-grid::-webkit-scrollbar-track {
            background: transparent;
        }
        .metric-grid::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }

        .portal-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 14px;
        }

        .portal-stat {
            display: block;
            padding: 20px 22px;
            border-radius: 12px;
            background: #fff;
            border: none;
            text-decoration: none;
            color: inherit;
            transition: transform .18s ease, box-shadow .18s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            flex: 0 0 auto;
            min-width: 160px;
        }

        a.portal-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(242, 101, 34, 0.12);
        }

        .portal-stat span {
            display: block;
            color: #666666;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .portal-stat strong {
            display: block;
            font-size: 1.6rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            color: #333333;
        }

        .action-grid,
        .workspace-grid,
        .summary-grid {
            display: grid;
            gap: 14px;
        }

        .action-grid {
            display: flex;
            gap: 14px;
            overflow-x: auto;
            padding-bottom: 6px;
            scrollbar-width: thin;
        }
        .action-grid::-webkit-scrollbar {
            height: 6px;
        }
        .action-grid::-webkit-scrollbar-track {
            background: transparent;
        }
        .action-grid::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }

        .workspace-grid {
            grid-template-columns: 1.15fr 0.85fr;
        }

        .summary-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .metric {
            padding: 20px 22px;
            border-radius: 12px;
            background: #fff;
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .metric-link {
            display: block;
            transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, background .18s ease;
        }

        .metric-link:hover {
            transform: translateY(-1px);
            border-color: rgba(242, 101, 34, 0.22);
            background: rgba(255,255,255,0.98);
            box-shadow: 0 16px 28px rgba(12, 48, 89, 0.08);
        }

        .metric span {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            font-weight: 700;
            margin-bottom: 6px;
        }

        .metric strong {
            display: block;
            font-size: 1.35rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .action-card,
        .activity-card {
            padding: 20px;
            border-radius: 12px;
            border: none;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            flex: 0 0 auto;
            min-width: 220px;
        }

        .action-card {
            display: grid;
            gap: 10px;
            align-content: start;
        }

        .action-card span,
        .activity-kicker {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            font-weight: 700;
        }

        .action-card strong,
        .activity-card strong {
            font-size: 1.08rem;
            letter-spacing: -0.03em;
        }

        .action-card p,
        .activity-card p {
            margin: 0;
            color: var(--muted);
        }

        a.action-card {
            transition: transform .18s ease, border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        a.action-card:hover {
            transform: translateY(-1px);
            border-color: rgba(242, 101, 34, 0.22);
            box-shadow: 0 16px 28px rgba(12, 48, 89, 0.08);
        }

        .action-card-primary {
            background: linear-gradient(135deg, #FFF3EB 0%, #FFE4D6 100%);
            border: 1.5px solid rgba(242, 101, 34, 0.18);
        }

        .action-card-primary span {
            color: {{ $siteContext->cssPrimaryDarkColor() }};
        }

        a.action-card-primary:hover {
            background: linear-gradient(135deg, #FFE4D6 0%, #FFD4C2 100%);
            border-color: rgba(242, 101, 34, 0.35);
            box-shadow: 0 12px 24px rgba(242, 101, 34, 0.12);
        }

        .activity-list {
            display: grid;
            gap: 12px;
        }

        .activity-item {
            padding: 14px 16px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            color: #fff;
        }

        .activity-item strong {
            color: #fff;
        }

        .activity-item p {
            color: rgba(255, 255, 255, 0.92);
        }

        .activity-item .inline-link {
            color: #fff;
            text-decoration: underline;
        }

        .activity-item .status.warning {
            background: rgba(216, 155, 50, 0.15);
            color: #8b5a00;
        }

        .activity-item .status.success {
            background: rgba(45, 123, 83, 0.15);
            color: #1d5639;
        }

        .activity-item .button.secondary {
            background: rgba(255, 255, 255, 0.94);
            color: var(--brand-dark);
            border-color: transparent;
        }

        .activity-item .button.secondary:hover {
            background: #fff;
            color: var(--brand-dark);
        }

        .activity-meta {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 8px;
        }

        .table-note {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.86rem;
            white-space: nowrap;
        }

        .inline-link {
            color: var(--brand-dark);
        }

        .inline-link:hover {
            text-decoration: underline;
        }

        .section-head {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-end;
            padding-bottom: 16px;
            margin-bottom: 20px;
            border-bottom: 1px solid var(--line);
            flex-wrap: wrap;
        }

        .section-head h3 {
            margin: 0;
            font-size: 1.4rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #333333;
            line-height: 1.25;
        }

        .section-head p {
            margin: 4px 0 0;
            color: #666666;
            font-size: 0.875rem;
            line-height: 1.5;
        }

        .invoice-detail-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .invoice-detail-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-wrap {
            overflow-x: auto;
            border-radius: 12px;
            border: none;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 720px;
            background: rgba(255,255,255,0.96);
        }

        th, td {
            padding: 14px 16px;
            text-align: left;
            border-bottom: 1px solid var(--line);
            vertical-align: top;
        }

        th {
            background: rgba(242, 101, 34,0.05);
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
        }

        tr:last-child td { border-bottom: none; }

        .status {
            display: inline-flex;
            padding: 6px 10px;
            border-radius: 999px;
            background: rgba(242, 101, 34,0.08);
            color: var(--brand-dark);
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .status.warning {
            background: rgba(216,155,50,0.12);
            color: #8b5a00;
        }

        .status.success {
            background: rgba(45,123,83,0.12);
            color: #1d5639;
        }

        .stack {
            display: grid;
            gap: 14px;
        }

        .content-note {
            padding: 14px 16px;
            border-radius: 14px;
            background: rgba(242, 101, 34, 0.06);
            border: 1px solid rgba(242, 101, 34, 0.14);
            font-size: 0.93rem;
            line-height: 1.6;
            color: var(--ink);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
        }

        .info-card,
        .list-card,
        .detail-card {
            padding: 16px 18px;
            border-radius: 12px;
            border: none;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .info-card span {
            display: block;
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            margin-bottom: 6px;
        }

        .info-card strong {
            font-size: 1.1rem;
            font-weight: 700;
            overflow-wrap: break-word;
            word-break: break-all;
        }

        .info-card p {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 0.9rem;
            font-weight: 400;
            line-height: 1.5;
        }

        /* Compact horizontal metadata strip used on order / quote detail pages */
        .order-meta-strip {
            display: flex;
            flex-wrap: wrap;
            margin: 0;
            padding: 0;
            list-style: none;
            border-top: 1px solid var(--line);
            margin-top: 14px;
        }

        .order-meta-strip li {
            flex: 1 1 140px;
            padding: 10px 18px;
            border-right: 1px solid var(--line);
        }

        .order-meta-strip li:last-child {
            border-right: none;
        }

        .order-meta-strip dt {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.09em;
            color: var(--muted);
            margin-bottom: 3px;
        }

        .order-meta-strip dd {
            margin: 0;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--ink);
            overflow-wrap: break-word;
        }
        .detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .list-card,
        .detail-card {
            padding: 0;
            overflow: hidden;
        }

        .card-head {
            padding: 16px 18px;
            border-bottom: 1px solid var(--line);
            background: rgba(242, 101, 34,0.04);
        }

        .card-head h4 {
            margin: 0;
            font-size: 1.05rem;
        }

        .card-head p {
            margin: 6px 0 0;
            color: var(--muted);
        }

        .file-list,
        .comment-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .file-item,
        .comment-item {
            padding: 16px 18px;
            border-top: 1px solid var(--line);
        }

        .file-item:first-child,
        .comment-item:first-child {
            border-top: 0;
        }

        .file-actions,
        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .file-actions .status.file-notice {
            display: block;
            width: 100%;
            white-space: normal;
            line-height: 1.45;
            border-radius: 14px;
            padding: 10px 12px;
            text-transform: none;
            letter-spacing: 0;
        }

        /* Table action column — used on orders and quotes list pages */
        .action-cell { white-space: nowrap; }

        .action-group {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: nowrap;
        }

        .action-group form { margin: 0; }
        .action-group .button { min-width: 0; white-space: nowrap; }
        .action-group .button.danger { color: #fff; }

        .button,
        button,
        input[type="submit"] {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 44px;
            padding: 11px 16px;
            border-radius: 16px;
            border: 1px solid transparent;
            background: linear-gradient(135deg, var(--brand), var(--brand-dark));
            color: white;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
        }

        .button.secondary,
        button.secondary {
            background: rgba(255,255,255,0.96);
            color: var(--ink);
            border-color: var(--line-strong);
        }

        .button.ghost,
        button.ghost {
            background: rgba(242, 101, 34,0.08);
            color: var(--brand-dark);
            border-color: rgba(242, 101, 34,0.16);
        }

        .button.danger,
        button.danger {
            background: linear-gradient(135deg, #bf5a57, #983a37);
        }

        form { margin: 0; }

        .form-grid {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        label {
            display: grid;
            gap: 8px;
            color: var(--ink);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .field-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .field-meta.required {
            min-height: auto;
            padding: 0;
            background: transparent;
            color: #d43f3a;
            font-size: 1.15rem;
            font-weight: 800;
            line-height: 1;
            letter-spacing: 0;
        }

        .field-help {
            margin-top: -2px;
            min-height: 18px;
            color: var(--muted);
            font-size: 0.88rem;
            line-height: 1.5;
            font-weight: 500;
        }

        .field-error {
            min-height: 18px;
            color: #b93a34;
            font-size: 0.86rem;
            line-height: 1.4;
            font-weight: 600;
        }

        input[type="text"],
        input[type="email"],
        input[type="date"],
        input[type="password"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            min-height: 46px;
            padding: 12px 14px;
            border-radius: 16px;
            border: 1px solid var(--line-strong);
            background: rgba(255,255,255,0.96);
            color: var(--ink);
            font: inherit;
        }

        input[type="date"] {
            appearance: none;
            -webkit-appearance: none;
            position: relative;
            padding-right: 44px;
        }

        input.is-invalid,
        select.is-invalid,
        textarea.is-invalid {
            border-color: rgba(185, 58, 52, 0.72);
            box-shadow: 0 0 0 4px rgba(185, 58, 52, 0.12);
        }

        textarea {
            min-height: 120px;
            resize: vertical;
        }

        .muted { color: var(--muted); }

        .empty-state {
            padding: 26px;
            text-align: center;
            color: var(--muted);
        }

        .pagination {
            display: flex;
            gap: 8px;
            justify-content: end;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .pagination a,
        .pagination span {
            display: inline-flex;
            min-width: 40px;
            min-height: 40px;
            padding: 10px 12px;
            align-items: center;
            justify-content: center;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(255,255,255,0.96);
            color: var(--ink);
        }

        .single-column {
            max-width: 720px;
        }

        /* Archive / list page filter bar */
        .filter-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
        }

        .filter-bar .filter-label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: var(--muted);
            font-size: 0.875rem;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Upload error shown on order submission / revision forms */
        .upload-error {
            margin-top: 8px;
            color: #b42318;
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Order / quote submission form — specialized field layouts */
        .dimension-field,
        .paired-field,
        .field-stack {
            display: grid;
            gap: 8px;
            color: var(--ink);
            font-weight: 700;
            font-size: 0.95rem;
        }

        .dimension-inputs {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            gap: 10px;
            align-items: center;
        }

        .dimension-divider {
            color: var(--muted);
            font-weight: 700;
            font-size: 1rem;
        }

        .paired-field-inputs {
            display: grid;
            grid-template-columns: minmax(110px, 0.8fr) minmax(0, 1.2fr);
            gap: 10px;
            align-items: start;
        }

        .upload-guidance {
            grid-column: 1 / -1;
            margin-top: -4px;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .upload-guidance strong { color: var(--ink); }

        .required-mark {
            color: #b42318;
            font-weight: 800;
            margin-left: 2px;
        }

        .field-stack > span {
            color: var(--ink);
            font-weight: 700;
            font-size: 0.95rem;
        }

        [hidden] { display: none !important; }

        /* Invoice filter bar */
        .invoice-filterbar {
            display: grid;
            gap: 14px;
            grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
            align-items: end;
            margin-bottom: 18px;
            padding: 18px;
            border: none;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .invoice-filterbar .filter-field {
            display: flex;
            flex-direction: column;
            gap: 8px;
            min-width: 0;
        }

        .invoice-filterbar .field-label {
            color: var(--muted);
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .invoice-filterbar .field-hint {
            grid-column: 1 / -1;
            color: var(--muted);
            font-size: 0.86rem;
            line-height: 1.5;
            padding: 2px 2px 0;
        }

        .invoice-filterbar input[type="date"] {
            min-height: 52px;
            border-radius: 16px;
            border: 1px solid rgba(17,31,45,0.12);
            background: #fff;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
            color: var(--ink);
            font-size: 0.98rem;
            font-weight: 600;
            padding: 0 14px;
        }

        .invoice-filterbar input[type="date"]::-webkit-date-and-time-value { text-align: left; }

        .invoice-filterbar .field-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            align-items: center;
        }

        /* Payment checkout and result page */
        .payment-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .payment-card-header h3 { margin: 0 0 4px; font-size: 1.15rem; letter-spacing: -0.02em; }
        .payment-card-header p { margin: 0; color: var(--muted); font-size: 0.88rem; }

        .payment-amount-col { text-align: right; flex-shrink: 0; }

        .payment-amount-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 4px;
        }

        .payment-amount-value {
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: var(--ink);
        }

        .payment-amount-note { font-size: 0.78rem; color: var(--muted); margin-top: 2px; }

        .payment-ref-strip {
            display: flex;
            gap: 10px;
            align-items: center;
            padding: 10px 14px;
            border-radius: 12px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .payment-ref-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            white-space: nowrap;
        }

        .payment-ref-value {
            font-size: 0.85rem;
            font-family: monospace;
            color: var(--ink);
            word-break: break-all;
        }

        .payment-status-banner {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            margin-bottom: 24px;
        }

        .payment-status-main { display: flex; gap: 14px; align-items: flex-start; }

        .payment-status-icon {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
        }

        .payment-status-icon.ok  { background: rgba(45,123,83,0.12);  color: #1d5639; }
        .payment-status-icon.fail { background: rgba(197,107,34,0.12); color: #7a4010; }

        .payment-detail-strip {
            display: flex;
            gap: 24px;
            align-items: flex-start;
            padding: 12px 16px;
            border-radius: 12px;
            background: var(--surface-soft);
            border: 1px solid var(--line);
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .payment-detail-item .di-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--muted);
            margin-bottom: 3px;
            font-weight: 700;
        }

        .payment-detail-item .di-value { font-size: 0.85rem; }
        .payment-detail-item .di-value.mono { font-family: monospace; word-break: break-all; }

        .td-right, .th-right { text-align: right; }

        .footer {
            margin-top: 48px;
            background: var(--footer);
            color: #475569;
            padding: 44px 0 18px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.2fr repeat(3, 1fr);
            gap: 24px;
        }

        .footer-card {
            padding: 22px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .footer-logo {
            height: 40px;
            width: auto;
            margin-bottom: 16px;
        }

        .footer-intro {
            margin: 0;
            color: #64748b;
        }

        .footer h3 {
            margin-top: 0;
            margin-bottom: 14px;
            color: #1f252d;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .footer ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: grid;
            gap: 10px;
        }

        .footer-link {
            color: #475569;
        }

        .footer-contact {
            display: grid;
            gap: 14px;
        }

        .footer-contact-item {
            display: grid;
            gap: 4px;
        }

        .footer-contact-item span {
            color: #94a3b8;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
        }

        .footer-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 24px;
            flex-wrap: wrap;
            padding: 18px 24px;
        }

        .footer-bar-brand {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .footer-bar-brand .footer-logo {
            height: 36px;
            margin: 0;
        }

        .footer-bar-brand .footer-intro {
            margin: 0;
            max-width: 340px;
        }

        .footer-bar-links {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .footer-bar-links .footer-link {
            color: rgba(255, 255, 255, 0.78);
            font-size: 0.9rem;
            font-weight: 500;
            white-space: nowrap;
            transition: color 0.2s ease;
        }

        .footer-bar-links .footer-link:hover {
            color: #FFE4D6;
        }

        .footer a:hover {
            color: var(--brand);
        }

        .footer-bottom {
            margin-top: 0;
            padding: 14px 24px;
            background: #F8FAFC;
            border-radius: 12px;
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            font-size: 0.92rem;
        }

        @media (max-width: 1080px) {
            .action-grid {
                display: flex;
                gap: 12px;
            }

            .detail-grid,
            .info-grid,
            .form-grid,
            .footer-grid,
            .workspace-grid,
            .summary-grid {
                grid-template-columns: 1fr 1fr;
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
            .nav-links > a,
            .nav-item > a,
            .nav-parent-toggle {
                border-radius: 0;
                padding: 12px 14px;
            }
            .nav-parent-toggle {
                justify-content: space-between;
            }
            .nav-item:hover .sub-nav,
            .nav-item.open .sub-nav {
                display: block;
            }
            .sub-nav {
                position: static;
                min-width: 100%;
                box-shadow: none;
                background: rgba(255,255,255,0.98);
                border-top: 1px solid rgba(17,31,45,0.06);
            }
            .sub-nav a {
                padding: 12px 18px;
            }
            .hero-grid,
            .section-head {
                display: grid;
            }
            .hero-meta {
                justify-items: start;
                text-align: left;
            }

            .customer-nav-shell {
                grid-template-columns: 1fr;
                align-items: stretch;
            }

            .customer-nav-grid,
            .customer-nav-actions {
                width: 100%;
            }

            .customer-nav-actions {
                justify-content: stretch;
                flex-wrap: wrap;
            }

            .customer-action-menu,
            .customer-nav-actions > .customer-tab.account {
                flex: 1 1 100%;
            }

            .customer-action-menu summary,
            .customer-nav-actions > .customer-tab.account {
                width: 100%;
                justify-content: center;
            }

            .customer-action-list {
                left: 0;
                right: 0;
            }
        }

        @media (max-width: 720px) {
            .topbar-inner {
                justify-content: center;
                text-align: center;
            }

            .account-chip {
                flex-wrap: wrap;
                justify-content: center;
            }

            .account-chip-actions {
                width: 100%;
                justify-content: center;
            }

            .page-content {
                padding: 18px 0 38px;
            }

            .customer-hero {
                padding: 20px;
            }

            .action-grid {
                display: flex;
                gap: 10px;
            }

            .detail-grid,
            .info-grid,
            .form-grid,
            .footer-grid,
            .workspace-grid,
            .summary-grid {
                grid-template-columns: 1fr;
            }

            .order-meta-strip li {
                flex: 1 1 45%;
                border-right: none;
                border-bottom: 1px solid var(--line);
            }

            .order-meta-strip li:last-child {
                border-bottom: none;
            }

            table {
                min-width: 100%;
            }

            th,
            td {
                white-space: normal;
                word-break: break-word;
            }

            .file-actions,
            .actions {
                width: 100%;
            }

            .file-actions .button,
            .file-actions button,
            .actions .button,
            .actions button {
                flex: 1 1 100%;
            }

            .invoice-filterbar .field-actions {
                width: 100%;
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

        [data-theme="dark"] {
            color-scheme: dark;
            --page-bg: #0B1120;
            --surface: #111827;
            --surface-soft: #0F172A;
            --ink: #F1F5F9;
            --muted: #94A3B8;
            --line: #334155;
            --line-strong: #475569;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
            --footer: #0B1120;
        }
        [data-theme="dark"] body { background: #0B1120; color: #F1F5F9; }
        [data-theme="dark"] .site-frame { background: #0B1120; }
        [data-theme="dark"] .topbar { background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%); }
        [data-theme="dark"] .account-chip { background: rgba(242,101,34,0.12); border-color: rgba(242,101,34,0.2); color: #F1F5F9; }
        [data-theme="dark"] .account-chip-meta strong { color: #F1F5F9; }
        [data-theme="dark"] .account-chip-link { background: #1E293B; border-color: #334155; color: #F1F5F9; }
        [data-theme="dark"] .account-chip-link:hover { background: rgba(242,101,34,0.15); color: #fff; border-color: rgba(242,101,34,0.25); }
        [data-theme="dark"] .site-header { background: #0F172A; border-bottom-color: #334155; }
        [data-theme="dark"] .nav-links > a,
        [data-theme="dark"] .nav-item > a,
        [data-theme="dark"] .nav-parent-toggle { color: #F1F5F9; }
        [data-theme="dark"] .sub-nav { background: #111827; box-shadow: 0 8px 24px rgba(0,0,0,0.35); }
        [data-theme="dark"] .sub-nav a { color: #F1F5F9; border-bottom-color: #334155; }
        [data-theme="dark"] .sub-nav a:hover,
        [data-theme="dark"] .sub-nav a.active { background: #0F172A; color: #fff; }
        [data-theme="dark"] .customer-hero,
        [data-theme="dark"] .customer-nav,
        [data-theme="dark"] .content-card { background: #111827; box-shadow: 0 4px 12px rgba(0,0,0,0.25); }
        [data-theme="dark"] .customer-hero { background: linear-gradient(180deg, rgba(242,101,34,0.08) 0%, rgba(17,24,39,0.96) 100%), #111827; }
        [data-theme="dark"] .eyebrow { background: rgba(242,101,34,0.18); color: #FFE4D6; }
        [data-theme="dark"] .hero-meta strong { color: #F1F5F9; }
        [data-theme="dark"] .customer-tab { color: #94A3B8; }
        [data-theme="dark"] .customer-tab:hover { color: #F1F5F9; background: rgba(255,255,255,0.04); }
        [data-theme="dark"] .customer-tab.active { color: var(--brand); }
        [data-theme="dark"] .customer-action.secondary { background: rgba(242,101,34,0.1); color: #FFE4D6; border-color: rgba(242,101,34,0.25); }
        [data-theme="dark"] .customer-action-list { background: #111827; border-color: #334155; box-shadow: 0 8px 24px rgba(0,0,0,0.35); }
        [data-theme="dark"] .customer-action-list a { color: #F1F5F9; }
        [data-theme="dark"] .customer-action-list a:hover { background: rgba(242,101,34,0.12); color: #FFE4D6; }
        [data-theme="dark"] .customer-action-list button { color: #F1F5F9; }
        [data-theme="dark"] .customer-action-list button:hover { background: rgba(242,101,34,0.12); color: #FFE4D6; }
        [data-theme="dark"] .alert,
        [data-theme="dark"] .alert-error { background: rgba(239,68,68,0.12); color: #fca5a5; border-color: rgba(239,68,68,0.25); }
        [data-theme="dark"] .alert-success,
        [data-theme="dark"] .alert.success { background: rgba(34,197,94,0.12); color: #86efac; border-color: rgba(34,197,94,0.25); }
        [data-theme="dark"] .portal-stat { background: #111827; box-shadow: 0 1px 3px rgba(0,0,0,0.25); }
        [data-theme="dark"] .portal-stat span { color: #94A3B8; }
        [data-theme="dark"] .portal-stat strong { color: #F1F5F9; }
        [data-theme="dark"] .metric { background: #111827; }
        [data-theme="dark"] .metric-link:hover { background: #111827; border-color: rgba(242,101,34,0.25); box-shadow: 0 16px 28px rgba(0,0,0,0.25); }
        [data-theme="dark"] .action-card,
        [data-theme="dark"] .activity-card { background: #111827; }
        [data-theme="dark"] a.action-card:hover { border-color: rgba(242,101,34,0.25); box-shadow: 0 16px 28px rgba(0,0,0,0.25); }
        [data-theme="dark"] .action-card-primary { background: linear-gradient(135deg, #2a1810 0%, #1f140e 100%); border-color: rgba(242,101,34,0.3); }
        [data-theme="dark"] .action-card-primary span { color: #FFE4D6; }
        [data-theme="dark"] a.action-card-primary:hover { background: linear-gradient(135deg, #3d2418 0%, #2a1810 100%); }
        [data-theme="dark"] .activity-item { background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dark) 100%); }
        [data-theme="dark"] .section-head { border-bottom-color: #334155; }
        [data-theme="dark"] .section-head h3 { color: #F1F5F9; }
        [data-theme="dark"] .section-head p { color: #94A3B8; }
        [data-theme="dark"] table { background: #111827; }
        [data-theme="dark"] th { background: rgba(242,101,34,0.08); color: #94A3B8; }
        [data-theme="dark"] td { border-bottom-color: #334155; }
        [data-theme="dark"] .status { background: rgba(242,101,34,0.12); color: #FFE4D6; }
        [data-theme="dark"] .status.warning { background: rgba(234,179,8,0.12); color: #fde047; }
        [data-theme="dark"] .status.success { background: rgba(34,197,94,0.12); color: #86efac; }
        [data-theme="dark"] .content-note { background: rgba(242,101,34,0.08); border-color: rgba(242,101,34,0.2); color: #F1F5F9; }
        [data-theme="dark"] .info-card,
        [data-theme="dark"] .list-card,
        [data-theme="dark"] .detail-card { background: #111827; }
        [data-theme="dark"] .card-head { background: rgba(242,101,34,0.06); border-bottom-color: #334155; }
        [data-theme="dark"] .card-head h4 { color: #F1F5F9; }
        [data-theme="dark"] .card-head p { color: #94A3B8; }
        [data-theme="dark"] .file-item,
        [data-theme="dark"] .comment-item { border-top-color: #334155; }
        [data-theme="dark"] .button,
        [data-theme="dark"] button,
        [data-theme="dark"] input[type="submit"] { color: #fff; }
        [data-theme="dark"] .button.secondary,
        [data-theme="dark"] button.secondary { background: #111827; color: #F1F5F9; border-color: #475569; }
        [data-theme="dark"] .button.ghost,
        [data-theme="dark"] button.ghost { background: rgba(242,101,34,0.1); color: #FFE4D6; border-color: rgba(242,101,34,0.25); }
        [data-theme="dark"] .button.danger,
        [data-theme="dark"] button.danger { background: linear-gradient(135deg, #7f1d1d, #991b1b); }
        [data-theme="dark"] label { color: #F1F5F9; }
        [data-theme="dark"] .field-help { color: #94A3B8; }
        [data-theme="dark"] .field-error { color: #fca5a5; }
        [data-theme="dark"] input[type="text"],
        [data-theme="dark"] input[type="email"],
        [data-theme="dark"] input[type="date"],
        [data-theme="dark"] input[type="password"],
        [data-theme="dark"] input[type="number"],
        [data-theme="dark"] textarea,
        [data-theme="dark"] select { background: #1E293B; border-color: #475569; color: #F1F5F9; }
        [data-theme="dark"] .muted { color: #94A3B8; }
        [data-theme="dark"] .empty-state { color: #94A3B8; }
        [data-theme="dark"] .pagination a,
        [data-theme="dark"] .pagination span { background: #111827; color: #F1F5F9; border-color: #334155; }
        [data-theme="dark"] .invoice-filterbar { background: #111827; }
        [data-theme="dark"] .invoice-filterbar input[type="date"] { border-color: #334155; background: #1E293B; color: #F1F5F9; box-shadow: inset 0 1px 0 rgba(255,255,255,0.05); }
        [data-theme="dark"] .payment-ref-strip,
        [data-theme="dark"] .payment-detail-strip { background: #0F172A; border-color: #334155; }
        [data-theme="dark"] .payment-status-icon.ok { background: rgba(34,197,94,0.12); color: #86efac; }
        [data-theme="dark"] .payment-status-icon.fail { background: rgba(234,179,8,0.12); color: #fde047; }
        [data-theme="dark"] .payment-amount-value { color: #F1F5F9; }
        [data-theme="dark"] .payment-ref-value { color: #F1F5F9; }
        [data-theme="dark"] .footer { background: #0B1120; color: #94A3B8; }
        [data-theme="dark"] .footer-card { background: rgba(255,255,255,0.04); border-color: #334155; }
        [data-theme="dark"] .footer-intro { color: #94A3B8; }
        [data-theme="dark"] .footer h3 { color: #F1F5F9; }
        [data-theme="dark"] .footer-link { color: #94A3B8; }
        [data-theme="dark"] .footer-bottom { background: #0F172A; color: #94A3B8; }
        [data-theme="dark"] .footer-bar-links .footer-link { color: #94A3B8; }
        [data-theme="dark"] .footer-bar-links .footer-link:hover { color: #FFE4D6; }
        [data-theme="dark"] .footer a:hover { color: var(--brand); }
        [data-theme="dark"] .inline-link { color: #FFE4D6; }
        [data-theme="dark"] .upload-error { color: #fca5a5; }
        [data-theme="dark"] .required-mark { color: #fca5a5; }
        [data-theme="dark"] .metric-grid::-webkit-scrollbar-thumb,
        [data-theme="dark"] .action-grid::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); }
        [data-theme="dark"] .order-meta-strip { border-top-color: #334155; }
        [data-theme="dark"] .order-meta-strip li { border-right-color: #334155; }
        [data-theme="dark"] .topbar a { color: #ffffff; }
        [data-theme="dark"] .topbar a:hover { color: rgba(255,255,255,0.86); }
        [data-theme="dark"] .activity-item .status.warning { background: rgba(234,179,8,0.15); color: #fde047; }
        [data-theme="dark"] .activity-item .status.success { background: rgba(34,197,94,0.15); color: #86efac; }
        [data-theme="dark"] .activity-item .button.secondary { background: #1E293B; color: #F1F5F9; }
        [data-theme="dark"] .activity-item .button.secondary:hover { background: #111827; color: #F1F5F9; }

        /* front-theme-overrides.css key overrides */
        [data-theme="dark"] body.front-theme { background: radial-gradient(circle at top left, rgba(242,101,34,0.12), transparent 28%), linear-gradient(180deg, #0B1120 0%, #0B1120 100%) !important; color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .site-frame { background: rgba(17,24,39,0.96) !important; box-shadow: 0 28px 80px rgba(0,0,0,0.35) !important; }
        [data-theme="dark"] body.front-theme .site-header { background: rgba(15,23,42,0.97) !important; border-bottom-color: #334155 !important; }
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
        [data-theme="dark"] body.front-theme .activity-item { background: linear-gradient(180deg, rgba(242,101,34,0.06), rgba(15,23,42,0.5)), #111827 !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme.customer-portal-theme .customer-hero.dashboard-hero { background: linear-gradient(180deg, rgba(242,101,34,0.06) 0%, rgba(17,24,39,0.98) 100%), #111827 !important; }
        [data-theme="dark"] body.front-theme.customer-portal-theme .customer-hero { background: transparent !important; }
        [data-theme="dark"] body.front-theme .hero-feature { background: rgba(17,24,39,0.9) !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .hero-feature-text { color: #F1F5F9 !important; }
        [data-theme="dark"] body.front-theme .hero-feature-text span { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .stat-card { background: rgba(17,24,39,0.96) !important; border-color: #334155 !important; }
        [data-theme="dark"] body.front-theme .stat-label { color: #94A3B8 !important; }
        [data-theme="dark"] body.front-theme .marketing-hero-grid { background: linear-gradient(135deg, #9a3a0f 0%, #F26522 62%, #f06e38 100%) !important; }
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
    </style>
    <link rel="stylesheet" href="{{ url('/css/front-theme-overrides.css') }}?v=2">
</head>
<body class="front-theme customer-portal-theme">
    @php
        $currentPath = request()->path();
        $customerName = request()->attributes->get('customerUser')?->display_name ?? session('customer_user_name');
        $customerNav = [
            ['label' => 'Dashboard', 'href' => '/dashboard.php', 'match' => ['dashboard.php']],
            ['label' => 'Orders', 'href' => '/view-orders.php', 'match' => ['new-order.php', 'vector-order.php', 'view-orders.php', 'view-order-detail.php', 'edit-order.php', 'disapprove-order.php', 'download.php', 'preview.php']],
            ['label' => 'Quotes', 'href' => '/view-quotes.php', 'match' => ['quote.php', 'vector_quote.php', 'vector-quote.php', 'digitizing_quote.php', 'digitizing-quote.php', 'view-quotes.php', 'view-quote-detail.php', 'edit-quote.php']],
            ['label' => 'Billing', 'href' => '/view-billing.php', 'match' => ['view-billing.php', 'payment.php', 'payment-proceed.php', 'successpay.php', 'referral-invoice.php']],
            ['label' => 'Paid Orders', 'href' => '/view-archive-orders.php', 'match' => ['view-paid-orders.php', 'view-archive-orders.php']],
            ['label' => 'Invoices', 'href' => '/view-invoices.php', 'match' => ['view-invoices.php', 'view-invoice-detail.php']],
        ];
        $customerStartLinks = [
            ['label' => 'Digitizing Order', 'href' => '/new-order.php', 'description' => 'Start a regular embroidery digitizing order.'],
            ['label' => 'Vector Order', 'href' => '/vector-order.php', 'description' => 'Submit a vector-only order directly.'],
            ['label' => 'Digitizing Quote', 'href' => '/quote.php', 'description' => 'Get digitizing pricing first before placing the order.'],
            ['label' => 'Vector Quote', 'href' => '/vector-quote.php', 'description' => 'Request vector pricing first before placing the order.'],
        ];
    @endphp

    <div class="site-frame">
        <div class="topbar">
            <div class="container topbar-inner">
                <a href="mailto:support@aplusdigitizng.com">Email Us: support@aplusdigitizng.com</a>
                <div class="topbar-links">
                    <div class="account-chip">
                        <div class="account-chip-meta">
                            <strong>{{ $customerName ?: 'Customer' }}</strong>
                        </div>
                        <div class="account-chip-actions">
                            <a class="account-chip-link" href="/my-profile.php">My Profile</a>
                            <a class="account-chip-link" href="/logout.php">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <header class="site-header">
            <div class="container nav-shell">
                <a class="brand" href="/login.php">
                    <img src="{{ $legacyAssetBase }}{{ $siteContext->logoPath() }}" alt="{{ $siteContext->displayLabel() }}">
                </a>

                <button class="nav-toggle" type="button" data-nav-toggle aria-expanded="false" aria-controls="public-navigation">Menu</button>

                <nav class="nav-links" id="public-navigation">
                    @foreach ($publicMenu as $item)
                        @php
                            $active = $currentPath === ltrim($item['href'], '/') || ($item['href'] === '/' && ($currentPath === '/' || $currentPath === ''));
                            $childActive = collect($item['children'] ?? [])->contains(fn ($child) => $currentPath === ltrim($child['href'], '/'));
                        @endphp
                        @if (! empty($item['children']))
                            <div class="nav-item">
                                <button class="nav-parent-toggle {{ $active || $childActive ? 'active' : '' }}" type="button" aria-expanded="false">{{ $item['label'] }}</button>
                                <div class="sub-nav">
                                    @foreach ($item['children'] as $child)
                                        <a class="{{ $currentPath === ltrim($child['href'], '/') ? 'active' : '' }}" href="{{ $child['href'] }}">{{ $child['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a class="{{ $active ? 'active' : '' }}" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                        @endif
                    @endforeach
                </nav>

                <button class="theme-toggle" type="button" aria-label="Toggle dark mode">
                    <span class="theme-toggle-light">🌙</span>
                    <span class="theme-toggle-dark">☀️</span>
                </button>
            </div>
        </header>

        <main class="page-content">
            <div class="container customer-shell">
                @hasSection('top_banner')
                    @yield('top_banner')
                @endif

                <section class="customer-hero @yield('hero_class')">
                    @hasSection('eyebrow')
                        <span class="eyebrow">@yield('eyebrow')</span>
                    @endif
                    <div class="hero-grid">
                        <div>
                            <h2>@yield('hero_title', $pageTitle ?? $siteContext->displayLabel())</h2>
                            <p>@yield('hero_text', 'Manage your orders, quotes, billing, and files in one place.') </p>
                        </div>
                        @hasSection('hero_meta')
                            <div class="hero-meta">
                                @yield('hero_meta')
                            </div>
                        @endif
                    </div>
                </section>

                <nav class="customer-nav">
                    <div class="customer-nav-shell">
                        <div class="customer-nav-grid">
                            @foreach ($customerNav as $item)
                                @php
                                    $active = collect($item['match'])->contains(fn ($pattern) => str_contains($currentPath, $pattern));
                                @endphp
                                <a class="customer-tab {{ $active ? 'active' : '' }}" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                            @endforeach
                        </div>
                        <div class="customer-nav-actions">
                            <details class="customer-action-menu">
                                <summary class="customer-action primary new-order">New Order</summary>
                                <div class="customer-action-list">
                                    @foreach ($customerStartLinks as $item)
                                        <a href="{{ $item['href'] }}">
                                            <strong>{{ $item['label'] }}</strong>
                                            <span>{{ $item['description'] }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            </details>
                            @php
                                $dashCustomer = request()->attributes->get('customerUser');
                                $dashUserId = $dashCustomer ? $dashCustomer->user_id : 0;
                                $dashEmail = $dashCustomer ? urlencode($dashCustomer->user_email) : '';
                                $dashFundOptions = [
                                    ['label' => '$1000 → $850', 'save' => 'Save 15%', 'url' => 'https://buy.stripe.com/9B66oI5vO77Sf0I7Xp6Ri0d?prefilled_promo_code=AFAPLUS15'],
                                    ['label' => '$500 → $450', 'save' => 'Save 10%', 'url' => 'https://buy.stripe.com/5kQ00k4rK2RCbOw7Xp6Ri0e?prefilled_promo_code=AFAPLUS10'],
                                    ['label' => '$300 → $275', 'save' => 'Save 8%', 'url' => 'https://buy.stripe.com/9B64gAf6o2RC9GocdF6Ri0f?prefilled_promo_code=AFAPLUS8'],
                                    ['label' => '$100 → $95', 'save' => 'Save 5%', 'url' => 'https://buy.stripe.com/fZu7sM8I09g03i04Ld6Ri0g?prefilled_promo_code=AFAPLUS5'],
                                ];
                                $customFundUrl = 'https://buy.stripe.com/3cs6pT2hTc6sa8E5kl';
                            @endphp
                            <details class="customer-action-menu">
                                <summary class="customer-action primary add-funds">Add Funds</summary>
                                <div class="customer-action-list">
                                    @foreach ($dashFundOptions as $option)
                                        <a href="{{ $option['url'] }}&client_reference_id=user_{{ $dashUserId }}&prefilled_email={{ $dashEmail }}" target="_blank" rel="noopener">
                                            <strong>{{ $option['label'] }}</strong>
                                            <span style="color:{{ $siteContext->cssPrimaryColor() }};">{{ $option['save'] }}</span>
                                        </a>
                                    @endforeach
                                    <a href="{{ $customFundUrl }}?client_reference_id=user_{{ $dashUserId }}&prefilled_email={{ $dashEmail }}" target="_blank" rel="noopener">
                                        <strong>Custom Amount</strong>
                                        <span style="color:{{ $siteContext->cssPrimaryColor() }}">No savings</span>
                                    </a>
                                </div>
                            </details>
                            @php
                                $sidebarPlans = [
                                    ['name'=>'Enterprise','amount'=>799.99,'designs'=>'100 Designs/Month'],
                                    ['name'=>'Pro','amount'=>399.99,'designs'=>'50 Designs/Month'],
                                    ['name'=>'Plus','amount'=>199.99,'designs'=>'25 Designs/Month'],
                                    ['name'=>'Starter','amount'=>79.99,'designs'=>'10 Designs/Month']
                                ];
                                $sidebarCustomer = request()->attributes->get('customerUser');
                                $sidebarSubscription = null;
                                if ($sidebarCustomer) {
                                    $sidebarTopup = \App\Models\CustomerTopup::query()
                                        ->where('user_id', $sidebarCustomer->user_id)
                                        ->where('status', 'completed')
                                        ->whereIn('amount', [79.99, 199.99, 399.99, 799.99])
                                        ->orderByDesc('completed_at')
                                        ->first();
                                    if ($sidebarTopup) {
                                        $sidebarPlanNames = [79.99 => 'Starter', 199.99 => 'Plus', 399.99 => 'Pro', 799.99 => 'Enterprise'];
                                        $sidebarSubscription = $sidebarPlanNames[(int) $sidebarTopup->amount] ?? null;
                                    }
                                }
                                $sidebarCurrentTier = $sidebarSubscription ? array_search($sidebarSubscription, array_column($sidebarPlans, 'name')) : null;
                            @endphp
                            <details class="customer-action-menu">
                                <summary class="customer-action primary">Subscribe</summary>
                                <div class="customer-action-list">
                                    @foreach ($sidebarPlans as $sidebarPlanIndex => $plan)
                                        @php
                                            $isCurrentPlan = $sidebarSubscription && $sidebarSubscription === $plan['name'];
                                            $canSelectPlan = ! $isCurrentPlan;
                                        @endphp
                                        <form method="post" action="/buy-subscription.php">
                                            @csrf
                                            <input type="hidden" name="amount" value="{{ $plan['amount'] }}">
                                            <button type="submit" @disabled(! $canSelectPlan)>
                                                <strong>{{ $plan['name'] }} ${{ $plan['amount'] }}/Month</strong>
                                                <span>{{ $plan['designs'] }}</span>
                                                <a href="/price-plan.php" class="benefits-link" onclick="event.stopPropagation();">see benefits</a>
                                            </button>
                                        </form>
                                    @endforeach
                                </div>
                            </details>
                        </div>
                    </div>
                </nav>

                <div class="flash">
                    @if (session('impersonator_admin_id'))
                        <div class="alert">
                            You are viewing this account as support for {{ session('impersonation_target_name', $customerName) }}.
                            <form method="post" action="{{ url('/stop-simulated-session') }}" style="display:inline-flex; margin-left:12px;">
                                @csrf
                                <button type="submit" class="button ghost">Return To Admin</button>
                            </form>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if ($errors->any())
                        <div class="alert alert-error">{{ $errors->first() }}</div>
                    @endif
                </div>

                @yield('content')
            </div>
        </main>

        <footer class="footer">
            <div class="container footer-bottom">
                <span>Copyrights &copy; 2010-{{ date('Y') }} All Rights Reserved by aplusdigitizing.com</span>
                <a href="mailto:support@aplusdigitizng.com" style="color: #ffffff; text-decoration: none;">support@aplusdigitizng.com</a>
            </div>
        </footer>
    </div>

    @include('shared.file-preview-modal')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.querySelector('[data-nav-toggle]');
            var navigation = document.getElementById('public-navigation');
            var parentToggles = document.querySelectorAll('.nav-parent-toggle');
            var customerActionMenus = document.querySelectorAll('.customer-action-menu');
            var forms = document.querySelectorAll('[data-form-validation]');

            if (!toggle || !navigation) {
                // keep running for customer action menus below
            }

            if (toggle && navigation) {
                toggle.addEventListener('click', function () {
                    var isOpen = navigation.classList.toggle('open');
                    toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            }

            parentToggles.forEach(function (button) {
                button.addEventListener('click', function () {
                    if (window.innerWidth > 860) {
                        return;
                    }

                    var item = button.closest('.nav-item');
                    var isOpen = item.classList.toggle('open');
                    button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                });
            });

            if (customerActionMenus.length) {
                document.addEventListener('click', function (event) {
                    customerActionMenus.forEach(function (menu) {
                        if (!menu.contains(event.target)) {
                            menu.removeAttribute('open');
                        }
                    });
                });

                document.addEventListener('keydown', function (event) {
                    if (event.key !== 'Escape') {
                        return;
                    }

                    customerActionMenus.forEach(function (menu) {
                        menu.removeAttribute('open');
                    });
                });
            }

            forms.forEach(function (form) {
                var controls = Array.prototype.slice.call(form.querySelectorAll('input, select, textarea')).filter(function (control) {
                    return control.name && control.type !== 'hidden' && control.type !== 'submit' && control.type !== 'button';
                });

                function errorNode(control) {
                    var wrapper = control.closest('label') || control.parentElement;
                    return wrapper ? wrapper.querySelector('[data-field-error]') : null;
                }

                function renderError(control, valid, message) {
                    var node = errorNode(control);

                    control.classList.toggle('is-invalid', !valid);
                    control.setAttribute('aria-invalid', valid ? 'false' : 'true');

                    if (node) {
                        node.textContent = valid ? '' : message;
                    }
                }

                function syncMatchValidity(control) {
                    var targetName = control.getAttribute('data-match');

                    if (!targetName) {
                        return;
                    }

                    var source = form.querySelector('[name="' + targetName + '"]');

                    if (!source) {
                        control.setCustomValidity('');
                        return;
                    }

                    if (control.value && source.value && control.value !== source.value) {
                        control.setCustomValidity(control.getAttribute('data-match-message') || 'This field must match.');
                        return;
                    }

                    control.setCustomValidity('');
                }

                function validateControl(control) {
                    if (control.disabled) {
                        return true;
                    }

                    syncMatchValidity(control);

                    var valid = control.checkValidity();
                    renderError(control, valid, valid ? '' : control.validationMessage);

                    return valid;
                }

                controls.forEach(function (control) {
                    control.addEventListener('blur', function () {
                        validateControl(control);
                    });

                    control.addEventListener('input', function () {
                        if (control.classList.contains('is-invalid') || control.hasAttribute('data-match')) {
                            validateControl(control);
                        }
                    });

                    control.addEventListener('change', function () {
                        validateControl(control);
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
        });
    </script>
    @include('shared.file-preview-script')
</body>
</html>
