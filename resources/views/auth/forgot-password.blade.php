<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — PulseWork</title>
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
        .input { width:100%; background:rgba(255,255,255,0.05); border:1.5px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px 14px; color:inherit; font-size:14px; font-family:'DM Sans',sans-serif; outline:none; transition:border-color .15s; }
        html[data-theme="light"] .input { background:white; border-color:rgba(0,0,0,0.12); }
        .input:focus { border-color:rgba(96,165,250,0.5); box-shadow:0 0 0 3px rgba(96,165,250,0.08); }
        .input::placeholder { color:#475569; }
        label { display:block; font-size:12px; font-weight:500; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
        .flash { background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.25); color:#6ee7b7; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:20px; }
        html[data-theme="light"] .flash { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
        .error-box { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#fca5a5; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
        html[data-theme="light"] .error-box { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
        .email-err { font-size:12px; color:#f87171; margin-top:5px; display:none; }
        .method-btn {
            width:100%; display:flex; align-items:center; gap:14px;
            padding:14px 16px; border-radius:12px; border:1.5px solid rgba(255,255,255,0.1);
            background:rgba(255,255,255,0.03); cursor:pointer;
            transition:all .2s; text-align:left; font-family:'DM Sans',sans-serif;
            color:inherit; margin-bottom:10px;
        }
        html[data-theme="light"] .method-btn { background:white; border-color:rgba(0,0,0,0.1); }
        .method-btn:hover:not([disabled]) { border-color:rgba(96,165,250,0.5); background:rgba(37,99,235,0.05); transform:translateY(-1px); }
        .method-btn[disabled] { opacity:.5; cursor:not-allowed; transform:none !important; }
        .method-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .method-title { font-size:14px; font-weight:500; margin-bottom:2px; }
        .method-desc  { font-size:12px; color:#64748b; line-height:1.4; }
        .method-arrow { margin-left:auto; color:#64748b; flex-shrink:0; transition:transform .15s; }
        .method-btn:hover:not([disabled]) .method-arrow { transform:translateX(3px); }
    </style>
</head>
<body>

<script>
document.documentElement.setAttribute('data-theme', localStorage.getItem('pw-theme') || 'dark');
</script>

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

    <h1 style="font-size:22px;font-weight:800;margin-bottom:6px;">Forgot password?</h1>
    <p style="font-size:14px;color:#64748b;margin-bottom:24px;font-weight:300;">
        Enter your email, then choose how to reset your password.
    </p>

    @if(session('status'))
        <div class="flash">✓ {{ session('status') }}</div>
    @endif

    @if($errors->any())
        <div class="error-box">{{ $errors->first() }}</div>
    @endif

    {{-- Shared email input (not inside any form) --}}
    <div style="margin-bottom:20px;">
        <label for="shared-email">Email address</label>
        <input class="input" type="email" id="shared-email"
               value="{{ old('email') }}"
               placeholder="you@example.com"
               autocomplete="email">
        <p class="email-err" id="email-err">Please enter a valid email address.</p>
    </div>

    {{-- OTP hidden form --}}
    <form method="POST" action="{{ route('password.email') }}" id="form-otp">
        @csrf
        <input type="hidden" name="method" value="otp">
        <input type="hidden" name="email"  id="otp-email-field">
    </form>

    {{-- Magic link hidden form --}}
    <form method="POST" action="{{ route('password.email') }}" id="form-link">
        @csrf
        <input type="hidden" name="method" value="link">
        <input type="hidden" name="email"  id="link-email-field">
    </form>

    {{-- Method label --}}
    <p style="font-size:12px;font-weight:500;color:#64748b;text-transform:uppercase;letter-spacing:.04em;margin-bottom:10px;">
        Choose reset method
    </p>

    {{-- OTP button --}}
    <button type="button" class="method-btn" id="btn-otp" onclick="chooseMethod('otp')">
        <div class="method-icon" style="background:rgba(99,102,241,0.12);">
            <svg width="20" height="20" fill="none" stroke="#a78bfa" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>
        <div style="flex:1;">
            <div class="method-title" id="otp-title">Send 6-digit code</div>
            <div class="method-desc">We email you a one-time code. Enter it on the next page to reset your password.</div>
        </div>
        <div class="method-arrow">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </button>

    {{-- Magic link button --}}
    <button type="button" class="method-btn" id="btn-link" onclick="chooseMethod('link')">
        <div class="method-icon" style="background:rgba(37,99,235,0.12);">
            <svg width="20" height="20" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
            </svg>
        </div>
        <div style="flex:1;">
            <div class="method-title" id="link-title">Send magic link</div>
            <div class="method-desc">We email you a secure link. Click it to go straight to the password reset page.</div>
        </div>
        <div class="method-arrow">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </div>
    </button>

    <p style="text-align:center;margin-top:20px;font-size:13px;color:#64748b;">
        Remember your password?
        <a href="{{ route('login') }}" style="color:#60a5fa;text-decoration:none;">Sign in</a>
    </p>
</div>

<script>
function chooseMethod(method) {
    const emailVal = document.getElementById('shared-email').value.trim();
    const emailErr = document.getElementById('email-err');
    const emailInp = document.getElementById('shared-email');

    // Validate
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailVal || !re.test(emailVal)) {
        emailInp.style.borderColor = '#f87171';
        emailErr.style.display = 'block';
        emailInp.focus();
        setTimeout(() => {
            emailInp.style.borderColor = '';
            emailErr.style.display = 'none';
        }, 3000);
        return;
    }

    // Disable both buttons to prevent double submit
    document.getElementById('btn-otp').setAttribute('disabled', true);
    document.getElementById('btn-link').setAttribute('disabled', true);

    // Show loading state on chosen button
    if (method === 'otp') {
        document.getElementById('otp-title').textContent = 'Sending code…';
        document.getElementById('btn-otp').style.borderColor = 'rgba(167,139,250,0.5)';
        // Fill OTP form email and submit
        document.getElementById('otp-email-field').value = emailVal;
        document.getElementById('form-otp').submit();
    } else {
        document.getElementById('link-title').textContent = 'Sending link…';
        document.getElementById('btn-link').style.borderColor = 'rgba(96,165,250,0.5)';
        // Fill link form email and submit
        document.getElementById('link-email-field').value = emailVal;
        document.getElementById('form-link').submit();
    }
}
</script>

</body>
</html>