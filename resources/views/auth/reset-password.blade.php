{{-- resources/views/auth/reset-password.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password — PulseWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { font-family:'DM Sans',sans-serif; background:#050c1a; color:#f1f5f9; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        h1 { font-family:'Syne',sans-serif; }
        .card { background:rgba(255,255,255,0.035); border:1px solid rgba(255,255,255,0.08); border-radius:20px; padding:36px; width:100%; max-width:420px; }
        .input { width:100%; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:10px; padding:10px 14px; color:#f1f5f9; font-size:14px; font-family:'DM Sans',sans-serif; outline:none; transition:border-color .15s; }
        .input:focus { border-color:rgba(96,165,250,0.5); box-shadow:0 0 0 3px rgba(96,165,250,0.08); }
        label { display:block; font-size:12px; font-weight:500; color:#94a3b8; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
        .btn { width:100%; background:#2563eb; color:white; padding:11px; border-radius:10px; font-size:14px; font-weight:500; border:none; cursor:pointer; transition:all .15s; }
        .btn:hover { background:#1d4ed8; }
    </style>
</head>
<body>
<div style="padding:24px;width:100%;">
    <div class="card" style="margin:0 auto;">
        <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;margin-bottom:28px;">
            <div style="width:28px;height:28px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
                <svg width="15" height="15" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span style="font-family:'Syne',sans-serif;font-weight:700;color:white;">PulseWork</span>
        </a>

        <h1 style="font-size:22px;font-weight:800;margin-bottom:6px;">Set new password</h1>
        <p style="font-size:14px;color:#64748b;margin-bottom:24px;font-weight:300;">Choose a strong password for your account.</p>

        @if($errors->any())
            <div style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#fca5a5;border-radius:10px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.store') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">
            <div style="display:grid;gap:16px;margin-bottom:20px;">
                <div>
                    <label>Email address</label>
                    <input class="input" type="email" name="email" value="{{ old('email', $request->email) }}" required>
                </div>
                <div>
                    <label>New password</label>
                    <input class="input" type="password" name="password" required>
                    <p style="font-size:11px;color:#475569;margin-top:4px;">Min 8 characters with uppercase, lowercase and numbers</p>
                </div>
                <div>
                    <label>Confirm new password</label>
                    <input class="input" type="password" name="password_confirmation" required>
                </div>
            </div>
            <button type="submit" class="btn">Reset password</button>
        </form>
    </div>
</div>
</body>
</html>