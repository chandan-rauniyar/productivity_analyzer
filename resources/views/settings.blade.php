{{-- resources/views/settings.blade.php --}}
@extends('layouts.app')
@section('title', 'Settings')
@php $active = 'settings'; @endphp

@section('content')
<style>
.card     { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:24px; margin-bottom:16px; }
.pw-label { display:block; font-size:12px; font-weight:500; color:var(--text-sec); margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
.pw-input { width:100%; background:rgba(255,255,255,0.05); border:1px solid var(--border); border-radius:10px; padding:10px 14px; color:var(--text-pri); font-size:14px; font-family:'DM Sans',sans-serif; outline:none; transition:border-color .15s; }
html[data-theme="light"] .pw-input { background:white; }
.pw-input:focus { border-color:rgba(96,165,250,0.5); box-shadow:0 0 0 3px rgba(96,165,250,0.08); }
.pw-select { appearance:none; background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%2364748b' viewBox='0 0 24 24'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E"); background-repeat:no-repeat; background-position:right 12px center; padding-right:36px; cursor:pointer; }
.btn-primary { background:#2563eb; color:white; padding:10px 20px; border-radius:10px; font-size:13px; font-weight:500; border:none; cursor:pointer; transition:all .15s; }
.btn-primary:hover { background:#1d4ed8; }

/* Toggle switch */
.toggle-wrap { display:flex; align-items:center; justify-content:space-between; padding:14px 0; border-bottom:1px solid var(--border); }
.toggle-wrap:last-child { border-bottom:none; }
.toggle-info h4 { font-size:14px; font-weight:500; color:var(--text-pri); margin-bottom:2px; }
.toggle-info p  { font-size:12px; color:var(--text-ter); }
.toggle { position:relative; display:inline-block; width:42px; height:24px; flex-shrink:0; }
.toggle input { opacity:0; width:0; height:0; }
.toggle-slider { position:absolute; inset:0; background:rgba(255,255,255,0.1); border-radius:24px; cursor:pointer; transition:.25s; }
html[data-theme="light"] .toggle-slider { background:#cbd5e1; }
.toggle-slider::before { content:''; position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:white; border-radius:50%; transition:.25s; }
.toggle input:checked + .toggle-slider { background:#2563eb; }
.toggle input:checked + .toggle-slider::before { transform:translateX(18px); }

.section-title { font-size:16px; font-weight:600; color:var(--text-pri); margin-bottom:4px; }
.section-desc  { font-size:13px; color:var(--text-ter); margin-bottom:20px; }
</style>

{{-- Header --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:var(--text-pri);">Settings</h1>
        <p style="font-size:13px;color:var(--text-sec);margin-top:2px;">Customise how PulseWork analyses your data</p>
    </div>
</div>

<form method="POST" action="{{ route('settings.update') }}">
@csrf

{{-- ── Work Hours ───────────────────────────────────────────── --}}
<div class="card">
    <p class="section-title">Work hours</p>
    <p class="section-desc">Used to detect after-hours emails and meetings, and to calculate focus time accurately.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <div>
            <label class="pw-label">Work day starts</label>
            <select name="work_start" class="pw-input pw-select">
                @for($h = 5; $h <= 12; $h++)
                    <option value="{{ $h }}"
                        {{ (auth()->user()->settings['work_start'] ?? 9) == $h ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::today()->setHour($h)->format('g:00 A') }}
                    </option>
                @endfor
            </select>
        </div>
        <div>
            <label class="pw-label">Work day ends</label>
            <select name="work_end" class="pw-input pw-select">
                @for($h = 14; $h <= 23; $h++)
                    <option value="{{ $h }}"
                        {{ (auth()->user()->settings['work_end'] ?? 18) == $h ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::today()->setHour($h)->format('g:00 A') }}
                    </option>
                @endfor
            </select>
        </div>
    </div>
</div>

{{-- ── Timezone ─────────────────────────────────────────────── --}}
<div class="card">
    <p class="section-title">Timezone</p>
    <p class="section-desc">Your local timezone is used to correctly interpret email and calendar timestamps.</p>
    <div>
        <label class="pw-label">Timezone</label>
        <select name="timezone" class="pw-input pw-select">
            @php
                $current = auth()->user()->settings['timezone'] ?? config('app.timezone', 'UTC');
                $zones = \DateTimeZone::listIdentifiers();
            @endphp
            @foreach($zones as $zone)
                <option value="{{ $zone }}" {{ $current === $zone ? 'selected' : '' }}>
                    {{ str_replace('_', ' ', $zone) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- ── Sync Frequency ───────────────────────────────────────── --}}
<div class="card">
    <p class="section-title">Data sync</p>
    <p class="section-desc">How often PulseWork automatically syncs your Outlook data.</p>
    <div>
        <label class="pw-label">Sync frequency</label>
        <select name="sync_frequency" class="pw-input pw-select">
            @foreach(['daily' => 'Daily (auto at 6 AM)', 'manual' => 'Manual only'] as $val => $label)
                <option value="{{ $val }}"
                    {{ (auth()->user()->settings['sync_frequency'] ?? 'daily') === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- ── Notifications ────────────────────────────────────────── --}}
<div class="card">
    <p class="section-title">Notifications</p>
    <p class="section-desc">Choose which emails PulseWork sends you.</p>

    <div>
        @php
            $settings = auth()->user()->settings ?? [];
        @endphp

        <div class="toggle-wrap">
            <div class="toggle-info">
                <h4>Weekly productivity report</h4>
                <p>Sent every Monday morning with last week's score, insights and best/worst day.</p>
            </div>
            <label class="toggle">
                <input type="checkbox" name="weekly_report" value="1"
                    {{ ($settings['weekly_report'] ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="toggle-wrap">
            <div class="toggle-info">
                <h4>Burnout alerts</h4>
                <p>Email notification when 3+ consecutive heavy days are detected.</p>
            </div>
            <label class="toggle">
                <input type="checkbox" name="burnout_alerts" value="1"
                    {{ ($settings['burnout_alerts'] ?? true) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>

        <div class="toggle-wrap">
            <div class="toggle-info">
                <h4>Email notifications</h4>
                <p>General product updates and tips from PulseWork.</p>
            </div>
            <label class="toggle">
                <input type="checkbox" name="email_notifications" value="1"
                    {{ ($settings['email_notifications'] ?? false) ? 'checked' : '' }}>
                <span class="toggle-slider"></span>
            </label>
        </div>
    </div>
</div>

{{-- ── Appearance ───────────────────────────────────────────── --}}
<div class="card">
    <p class="section-title">Appearance</p>
    <p class="section-desc">Colour theme preference. You can also toggle this from the navbar.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <button type="button" id="set-dark"
            onclick="setTheme('dark')"
            style="padding:14px;border-radius:12px;border:1.5px solid var(--border);background:#050c1a;color:#f1f5f9;cursor:pointer;transition:all .15s;font-size:13px;font-weight:500;display:flex;align-items:center;justify-content:center;gap:8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
            Dark mode
        </button>
        <button type="button" id="set-light"
            onclick="setTheme('light')"
            style="padding:14px;border-radius:12px;border:1.5px solid var(--border);background:#f0f4f8;color:#0f172a;cursor:pointer;transition:all .15s;font-size:13px;font-weight:500;display:flex;align-items:center;justify-content:center;gap:8px;">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/></svg>
            Light mode
        </button>
    </div>
</div>

{{-- Save --}}
<div style="display:flex;justify-content:flex-end;margin-top:8px;">
    <button type="submit" class="btn-primary">Save all settings</button>
</div>

</form>

@endsection

@push('scripts')
<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('pw-theme', theme);
    // Highlight active button
    document.getElementById('set-dark').style.borderColor  = theme === 'dark'  ? '#3b82f6' : 'var(--border)';
    document.getElementById('set-light').style.borderColor = theme === 'light' ? '#3b82f6' : 'var(--border)';
}

// Highlight current on load
const cur = localStorage.getItem('pw-theme') || 'dark';
setTheme(cur);
</script>
@endpush