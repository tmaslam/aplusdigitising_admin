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
    <title>APlus Team Portal</title>
    <style>
        :root {
            --bg: #f4efe6;
            --panel: rgba(255,255,255,0.88);
            --ink: #15212c;
            --muted: #66717d;
            --line: rgba(21, 33, 44, 0.2);
            --accent: #1d6d5f;
            --accent-dark: #11433a;
            --shadow: 0 24px 60px rgba(21, 33, 44, 0.14);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: "Avenir Next", "Segoe UI", sans-serif;
            overflow-x: hidden;
            background:
                radial-gradient(circle at top left, rgba(29,109,95,0.12), transparent 32%),
                radial-gradient(circle at bottom right, rgba(181,112,57,0.1), transparent 26%),
                linear-gradient(180deg, #fbf8f2 0%, #eee4d6 100%);
            color: var(--ink);
        }
        .panel {
            width: min(460px, 100%);
            padding: clamp(22px, 3vw, 28px);
            border-radius: 28px;
            background: var(--panel);
            border: 1px solid rgba(255,255,255,0.7);
            box-shadow: var(--shadow);
        }
        h1 { margin: 0; font-size: 2rem; line-height: 0.96; letter-spacing: -0.05em; }
        p { color: var(--muted); line-height: 1.6; }
        .field { display: grid; gap: 8px; margin-top: 14px; }
        label { font-size: 0.84rem; font-weight: 700; color: var(--muted); }
        input {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 13px 14px;
            background: rgba(255,255,255,0.96);
            color: var(--ink);
            font: inherit;
        }
        input:focus {
            outline: none;
            border-color: rgba(29,109,95,0.58);
            box-shadow: 0 0 0 4px rgba(29,109,95,0.12);
        }
        button {
            width: 100%;
            margin-top: 18px;
            border: 0;
            border-radius: 14px;
            padding: 13px 16px;
            background: linear-gradient(135deg, var(--accent), var(--accent-dark));
            color: #fff;
            font-weight: 800;
            font: inherit;
            cursor: pointer;
        }
        .alert {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(181,112,57,0.12);
            border: 1px solid rgba(181,112,57,0.2);
            color: #8d5625;
        }
        @media (max-width: 640px) {
            body { padding: 16px; }
            .panel { border-radius: 22px; padding: 20px; }
            h1 { font-size: 1.75rem; }
        }
        .theme-toggle {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 50;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.06);
            background: rgba(0,0,0,0.04);
            color: #475569;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease;
        }
        .theme-toggle:hover {
            background: rgba(0,0,0,0.08);
        }
        [data-theme="dark"] {
            color-scheme: dark;
            --bg: #0B1120;
            --panel: #111827;
            --ink: #F1F5F9;
            --muted: #94A3B8;
            --line: rgba(255,255,255,0.08);
            --accent: #2dd4bf;
            --accent-dark: #14b8a6;
            --shadow: 0 24px 60px rgba(0,0,0,0.45);
        }
        [data-theme="dark"] body {
            background:
                radial-gradient(circle at top left, rgba(45,212,191,0.12), transparent 32%),
                radial-gradient(circle at bottom right, rgba(20,184,166,0.1), transparent 26%),
                linear-gradient(180deg, #0B1120 0%, #111827 100%);
            color: var(--ink);
        }
        [data-theme="dark"] .panel {
            background: var(--panel);
            border-color: rgba(51,65,85,0.5);
        }
        [data-theme="dark"] input {
            background: #1E293B;
            color: var(--ink);
            border-color: var(--line);
        }
        [data-theme="dark"] input:focus {
            border-color: rgba(45,212,191,0.58);
            box-shadow: 0 0 0 4px rgba(45,212,191,0.12);
        }
        [data-theme="dark"] .alert {
            background: rgba(45,212,191,0.12);
            border-color: rgba(45,212,191,0.2);
            color: #2dd4bf;
        }
        [data-theme="dark"] .theme-toggle {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.08);
            color: #94A3B8;
        }
        [data-theme="dark"] .theme-toggle:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
        <span id="themeIcon">🌙</span>
    </button>
    <form class="panel" method="post" action="{{ url('/team/login') }}">
        @csrf
        <img src="{{ url($siteContext->logoPath()) }}" alt="{{ $siteContext->displayLabel() }}" style="max-width: 180px; width: 100%; height: auto; margin-bottom: 10px; display: block;">
        <h1 style="font-size: 1.6rem; margin: 0;">Team Portal</h1>
        <p>Authorized users only.</p>

        @if (session('success'))
            <div class="alert">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert">{{ $errors->first() }}</div>
        @endif

        <div class="field">
            <label for="txtLogin">User Name</label>
            <input id="txtLogin" name="txtLogin" type="text" value="{{ old('txtLogin') }}" autofocus>
        </div>

        <div class="field">
            <label for="txtPassword">Password</label>
            <input id="txtPassword" name="txtPassword" type="password">
        </div>

        @include('shared.turnstile')
        <button type="submit">Sign In</button>
    </form>
    <script>
    (function() {
        var themeBtn = document.getElementById('themeToggle');
        var themeIcon = document.getElementById('themeIcon');
        function updateThemeUI() {
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (themeIcon) themeIcon.textContent = isDark ? '☀️' : '🌙';
        }
        if (themeBtn) {
            themeBtn.addEventListener('click', function () {
                var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                var next = isDark ? 'light' : 'dark';
                document.documentElement.setAttribute('data-theme', next);
                localStorage.setItem('admin-theme', next);
                updateThemeUI();
            });
        }
        updateThemeUI();
    })();
    </script>
</body>
</html>
