{{-- ════════════════════════════════════════════════════════════
    resources/views/components/navbar.blade.php
    Usage: <x-navbar :active="'dashboard'" />
    active: 'dashboard' | 'profile' | 'history' | 'settings'
════════════════════════════════════════════════════════════ --}}
@props(['active' => 'dashboard'])

<style>
.pw-nav { position:sticky;top:0;z-index:50;background:rgba(5,12,26,0.88);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,0.07); }
.pw-nav-inner { max-width:1200px;margin:0 auto;padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between;gap:16px; }
.pw-brand { display:flex;align-items:center;gap:8px;text-decoration:none;flex-shrink:0; }
.pw-brand-icon { width:30px;height:30px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:8px;display:flex;align-items:center;justify-content:center; }
.pw-brand-name { font-family:'Syne',sans-serif;font-weight:700;color:white;font-size:16px; }

/* Nav links */
.pw-nav-links { display:flex;align-items:center;gap:2px; }
.pw-nav-link { display:flex;align-items:center;gap:6px;padding:6px 12px;border-radius:8px;font-size:13px;font-weight:400;color:#64748b;text-decoration:none;transition:all .15s;white-space:nowrap; }
.pw-nav-link:hover { background:rgba(255,255,255,0.06);color:#e2e8f0; }
.pw-nav-link.pw-active { background:rgba(37,99,235,0.12);color:#60a5fa; }
.pw-nav-link svg { width:15px;height:15px;flex-shrink:0; }

/* User menu */
.pw-user-chip { display:flex;align-items:center;gap:8px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-radius:100px;padding:4px 12px 4px 4px;cursor:pointer;transition:all .15s; }
.pw-user-chip:hover { background:rgba(255,255,255,0.07);border-color:rgba(255,255,255,0.14); }
.pw-avatar { width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,#1e3a8a,#4f46e5);display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:12px;color:white;overflow:hidden;flex-shrink:0; }

/* Dropdown */
.pw-dropdown { display:none;position:absolute;top:54px;right:24px;background:#0d1b33;border:1px solid rgba(255,255,255,0.1);border-radius:14px;padding:6px;min-width:220px;z-index:200;box-shadow:0 20px 60px rgba(0,0,0,0.6); }
.pw-dropdown.open { display:block;animation:dropIn .15s cubic-bezier(.4,0,.2,1); }
@keyframes dropIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.pw-dd-header { padding:10px 12px 12px;border-bottom:1px solid rgba(255,255,255,0.07);margin-bottom:4px; }
.pw-dd-name { font-size:13px;font-weight:500;color:#f1f5f9; }
.pw-dd-email { font-size:11px;color:#475569;margin-top:2px;word-break:break-all; }
.pw-dd-item { display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;font-size:13px;color:#94a3b8;text-decoration:none;transition:all .15s;cursor:pointer;border:none;background:transparent;width:100%;text-align:left; }
.pw-dd-item:hover { background:rgba(255,255,255,0.06);color:#f1f5f9; }
.pw-dd-item.active { color:#60a5fa;background:rgba(37,99,235,0.08); }
.pw-dd-item svg { width:14px;height:14px;flex-shrink:0; }
.pw-dd-sep { border:none;border-top:1px solid rgba(255,255,255,0.07);margin:4px 0; }

/* Sync button */
.pw-sync-btn { display:flex;align-items:center;gap:6px;background:#2563eb;color:white;border:none;border-radius:8px;padding:7px 14px;font-size:12px;font-weight:500;cursor:pointer;transition:all .15s;white-space:nowrap; }
.pw-sync-btn:hover { background:#1d4ed8; }
.pw-sync-btn:disabled { opacity:.6;cursor:not-allowed; }
.pw-sync-icon { width:13px;height:13px; }

/* Flash notification */
.pw-flash { position:fixed;top:68px;right:24px;z-index:300;max-width:360px;border-radius:12px;padding:12px 16px;font-size:13px;display:flex;align-items:flex-start;gap:10px;box-shadow:0 8px 32px rgba(0,0,0,0.4);animation:slideInRight .3s cubic-bezier(.4,0,.2,1);transition:opacity .4s,transform .4s; }
.pw-flash.hide { opacity:0;transform:translateX(16px); }
.pw-flash-success { background:#0c2018;border:1px solid rgba(52,211,153,0.3);color:#6ee7b7; }
.pw-flash-error   { background:#200c0c;border:1px solid rgba(239,68,68,0.3);color:#fca5a5; }
.pw-flash-info    { background:#0c1828;border:1px solid rgba(96,165,250,0.3);color:#93c5fd; }
.pw-flash-close { margin-left:auto;cursor:pointer;opacity:.6;flex-shrink:0;background:transparent;border:none;color:inherit;font-size:16px;line-height:1;padding:0; }
.pw-flash-close:hover { opacity:1; }
@keyframes slideInRight { from{opacity:0;transform:translateX(16px)} to{opacity:1;transform:translateY(0)} }

/* Mobile hamburger */
.pw-hamburger { display:none;background:transparent;border:none;cursor:pointer;padding:4px; }
.pw-mobile-menu { display:none;position:fixed;inset:56px 0 0;background:rgba(5,12,26,0.98);backdrop-filter:blur(16px);z-index:40;padding:16px;overflow-y:auto; }
.pw-mobile-menu.open { display:block; }

@media (max-width:768px) {
    .pw-nav-links { display:none; }
    .pw-hamburger { display:block; }
    .pw-user-chip .pw-name-label { display:none; }
    .pw-dropdown { right:8px; }
}
</style>

<nav class="pw-nav" role="navigation" aria-label="Main navigation">
    <div class="pw-nav-inner">

        {{-- Brand --}}
        <a href="{{ route('home') }}" class="pw-brand">
            <div class="pw-brand-icon">
                <svg width="16" height="16" fill="none" stroke="white" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span class="pw-brand-name">PulseWork</span>
        </a>

        {{-- Desktop nav links --}}
        <div class="pw-nav-links" role="menubar">
            <a href="{{ route('dashboard') }}"
               class="pw-nav-link {{ $active === 'dashboard' ? 'pw-active' : '' }}"
               role="menuitem">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('history') }}"
               class="pw-nav-link {{ $active === 'history' ? 'pw-active' : '' }}"
               role="menuitem">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                History
            </a>
            <a href="{{ route('profile.show') }}"
               class="pw-nav-link {{ $active === 'profile' ? 'pw-active' : '' }}"
               role="menuitem">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profile
            </a>
            <a href="{{ route('settings') }}"
               class="pw-nav-link {{ $active === 'settings' ? 'pw-active' : '' }}"
               role="menuitem">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Settings
            </a>
        </div>

        {{-- Right side --}}
        <div style="display:flex;align-items:center;gap:8px;position:relative;">

            {{-- Sync button (show on dashboard only) --}}
            @if($active === 'dashboard')
                <form method="POST" action="{{ route('dashboard.sync') }}" id="pw-sync-form">
                    @csrf
                    <button type="submit" class="pw-sync-btn" id="pw-sync-btn" aria-label="Sync Outlook data">
                        <svg class="pw-sync-icon" id="pw-sync-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sync
                    </button>
                </form>
            @endif

            {{-- Mobile hamburger --}}
            <button class="pw-hamburger" id="pw-hamburger" aria-label="Toggle mobile menu" aria-expanded="false">
                <svg width="20" height="20" fill="none" stroke="#94a3b8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- User chip --}}
            <div class="pw-user-chip" id="pw-user-chip"
                 onclick="pwToggleDropdown()"
                 role="button"
                 aria-haspopup="true"
                 aria-expanded="false"
                 aria-label="User menu">
                <div class="pw-avatar" aria-hidden="true">
                    @if(auth()->user()->avatar_url)
                        <img src="{{ auth()->user()->avatar_url }}" style="width:100%;height:100%;object-fit:cover;" alt="{{ auth()->user()->name }}">
                    @else
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    @endif
                </div>
                <span class="pw-name-label" style="font-size:13px;color:#e2e8f0;max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    {{ explode(' ', auth()->user()->name)[0] }}
                </span>
                <svg width="11" height="11" fill="none" stroke="#475569" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>

            {{-- Dropdown menu --}}
            <div class="pw-dropdown" id="pw-dropdown" role="menu" aria-label="User actions">
                <div class="pw-dd-header">
                    <div class="pw-dd-name">{{ auth()->user()->name }}</div>
                    <div class="pw-dd-email">{{ auth()->user()->email }}</div>
                    @if(auth()->user()->oauthToken)
                        <div style="display:inline-flex;align-items:center;gap:4px;background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.2);border-radius:100px;padding:2px 8px;font-size:10px;color:#6ee7b7;margin-top:6px;">
                            <span style="width:5px;height:5px;background:#34d399;border-radius:50%;display:inline-block;"></span>
                            Outlook connected
                        </div>
                    @else
                        <a href="{{ route('auth.microsoft') }}" style="display:inline-flex;align-items:center;gap:4px;background:rgba(37,99,235,0.1);border:1px solid rgba(37,99,235,0.25);border-radius:100px;padding:2px 8px;font-size:10px;color:#93c5fd;margin-top:6px;text-decoration:none;">
                            + Connect Outlook
                        </a>
                    @endif
                </div>

                <a href="{{ route('dashboard') }}"
                   class="pw-dd-item {{ $active === 'dashboard' ? 'active' : '' }}"
                   role="menuitem">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('history') }}"
                   class="pw-dd-item {{ $active === 'history' ? 'active' : '' }}"
                   role="menuitem">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>
                    History & Trends
                </a>
                <a href="{{ route('profile.show') }}"
                   class="pw-dd-item {{ $active === 'profile' ? 'active' : '' }}"
                   role="menuitem">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Profile & Settings
                </a>
                <a href="{{ route('settings') }}"
                   class="pw-dd-item {{ $active === 'settings' ? 'active' : '' }}"
                   role="menuitem">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Settings
                </a>

                <hr class="pw-dd-sep">

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="pw-dd-item" style="color:#f87171;" role="menuitem">
                        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Log out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

{{-- Mobile nav menu --}}
<div class="pw-mobile-menu" id="pw-mobile-menu" role="dialog" aria-label="Mobile navigation">
    <div style="display:flex;flex-direction:column;gap:4px;">
        @foreach([['dashboard','Dashboard'],['history','History'],['profile','Profile'],['settings','Settings']] as [$route,$label])
        <a href="{{ route($route === 'profile' ? 'profile.show' : $route) }}"
           style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-radius:10px;font-size:15px;color:{{ $active === $route ? '#60a5fa' : '#94a3b8' }};text-decoration:none;background:{{ $active === $route ? 'rgba(37,99,235,0.1)' : 'transparent' }};">
            {{ $label }}
        </a>
        @endforeach
        <hr style="border:none;border-top:1px solid rgba(255,255,255,0.07);margin:8px 0;">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" style="width:100%;display:flex;align-items:center;padding:12px 16px;border-radius:10px;font-size:15px;color:#f87171;background:transparent;border:none;cursor:pointer;text-align:left;">
                Log out
            </button>
        </form>
    </div>
</div>

{{-- Flash notifications (auto-dismiss) --}}
@if(session('status'))
    <div class="pw-flash pw-flash-success" id="pw-flash" role="alert" aria-live="polite">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('status') }}</span>
        <button class="pw-flash-close" onclick="pwDismissFlash()" aria-label="Dismiss notification">×</button>
    </div>
@endif
@if(session('error'))
    <div class="pw-flash pw-flash-error" id="pw-flash" role="alert" aria-live="assertive">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span>{{ session('error') }}</span>
        <button class="pw-flash-close" onclick="pwDismissFlash()" aria-label="Dismiss notification">×</button>
    </div>
@endif

<script>
// ── Dropdown ──────────────────────────────────────────────────
function pwToggleDropdown() {
    const d  = document.getElementById('pw-dropdown');
    const c  = document.getElementById('pw-user-chip');
    const open = d.classList.toggle('open');
    c.setAttribute('aria-expanded', open);
}
document.addEventListener('click', function(e) {
    if (!e.target.closest('#pw-user-chip') && !e.target.closest('#pw-dropdown')) {
        document.getElementById('pw-dropdown')?.classList.remove('open');
        document.getElementById('pw-user-chip')?.setAttribute('aria-expanded','false');
    }
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.getElementById('pw-dropdown')?.classList.remove('open');
        document.getElementById('pw-user-chip')?.setAttribute('aria-expanded','false');
    }
});

// ── Mobile menu ───────────────────────────────────────────────
document.getElementById('pw-hamburger')?.addEventListener('click', function() {
    const m    = document.getElementById('pw-mobile-menu');
    const open = m.classList.toggle('open');
    this.setAttribute('aria-expanded', open);
});

// ── Sync spinner ──────────────────────────────────────────────
document.getElementById('pw-sync-form')?.addEventListener('submit', function() {
    const btn  = document.getElementById('pw-sync-btn');
    const icon = document.getElementById('pw-sync-icon');
    if (!btn) return;
    btn.disabled  = true;
    btn.innerHTML = `<svg class="pw-sync-icon" style="animation:spin .8s linear infinite" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Syncing…`;
});

// ── Flash auto-dismiss (4 seconds) ───────────────────────────
function pwDismissFlash() {
    const f = document.getElementById('pw-flash');
    if (!f) return;
    f.classList.add('hide');
    setTimeout(() => f.remove(), 450);
}
setTimeout(() => pwDismissFlash(), 4000);
</script>

<style>
@keyframes spin { from{transform:rotate(0deg)} to{transform:rotate(360deg)} }
</style>