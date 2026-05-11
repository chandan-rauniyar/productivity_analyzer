{{-- ════════════════════════════════════════════════════════════
    resources/views/layouts/app.blade.php
    Master layout for all authenticated pages.
    Usage: @extends('layouts.app') @section('content') ... @endsection
    Props passed to section: $title, $active (nav highlight)
════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'PulseWork') — PulseWork</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <style>
        /* ── CSS custom properties for light/dark ── */
        :root {
            --bg-base:     #050c1a;
            --bg-card:     rgba(255,255,255,0.035);
            --bg-card-hov: rgba(255,255,255,0.06);
            --border:      rgba(255,255,255,0.07);
            --border-hov:  rgba(255,255,255,0.12);
            --text-pri:    #f1f5f9;
            --text-sec:    #94a3b8;
            --text-ter:    #475569;
            --accent:      #2563eb;
            --accent-hov:  #1d4ed8;
        }
        html[data-theme="light"] {
            --bg-base:     #f0f4f8;
            --bg-card:     rgba(255,255,255,0.9);
            --bg-card-hov: rgba(255,255,255,1);
            --border:      rgba(0,0,0,0.08);
            --border-hov:  rgba(0,0,0,0.14);
            --text-pri:    #0f172a;
            --text-sec:    #475569;
            --text-ter:    #94a3b8;
            --accent:      #2563eb;
            --accent-hov:  #1d4ed8;
        }

        * { box-sizing:border-box; margin:0; padding:0; }
        html { scroll-behavior:smooth; }
        body {
            font-family:'DM Sans',sans-serif;
            background:var(--bg-base);
            color:var(--text-pri);
            min-height:100vh;
            transition:background .3s, color .3s;
        }
        h1,h2,h3,h4,.display { font-family:'Syne',sans-serif; }
        .mono { font-family:'DM Mono',monospace; }

        /* ── Cards ── */
        .card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:16px;
            padding:24px;
            transition:background .2s,border-color .2s;
        }
        .card:hover { background:var(--bg-card-hov); }
        .card-plain { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:24px; }

        /* ── Inputs ── */
        .pw-input {
            width:100%;
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:10px;
            padding:10px 14px;
            color:var(--text-pri);
            font-size:14px;
            font-family:'DM Sans',sans-serif;
            outline:none;
            transition:border-color .15s,box-shadow .15s;
        }
        .pw-input:focus {
            border-color:rgba(96,165,250,0.5);
            box-shadow:0 0 0 3px rgba(96,165,250,0.08);
        }
        .pw-input::placeholder { color:var(--text-ter); }
        .pw-label {
            display:block;
            font-size:12px;
            font-weight:500;
            color:var(--text-sec);
            margin-bottom:6px;
            text-transform:uppercase;
            letter-spacing:.04em;
        }

        /* ── Theme toggle ── */
        .theme-toggle {
            display:flex;
            align-items:center;
            justify-content:center;
            width:34px;
            height:34px;
            border-radius:8px;
            background:var(--bg-card);
            border:1px solid var(--border);
            cursor:pointer;
            transition:all .15s;
            color:var(--text-sec);
        }
        .theme-toggle:hover { background:var(--bg-card-hov); color:var(--text-pri); }
        .theme-toggle svg { width:16px;height:16px; }
        /* Show sun in dark mode, moon in light mode */
        html[data-theme="dark"]  .icon-moon { display:none; }
        html[data-theme="light"] .icon-sun  { display:none; }

        /* ── Orb bg decorations (dark only) ── */
        html[data-theme="dark"] .pw-orb { display:block; }
        html[data-theme="light"] .pw-orb { display:none; }

        /* ── Page wrapper ── */
        .pw-page { max-width:1200px;margin:0 auto;padding:32px 24px; }

        /* ── Utility ── */
        .text-pri { color:var(--text-pri); }
        .text-sec { color:var(--text-sec); }
        .text-ter { color:var(--text-ter); }

        @keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
    </style>

    @stack('styles')
</head>
<body>

{{-- Background orbs (dark mode only) --}}
<div class="pw-orb" style="position:fixed;inset:0;pointer-events:none;overflow:hidden;z-index:0;" aria-hidden="true">
    <div style="position:absolute;top:-200px;right:-100px;width:500px;height:500px;background:rgba(37,99,235,0.08);border-radius:50%;filter:blur(80px);"></div>
    <div style="position:absolute;top:50%;left:-100px;width:400px;height:400px;background:rgba(99,102,241,0.06);border-radius:50%;filter:blur(80px);"></div>
    <div style="position:absolute;bottom:-100px;right:30%;width:350px;height:350px;background:rgba(52,211,153,0.05);border-radius:50%;filter:blur:80px;"></div>
</div>

<div style="position:relative;z-index:1;">

{{-- Navbar component --}}
<x-navbar :active="$active ?? 'dashboard'"/>

{{-- Theme toggle — floating in nav area (injected via JS into navbar right) --}}

{{-- Main content --}}
<main class="pw-page" id="pw-main">
    @yield('content')
</main>

</div>

{{-- Inject theme toggle into navbar --}}
<script>
(function() {
    // ── Persist theme ──────────────────────────────────────────
    const saved = localStorage.getItem('pw-theme') || 'dark';
    document.documentElement.setAttribute('data-theme', saved);

    // ── Inject toggle button into navbar right area ────────────
    function injectToggle() {
        const rightArea = document.querySelector('.pw-nav-inner > div:last-child');
        if (!rightArea || document.getElementById('pw-theme-toggle')) return;

        const btn = document.createElement('button');
        btn.id        = 'pw-theme-toggle';
        btn.className = 'theme-toggle';
        btn.setAttribute('aria-label', 'Toggle colour theme');
        btn.innerHTML = `
            <svg class="icon-sun" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
            </svg>
            <svg class="icon-moon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>`;

        btn.addEventListener('click', function() {
            const html  = document.documentElement;
            const theme = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', theme);
            localStorage.setItem('pw-theme', theme);
            // Update Chart.js defaults if charts exist
            if (window.Chart) {
                const isDark = theme === 'dark';
                Chart.defaults.color = isDark ? '#64748b' : '#6b7280';
            }
        });

        // Insert before the user chip
        const chip = document.getElementById('pw-user-chip');
        rightArea.insertBefore(btn, chip);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', injectToggle);
    } else {
        injectToggle();
    }
})();
</script>

@stack('scripts')

</body>
</html>