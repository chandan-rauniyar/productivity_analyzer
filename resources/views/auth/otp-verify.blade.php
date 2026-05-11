{{-- ════════════════════════════════════════════════════════════
    resources/views/auth/otp-verify.blade.php
════════════════════════════════════════════════════════════ --}}
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter reset code — PulseWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@500&display=swap" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        html[data-theme="dark"]  body { background:#050c1a; color:#f1f5f9; }
        html[data-theme="light"] body { background:#f0f4f8; color:#0f172a; }
        body { font-family:'DM Sans',sans-serif; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:24px; transition:background .3s; }
        h1 { font-family:'Syne',sans-serif; }
        .card { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:36px; width:100%; max-width:420px; }
        html[data-theme="light"] .card { background:white; border-color:rgba(0,0,0,0.08); box-shadow:0 4px 24px rgba(0,0,0,0.06); }

        /* OTP digit inputs */
        .otp-inputs { display:flex; gap:10px; justify-content:center; margin:24px 0; }
        .otp-digit { width:48px; height:60px; border-radius:10px; border:1.5px solid rgba(255,255,255,0.15); background:rgba(255,255,255,0.05); color:#f1f5f9; font-family:'DM Mono',monospace; font-size:24px; font-weight:500; text-align:center; outline:none; transition:all .15s; caret-color:transparent; }
        html[data-theme="light"] .otp-digit { border-color:rgba(0,0,0,0.15); background:white; color:#0f172a; }
        .otp-digit:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,0.15); }
        .otp-digit.filled { border-color:rgba(96,165,250,0.5); background:rgba(37,99,235,0.08); }

        .input { width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px 14px; color:inherit; font-size:14px; outline:none; transition:border-color .15s; }
        html[data-theme="light"] .input { background:white; border-color:rgba(0,0,0,0.12); }
        .input:focus { border-color:rgba(96,165,250,0.5); }
        label { display:block; font-size:12px; font-weight:500; color:#64748b; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
        .btn { width:100%; background:#2563eb; color:white; padding:11px; border-radius:10px; font-size:14px; font-weight:500; border:none; cursor:pointer; transition:all .15s; }
        .btn:hover { background:#1d4ed8; }
        .error { background:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.25); color:#fca5a5; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
        .success { background:rgba(52,211,153,0.1); border:1px solid rgba(52,211,153,0.25); color:#6ee7b7; border-radius:10px; padding:12px 16px; font-size:13px; margin-bottom:16px; }
    </style>
</head>
<body>
<div class="card">
    <!-- Logo -->
    <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;margin-bottom:28px;">
        <div style="width:28px;height:28px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
            <svg width="15" height="15" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:16px;">PulseWork</span>
    </a>

    <h1 style="font-size:22px;font-weight:800;margin-bottom:6px;">Enter your reset code</h1>
    <p style="font-size:14px;color:#64748b;margin-bottom:20px;font-weight:300;">
        We sent a 6-digit code to <strong style="color:inherit;">{{ $email }}</strong>.
        Check your inbox — also check spam.
    </p>

    @if(session('status'))
        <div class="success">✓ {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('password.verify-otp') }}" id="otp-form">
        @csrf
        <input type="hidden" name="email" value="{{ $email }}">
        <input type="hidden" name="otp" id="otp-hidden">

        <!-- Visual digit inputs -->
        <div class="otp-inputs" role="group" aria-label="6-digit reset code">
            @for($i = 0; $i < 6; $i++)
                <input type="tel" maxlength="1" class="otp-digit" id="otp-{{ $i }}"
                       inputmode="numeric" pattern="[0-9]"
                       aria-label="Digit {{ $i + 1 }} of 6"
                       autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}">
            @endfor
        </div>

        <button type="submit" class="btn" id="verify-btn" disabled>Verify code</button>
    </form>

    <div style="margin-top:20px;display:flex;flex-direction:column;gap:10px;text-align:center;">
        <p style="font-size:13px;color:#64748b;">
            Prefer a magic link?
            <a href="{{ route('password.request') }}" style="color:#60a5fa;text-decoration:none;">Request a new one</a>
        </p>
        <p style="font-size:13px;color:#64748b;">
            Didn't get an email?
            <a href="{{ route('password.request') }}?email={{ urlencode($email) }}" style="color:#60a5fa;text-decoration:none;">Resend code</a>
        </p>
        <p style="font-size:13px;color:#64748b;">
            <a href="{{ route('login') }}" style="color:#64748b;text-decoration:none;">← Back to sign in</a>
        </p>
    </div>
</div>

<script>
// Theme
const theme = localStorage.getItem('pw-theme') || 'dark';
document.documentElement.setAttribute('data-theme', theme);

// OTP input navigation
const inputs = document.querySelectorAll('.otp-digit');
const hidden  = document.getElementById('otp-hidden');
const btn     = document.getElementById('verify-btn');

function syncHidden() {
    const val = [...inputs].map(i => i.value).join('');
    hidden.value = val;
    btn.disabled = val.length < 6;
}

inputs.forEach((input, idx) => {
    input.addEventListener('input', function(e) {
        // Accept paste of full code on first input
        if (this.value.length > 1) {
            const digits = this.value.replace(/\D/g,'').slice(0,6).split('');
            digits.forEach((d,i) => { if(inputs[i]) inputs[i].value = d; });
            inputs[Math.min(digits.length, 5)].focus();
            syncHidden(); return;
        }
        if (this.value && idx < 5) inputs[idx+1].focus();
        this.classList.toggle('filled', !!this.value);
        syncHidden();
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Backspace' && !this.value && idx > 0) {
            inputs[idx-1].value = '';
            inputs[idx-1].focus();
            inputs[idx-1].classList.remove('filled');
            syncHidden();
        }
        if (e.key === 'ArrowLeft'  && idx > 0) inputs[idx-1].focus();
        if (e.key === 'ArrowRight' && idx < 5) inputs[idx+1].focus();
    });

    // Pre-fill from ?otp= param (dev convenience)
    const params = new URLSearchParams(location.search);
    if (params.get('otp') && idx === 0) {
        const digits = params.get('otp').slice(0,6).split('');
        digits.forEach((d,i) => { if(inputs[i]) { inputs[i].value=d; inputs[i].classList.add('filled'); } });
        syncHidden();
    }
});

// Auto-submit when all 6 entered
document.getElementById('otp-form').addEventListener('submit', function() {
    btn.disabled = true;
    btn.textContent = 'Verifying…';
});

// Focus first input
inputs[0]?.focus();
</script>
</body>
</html>