<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create account — PulseWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        html[data-theme="dark"]  body { background:#050c1a; color:#f1f5f9; }
        html[data-theme="light"] body { background:#f0f4f8; color:#0f172a; }
        body { font-family:'DM Sans',sans-serif; min-height:100vh; display:flex; transition:background .3s; }
        h1,h2 { font-family:'Syne',sans-serif; }

        .left-panel {
            flex:1; display:flex; flex-direction:column; justify-content:center;
            padding:60px; position:relative; overflow:hidden;
        }
        html[data-theme="dark"]  .left-panel { background:linear-gradient(160deg,#0a1628,#0c1a32); }
        html[data-theme="light"] .left-panel { background:linear-gradient(160deg,#1e3a8a,#4338ca); }

        .right-panel {
            width:100%; max-width:480px; display:flex; flex-direction:column;
            justify-content:center; padding:40px 36px;
        }
        html[data-theme="dark"]  .right-panel { background:#050c1a; }
        html[data-theme="light"] .right-panel { background:#ffffff; }

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

        .divider { display:flex; align-items:center; gap:12px; font-size:12px; margin:16px 0; }
        html[data-theme="dark"]  .divider { color:#334155; }
        html[data-theme="light"] .divider { color:#cbd5e1; }
        .divider::before,.divider::after { content:''; flex:1; height:1px; }
        html[data-theme="dark"]  .divider::before, html[data-theme="dark"]  .divider::after { background:rgba(255,255,255,0.07); }
        html[data-theme="light"] .divider::before, html[data-theme="light"] .divider::after { background:rgba(0,0,0,0.08); }

        .alert-error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#fca5a5; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
        html[data-theme="light"] .alert-error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }

        .hint { font-size:11px; color:#475569; margin-top:4px; }

        .rec-pill { display:inline-flex; align-items:center; gap:4px; font-size:10px; font-weight:500; background:rgba(37,99,235,0.12); border:1px solid rgba(37,99,235,0.25); color:#93c5fd; border-radius:100px; padding:2px 8px; margin-bottom:10px; }
        html[data-theme="light"] .rec-pill { background:#eff6ff; border-color:#bfdbfe; color:#1e40af; }

        .input-wrap { position:relative; }
        .eye-btn { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:transparent; border:none; cursor:pointer; color:#64748b; padding:0; display:flex; align-items:center; }
        .eye-btn:hover { color:#94a3b8; }

        /* Password strength */
        .strength-bar  { height:3px; border-radius:2px; margin-top:6px; overflow:hidden; }
        html[data-theme="dark"]  .strength-bar { background:rgba(255,255,255,0.07); }
        html[data-theme="light"] .strength-bar { background:rgba(0,0,0,0.08); }
        .strength-fill { height:100%; border-radius:2px; transition:width .3s,background .3s; width:0%; }
    </style>
</head>
<body>

<script>
document.documentElement.setAttribute('data-theme', localStorage.getItem('pw-theme') || 'dark');
</script>

{{-- ── LEFT PANEL ──────────────────────────────────────────── --}}
<div class="left-panel hidden lg:flex">
    <div style="position:absolute;bottom:-80px;left:-80px;width:350px;height:350px;background:rgba(99,102,241,0.08);border-radius:50%;filter:blur(60px);pointer-events:none;"></div>
    <div style="position:absolute;top:-60px;right:-60px;width:280px;height:280px;background:rgba(37,99,235,0.08);border-radius:50%;filter:blur(60px);pointer-events:none;"></div>

    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;margin-bottom:64px;position:relative;">
        <div style="width:36px;height:36px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:9px;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 16px rgba(37,99,235,0.4);">
            <svg width="19" height="19" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:20px;color:white;">PulseWork</span>
    </a>

    <h2 style="font-size:clamp(26px,3vw,34px);font-weight:800;line-height:1.2;color:white;margin-bottom:16px;position:relative;">
        Start understanding<br>your work habits.
    </h2>
    <p style="font-size:14px;color:rgba(255,255,255,0.45);line-height:1.75;max-width:320px;font-weight:300;position:relative;">
        Join professionals using PulseWork to track focus time, reduce meeting overload, and prevent burnout.
    </p>

    <div style="margin-top:40px;display:flex;flex-direction:column;gap:14px;position:relative;">
        @foreach([
            'Free to start — no credit card needed',
            'Set up in under 60 seconds',
            'Works with personal and work Microsoft accounts',
            'Your email content is never read',
        ] as $f)
        <div style="display:flex;align-items:center;gap:10px;">
            <svg width="16" height="16" fill="#34d399" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
            <span style="font-size:13px;color:rgba(255,255,255,0.55);">{{ $f }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ── RIGHT FORM PANEL ────────────────────────────────────── --}}
<div class="right-panel">

    {{-- Mobile logo --}}
    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;margin-bottom:32px;" class="lg:hidden">
        <div style="width:28px;height:28px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;">PulseWork</span>
    </a>

    <h1 style="font-size:24px;font-weight:800;margin-bottom:4px;">Create your account</h1>
    <p style="font-size:14px;color:#64748b;margin-bottom:24px;font-weight:300;">Free forever. No credit card required.</p>

    @if($errors->any())
        <div class="alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Microsoft sign up --}}
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
            Sign up with Microsoft
        </a>
    </div>
    <p style="font-size:11px;color:#334155;text-align:center;margin-bottom:4px;">Connects Outlook automatically</p>

    <div class="divider">or register with email</div>

    <form method="POST" action="{{ route('register.store') }}">
        @csrf
        <div style="display:grid;gap:13px;margin-bottom:16px;">
            <div>
                <label>Full name</label>
                <input class="input" type="text" name="name"
                       value="{{ old('name') }}" required autofocus
                       placeholder="Your full name">
            </div>
            <div>
                <label>Email address</label>
                <input class="input" type="email" name="email"
                       value="{{ old('email') }}" required
                       placeholder="you@example.com">
            </div>
            <div>
                <label>Password</label>
                <div class="input-wrap">
                    <input class="input" type="password" name="password" id="pw1"
                           required placeholder="Min 8 characters"
                           style="padding-right:40px;"
                           oninput="checkStrength(this.value)">
                    <button type="button" class="eye-btn" onclick="toggleEye('pw1')" aria-label="Show/hide password">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <div class="strength-bar"><div class="strength-fill" id="str-fill"></div></div>
                <p class="hint" id="str-label">Use uppercase, lowercase, and numbers</p>
            </div>
            <div>
                <label>Confirm password</label>
                <div class="input-wrap">
                    <input class="input" type="password" name="password_confirmation" id="pw2"
                           required placeholder="Repeat your password"
                           style="padding-right:40px;"
                           oninput="checkMatch()">
                    <button type="button" class="eye-btn" onclick="toggleEye('pw2')" aria-label="Show/hide confirm password">
                        <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                </div>
                <p class="hint" id="match-msg"></p>
            </div>
        </div>

        <button type="submit" class="btn-primary">
            Create account
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:13px;color:#64748b;">
        Already have an account?
        <a href="{{ route('login') }}" style="color:#60a5fa;text-decoration:none;font-weight:500;">Sign in</a>
    </p>
</div>

<script>
function toggleEye(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
}

function checkStrength(val) {
    const met = [val.length>=8,/[A-Z]/.test(val),/[a-z]/.test(val),/[0-9]/.test(val)].filter(Boolean).length;
    const lvls = [
        {w:'0%',  bg:'#ef4444', t:'Use uppercase, lowercase, and numbers'},
        {w:'25%', bg:'#ef4444', t:'Too weak — add more variety'},
        {w:'50%', bg:'#f59e0b', t:'Getting there — add numbers or uppercase'},
        {w:'75%', bg:'#3b82f6', t:'Good password'},
        {w:'100%',bg:'#10b981', t:'Strong password ✓'},
    ];
    const l = lvls[met];
    document.getElementById('str-fill').style.cssText = `width:${l.w};background:${l.bg};`;
    document.getElementById('str-label').textContent = l.t;
    checkMatch();
}

function checkMatch() {
    const pw  = document.getElementById('pw1').value;
    const cf  = document.getElementById('pw2').value;
    const msg = document.getElementById('match-msg');
    if (!cf) { msg.textContent = ''; return; }
    msg.textContent  = pw === cf ? '✓ Passwords match' : '✗ Passwords do not match';
    msg.style.color  = pw === cf ? '#34d399' : '#f87171';
}
</script>

</body>
</html>