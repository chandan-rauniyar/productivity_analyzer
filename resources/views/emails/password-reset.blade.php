<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset your PulseWork password</title>
<style>
  body { margin:0; padding:0; background:#f0f4f8; font-family:'Segoe UI',Arial,sans-serif; }
  .wrap { max-width:520px; margin:40px auto; background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 24px rgba(0,0,0,0.08); }
  .header { background:linear-gradient(135deg,#1e3a8a,#4f46e5); padding:32px 40px; text-align:center; }
  .logo { display:inline-flex; align-items:center; gap:8px; }
  .logo-icon { width:32px; height:32px; background:rgba(255,255,255,0.2); border-radius:8px; display:inline-flex; align-items:center; justify-content:center; }
  .logo-name { font-size:20px; font-weight:700; color:white; }
  .body { padding:36px 40px; }
  .greeting { font-size:18px; font-weight:600; color:#0f172a; margin-bottom:8px; }
  .text { font-size:15px; color:#475569; line-height:1.7; margin-bottom:20px; }
  .otp-box { background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:24px; text-align:center; margin:24px 0; }
  .otp-label { font-size:12px; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; font-weight:600; margin-bottom:12px; }
  .otp-code { font-size:40px; font-weight:700; letter-spacing:12px; color:#1e3a8a; font-family:'Courier New',monospace; }
  .otp-expiry { font-size:12px; color:#94a3b8; margin-top:10px; }
  .divider { display:flex; align-items:center; gap:12px; color:#cbd5e1; font-size:13px; margin:24px 0; }
  .divider::before, .divider::after { content:''; flex:1; height:1px; background:#e2e8f0; }
  .magic-btn { display:block; background:#2563eb; color:white; text-decoration:none; text-align:center; padding:14px 24px; border-radius:10px; font-size:15px; font-weight:600; margin:20px 0; }
  .magic-link-text { font-size:12px; color:#94a3b8; word-break:break-all; margin-top:8px; }
  .security-note { background:#fef9ec; border:1px solid #fde68a; border-radius:10px; padding:14px 16px; font-size:13px; color:#92400e; margin-top:24px; }
  .footer { background:#f8fafc; border-top:1px solid #e2e8f0; padding:20px 40px; text-align:center; font-size:12px; color:#94a3b8; }
</style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <div class="logo">
            <div class="logo-icon">
                <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="logo-name">PulseWork</span>
        </div>
    </div>

    <div class="body">
        <div class="greeting">Hi {{ $user->name }},</div>
        <p class="text">We received a request to reset the password for your PulseWork account. You can reset it using either the code below or by clicking the magic link — whichever is easier.</p>

        {{-- OTP Code --}}
        <div class="otp-box">
            <div class="otp-label">Your 6-digit reset code</div>
            <div class="otp-code">{{ $otp }}</div>
            <div class="otp-expiry">Expires in 15 minutes · One use only</div>
        </div>

        <p class="text" style="font-size:14px;">Enter this code on the PulseWork reset page. Go to <a href="{{ route('password.otp-form') }}" style="color:#2563eb;">the reset page</a>, enter your email and this code, then choose a new password.</p>

        <div class="divider">or use the magic link</div>

        <a href="{{ $magicLink }}" class="magic-btn">Reset password instantly →</a>
        <p class="magic-link-text">{{ $magicLink }}</p>

        <div class="security-note">
            <strong>Didn't request this?</strong> You can safely ignore this email. Your password won't change unless you follow one of the options above.
        </div>
    </div>

    <div class="footer">
        <p>PulseWork · Privacy-first productivity analytics</p>
        <p style="margin-top:4px;">This email was sent to {{ $user->email }}</p>
    </div>
</div>
</body>
</html>