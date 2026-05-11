<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile — PulseWork</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; background: #050c1a; color: #f1f5f9; min-height: 100vh; }
        h1,h2,h3,.display { font-family: 'Syne', sans-serif; }
        .mono { font-family: 'DM Mono', monospace; }

        /* Cards */
        .card { background: rgba(255,255,255,0.035); border: 1px solid rgba(255,255,255,0.07); border-radius: 16px; padding: 28px; }
        .card-danger { background: rgba(239,68,68,0.05); border: 1px solid rgba(239,68,68,0.15); border-radius: 16px; padding: 28px; }

        /* Inputs */
        .input {
            width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 10px; padding: 10px 14px; color: #f1f5f9; font-size: 14px;
            font-family: 'DM Sans', sans-serif; transition: border-color .15s; outline: none;
        }
        .input:focus { border-color: rgba(96,165,250,0.5); box-shadow: 0 0 0 3px rgba(96,165,250,0.08); }
        .input::placeholder { color: #475569; }
        label { display: block; font-size: 12px; font-weight: 500; color: #94a3b8; margin-bottom: 6px; text-transform: uppercase; letter-spacing: .04em; }

        /* Buttons */
        .btn-primary { background: #2563eb; color: white; padding: 9px 18px; border-radius: 9px; font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all .15s; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-ghost { background: transparent; color: #94a3b8; padding: 9px 18px; border-radius: 9px; font-size: 13px; font-weight: 500; border: 1px solid rgba(255,255,255,0.1); cursor: pointer; transition: all .15s; }
        .btn-ghost:hover { background: rgba(255,255,255,0.05); color: #f1f5f9; }
        .btn-danger { background: rgba(239,68,68,0.1); color: #f87171; padding: 9px 18px; border-radius: 9px; font-size: 13px; font-weight: 500; border: 1px solid rgba(239,68,68,0.25); cursor: pointer; transition: all .15s; }
        .btn-danger:hover { background: rgba(239,68,68,0.2); }

        /* Sidebar nav */
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 9px; font-size: 14px; color: #64748b; cursor: pointer; transition: all .15s; text-decoration: none; }
        .nav-item:hover, .nav-item.active { background: rgba(255,255,255,0.06); color: #f1f5f9; }
        .nav-item.active { color: #60a5fa; }

        /* Stat chips */
        .stat-chip { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 16px; text-align: center; }

        /* Avatar ring */
        .avatar-ring { width: 80px; height: 80px; border-radius: 50%; border: 2px solid rgba(37,99,235,0.4); padding: 2px; }
        .avatar-inner { width: 100%; height: 100%; border-radius: 50%; background: linear-gradient(135deg,#1e3a8a,#4f46e5); display: flex; align-items: center; justify-content: center; font-family: 'Syne',sans-serif; font-weight: 700; font-size: 28px; color: white; overflow: hidden; }

        /* Section tabs */
        .tab { display: none; }
        .tab.active { display: block; }

        /* Flash */
        .flash-success { background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.25); color: #6ee7b7; border-radius: 10px; padding: 12px 16px; font-size: 14px; margin-bottom: 20px; }
        .flash-error   { background: rgba(239,68,68,0.1);  border: 1px solid rgba(239,68,68,0.25);  color: #fca5a5; border-radius: 10px; padding: 12px 16px; font-size: 14px; margin-bottom: 20px; }

        /* Modal */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); z-index: 50; display: none; align-items: center; justify-content: center; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: #0f1e38; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px; padding: 28px; max-width: 420px; width: 90%; }

        /* Connected badge */
        .connected-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(52,211,153,0.1); border: 1px solid rgba(52,211,153,0.25); border-radius: 100px; padding: 3px 10px; font-size: 11px; color: #6ee7b7; font-weight: 500; }
        .disconnected-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(100,116,139,0.1); border: 1px solid rgba(100,116,139,0.25); border-radius: 100px; padding: 3px 10px; font-size: 11px; color: #94a3b8; font-weight: 500; }
    </style>
</head>
<body>

<!-- ═══════════════════ NAVBAR ════════════════════════════════ -->
<header style="position:sticky;top:0;z-index:40;background:rgba(5,12,26,0.85);backdrop-filter:blur(16px);border-bottom:1px solid rgba(255,255,255,0.06);">
    <div style="max-width:1100px;margin:0 auto;padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between;">
        <!-- Logo -->
        <a href="{{ route('home') }}" style="display:flex;align-items:center;gap:8px;text-decoration:none;">
            <div style="width:28px;height:28px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
                <svg width="15" height="15" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <span style="font-family:'Syne',sans-serif;font-weight:700;color:white;font-size:16px;">PulseWork</span>
        </a>

        <!-- Nav links -->
        <nav style="display:flex;align-items:center;gap:4px;">
            <a href="{{ route('dashboard') }}" style="padding:6px 12px;border-radius:8px;font-size:13px;color:#94a3b8;text-decoration:none;transition:color .15s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Dashboard</a>
            <a href="{{ route('profile.show') }}" style="padding:6px 12px;border-radius:8px;font-size:13px;color:white;text-decoration:none;background:rgba(255,255,255,0.06);">Profile</a>
        </nav>

        <!-- User chip -->
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:100px;padding:4px 14px 4px 4px;cursor:pointer;" onclick="toggleUserMenu()">
                <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#1e3a8a,#4f46e5);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:12px;overflow:hidden;">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <span style="font-size:13px;color:#e2e8f0;">{{ explode(' ', auth()->user()->name)[0] }}</span>
                <svg width="12" height="12" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>

            <!-- Dropdown -->
            <div id="user-menu" style="display:none;position:absolute;top:52px;right:24px;background:#0f1e38;border:1px solid rgba(255,255,255,0.1);border-radius:12px;padding:8px;min-width:180px;z-index:50;box-shadow:0 16px 40px rgba(0,0,0,0.5);">
                <div style="padding:8px 12px 10px;border-bottom:1px solid rgba(255,255,255,0.07);margin-bottom:6px;">
                    <p style="font-size:13px;font-weight:500;color:#f1f5f9;">{{ auth()->user()->name }}</p>
                    <p style="font-size:11px;color:#64748b;margin-top:1px;">{{ auth()->user()->email }}</p>
                </div>
                <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;font-size:13px;color:#94a3b8;text-decoration:none;transition:all .15s;" onmouseover="this.style.background='rgba(255,255,255,0.05)';this.style.color='white'" onmouseout="this.style.background='';this.style.color='#94a3b8'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('profile.show') }}" style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;font-size:13px;color:#60a5fa;text-decoration:none;background:rgba(37,99,235,0.08);">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile & Settings
                </a>
                <div style="border-top:1px solid rgba(255,255,255,0.07);margin:6px 0;"></div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" style="width:100%;display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;font-size:13px;color:#f87171;background:transparent;border:none;cursor:pointer;text-align:left;transition:all .15s;" onmouseover="this.style.background='rgba(239,68,68,0.08)'" onmouseout="this.style.background=''">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<main style="max-width:1100px;margin:0 auto;padding:40px 24px;">

    <!-- Flash messages -->
    @if(session('status'))
        <div class="flash-success">✓ {{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="flash-error">{{ $errors->first() }}</div>
    @endif

    <div style="display:grid;grid-template-columns:220px 1fr;gap:28px;align-items:start;">

        <!-- ════ SIDEBAR ════ -->
        <aside>
            <!-- Avatar + name -->
            <div style="text-align:center;padding:20px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:16px;margin-bottom:12px;">
                <div style="display:flex;justify-content:center;margin-bottom:12px;">
                    <div class="avatar-ring">
                        <div class="avatar-inner">
                            @if(auth()->user()->avatar_url)
                                <img src="{{ auth()->user()->avatar_url }}" style="width:100%;height:100%;object-fit:cover;" alt="">
                            @else
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </div>
                    </div>
                </div>
                <p style="font-family:'Syne',sans-serif;font-weight:700;font-size:15px;color:white;margin-bottom:2px;">{{ auth()->user()->name }}</p>
                <p style="font-size:12px;color:#64748b;margin-bottom:10px;">{{ auth()->user()->email }}</p>
                @if($token)
                    <span class="connected-badge"><span style="width:5px;height:5px;background:#34d399;border-radius:50%;display:inline-block;"></span>Outlook connected</span>
                @else
                    <span class="disconnected-badge">Outlook not connected</span>
                @endif
            </div>

            <!-- Nav -->
            <nav style="display:flex;flex-direction:column;gap:2px;">
                <a onclick="showTab('overview')"  class="nav-item active" id="tab-btn-overview">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Overview
                </a>
                <a onclick="showTab('account')"  class="nav-item" id="tab-btn-account">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Account
                </a>
                <a onclick="showTab('security')" class="nav-item" id="tab-btn-security">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Security
                </a>
                <a onclick="showTab('outlook')"  class="nav-item" id="tab-btn-outlook">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Outlook
                </a>
                <a onclick="showTab('data')"     class="nav-item" id="tab-btn-data">
                    <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    Data & Privacy
                </a>
                <a onclick="showTab('danger')"   class="nav-item" id="tab-btn-danger">
                    <svg width="15" height="15" fill="none" stroke="#f87171" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span style="color:#f87171;">Danger Zone</span>
                </a>
            </nav>
        </aside>

        <!-- ════ MAIN CONTENT ════ -->
        <div>

            <!-- ── Overview tab ── -->
            <div id="tab-overview" class="tab active">
                <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;">Overview</h2>

                <!-- Stats row -->
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;">
                    <div class="stat-chip">
                        <p style="font-size:22px;font-weight:700;font-family:'Syne',sans-serif;">{{ $totalEmailDays ?? 0 }}</p>
                        <p style="font-size:11px;color:#64748b;margin-top:3px;">days tracked</p>
                    </div>
                    <div class="stat-chip">
                        <p style="font-size:22px;font-weight:700;font-family:'Syne',sans-serif;">{{ number_format($totalEmailsRecv ?? 0) }}</p>
                        <p style="font-size:11px;color:#64748b;margin-top:3px;">emails analysed</p>
                    </div>
                    <div class="stat-chip">
                        <p style="font-size:22px;font-weight:700;font-family:'Syne',sans-serif;">{{ round($avgScore ?? 0) }}</p>
                        <p style="font-size:11px;color:#64748b;margin-top:3px;">avg score</p>
                    </div>
                    <div class="stat-chip">
                        <p style="font-size:22px;font-weight:700;font-family:'Syne',sans-serif;">{{ round(($totalMeetingMins ?? 0) / 60) }}</p>
                        <p style="font-size:11px;color:#64748b;margin-top:3px;">meeting hrs total</p>
                    </div>
                </div>

                <!-- Profile info read-only -->
                <div class="card">
                    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">Account details</h3>
                    <div style="display:grid;gap:12px;">
                        @foreach([['Name', auth()->user()->name],['Email', auth()->user()->email],['Organisation', auth()->user()->organisation ?? '—'],['Member since', auth()->user()->created_at->format('F j, Y')],['Last synced', auth()->user()->last_synced_at?->diffForHumans() ?? 'Never']] as [$label, $val])
                        <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                            <span style="font-size:13px;color:#64748b;">{{ $label }}</span>
                            <span style="font-size:13px;color:#e2e8f0;">{{ $val }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- ── Account tab ── -->
            <div id="tab-account" class="tab">
                <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;">Account settings</h2>

                <!-- Update name / org -->
                <div class="card" style="margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;">Personal information</h3>
                    <form method="POST" action="{{ route('profile.update-info') }}">
                        @csrf @method('PATCH')
                        <div style="display:grid;gap:14px;">
                            <div>
                                <label>Full name</label>
                                <input class="input" type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                            </div>
                            <div>
                                <label>Organisation / company</label>
                                <input class="input" type="text" name="organisation" value="{{ old('organisation', auth()->user()->organisation) }}" placeholder="Your company name">
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" class="btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>

                <!-- Change email -->
                <div class="card">
                    <h3 style="font-size:15px;font-weight:600;margin-bottom:4px;">Email address</h3>
                    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Changing your email requires your current password for verification.</p>
                    <form method="POST" action="{{ route('profile.update-email') }}">
                        @csrf @method('PATCH')
                        <div style="display:grid;gap:14px;">
                            <div>
                                <label>New email address</label>
                                <input class="input" type="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                            </div>
                            <div>
                                <label>Current password</label>
                                <input class="input" type="password" name="password" placeholder="Enter your password to confirm" required>
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" class="btn-primary">Update email</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Security tab ── -->
            <div id="tab-security" class="tab">
                <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;">Security</h2>

                <!-- Change password -->
                <div class="card" style="margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:600;margin-bottom:4px;">Change password</h3>
                    <p style="font-size:13px;color:#64748b;margin-bottom:16px;">Use a strong password with at least 8 characters, uppercase, lowercase, and numbers.</p>
                    <form method="POST" action="{{ route('profile.update-password') }}">
                        @csrf @method('PATCH')
                        <div style="display:grid;gap:14px;">
                            <div>
                                <label>Current password</label>
                                <input class="input" type="password" name="current_password" required>
                            </div>
                            <div>
                                <label>New password</label>
                                <input class="input" type="password" name="password" required>
                            </div>
                            <div>
                                <label>Confirm new password</label>
                                <input class="input" type="password" name="password_confirmation" required>
                            </div>
                        </div>
                        <div style="margin-top:16px;">
                            <button type="submit" class="btn-primary">Change password</button>
                        </div>
                    </form>
                </div>

                <!-- Security info -->
                <div class="card">
                    <h3 style="font-size:15px;font-weight:600;margin-bottom:14px;">Session info</h3>
                    <div style="display:grid;gap:10px;">
                        <div style="display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,0.05);">
                            <span style="font-size:13px;color:#64748b;">Microsoft token expires</span>
                            <span class="mono" style="font-size:12px;color:#e2e8f0;">{{ $token?->expires_at?->format('M j, Y H:i') ?? 'Not connected' }}</span>
                        </div>
                        <div style="display:flex;justify-content:space-between;padding:10px 0;">
                            <span style="font-size:13px;color:#64748b;">Account created</span>
                            <span style="font-size:13px;color:#e2e8f0;">{{ auth()->user()->created_at->format('M j, Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Outlook tab ── -->
            <div id="tab-outlook" class="tab">
                <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;">Microsoft Outlook</h2>

                @if($token)
                <div class="card" style="margin-bottom:16px;">
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                        <div style="width:40px;height:40px;background:rgba(37,99,235,0.15);border-radius:10px;display:flex;align-items:center;justify-content:center;">
                            <svg width="20" height="20" viewBox="0 0 21 21"><rect x="1" y="1" width="9" height="9" fill="#F25022"/><rect x="11" y="1" width="9" height="9" fill="#7FBA00"/><rect x="1" y="11" width="9" height="9" fill="#00A4EF"/><rect x="11" y="11" width="9" height="9" fill="#FFB900"/></svg>
                        </div>
                        <div>
                            <p style="font-size:14px;font-weight:500;color:#f1f5f9;">Microsoft account connected</p>
                            <p style="font-size:12px;color:#64748b;">{{ auth()->user()->email }}</p>
                        </div>
                        <span class="connected-badge" style="margin-left:auto;"><span style="width:5px;height:5px;background:#34d399;border-radius:50%;display:inline-block;"></span>Active</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.03);border-radius:10px;padding:14px;margin-bottom:16px;">
                        <p style="font-size:12px;color:#64748b;margin-bottom:8px;text-transform:uppercase;letter-spacing:.04em;font-weight:500;">Granted permissions</p>
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @foreach(is_array($token->scopes) ? $token->scopes : [] as $scope)
                                <span class="mono" style="font-size:11px;background:rgba(37,99,235,0.1);border:1px solid rgba(37,99,235,0.2);border-radius:6px;padding:2px 8px;color:#93c5fd;">{{ $scope }}</span>
                            @endforeach
                        </div>
                    </div>
                    <form method="POST" action="{{ route('profile.disconnect-outlook') }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-danger" onclick="return confirm('Disconnect your Microsoft account? Your analytics data will be kept.')">
                            Disconnect Outlook account
                        </button>
                    </form>
                </div>
                @else
                <div class="card" style="text-align:center;padding:40px;">
                    <div style="width:48px;height:48px;background:rgba(100,116,139,0.1);border-radius:12px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                        <svg width="24" height="24" fill="none" stroke="#64748b" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <p style="font-size:15px;font-weight:500;margin-bottom:8px;">No Outlook account connected</p>
                    <p style="font-size:13px;color:#64748b;margin-bottom:20px;">Connect your Microsoft account to start syncing email and calendar data.</p>
                    <a href="{{ route('auth.microsoft') }}" class="btn-primary" style="display:inline-flex;align-items:center;gap:8px;">
                        <svg width="14" height="14" viewBox="0 0 21 21"><rect x="1" y="1" width="9" height="9" fill="white" opacity=".9"/><rect x="11" y="1" width="9" height="9" fill="white" opacity=".7"/><rect x="1" y="11" width="9" height="9" fill="white" opacity=".7"/><rect x="11" y="11" width="9" height="9" fill="white" opacity=".9"/></svg>
                        Connect Microsoft Outlook
                    </a>
                </div>
                @endif
            </div>

            <!-- ── Data & Privacy tab ── -->
            <div id="tab-data" class="tab">
                <h2 style="font-size:20px;font-weight:700;margin-bottom:20px;">Data & Privacy</h2>

                <div class="card" style="margin-bottom:16px;">
                    <h3 style="font-size:15px;font-weight:600;margin-bottom:4px;">What we store</h3>
                    <p style="font-size:13px;color:#64748b;margin-bottom:14px;">PulseWork only stores computed metrics — never raw email content.</p>
                    <div style="display:grid;gap:8px;">
                        @foreach([['Email timestamps (when received/sent)', true],['Email count per day', true],['After-hours email count', true],['Average response time (hours only)', true],['Calendar event durations', true],['Meeting slot times', true],['Email subjects or bodies', false],['Email content or attachments', false],['Contact names or addresses', false],['Microsoft passwords', false]] as [$item, $stored])
                        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid rgba(255,255,255,0.04);">
                            @if($stored)
                                <svg width="14" height="14" fill="#60a5fa" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                <span style="font-size:13px;color:#94a3b8;">{{ $item }}</span>
                                <span style="margin-left:auto;font-size:11px;color:#60a5fa;background:rgba(37,99,235,0.1);border-radius:4px;padding:1px 6px;">stored</span>
                            @else
                                <svg width="14" height="14" fill="#f87171" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                <span style="font-size:13px;color:#94a3b8;">{{ $item }}</span>
                                <span style="margin-left:auto;font-size:11px;color:#f87171;background:rgba(239,68,68,0.1);border-radius:4px;padding:1px 6px;">never stored</span>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-danger">
                    <h3 style="font-size:15px;font-weight:600;color:#fca5a5;margin-bottom:4px;">Delete all analytics data</h3>
                    <p style="font-size:13px;color:#94a3b8;margin-bottom:16px;">Permanently delete all email metrics, calendar metrics, and productivity scores. Your account will remain active. This cannot be undone.</p>
                    <form method="POST" action="{{ route('profile.delete-data') }}">
                        @csrf @method('DELETE')
                        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <input class="input" type="text" name="confirm_delete" placeholder='Type DELETE to confirm' style="max-width:220px;" required>
                            <button type="submit" class="btn-danger">Delete all data</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ── Danger Zone tab ── -->
            <div id="tab-danger" class="tab">
                <h2 style="font-size:20px;font-weight:700;color:#f87171;margin-bottom:20px;">Danger Zone</h2>

                <div class="card-danger">
                    <h3 style="font-size:15px;font-weight:600;color:#fca5a5;margin-bottom:4px;">Delete account permanently</h3>
                    <p style="font-size:13px;color:#94a3b8;margin-bottom:20px;">This will immediately and permanently delete your account, all analytics data, and revoke Microsoft access. This action <strong style="color:#f87171;">cannot be undone</strong>.</p>
                    <form method="POST" action="{{ route('profile.delete-account') }}">
                        @csrf @method('DELETE')
                        <div style="display:grid;gap:14px;">
                            <div>
                                <label style="color:#94a3b8;">Your password</label>
                                <input class="input" type="password" name="password" required placeholder="Enter your password">
                            </div>
                            <div>
                                <label style="color:#94a3b8;">Type <span class="mono" style="color:#f87171;">DELETE MY ACCOUNT</span> to confirm</label>
                                <input class="input" type="text" name="confirm_delete" required placeholder="DELETE MY ACCOUNT">
                            </div>
                        </div>
                        <div style="margin-top:20px;">
                            <button type="submit" class="btn-danger" style="background:rgba(239,68,68,0.2);border-color:rgba(239,68,68,0.5);" onclick="return confirm('This will permanently delete your account. Are you absolutely sure?')">
                                Permanently delete my account
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div><!-- end main content -->
    </div><!-- end grid -->
</main>

<script>
function showTab(name) {
    document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    document.getElementById('tab-btn-' + name).classList.add('active');
}

function toggleUserMenu() {
    const m = document.getElementById('user-menu');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('[onclick="toggleUserMenu()"]') && !e.target.closest('#user-menu')) {
        const m = document.getElementById('user-menu');
        if(m) m.style.display = 'none';
    }
});

// Auto-open tab from URL hash
const hash = window.location.hash.replace('#','');
if (['overview','account','security','outlook','data','danger'].includes(hash)) showTab(hash);
</script>
</body>
</html>