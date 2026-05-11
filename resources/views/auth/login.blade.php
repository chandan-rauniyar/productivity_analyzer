<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in — PulseWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        html[data-theme="dark"]  body { background:#050c1a; color:#f1f5f9; }
        html[data-theme="light"] body { background:#f0f4f8; color:#0f172a; }
        body { font-family:'DM Sans',sans-serif; min-height:100vh; display:flex; transition:background .3s; }
        h1,h2 { font-family:'Syne',sans-serif; }

        /* ── Left panel ── */
        .left-panel {
            flex:1; display:flex; flex-direction:column; justify-content:center;
            padding:60px; position:relative; overflow:hidden;
        }
        html[data-theme="dark"]  .left-panel { background:linear-gradient(135deg,#0a1628,#0f1e38); }
        html[data-theme="light"] .left-panel { background:linear-gradient(135deg,#1e3a8a,#3730a3); }

        /* ── Right form panel ── */
        .right-panel {
            width:100%; max-width:480px; display:flex; flex-direction:column;
            justify-content:center; padding:40px 36px;
        }
        html[data-theme="dark"]  .right-panel { background:#050c1a; }
        html[data-theme="light"] .right-panel { background:#ffffff; }

        /* ── Inputs ── */
        .input {
            width:100%; border-radius:10px; padding:11px 14px;
            font-size:14px; font-family:'DM Sans',sans-serif;
            outline:none; transition:border-color .15s, box-shadow .15s;
        }
        html[data-theme="dark"]  .input { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); color:#f1f5f9; }
        html[data-theme="light"] .input { background:#f8fafc; border:1px solid rgba(0,0,0,0.12); color:#0f172a; }
        .input:focus { border-color:rgba(96,165,250,0.6); box-shadow:0 0 0 3px rgba(96,165,250,0.1); }
        .input::placeholder { color:#64748b; }

        label {
            display:block; font-size:11px; font-weight:600; letter-spacing:.06em;
            text-transform:uppercase; margin-bottom:6px;
        }
        html[data-theme="dark"]  label { color:#64748b; }
        html[data-theme="light"] label { color:#475569; }

        /* ── Buttons ── */
        .btn-primary {
            width:100%; background:#2563eb; color:white; padding:12px;
            border-radius:10px; font-size:14px; font-weight:500; border:none;
            cursor:pointer; transition:all .15s; font-family:'DM Sans',sans-serif;
            display:flex; align-items:center; justify-content:center; gap:8px;
        }
        .btn-primary:hover { background:#1d4ed8; transform:translateY(-1px); box-shadow:0 4px 16px rgba(37,99,235,0.35); }

        .btn-ms {
            width:100%; display:flex; align-items:center; justify-content:center;
            gap:10px; border-radius:10px; padding:12px; font-size:14px;
            font-weight:500; cursor:pointer; transition:all .15s; text-decoration:none;
            font-family:'DM Sans',sans-serif;
        }
        html[data-theme="dark"]  .btn-ms { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); color:#e2e8f0; }
        html[data-theme="light"] .btn-ms { background:white; border:1px solid rgba(0,0,0,0.12); color:#0f172a; box-shadow:0 1px 4px rgba(0,0,0,0.06); }
        .btn-ms:hover { transform:translateY(-1px); }
        html[data-theme="dark"]  .btn-ms:hover { background:rgba(255,255,255,0.09); border-color:rgba(255,255,255,0.2); }
        html[data-theme="light"] .btn-ms:hover { background:#f8fafc; box-shadow:0 2px 8px rgba(0,0,0,0.1); }

        /* ── Divider ── */
        .divider { display:flex; align-items:center; gap:12px; font-size:12px; margin:18px 0; }
        html[data-theme="dark"]  .divider { color:#334155; }
        html[data-theme="light"] .divider { color:#cbd5e1; }
        .divider::before,.divider::after { content:''; flex:1; height:1px; }
        html[data-theme="dark"]  .divider::before, html[data-theme="dark"]  .divider::after { background:rgba(255,255,255,0.07); }
        html[data-theme="light"] .divider::before, html[data-theme="light"] .divider::after { background:rgba(0,0,0,0.08); }

        /* ── Alerts ── */
        .alert-success { background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.25); color:#6ee7b7; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:20px; display:flex; align-items:center; gap:8px; }
        .alert-error   { background:rgba(239,68,68,0.1);  border:1px solid rgba(239,68,68,0.25);  color:#fca5a5; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:20px; }
        html[data-theme="light"] .alert-success { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
        html[data-theme="light"] .alert-error   { background:#fef2f2; border-color:#fecaca; color:#991b1b; }

        /* ── Remember checkbox ── */
        .remember-row { display:flex; align-items:center; gap:8px; margin-bottom:20px; cursor:pointer; }
        .remember-row input { width:15px; height:15px; accent-color:#2563eb; cursor:pointer; }
        .remember-row span { font-size:13px; }
        html[data-theme="dark"]  .remember-row span { color:#94a3b8; }
        html[data-theme="light"] .remember-row span { color:#475569; }

        /* ── Trust badges ── */
        .trust-item { display:flex; align-items:center; gap:10px; }
        .trust-dot { width:18px; height:18px; background:rgba(52,211,153,0.15); border:1px solid rgba(52,211,153,0.3); border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }

        /* ── Recommended pill ── */
        .rec-pill { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:500; background:rgba(37,99,235,0.12); border:1px solid rgba(37,99,235,0.25); color:#93c5fd; border-radius:100px; padding:2px 8px; margin-bottom:12px; }
        html[data-theme="light"] .rec-pill { background:#eff6ff; border-color:#bfdbfe; color:#1e40af; }

        /* ── Input wrapper for eye toggle ── */
        .input-wrap { position:relative; }
        .eye-btn { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:transparent; border:none; cursor:pointer; color:#64748b; padding:0; display:flex; align-items:center; }
        .eye-btn:hover { color:#94a3b8; }
    </style>
</head>
<body>

<script>
// Apply saved theme immediately to prevent flash
document.documentElement.setAttribute('data-theme', localStorage.getItem('pw-theme') || 'dark');
</script>

{{-- ── LEFT DECORATIVE PANEL ──────────────────────────────── --}}
<div class="left-panel hidden lg:flex">
    <div style="position:absolute;top:-100px;right:-100px;width:400px;height:400px;background:rgba(37,99,235,0.12);border-radius:50%;filter:blur(80px);pointer-events:none;"></div>
    <div style="position:absolute;bottom:-60px;left:-60px;width:300px;height:300px;background:rgba(99,102,241,0.08);border-radius:50%;filter:blur(60px);pointer-events:none;"></div>

    {{-- Logo --}}
    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;margin-bottom:64px;position:relative;">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:9px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(37,99,235,0.4);">
            <svg width="19" height="19" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:20px;color:white;">PulseWork</span>
    </a>

    <h2 style="font-size:clamp(26px,3vw,36px);font-weight:800;line-height:1.15;color:white;margin-bottom:16px;position:relative;">
        Your productivity<br>data, finally clear.
    </h2>
    <p style="font-size:15px;color:rgba(255,255,255,0.5);line-height:1.75;max-width:340px;font-weight:300;position:relative;">
        Connect Outlook, sync once, and see your email patterns, meeting load, focus time, and burnout risk in one dashboard.
    </p>

    <div style="margin-top:40px;display:flex;flex-direction:column;gap:14px;position:relative;">
        @foreach(['Email content is never read or stored','Tokens encrypted at rest in the database','Disconnect your account anytime'] as $t)
        <div class="trust-item">
            <div class="trust-dot">
                <svg width="9" height="9" fill="#34d399" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            </div>
            <span style="font-size:13px;color:rgba(255,255,255,0.55);">{{ $t }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ── RIGHT FORM PANEL ────────────────────────────────────── --}}
<div class="right-panel">

    {{-- Mobile logo --}}
    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;margin-bottom:36px;" class="lg:hidden">
        <div style="width:28px;height:28px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;">PulseWork</span>
    </a>

    <h1 style="font-size:24px;font-weight:800;margin-bottom:6px;">Welcome back</h1>
    <p style="font-size:14px;color:#64748b;margin-bottom:28px;font-weight:300;">Sign in to your account to continue.</p>

    {{-- Flash messages --}}
    @if(session('status'))
        <div class="alert-success">
            <svg width="15" height="15" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            {{ session('status') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Microsoft login button --}}
    <div style="margin-bottom:4px;">
        <div class="rec-pill">
            <svg width="8" height="8" fill="#60a5fa" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
            Recommended
        </div>
        <a href="{{ route('auth.microsoft') }}" class="btn-ms">
            <svg width="18" height="18" viewBox="0 0 21 21">
                <rect x="1"  y="1"  width="9" height="9" fill="#F25022"/>
                <rect x="11" y="1"  width="9" height="9" fill="#7FBA00"/>
                <rect x="1"  y="11" width="9" height="9" fill="#00A4EF"/>
                <rect x="11" y="11" width="9" height="9" fill="#FFB900"/>
            </svg>
            Continue with Microsoft
        </a>
    </div>
    <p style="font-size:11px;color:#334155;text-align:center;margin-bottom:4px;">Connects Outlook automatically</p>

    <div class="divider">or sign in with email</div>

    {{-- Email / password form --}}
    <form method="POST" action="{{ route('login.store') }}">
        @csrf
        <div style="display:grid;gap:14px;margin-bottom:4px;">
            <div>
                <label>Email address</label>
                <input class="input" type="email" name="email"
                       value="{{ old('email') }}" required autofocus
                       placeholder="you@example.com">
            </div>
            <div>
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                    <label style="margin-bottom:0;">Password</label>
                    <a href="{{ route('password.request') }}"
                       style="font-size:12px;color:#60a5fa;text-decoration:none;font-weight:400;">
                        Forgot password?
                    </a>
                </div>
                <div class="input-wrap">
                    <input class="input" type="password" name="password" id="pw-field"
                           required placeholder="••••••••" style="padding-right:40px;">
                    <button type="button" class="eye-btn" onclick="togglePw()" aria-label="Show/hide password">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <label class="remember-row" style="margin-top:12px;">
            <input type="checkbox" name="remember">
            <span>Remember me for 30 days</span>
        </label>

        <button type="submit" class="btn-primary">
            Sign in
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </button>
    </form>

    <p style="text-align:center;margin-top:24px;font-size:13px;color:#64748b;">
        Don't have an account?
        <a href="{{ route('register') }}" style="color:#60a5fa;text-decoration:none;font-weight:500;">Create one free</a>
    </p>
</div>

<script>
function togglePw() {
    const f = document.getElementById('pw-field');
    f.type = f.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>