@php
if (!isset($siteContext)) {
    try {
        $siteContext = app(\App\Support\SiteContext::class);
    } catch (\Throwable $e) {
        $siteContext = new \App\Support\SiteContext(
            id: null,
            legacyKey: config('sites.primary_legacy_key', '1dollar'),
            slug: 'admin',
            name: 'Admin Portal',
            brandName: 'Admin Portal',
            host: parse_url((string) config('app.url', 'http://localhost'), PHP_URL_HOST) ?: 'localhost',
            supportEmail: (string) config('mail.from.address', ''),
            fromEmail: (string) config('mail.from.address', ''),
            websiteAddress: (string) config('app.url', ''),
            isPrimary: true,
        );
    }
}
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Digitizing Jobs Admin')</title>
    <link rel="icon" type="image/png" href="{{ $siteContext->faviconPath() }}?v=2">
    <style>
        :root {
            color-scheme: light;
            --bg: #f5f5f5;
            --panel: #ffffff;
            --panel-strong: #ffffff;
            --ink: #1f2937;
            --muted: #6b7280;
            --line: rgba(31, 41, 55, 0.12);
            --line-strong: rgba(31, 41, 55, 0.22);
            --accent: #64748b;
            --accent-dark: #475569;
            --accent-soft: #f1f5f9;
            --warning: #64748b;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

@include('shared.file-preview-styles')

        * { box-sizing: border-box; }
        [hidden] { display: none !important; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Avenir Next", "Segoe UI", sans-serif;
            color: var(--ink);
            overflow-x: hidden;
            background: #f5f5f5;
        }
        body.nav-open { overflow: hidden; }
        a { color: inherit; text-decoration: none; }
        .shell { display: grid; grid-template-columns: 300px minmax(0, 1fr); min-height: 100vh; }
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(18, 32, 46, 0.42);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.22s ease;
            z-index: 35;
        }
        .sidebar {
            padding: 28px 20px;
            border-right: 1px solid rgba(0,0,0,0.08);
            background: #e2e8f0;
            color: #475569;
            backdrop-filter: blur(16px);
            position: sticky;
            top: 0;
            align-self: start;
            min-height: 100vh;
            max-height: 100vh;
            overflow-y: auto;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            z-index: 40;
        }
        .sidebar-close,
        .mobile-nav-toggle {
            display: none;
            border: 0;
            border-radius: 999px;
            padding: 10px 14px;
            min-height: 40px;
            background: rgba(255,255,255,0.12);
            color: #fff;
            font-weight: 800;
            cursor: pointer;
            box-shadow: none;
        }
        .sidebar-close {
            background: rgba(0,0,0,0.08);
            color: #475569;
            margin-bottom: 14px;
        }
        .mobile-nav-toggle {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
        }
        .brand {
            display: block;
            padding: 18px 18px 22px;
            border-radius: 24px;
            background-color: #f1f5f9;
            background-image: none;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.1);
            position: sticky;
            top: -28px;
            z-index: 10;
            cursor: pointer;
            transition: background-image 0.2s ease, box-shadow 0.2s ease;
        }
        .brand:hover {
            background-image: linear-gradient(145deg, rgba(255,255,255,0.18), rgba(255,255,255,0.06));
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.14), 0 4px 12px rgba(0,0,0,0.2);
        }
        .brand-logo { display: block; width: 100%; max-width: 160px; height: auto; margin: 0 auto 10px; }
        .brand-label { margin: 0; font-size: 1.3rem; line-height: 1; letter-spacing: 0.02em; color: #000000; font-weight: 800; text-align: center; }
        .brand p { margin: 10px 0 0; color: rgba(255,255,255,0.7); font-size: 0.92rem; line-height: 1.6; }
        .section-title {
            margin: 26px 10px 10px;
            font-size: 0.72rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 800;
        }
        .section-title-toggle {
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
            margin: 26px 10px 10px;
            font-size: 0.72rem;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 800;
            padding: 4px 0;
            transition: color 0.2s ease;
        }
        .section-title-toggle:hover { color: #334155; }
        .section-title-toggle::after {
            content: '▸';
            font-size: 0.9rem;
            transition: transform 0.2s ease;
            display: inline-block;
        }
        .section-title-toggle.open::after { transform: rotate(90deg); }
        .nav-list { display: grid; gap: 10px; }
        .nav-list-collapsible {
            display: grid;
            gap: 10px;
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: max-height 0.3s ease, opacity 0.25s ease, margin 0.25s ease;
        }
        .nav-list-collapsible.open {
            max-height: 800px;
            opacity: 1;
        }
        .nav-card {
            background: rgba(0,0,0,0.04);
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 20px;
            padding: 12px;
        }
        .nav-card a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 14px;
            color: #475569;
            transition: background 0.2s ease;
        }
        .nav-card a:hover, .nav-card a.active { background: #64748b; color: #ffffff; }
        .nav-card a:hover .count, .nav-card a.active .count { color: #ffffff; }
        .count {
            min-width: 32px;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(100, 116, 139, 0.15);
            color: #64748b;
            text-align: center;
            font-size: 0.82rem;
            font-weight: 800;
        }
        .main {
            padding: clamp(16px, 2.2vw, 28px);
            min-width: 0;
        }
        .topbar {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            padding: 18px 22px;
            background: #e2e8f0;
            border: 1px solid rgba(0,0,0,0.08);
            border-radius: 24px;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 30;
        }
        .topbar h2 { color: #1e293b; }
        .topbar p { color: #475569; }
        .topbar .muted { color: #64748b; }
        .topbar-copy {
            min-width: 0;
            max-width: 60rem;
        }
        .topbar h2 { margin: 0; font-size: 1.85rem; line-height: 1.08; letter-spacing: -0.04em; }
        .topbar p { margin: 8px 0 0; max-width: 48rem; line-height: 1.6; }
        .user-meta { text-align: right; }
        .user-meta strong { display: block; color: #1e293b; }
        .topbar-actions {
            display: inline-flex;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
            margin-top: 8px;
        }
        .logout {
            display: inline-flex;
            padding: 10px 14px;
            border-radius: 999px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: white;
            font-weight: 800;
        }
        .content {
            margin-top: 22px;
            display: grid;
            gap: 22px;
            min-width: 0;
        }
        .card {
            background: var(--panel);
            border: 1px solid rgba(255,255,255,0.66);
            border-radius: 26px;
            box-shadow: var(--shadow);
            backdrop-filter: blur(14px);
            overflow: hidden;
        }
        .card-body { padding: clamp(16px, 2vw, 22px); }
        .card-body > h3,
        .card-body > h4 {
            margin: 0;
            letter-spacing: -0.02em;
        }
        .card-body > p {
            line-height: 1.65;
        }
        .section-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            flex-wrap: wrap;
            padding-bottom: 14px;
            margin-bottom: 18px;
            border-bottom: 1px solid rgba(24, 34, 45, 0.1);
        }
        .section-head h3 {
            margin: 0;
            font-size: 1.28rem;
            line-height: 1.2;
            letter-spacing: -0.03em;
        }
        .section-head:last-child {
            margin-bottom: 0;
        }
        .section-copy {
            margin: 6px 0 0;
            max-width: 74ch;
            color: var(--muted);
            line-height: 1.65;
        }
        .action-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }
        .stack {
            display: grid;
            gap: 16px;
        }
        .subcard,
        .content .card .card {
            background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(247, 243, 234, 0.82));
            border: 1px solid rgba(24, 34, 45, 0.12);
            box-shadow: 0 14px 34px rgba(20, 33, 49, 0.08);
        }
        .subcard .card-body,
        .content .card .card .card-body {
            padding: clamp(14px, 1.8vw, 20px);
        }
        .stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 16px; }
        .workflow-focus-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        .stat-link {
            display: block;
            color: inherit;
            min-width: 0;
        }
        .stat-link .stat {
            height: 100%;
            min-width: 0;
            transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
        }
        .stat-link:hover .stat {
            transform: translateY(-2px);
            border-color: rgba(100, 116, 139, 0.28);
            box-shadow: 0 18px 34px rgba(0, 0, 0, 0.08);
        }
        .stat {
            padding: 18px;
            border-radius: 22px;
            background: linear-gradient(180deg, rgba(255,255,255,0.82), rgba(255,255,255,0.56));
            border: 1px solid var(--line);
        }
        .stat > .muted:first-child {
            display: block;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--muted);
        }
        .stat strong {
            display: block;
            margin-top: 10px;
            font-size: 1.45rem;
            line-height: 1.08;
            letter-spacing: -0.04em;
        }
        .stat > .muted:not(:first-child) {
            font-size: 0.9rem;
            line-height: 1.55;
            color: var(--muted);
        }
        .stat > strong + .muted {
            margin-top: 8px;
        }
        .stat > .muted:last-child {
            margin-top: 10px;
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--accent-dark);
        }
        .muted { color: var(--muted); }
        .table-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            border: 1px solid rgba(24, 34, 45, 0.12);
            border-radius: 18px;
            background: rgba(255,255,255,0.62);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);
        }
        table {
            width: 100%;
            min-width: 720px;
            border-collapse: collapse;
        }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid var(--line); vertical-align: top; }
        th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--muted);
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 1;
            background: rgba(250, 246, 239, 0.95);
            backdrop-filter: blur(10px);
        }
        td {
            word-break: break-word;
        }
        .cell-nowrap {
            white-space: nowrap;
        }
        .cell-wrap-md {
            max-width: 220px;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        .cell-wrap-lg {
            max-width: 320px;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: anywhere;
        }
        tbody tr:nth-child(even) td {
            background: rgba(255,255,255,0.34);
        }
        tbody tr:hover td {
            background: rgba(100, 116, 139, 0.06);
        }
        .badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 10px;
            min-height: 36px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent-dark);
            font-size: 0.82rem;
            font-weight: 800;
            line-height: 1;
            white-space: nowrap;
        }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 14px 16px;
            align-items: end;
            padding: 16px;
            border: 1px solid rgba(24, 34, 45, 0.12);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,0.82), rgba(244, 238, 228, 0.72));
        }
        .field {
            display: grid;
            gap: 8px;
            min-width: 180px;
            flex: 1 1 220px;
            max-width: 320px;
        }
        .filter-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 16px;
            align-items: end;
            padding: 16px;
            border: 1px solid rgba(24, 34, 45, 0.12);
            border-radius: 20px;
            background: linear-gradient(180deg, rgba(255,255,255,0.82), rgba(244, 238, 228, 0.72));
        }
        .filter-grid label {
            display: grid;
            gap: 6px;
            min-width: 160px;
            flex: 1 1 200px;
            max-width: 280px;
            font-weight: 700;
            font-size: 0.84rem;
            color: var(--muted);
            line-height: 1.3;
        }
        .filter-grid > div {
            flex: 0 0 auto;
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        label { font-size: 0.84rem; color: var(--muted); font-weight: 700; line-height: 1.3; }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="search"],
        input[type="date"],
        input[type="datetime-local"],
        input[type="file"],
        select {
            width: 100%;
            border: 1px solid var(--line-strong);
            border-radius: 14px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.96);
            color: var(--ink);
            box-shadow: inset 0 1px 2px rgba(24, 34, 45, 0.04);
            min-height: 46px;
            line-height: 1.35;
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="number"],
        input[type="search"],
        select {
            appearance: none;
        }
        input[type="file"] {
            padding: 10px 12px;
            cursor: pointer;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus,
        input[type="number"]:focus,
        input[type="search"]:focus,
        input[type="date"]:focus,
        input[type="datetime-local"]:focus,
        input[type="file"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: rgba(100, 116, 139, 0.62);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(100, 116, 139, 0.12);
        }
        input[type="checkbox"],
        input[type="radio"] {
            width: 18px;
            height: 18px;
            accent-color: var(--accent);
            cursor: pointer;
        }
        button,
        a.button,
        .button {
            border: 0;
            border-radius: 14px;
            padding: 6px 12px;
            min-height: 36px;
            background: #e2e8f0;
            color: #475569;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            line-height: 1.1;
            box-shadow: none;
            transition: transform 0.16s ease, box-shadow 0.16s ease, filter 0.16s ease;
            white-space: nowrap;
            text-decoration: none;
            font-size: 0.8rem;
        }
        button:hover,
        a.button:hover,
        .button:hover {
            filter: brightness(0.96);
            transform: translateY(-1px);
        }
        button.secondary,
        a.button.secondary,
        .button.secondary {
            background: linear-gradient(180deg, rgba(255,255,255,0.92), rgba(240,236,228,0.88));
            color: var(--ink);
            box-shadow: 0 2px 8px rgba(18, 60, 85, 0.08);
            border: 1px solid rgba(24, 34, 45, 0.15);
        }
        button,
        a.button,
        .badge,
        .logout {
            -webkit-tap-highlight-color: transparent;
        }
        .pagination { display: flex; gap: 8px; flex-wrap: wrap; }
        .pagination-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .pagination-meta {
            color: var(--muted);
            font-size: 0.88rem;
        }
        .pagination a, .pagination span {
            min-width: 38px;
            min-height: 38px;
            padding: 8px 11px;
            border-radius: 10px;
            background: rgba(255,255,255,0.86);
            border: 1px solid var(--line);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            font-size: 0.9rem;
            font-weight: 700;
        }
        .pagination a:hover {
            border-color: rgba(100, 116, 139, 0.32);
            background: #ffffff;
        }
        .pagination .current {
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            border-color: transparent;
            color: #ffffff;
        }
        .pagination .disabled {
            opacity: 0.46;
        }
        .alert {
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(100, 116, 139, 0.12);
            color: #475569;
            border: 1px solid rgba(100, 116, 139, 0.18);
        }
        textarea {
            width: 100%;
            border: 1px solid var(--line-strong);
            border-radius: 14px;
            padding: 12px 14px;
            background: rgba(255,255,255,0.96);
            color: var(--ink);
            font: inherit;
            line-height: 1.45;
            box-shadow: inset 0 1px 2px rgba(24, 34, 45, 0.04);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }
        .table-wrap td {
            vertical-align: middle;
        }
        .table-wrap th.action-col,
        .table-wrap td.action-col {
            min-width: 340px;
            width: 340px;
            white-space: normal;
            word-break: normal;
        }
        .table-wrap td.action-col {
            vertical-align: top;
        }
        .table-wrap td.action-col .action-row {
            display: inline-flex;
            flex-wrap: nowrap;
            gap: 8px;
            min-width: max-content;
            align-items: center;
        }
        .table-wrap td.action-col form {
            flex-wrap: nowrap;
        }
        .table-wrap td form {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 0;
        }
        .table-wrap td .badge,
        .table-wrap td button {
            margin: 0;
        }
        .table-wrap td > div[style*="display:flex"] {
            align-items: center;
        }
        .empty-state {
            padding: 18px;
            border: 1px dashed rgba(24, 34, 45, 0.2);
            border-radius: 18px;
            background: rgba(255,255,255,0.45);
            color: var(--muted);
        }
        @media (max-width: 1200px) {
            .stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .workflow-focus-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 960px) {
            .shell { display: block; }
            .sidebar-overlay { display: block; }
            .sidebar {
                position: fixed;
                inset: 0 auto 0 0;
                width: min(86vw, 320px);
                min-height: 100dvh;
                max-height: 100dvh;
                border-right: 1px solid rgba(255,255,255,0.12);
                border-bottom: 0;
                padding: 20px 16px;
                transform: translateX(-108%);
                transition: transform 0.24s ease;
                box-shadow: 0 28px 60px rgba(18, 32, 46, 0.34);
            }
            body.nav-open .sidebar { transform: translateX(0); }
            body.nav-open .sidebar-overlay {
                opacity: 1;
                pointer-events: auto;
            }
            .sidebar-close,
            .mobile-nav-toggle { display: inline-flex; }
            .main { padding: 18px; }
            .topbar { flex-direction: column; align-items: flex-start; }
            .topbar-copy { max-width: none; }
            .user-meta { text-align: left; }
            .topbar-actions {
                justify-content: flex-start;
                width: 100%;
                flex-wrap: wrap;
            }
            .field {
                min-width: 0;
                max-width: none;
                flex-basis: calc(50% - 8px);
            }
            .nav-card a {
                padding: 12px;
            }
        }
        @media (max-width: 640px) {
            .stats { grid-template-columns: 1fr; }
            .workflow-focus-grid { grid-template-columns: 1fr; }
            .main { padding: 14px; }
            .topbar { padding: 16px; border-radius: 20px; }
            .topbar h2 { font-size: 1.45rem; }
            .topbar p { font-size: 0.95rem; }
            .section-head h3 { font-size: 1.12rem; }
            .stat strong { font-size: 1.28rem; }
            .card { border-radius: 22px; }
            .card-body { padding: 16px; }
            .field {
                flex-basis: 100%;
            }
            .toolbar,
            .filter-grid {
                gap: 12px;
                padding: 14px;
            }
            .filter-grid label {
                flex-basis: 100%;
                max-width: 100%;
            }
            .filter-grid > div {
                flex-basis: 100%;
                flex-wrap: wrap;
            }
            .topbar-actions,
            .pagination,
            .pagination-nav {
                width: 100%;
            }
            .table-wrap {
                border-radius: 16px;
            }
            table {
                min-width: 700px;
            }
            th, td {
                padding: 12px 10px;
            }
            .table-wrap th.action-col,
            .table-wrap td.action-col {
                min-width: 320px;
                width: 320px;
            }
            .pagination-nav {
                align-items: flex-start;
            }
            .section-head {
                margin-bottom: 16px;
                padding-bottom: 12px;
            }
        }
    </style>
</head>
<body>
@php
    $originContext = (string) request('back', request('source', ''));
    $queueContext = (string) (request()->route('queue') ?? ($originContext !== '' ? $originContext : request('queue', request('page', ''))));
    $activeQueue = \App\Support\AdminOrderQueues::match($queueContext);
    if ($activeQueue === null && request()->route('queue')) {
        $activeQueue = \App\Support\AdminOrderQueues::normalize((string) request()->route('queue'));
    }
    $reportContext = strtolower(trim($originContext));
    $activeCustomers = (
        request()->is('v/customer_list.php')
        || request()->is('v/customer-detail.php')
        || request()->is('v/edit-customer-detail.php')
    ) && $reportContext !== 'customer-approvals';
    $activeCustomerApprovals = request()->is('v/customer-approvals.php')
        || ((request()->is('v/customer-detail.php') || request()->is('v/edit-customer-detail.php')) && $reportContext === 'customer-approvals');
    $activeTeams = request()->is('v/show-all-teams.php')
        || request()->is('v/create-teams.php');
    $activeBusinessBilling = match (true) {
        request()->is('v/all-payment-due.php'),
        request()->is('v/payment-due-detail.php') && $reportContext === 'all-payment-due',
        $reportContext === 'all-payment-due' => 'due',
        request()->is('v/payment-recieved.php'),
        request()->is('v/payment-recieved-detail.php') && $reportContext === 'payment-recieved',
        $reportContext === 'payment-recieved' => 'received',
        default => null,
    };
    $activeBillingReport = match (true) {
        request()->is('v/payment-due-report.php'),
        request()->is('v/payment-due-detail.php') && $reportContext === 'payment-due-report',
        $reportContext === 'payment-due-report' => 'due',
        request()->is('v/payment-recieved-report.php'),
        request()->is('v/payment-recieved-detail.php') && $reportContext === 'payment-recieved-report',
        $reportContext === 'payment-recieved-report' => 'received',
        default => null,
    };
    $activeTeamReport = request()->is('v/monthly-reports.php');
    $activeLoginHistory = request()->is('v/login_history.php');
    $activeSecurityEvents = request()->is('v/security-events.php');
    $activeBlockedCustomers = request()->is('v/block-customer_list.php');
    $activeBlockedIps = request()->is('v/blocked-ip-list.php') || request()->is('v/block_ip.php');
    $activeChangePassword = request()->is('v/change-password.php');
    $orderQueueNav = \App\Support\AdminOrderQueues::navigation($navCounts ?? [], 'orders');
    $quoteQueueNav = \App\Support\AdminOrderQueues::navigation($navCounts ?? [], 'quotes');
@endphp
<div class="shell">
    <aside class="sidebar">
        <button type="button" class="sidebar-close" data-mobile-nav-close>Close Menu</button>
        <a href="{{ url('/v/welcome.php') }}" class="brand">
            <img src="{{ url($siteContext->logoPath()) }}" alt="{{ $siteContext->displayLabel() }}" class="brand-logo">
            <div class="brand-label">Admin Portal</div>
        </a>

        <div class="section-title">Order Management</div>
        <div class="nav-list">
            <div class="nav-card">
                <a href="{{ \App\Support\AdminOrderQueues::url('all-orders') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'all-orders' ? 'active' : '' }}"><span>All Orders</span><span class="count">{{ $navCounts['all_orders'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('new-orders') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'new-orders' ? 'active' : '' }}"><span>New Orders</span><span class="count">{{ $navCounts['new_orders'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('disapproved-orders') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'disapproved-orders' ? 'active' : '' }}"><span>Edit Requests</span><span class="count">{{ $navCounts['disapproved_orders'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('designer-orders') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'designer-orders' ? 'active' : '' }}"><span>Designer Orders</span><span class="count">{{ $navCounts['designer_orders'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('designer-completed') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'designer-completed' ? 'active' : '' }}"><span>Designer Complete</span><span class="count">{{ $navCounts['designer_completed_orders'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('approval-waiting') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'approval-waiting' ? 'active' : '' }}"><span>Vendor Complete</span><span class="count">{{ $navCounts['approval_waiting_orders'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('approved-orders') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'approved-orders' ? 'active' : '' }}"><span>Approved (Unpaid)</span><span class="count">{{ $navCounts['approved_orders'] ?? 0 }}</span></a>
                <a href="{{ url('/v/payment-recieved.php') }}" class="{{ $activeBusinessBilling === 'received' ? 'active' : '' }}"><span>Paid Orders</span><span class="count">{{ $navCounts['received_payments'] ?? 0 }}</span></a>
            </div>
        </div>

        <div class="section-title">Quotes</div>
        <div class="nav-list">
            <div class="nav-card">
                <a href="{{ \App\Support\AdminOrderQueues::url('new-quotes') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'new-quotes' ? 'active' : '' }}"><span>New Quotes</span><span class="count">{{ $navCounts['new_quotes'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('quote-negotiations') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'quote-negotiations' ? 'active' : '' }}"><span>Quote Negotiations</span><span class="count">{{ $navCounts['quote_negotiations'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('assigned-quotes') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'assigned-quotes' ? 'active' : '' }}"><span>Assigned Quotes</span><span class="count">{{ $navCounts['assigned_quotes'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('designer-completed-quotes') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'designer-completed-quotes' ? 'active' : '' }}"><span>Designer Complete</span><span class="count">{{ $navCounts['designer_completed_quotes'] ?? 0 }}</span></a>
                <a href="{{ \App\Support\AdminOrderQueues::url('completed-quotes') }}" class="{{ (request()->is('v/orders/*') || request()->is('v/orders.php')) && $activeQueue !== null && $activeQueue === 'completed-quotes' ? 'active' : '' }}"><span>Completed Quotes</span><span class="count">{{ $navCounts['completed_quotes'] ?? 0 }}</span></a>
            </div>
        </div>

        <div class="section-title">Business</div>
        <div class="nav-list">
            <div class="nav-card">
                <a href="{{ url('/v/customer_list.php') }}" class="{{ $activeCustomers ? 'active' : '' }}"><span>Active Customers</span><span class="count">{{ $navCounts['customers'] ?? 0 }}</span></a>
                <a href="{{ url('/v/customer-approvals.php') }}" class="{{ $activeCustomerApprovals ? 'active' : '' }}"><span>Pending Customers</span><span class="count">{{ $navCounts['pending_customer_approvals'] ?? 0 }}</span></a>
                <a href="{{ url('/v/block-customer_list.php') }}" class="{{ $activeBlockedCustomers ? 'active' : '' }}"><span>Inactive Customers</span><span class="count">{{ $navCounts['blocked_customers'] ?? 0 }}</span></a>
                <a href="{{ url('/v/show-all-teams.php') }}" class="{{ $activeTeams ? 'active' : '' }}"><span>Teams</span><span class="count">{{ $navCounts['teams'] ?? 0 }}</span></a>
            </div>
        </div>

        <div class="section-title">Reports</div>
        <div class="nav-list">
            <div class="nav-card">
                <a href="{{ url('/v/payment-due-report.php') }}" class="{{ $activeBillingReport === 'due' ? 'active' : '' }}"><span>Payment Due Report</span></a>
                <a href="{{ url('/v/payment-recieved-report.php') }}" class="{{ $activeBillingReport === 'received' ? 'active' : '' }}"><span>Payment Received Report</span></a>
                <a href="{{ url('/v/monthly-reports.php') }}" class="{{ $activeTeamReport ? 'active' : '' }}"><span>Team Report</span></a>
                <a href="{{ url('/v/login_history.php') }}" class="{{ $activeLoginHistory ? 'active' : '' }}"><span>Login History</span></a>
            </div>
        </div>

        <div class="section-title-toggle" data-toggle="security" id="security-toggle">Security</div>
        <div class="nav-list-collapsible" id="security-nav">
            <div class="nav-card">
                <a href="{{ url('/v/security-events.php') }}" class="{{ $activeSecurityEvents ? 'active' : '' }}"><span>Security Events</span><span class="count">{{ $navCounts['security_alerts'] ?? 0 }}</span></a>
                <a href="{{ url('/v/blocked-ip-list.php') }}" class="{{ $activeBlockedIps ? 'active' : '' }}"><span>Blocked IPs</span></a>
                <a href="{{ url('/v/change-password.php') }}" class="{{ $activeChangePassword ? 'active' : '' }}"><span>Change Password</span></a>
            </div>
        </div>

        <div class="section-title-toggle" data-toggle="extras" id="extras-toggle">Extras</div>
        <div class="nav-list-collapsible" id="extras-nav">
            <div class="nav-card">
                <a href="{{ url('/v/create-order.php') }}" class="{{ request()->is('v/create-order.php') ? 'active' : '' }}"><span>Create Order/Quote</span></a>
                <a href="{{ url('/v/notify-customers.php') }}" class="{{ request()->is('v/notify-customers.php') ? 'active' : '' }}"><span>Notify Customers</span></a>
                <a href="{{ url('/v/email-templates.php') }}" class="{{ request()->is('v/email-templates.php') || request()->is('v/email-templates-create.php') || request()->is('v/email-templates/*/edit') ? 'active' : '' }}"><span>Email Templates</span></a>
                <a href="{{ url('/v/site-payments.php') }}" class="{{ request()->is('v/site-payments.php') || request()->is('v/site-payments/*/edit') ? 'active' : '' }}"><span>Site Payments</span></a>
                <a href="{{ url('/v/site-pricing.php') }}" class="{{ request()->is('v/site-pricing.php') || request()->is('v/site-pricing-create.php') || request()->is('v/site-pricing/*/edit') ? 'active' : '' }}"><span>Site Pricing</span></a>
                <a href="{{ url('/v/site-offers.php') }}" class="{{ request()->is('v/site-offers.php') || request()->is('v/site-offers-create.php') || request()->is('v/site-offers/*/edit') ? 'active' : '' }}"><span>Site Offers</span></a>
                <a href="{{ url('/v/offer-claims.php') }}" class="{{ request()->is('v/offer-claims.php') || request()->is('v/site-offers/*/claims') ? 'active' : '' }}"><span>Offer Claims</span></a>
                <a href="{{ url('/v/transaction-history.php') }}" class="{{ request()->is('v/transaction-history.php') || request()->is('v/pay-now.php') ? 'active' : '' }}"><span>Transactions</span></a>
                <a href="{{ url('/v/customer-payment-inventory.php') }}" class="{{ request()->is('v/customer-payment-inventory.php') ? 'active' : '' }}"><span>Customer Payment Inventory</span></a>
            </div>
        </div>

        <div class="section-title">Admin</div>
        <div class="nav-list">
            <div class="nav-card">
                <a href="{{ url('/v/logout.php') }}"><span>Log Out</span></a>
            </div>
        </div>
    </aside>

    <main class="main">
        <section class="topbar">
            <div class="topbar-copy">
                <button type="button" class="mobile-nav-toggle" data-mobile-nav-toggle>Menu</button>
                <h2>@yield('page_heading', 'Admin Panel')</h2>
                <p>@yield('page_subheading', 'Review and manage daily admin operations.')</p>
            </div>
            <div class="user-meta">
                <strong>{{ $adminUser->display_name ?? $adminUser->user_name ?? 'Admin' }}</strong>
                <span class="muted">{{ $adminUser->user_name ?? '' }}</span>
                <div class="topbar-actions">
                    <a class="badge" href="{{ url('/v/welcome.php') }}">Dashboard</a>
                    @if (session('impersonator_admin_id'))
                        <form method="post" action="{{ url('/stop-simulated-session') }}">
                            @csrf
                            <button type="submit" class="badge" style="border:0;">Return To Admin</button>
                        </form>
                    @endif
                    <a class="logout" href="{{ url('/v/logout.php') }}">Log Out</a>
                </div>
            </div>
        </section>

        <section class="content">
            @if (session('impersonator_admin_id'))
                <div class="alert">You are currently inside a simulated admin session for {{ session('impersonation_target_name', $adminUser->display_name ?? $adminUser->user_name) }}.</div>
            @endif
            @if (session('success'))
                <div class="alert">{{ session('success') }}</div>
            @endif
            @yield('content')
        </section>
    </main>
</div>
@include('shared.file-preview-modal')
<div class="sidebar-overlay" data-mobile-nav-overlay></div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const body = document.body;
    const sidebar = document.querySelector('.sidebar');
    const toggle = document.querySelector('[data-mobile-nav-toggle]');
    const close = document.querySelector('[data-mobile-nav-close]');
    const overlay = document.querySelector('[data-mobile-nav-overlay]');

    const closeMobileNav = function () {
        body.classList.remove('nav-open');
    };

    if (toggle) {
        toggle.addEventListener('click', function () {
            body.classList.toggle('nav-open');
        });
    }

    if (close) {
        close.addEventListener('click', closeMobileNav);
    }

    if (overlay) {
        overlay.addEventListener('click', closeMobileNav);
    }

    if (sidebar) {
        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth <= 960) {
                    closeMobileNav();
                }
            });
        });
    }

    // Sidebar collapsible sections (Security, Extras)
    (function () {
        const toggles = document.querySelectorAll('[data-toggle]');
        toggles.forEach(function (toggle) {
            const targetId = toggle.getAttribute('data-toggle') + '-nav';
            const target = document.getElementById(targetId);
            if (!target) return;

            // Auto-open if any link inside is active
            const hasActive = target.querySelector('a.active') !== null;
            const storageKey = 'onedollar-admin-sidebar-' + toggle.getAttribute('data-toggle');
            const stored = window.sessionStorage.getItem(storageKey);

            if (stored === 'open' || (stored !== 'closed' && hasActive)) {
                toggle.classList.add('open');
                target.classList.add('open');
            }

            toggle.addEventListener('click', function () {
                const isOpen = toggle.classList.toggle('open');
                target.classList.toggle('open', isOpen);
                window.sessionStorage.setItem(storageKey, isOpen ? 'open' : 'closed');
            });
        });
    })();

    if (!sidebar || !window.sessionStorage) {
        return;
    }

    const storageKey = 'onedollar-admin-sidebar-scroll';
    const savedPosition = window.sessionStorage.getItem(storageKey);

    if (savedPosition !== null) {
        window.requestAnimationFrame(function () {
            sidebar.scrollTop = parseInt(savedPosition, 10) || 0;
        });
    }

    const persistSidebarScroll = function () {
        window.sessionStorage.setItem(storageKey, String(sidebar.scrollTop));
    };

    sidebar.addEventListener('scroll', persistSidebarScroll, { passive: true });
    window.addEventListener('beforeunload', persistSidebarScroll);
    document.querySelectorAll('a, form').forEach(function (element) {
        const eventName = element.tagName === 'FORM' ? 'submit' : 'click';
        element.addEventListener(eventName, persistSidebarScroll);
    });
});
</script>
@include('shared.file-preview-script')
</body>
</html>
