<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — PulseWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        html[data-theme="dark"]  body { background:#050c1a; color:#f1f5f9; }
        html[data-theme="light"] body { background:#f0f4f8; color:#0f172a; }
        body { font-family:'DM Sans',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; }
        h1 { font-family:'Syne',sans-serif; }
        .card { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:36px; width:100%; max-width:420px; }
        html[data-theme="light"] .card { background:white; border-color:rgba(0,0,0,0.08); box-shadow:0 4px 24px rgba(0,0,0,0.06); }
        .input { width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px 14px; color:inherit; font-size:14px; font-family:'DM Sans',sans-serif; outline:none; transition:border-color .15s; }
        html[data-theme="light"] .input { background:white; border-color:rgba(0,0,0,0.12); }
        .input:focus { border-color:rgba(96,165,250,0.5); box-shadow:0 0 0 3px rgba(96,165,250,0.08); }
        .input::placeholder { color:#475569; }
        .input-wrap { position:relative; }
        .eye-btn { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:transparent; border:none; cursor:pointer; color:#64748b; padding:0; display:flex; align-items:center; }
        label { display:block; font-size:12px; font-weight:500; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
        .btn { width:100%; background:#2563eb; color:white; padding:11px; border-radius:10px; font-size:14px; font-weight:500; border:none; cursor:pointer; transition:all .15s; font-family:'DM Sans',sans-serif; }
        .btn:hover { background:#1d4ed8; }
        .btn:disabled { opacity:.6; cursor:not-allowed; }
        .error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#fca5a5; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
        html[data-theme="light"] .error { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .verified-badge { display:flex; align-items:center; gap:8px; background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.25); border-radius:10px; padding:10px 14px; font-size:13px; color:#6ee7b7; margin-bottom:20px; }
        html[data-theme="light"] .verified-badge { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
        .strength-bar { height:3px; border-radius:2px; background:rgba(255,255,255,0.08); margin-top:8px; overflow:hidden; }
        html[data-theme="light"] .strength-bar { background:rgba(0,0,0,0.08); }
        .strength-fill { height:100%; border-radius:2px; transition:width .3s,background .3s; }
        .strength-label { font-size:11px; margin-top:4px; }
        .match-msg { font-size:12px; margin-top:5px; }
    </style>
</head>
<body>
<div class="card">

    {{-- Logo --}}
    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;margin-bottom:28px;">
        <div style="width:28px;height:28px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" fill="none" stroke="white" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
        </div>
        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;">PulseWork</span>
    </a>

    {{-- Verified badge --}}
    <div class="verified-badge">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
        </svg>
        Magic link verified — set your new password
    </div>

    <h1 style="font-size:22px;font-weight:800;margin-bottom:6px;">Set new password</h1>
    <p style="font-size:14px;color:#64748b;margin-bottom:24px;font-weight:300;">
        Resetting password for <strong style="font-weight:500;color:inherit;">{{ $email }}</strong>
    </p>

    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.link-reset') }}" id="reset-form">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="token" value="{{ $token }}">

        <div style="display:grid;gap:16px;margin-bottom:20px;">

            {{-- New password --}}
            <div>
                <label for="password">New password</label>
                <div class="input-wrap">
                    <input class="input" type="password" name="password" id="password"
                           required autofocus placeholder="Min 8 characters"
                           style="padding-right:40px;"
                           oninput="checkStrength(this.value)">
                    <button type="button" class="eye-btn" onclick="toggleEye('password')" aria-label="Show/hide password">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <div class="strength-bar">
                    <div class="strength-fill" id="strength-fill" style="width:0%;background:#ef4444;"></div>
                </div>
                <p class="strength-label" id="strength-label" style="color:#64748b;">Enter a password</p>
            </div>

            {{-- Confirm password --}}
            <div>
                <label for="password_confirmation">Confirm new password</label>
                <div class="input-wrap">
                    <input class="input" type="password" name="password_confirmation"
                           id="password_confirmation" required placeholder="Repeat your password"
                           style="padding-right:40px;"
                           oninput="checkMatch()">
                    <button type="button" class="eye-btn" onclick="toggleEye('password_confirmation')" aria-label="Show/hide confirm password">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
                <p class="match-msg" id="match-msg" style="color:#64748b;"></p>
            </div>
        </div>

        <button type="submit" class="btn" id="submit-btn">Reset password</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:13px;color:#64748b;">
        <a href="{{ route('login') }}" style="color:#64748b;text-decoration:none;">← Back to sign in</a>
    </p>
</div>

<script>
document.documentElement.setAttribute('data-theme', localStorage.getItem('pw-theme') || 'dark');

function toggleEye(id) {
    const f = document.getElementById(id);
    f.type = f.type === 'password' ? 'text' : 'password';
}

function checkStrength(val) {
    const met = [val.length>=8, /[A-Z]/.test(val), /[a-z]/.test(val), /[0-9]/.test(val)].filter(Boolean).length;
    const levels = [
        {w:'0%',  bg:'#ef4444', text:'Enter a password',  c:'#64748b'},
        {w:'25%', bg:'#ef4444', text:'Too weak',          c:'#f87171'},
        {w:'50%', bg:'#f59e0b', text:'Could be stronger', c:'#fbbf24'},
        {w:'75%', bg:'#3b82f6', text:'Good password',     c:'#60a5fa'},
        {w:'100%',bg:'#10b981', text:'Strong password',   c:'#34d399'},
    ];
    const l = levels[met];
    document.getElementById('strength-fill').style.cssText = `width:${l.w};background:${l.bg};`;
    const lbl = document.getElementById('strength-label');
    lbl.textContent = l.text; lbl.style.color = l.c;
    checkMatch();
}

function checkMatch() {
    const pw = document.getElementById('password').value;
    const cf = document.getElementById('password_confirmation').value;
    const msg = document.getElementById('match-msg');
    const btn = document.getElementById('submit-btn');
    if (!cf) { msg.textContent=''; return; }
    if (pw === cf) { msg.textContent='✓ Passwords match'; msg.style.color='#34d399'; btn.disabled=false; }
    else           { msg.textContent='✗ Passwords do not match'; msg.style.color='#f87171'; btn.disabled=true; }
}

document.getElementById('reset-form').addEventListener('submit', function() {
    const btn = document.getElementById('submit-btn');
    btn.disabled=true; btn.textContent='Resetting…';
});
</script>
</body>
</html>