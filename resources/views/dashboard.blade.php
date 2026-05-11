{{-- resources/views/dashboard.blade.php --}}
@extends('layouts.app')
@section('title', 'Dashboard')
@php $active = 'dashboard'; @endphp

@section('content')
<style>
.card { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:20px; transition:background .2s; }
.ring-fill { fill:none; stroke-width:8; stroke-linecap:round; transform:rotate(-90deg); transform-origin:50% 50%; transition:stroke-dashoffset 1.4s cubic-bezier(.4,0,.2,1); }
.ring-track { fill:none; stroke-width:8; }
.sub-bar  { height:4px; border-radius:2px; background:rgba(255,255,255,0.07); overflow:hidden; margin-top:4px; }
html[data-theme="light"] .sub-bar { background:rgba(0,0,0,0.08); }
.sub-fill { height:100%; border-radius:2px; transition:width 1.2s cubic-bezier(.4,0,.2,1); }
.meeting-block { background:linear-gradient(90deg,#2563eb,#6366f1); border-radius:6px; padding:8px 12px; transition:transform .15s; }
.meeting-block:hover { transform:scaleX(1.01); }
.insight-pill { display:flex; align-items:flex-start; gap:8px; border-radius:10px; padding:10px 12px; font-size:13px; line-height:1.5; margin-bottom:8px; }
.ip-success { background:rgba(52,211,153,0.08);  border:1px solid rgba(52,211,153,0.2);  color:#6ee7b7; }
.ip-warning { background:rgba(251,191,36,0.08);  border:1px solid rgba(251,191,36,0.2);  color:#fde68a; }
.ip-danger  { background:rgba(239,68,68,0.08);   border:1px solid rgba(239,68,68,0.2);   color:#fca5a5; }
.ip-info    { background:rgba(96,165,250,0.08);  border:1px solid rgba(96,165,250,0.2);  color:#93c5fd; }
.ip-tip     { background:rgba(167,139,250,0.08); border:1px solid rgba(167,139,250,0.2); color:#c4b5fd; }
html[data-theme="light"] .ip-success { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
html[data-theme="light"] .ip-warning { background:#fffbeb; border-color:#fde68a; color:#92400e; }
html[data-theme="light"] .ip-danger  { background:#fef2f2; border-color:#fecaca; color:#991b1b; }
html[data-theme="light"] .ip-info    { background:#eff6ff; border-color:#bfdbfe; color:#1e40af; }
html[data-theme="light"] .ip-tip     { background:#faf5ff; border-color:#e9d5ff; color:#6b21a8; }
.stat-label { font-size:11px; font-weight:500; text-transform:uppercase; letter-spacing:.05em; color:var(--text-ter); margin-bottom:8px; }
.stat-num   { font-family:'Syne',sans-serif; font-size:28px; font-weight:700; color:var(--text-pri); line-height:1; }
.stat-sub   { font-size:12px; color:var(--text-ter); margin-top:6px; }
.stat-icon  { width:36px; height:36px; border-radius:9px; display:flex; align-items:center; justify-content:center; }
.focus-pill { background:rgba(52,211,153,0.08); border:1px solid rgba(52,211,153,0.2); color:#34d399; border-radius:8px; padding:8px 12px; font-size:13px; display:flex; align-items:center; gap:8px; margin-bottom:8px; }
html[data-theme="light"] .focus-pill { background:#f0fdf4; border-color:#bbf7d0; color:#166534; }
@keyframes burnoutPulse { 0%,100%{opacity:1} 50%{opacity:.7} }
.burnout-banner { animation:burnoutPulse 2.5s ease infinite; background:rgba(239,68,68,0.08); border:1px solid rgba(239,68,68,0.25); border-radius:12px; padding:12px 16px; display:flex; align-items:center; gap:12px; margin-bottom:20px; }
html[data-theme="light"] .burnout-banner { background:#fef2f2; border-color:#fecaca; }
.connect-card { background:var(--bg-card); border:1px solid var(--border); border-radius:20px; padding:48px; text-align:center; max-width:400px; margin:60px auto; }
.chart-wrap { position:relative; height:180px; }
.grid-4 { display:grid; grid-template-columns:repeat(4,1fr); gap:14px; }
.grid-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }
@media(max-width:900px){ .grid-4{grid-template-columns:1fr 1fr;} .grid-3{grid-template-columns:1fr;} }
@media(max-width:600px){ .grid-4{grid-template-columns:1fr;} .grid-2{grid-template-columns:1fr;} }
/* Historical sync panel */
.hist-hidden { display:none !important; }
.hist-date-input {
    background:var(--bg-card); border:1px solid var(--border);
    border-radius:8px; padding:8px 12px; font-size:13px;
    color:var(--text-pri); outline:none;
    font-family:'DM Sans',sans-serif; transition:border-color .15s;
}
.hist-date-input:focus { border-color:rgba(96,165,250,0.5); }
.hist-preset-btn {
    padding:5px 10px; border-radius:6px; font-size:12px;
    border:1px solid var(--border); background:transparent;
    color:var(--text-sec); cursor:pointer;
    font-family:'DM Sans',sans-serif; transition:all .15s;
}
.hist-preset-btn:hover { border-color:rgba(96,165,250,0.4); color:var(--text-pri); }
</style>

{{-- ══ NOT CONNECTED ══════════════════════════════════════════ --}}
@if(! isset($token) || ! $token)
<div class="connect-card">
    <div style="width:56px;height:56px;background:rgba(37,99,235,0.1);border:1px solid rgba(37,99,235,0.25);border-radius:14px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
        <svg width="26" height="26" fill="none" stroke="#60a5fa" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <h2 style="font-size:18px;font-weight:700;margin-bottom:8px;color:var(--text-pri);">Connect Outlook to begin</h2>
    <p style="font-size:14px;color:var(--text-sec);margin-bottom:24px;line-height:1.6;">Connect your Microsoft account to start analysing email and calendar productivity patterns.</p>
    <a href="{{ route('auth.microsoft') }}"
       style="display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:white;padding:11px 20px;border-radius:10px;font-size:14px;font-weight:500;text-decoration:none;transition:background .15s;"
       onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
        <svg width="16" height="16" viewBox="0 0 21 21"><rect x="1" y="1" width="9" height="9" fill="#fff" opacity=".9"/><rect x="11" y="1" width="9" height="9" fill="#fff" opacity=".7"/><rect x="1" y="11" width="9" height="9" fill="#fff" opacity=".7"/><rect x="11" y="11" width="9" height="9" fill="#fff" opacity=".9"/></svg>
        Connect Microsoft Outlook
    </a>
    <p style="font-size:12px;color:var(--text-ter);margin-top:16px;">Email content is never read or stored.</p>
</div>

@else

{{-- ══ BURNOUT ALERT ══════════════════════════════════════════ --}}
@if(isset($burnout) && $burnout)
<div class="burnout-banner" role="alert">
    <span style="font-size:20px;" aria-hidden="true">🚨</span>
    <div style="flex:1;">
        <p style="font-size:13px;font-weight:600;color:#fca5a5;margin-bottom:2px;">Burnout Risk Detected</p>
        <p style="font-size:13px;color:var(--text-sec);">{{ $burnout['reason'] }}</p>
    </div>
    <span style="font-size:11px;background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.3);border-radius:100px;padding:3px 10px;white-space:nowrap;">
        Severity {{ $burnout['severity'] }}/3
    </span>
</div>
@endif

{{-- ══ HEADER ═════════════════════════════════════════════════ --}}
<div style="margin-bottom:20px;">
    <h1 style="font-size:20px;font-weight:700;color:var(--text-pri);">
        Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 17 ? 'afternoon' : 'evening') }},
        {{ explode(' ', auth()->user()->name)[0] }} 👋
    </h1>
    <p style="font-size:13px;color:var(--text-sec);margin-top:3px;">
        {{ now()->format('l, F j, Y') }}
        @if(auth()->user()->last_synced_at)
            · Last synced {{ auth()->user()->last_synced_at->diffForHumans() }}
        @endif
    </p>
</div>

{{-- ══ HISTORICAL SYNC PANEL ══════════════════════════════════ --}}
<div style="margin-bottom:20px;">
    <button type="button"
        onclick="document.getElementById('hist-panel').classList.toggle('hist-hidden');document.getElementById('hist-chevron').style.transform=document.getElementById('hist-panel').classList.contains('hist-hidden')?'rotate(0deg)':'rotate(180deg)'"
        style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--text-sec);background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:6px 12px;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif;"
        onmouseover="this.style.color='var(--text-pri)'"
        onmouseout="this.style.color='var(--text-sec)'">
        <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        Sync historical data
        <svg width="11" height="11" id="hist-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="transition:transform .2s;">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    <div id="hist-panel" class="hist-hidden"
         style="margin-top:10px;background:var(--bg-card);border:1px solid var(--border);border-radius:14px;padding:20px;">

        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin-bottom:16px;">
            <div>
                <p style="font-size:14px;font-weight:500;color:var(--text-pri);margin-bottom:4px;">Sync past data</p>
                <p style="font-size:12px;color:var(--text-ter);line-height:1.6;max-width:480px;">
                    Fetch up to <strong style="font-weight:500;color:var(--text-sec);">90 days</strong> of historical
                    email and calendar metadata from Microsoft. Great for populating your dashboard on first use.
                    Empty days (weekends with no activity) are automatically skipped.
                </p>
            </div>
            <div style="flex-shrink:0;background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.2);border-radius:8px;padding:6px 10px;font-size:11px;color:#fbbf24;white-space:nowrap;">
                ⚠ Takes 1–3 min for 90 days
            </div>
        </div>

        <form method="POST" action="{{ route('dashboard.sync-historical') }}" id="hist-form">
            @csrf

            {{-- Date inputs --}}
            <div style="display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;margin-bottom:12px;">
                <div>
                    <label style="display:block;font-size:11px;font-weight:500;color:var(--text-ter);text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;">From date</label>
                    <input type="date" name="from_date" id="hist-from" required
                           class="hist-date-input"
                           max="{{ now()->subDay()->toDateString() }}"
                           min="{{ now()->subDays(90)->toDateString() }}"
                           onchange="histUpdateCount()">
                </div>
                <div>
                    <label style="display:block;font-size:11px;font-weight:500;color:var(--text-ter);text-transform:uppercase;letter-spacing:.04em;margin-bottom:5px;">To date</label>
                    <input type="date" name="to_date" id="hist-to" required
                           class="hist-date-input"
                           max="{{ now()->toDateString() }}"
                           min="{{ now()->subDays(90)->toDateString() }}"
                           onchange="histUpdateCount()">
                </div>
            </div>

            {{-- Preset buttons --}}
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:12px;">
                <span style="font-size:11px;color:var(--text-ter);">Quick select:</span>
                <button type="button" class="hist-preset-btn" onclick="histPreset(7)">Last 7 days</button>
                <button type="button" class="hist-preset-btn" onclick="histPreset(30)">Last 30 days</button>
                <button type="button" class="hist-preset-btn" onclick="histPreset(60)">Last 60 days</button>
                <button type="button" class="hist-preset-btn" onclick="histPreset(90)">Last 90 days</button>
            </div>

            {{-- Day count preview --}}
            <p id="hist-count" style="font-size:12px;color:var(--text-ter);margin-bottom:14px;min-height:18px;"></p>

            {{-- Submit --}}
            <button type="submit" id="hist-submit"
                style="display:inline-flex;align-items:center;gap:7px;padding:9px 20px;border-radius:9px;font-size:13px;font-weight:500;background:#2563eb;color:white;border:none;cursor:pointer;transition:all .15s;font-family:'DM Sans',sans-serif;"
                onmouseover="this.style.background='#1d4ed8'"
                onmouseout="this.style.background='#2563eb'">
                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Start historical sync
            </button>
        </form>
    </div>
</div>

{{-- ══ ROW 1 — Score + Stats ══════════════════════════════════ --}}
<div class="grid-4" style="margin-bottom:14px;">
    {{-- Score ring --}}
    <div class="card" style="display:flex;flex-direction:column;align-items:center;justify-content:center;gap:4px;">
        <p class="stat-label">Today's Score</p>
        <div style="position:relative;width:110px;height:110px;">
            <svg style="width:100%;height:100%;" viewBox="0 0 100 100">
                <circle class="ring-track" cx="50" cy="50" r="42" stroke="rgba(255,255,255,0.07)"/>
                <circle class="ring-fill" cx="50" cy="50" r="42"
                    stroke="{{ ($score??0) >= 70 ? '#4ade80' : (($score??0) >= 50 ? '#60a5fa' : (($score??0) >= 30 ? '#fbbf24' : '#f87171')) }}"
                    stroke-dasharray="263.9"
                    stroke-dashoffset="{{ 263.9 - (263.9 * (($score??0) / 100)) }}"/>
            </svg>
            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;">
                <span style="font-family:'Syne',sans-serif;font-size:26px;font-weight:700;color:var(--text-pri);">{{ $score ?? 0 }}</span>
                <span style="font-size:11px;color:var(--text-ter);">/100</span>
            </div>
        </div>
        <p style="font-size:13px;font-weight:500;color:{{ ($score??0) >= 70 ? '#4ade80' : (($score??0) >= 50 ? '#60a5fa' : (($score??0) >= 30 ? '#fbbf24' : '#f87171')) }};">
            {{ ($score??0) >= 70 ? 'Excellent' : (($score??0) >= 50 ? 'Good' : (($score??0) >= 30 ? 'Fair' : 'Needs Attention')) }}
        </p>
        @if(isset($scores) && count($scores) > 0)
        <div style="width:100%;margin-top:8px;">
            @foreach([['Email',$scores['email_score']??0,'#60a5fa'],['Calendar',$scores['calendar_score']??0,'#a78bfa'],['Balance',$scores['balance_score']??0,'#34d399']] as [$lbl,$val,$clr])
            <div style="margin-bottom:6px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
                    <span style="font-size:10px;color:var(--text-ter);">{{ $lbl }}</span>
                    <span style="font-size:10px;color:var(--text-ter);">{{ $val }}</span>
                </div>
                <div class="sub-bar"><div class="sub-fill" style="width:{{ $val }}%;background:{{ $clr }};"></div></div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Emails --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
            <p class="stat-label">Emails</p>
            <div class="stat-icon" style="background:rgba(59,130,246,0.1);">
                <svg width="16" height="16" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <p class="stat-num">{{ $todayEmail?->received_count ?? '—' }}</p>
        <p class="stat-sub">received today</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
            <div style="text-align:center;">
                <p style="font-size:14px;font-weight:600;color:var(--text-pri);">{{ $todayEmail?->sent_count ?? 0 }}</p>
                <p style="font-size:11px;color:var(--text-ter);">sent</p>
            </div>
            <div style="text-align:center;">
                <p style="font-size:14px;font-weight:600;color:{{ ($todayEmail?->after_hours_count ?? 0) > 5 ? '#fbbf24' : 'var(--text-pri)' }};">{{ $todayEmail?->after_hours_count ?? 0 }}</p>
                <p style="font-size:11px;color:var(--text-ter);">after-hrs</p>
            </div>
        </div>
    </div>

    {{-- Meetings --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
            <p class="stat-label">Meetings</p>
            <div class="stat-icon" style="background:rgba(99,102,241,0.1);">
                <svg width="16" height="16" fill="none" stroke="#a78bfa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <p class="stat-num">{{ $todayCalendar ? round($todayCalendar->meeting_minutes / 60, 1) : '—' }}<span style="font-size:14px;font-weight:400;color:var(--text-ter);">hrs</span></p>
        <p class="stat-sub">{{ $todayCalendar?->total_meetings ?? 0 }} meetings today</p>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
            <div style="text-align:center;">
                <p style="font-size:14px;font-weight:600;color:{{ ($todayCalendar?->back_to_back_count ?? 0) > 1 ? '#fbbf24' : 'var(--text-pri)' }};">{{ $todayCalendar?->back_to_back_count ?? 0 }}</p>
                <p style="font-size:11px;color:var(--text-ter);">back-to-back</p>
            </div>
            <div style="text-align:center;">
                <p style="font-size:14px;font-weight:600;color:{{ ($todayCalendar?->after_hours_meetings ?? 0) > 0 ? '#f87171' : 'var(--text-pri)' }};">{{ $todayCalendar?->after_hours_meetings ?? 0 }}</p>
                <p style="font-size:11px;color:var(--text-ter);">after-hrs</p>
            </div>
        </div>
    </div>

    {{-- Focus --}}
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:10px;">
            <p class="stat-label">Focus Time</p>
            <div class="stat-icon" style="background:rgba(52,211,153,0.1);">
                <svg width="16" height="16" fill="none" stroke="#34d399" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="stat-num">{{ $todayCalendar ? round($todayCalendar->focus_time_minutes / 60, 1) : '—' }}<span style="font-size:14px;font-weight:400;color:var(--text-ter);">hrs</span></p>
        <p class="stat-sub">uninterrupted blocks</p>
        <div style="margin-top:12px;padding-top:12px;border-top:1px solid var(--border);">
            <p style="font-size:11px;color:var(--text-ter);">Avg response time</p>
            <p style="font-size:14px;font-weight:500;color:var(--text-pri);margin-top:2px;">{{ $todayEmail?->avg_response_hours ? $todayEmail->avg_response_hours . ' hrs' : '—' }}</p>
        </div>
    </div>
</div>

{{-- ══ ROW 2 — Charts ═════════════════════════════════════════ --}}
<div class="grid-2" style="margin-bottom:14px;">
    <div class="card">
        <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:2px;">Email Load — 7 days</h3>
        <p style="font-size:11px;color:var(--text-ter);margin-bottom:14px;">Received vs sent</p>
        <div class="chart-wrap"><canvas id="emailChart"></canvas></div>
    </div>
    <div class="card">
        <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:2px;">Email Activity by Hour — Today</h3>
        <p style="font-size:11px;color:var(--text-ter);margin-bottom:14px;">Red = outside work hours</p>
        <div class="chart-wrap"><canvas id="hourlyChart"></canvas></div>
    </div>
    <div class="card">
        <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:2px;">Time Distribution — Week</h3>
        <p style="font-size:11px;color:var(--text-ter);margin-bottom:14px;">Meeting vs focus (hours)</p>
        <div class="chart-wrap"><canvas id="donutChart"></canvas></div>
    </div>
    <div class="card">
        <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:2px;">Productivity Score — 7 days</h3>
        <p style="font-size:11px;color:var(--text-ter);margin-bottom:14px;">Daily 0–100 trend</p>
        <div class="chart-wrap"><canvas id="scoreChart"></canvas></div>
    </div>
</div>

{{-- ══ ROW 3 — Calendar + Insights + Focus Windows ═══════════ --}}
<div class="grid-3" style="margin-bottom:14px;">
    {{-- Calendar --}}
    <div class="card">
        <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:14px;">Today's Calendar</h3>
        @php $slots = $todayCalendar?->meeting_slots ?? []; @endphp
        @if(count($slots) > 0)
            <div style="max-height:260px;overflow-y:auto;padding-right:4px;display:flex;flex-direction:column;gap:8px;">
                @foreach($slots as $slot)
                <div style="display:flex;gap:10px;align-items:flex-start;">
                    <div style="text-align:right;flex-shrink:0;min-width:44px;">
                        <p style="font-size:11px;font-family:'DM Mono',monospace;color:var(--text-ter);">{{ $slot['start'] }}</p>
                        <p style="font-size:10px;font-family:'DM Mono',monospace;color:var(--text-ter);opacity:.6;">{{ $slot['end'] }}</p>
                    </div>
                    <div class="meeting-block" style="flex:1;">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:6px;">
                            <p style="font-size:12px;font-weight:500;color:white;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ \Illuminate\Support\Str::limit($slot['title'] ?? 'Meeting', 28) }}</p>
                            @if($slot['online'] ?? false)<span style="font-size:9px;background:rgba(52,211,153,0.2);color:#34d399;border-radius:4px;padding:1px 5px;flex-shrink:0;">online</span>@endif
                        </div>
                        <p style="font-size:11px;color:rgba(255,255,255,0.5);margin-top:2px;">{{ $slot['duration'] }}min</p>
                    </div>
                </div>
                @endforeach
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:6px;margin-top:14px;padding-top:12px;border-top:1px solid var(--border);text-align:center;">
                <div><p style="font-size:14px;font-weight:600;color:var(--text-pri);">{{ $todayCalendar->total_meetings }}</p><p style="font-size:10px;color:var(--text-ter);">meetings</p></div>
                <div><p style="font-size:14px;font-weight:600;color:{{ $todayCalendar->back_to_back_count > 1 ? '#fbbf24' : 'var(--text-pri)' }};">{{ $todayCalendar->back_to_back_count }}</p><p style="font-size:10px;color:var(--text-ter);">back-to-back</p></div>
                <div><p style="font-size:14px;font-weight:600;color:{{ $todayCalendar->after_hours_meetings > 0 ? '#f87171' : 'var(--text-pri)' }};">{{ $todayCalendar->after_hours_meetings }}</p><p style="font-size:10px;color:var(--text-ter);">after-hrs</p></div>
            </div>
        @else
            <div style="text-align:center;padding:32px 0;color:var(--text-ter);">
                <svg width="36" height="36" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="margin:0 auto 8px;opacity:.3;display:block;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p style="font-size:13px;">No meetings today</p>
                <p style="font-size:12px;margin-top:4px;opacity:.6;">Great day for deep work!</p>
            </div>
        @endif
    </div>

    {{-- Insights --}}
    <div class="card">
        <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:14px;">Insights</h3>
        @if(isset($insights) && count($insights) > 0)
            <div style="max-height:320px;overflow-y:auto;padding-right:4px;">
                @foreach($insights as $insight)
                <div class="insight-pill ip-{{ $insight['type'] ?? 'info' }}">
                    <span style="font-size:15px;flex-shrink:0;">{{ $insight['icon'] ?? '·' }}</span>
                    <span>{{ $insight['text'] }}</span>
                </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:32px 0;color:var(--text-ter);">
                <p style="font-size:13px;">Sync your data to see personalised insights.</p>
            </div>
        @endif
    </div>

    {{-- Focus + Weekly --}}
    <div style="display:flex;flex-direction:column;gap:14px;">
        <div class="card" style="flex:1;">
            <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:4px;">Best Focus Windows</h3>
            <p style="font-size:11px;color:var(--text-ter);margin-bottom:12px;">Least meeting-heavy 2hr blocks this week</p>
            @if(isset($focusWindows) && count($focusWindows) > 0)
                @foreach($focusWindows as $window)
                <div class="focus-pill">
                    <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $window }}
                </div>
                @endforeach
                <p style="font-size:11px;color:var(--text-ter);margin-top:6px;">→ Block these for deep work</p>
            @else
                <p style="font-size:12px;color:var(--text-ter);">Sync more days to get recommendations.</p>
            @endif
        </div>

        @if(isset($weeklyScore) && $weeklyScore)
        <div class="card">
            <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:12px;">This Week</h3>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:12px;">
                <div style="background:rgba(52,211,153,0.06);border:1px solid rgba(52,211,153,0.15);border-radius:10px;padding:10px;text-align:center;">
                    <p style="font-size:10px;color:#34d399;margin-bottom:4px;">Best day</p>
                    <p style="font-size:13px;font-weight:600;color:#6ee7b7;">{{ $weeklyScore->best_day ?? '—' }}</p>
                </div>
                <div style="background:rgba(239,68,68,0.06);border:1px solid rgba(239,68,68,0.15);border-radius:10px;padding:10px;text-align:center;">
                    <p style="font-size:10px;color:#f87171;margin-bottom:4px;">Worst day</p>
                    <p style="font-size:13px;font-weight:600;color:#fca5a5;">{{ $weeklyScore->worst_day ?? '—' }}</p>
                </div>
            </div>
            <div style="text-align:center;">
                <p style="font-size:11px;color:var(--text-ter);">Weekly avg score</p>
                <p style="font-family:'Syne',sans-serif;font-size:24px;font-weight:700;color:var(--text-pri);">{{ $weeklyScore->score }}<span style="font-size:13px;color:var(--text-ter);">/100</span></p>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ══ ROW 4 — 4-week trend ═══════════════════════════════════ --}}
@if(isset($trendScores) && $trendScores->count() > 0)
<div class="card" style="margin-bottom:14px;">
    <h3 style="font-size:13px;font-weight:600;color:var(--text-pri);margin-bottom:2px;">4-Week Productivity Trend</h3>
    <p style="font-size:11px;color:var(--text-ter);margin-bottom:14px;">Weekly average score</p>
    <div style="height:110px;"><canvas id="trendChart"></canvas></div>
</div>
@endif

<!-- {{-- ══ DEBUG ══════════════════════════════════════════════════ --}}
@if(config('app.debug'))
<div style="background:rgba(0,0,0,0.3);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:16px;margin-top:14px;font-size:11px;font-family:'DM Mono',monospace;color:#475569;">
    <p style="color:#334155;margin-bottom:8px;font-weight:500;">Debug</p>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;">
        <span>Token expires:</span><span>{{ $token->expires_at?->toDateTimeString() ?? 'N/A' }}</span><span></span>
        <span>Scopes:</span><span style="grid-column:span 2;">{{ is_array($token->scopes) ? implode(', ', $token->scopes) : 'N/A' }}</span>
        <span>Last synced:</span><span>{{ auth()->user()->last_synced_at?->toDateTimeString() ?? 'Never' }}</span><span></span>
        <span>Score:</span><span>{{ $score ?? 0 }}/100</span><span></span>
    </div>
</div>
@endif -->

@endif {{-- end token check --}}

@endsection

@push('scripts')
<script>
// ── Historical sync JS ─────────────────────────────────────────
function histPreset(days) {
    const today     = new Date();
    const from      = new Date();
    from.setDate(today.getDate() - days);
    const yesterday = new Date();
    yesterday.setDate(today.getDate() - 1);
    const fmt = d => d.toISOString().split('T')[0];
    document.getElementById('hist-from').value = fmt(from);
    document.getElementById('hist-to').value   = fmt(yesterday);
    histUpdateCount();
}

function histUpdateCount() {
    const from = document.getElementById('hist-from').value;
    const to   = document.getElementById('hist-to').value;
    const msg  = document.getElementById('hist-count');
    if (!from || !to) { msg.textContent = ''; return; }
    const days = Math.round((new Date(to) - new Date(from)) / 86400000) + 1;
    if (days < 1)  { msg.textContent = '⚠ "To" date must be after "From" date.'; msg.style.color = '#f87171'; return; }
    if (days > 90) { msg.textContent = '⚠ Maximum 90 days. Please shorten the range.'; msg.style.color = '#f87171'; return; }
    const est = days <= 7 ? 'under 30 seconds' : days <= 30 ? 'about 1 minute' : days <= 60 ? 'about 2 minutes' : 'up to 3 minutes';
    msg.textContent = `${days} days selected — estimated time: ${est}. Do not close the page.`;
    msg.style.color = 'var(--text-ter)';
}

document.getElementById('hist-form')?.addEventListener('submit', function(e) {
    const from = document.getElementById('hist-from').value;
    const to   = document.getElementById('hist-to').value;
    if (!from || !to) { e.preventDefault(); return; }
    const days = Math.round((new Date(to) - new Date(from)) / 86400000) + 1;
    if (days > 90 || days < 1) { e.preventDefault(); return; }
    const btn = document.getElementById('hist-submit');
    btn.disabled = true;
    btn.innerHTML = `<svg width="14" height="14" style="animation:spin .8s linear infinite" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Syncing ${days} days — please wait…`;
    btn.style.opacity = '0.75';
});

// ── Charts ─────────────────────────────────────────────────────
const isDark  = document.documentElement.getAttribute('data-theme') !== 'light';
const gridClr = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.05)';
Chart.defaults.color = isDark ? '#64748b' : '#6b7280';
Chart.defaults.font  = { family:"'DM Sans',sans-serif", size:11 };

const labels      = @json($chartLabels ?? []);
const received    = @json($chartReceived ?? []);
const sent        = @json($chartSent ?? []);
const meetingMins = @json($chartMeetingMins ?? []);
const focusMins   = @json($chartFocusMins ?? []);
const scores      = @json($chartScores ?? []);
const hourly      = @json($hourlyDist ?? array_fill(0, 24, 0));
const trendLabels = @json($trendLabels ?? []);
const trendScores = @json($trendScores ?? []);
const hourLabels  = Array.from({length:24},(_,i)=>i===0?'12a':(i<12?i+'a':(i===12?'12p':(i-12)+'p')));

const noGrid = {display:false};
const grid   = {color:gridClr,drawBorder:false};
const base   = {responsive:true,maintainAspectRatio:false};

new Chart(document.getElementById('emailChart'),{type:'bar',data:{labels,datasets:[
    {label:'Received',data:received,backgroundColor:'#3b82f6',borderRadius:{topLeft:3,topRight:3},borderSkipped:false},
    {label:'Sent',data:sent,backgroundColor:isDark?'#1e3a5f':'#bfdbfe',borderRadius:{topLeft:3,topRight:3},borderSkipped:false},
]},options:{...base,plugins:{legend:{position:'bottom',labels:{boxWidth:8,padding:12}}},scales:{x:{grid:noGrid,border:{display:false}},y:{grid,border:{display:false},beginAtZero:true}}}});

new Chart(document.getElementById('hourlyChart'),{type:'bar',data:{labels:hourLabels,datasets:[{data:hourly,backgroundColor:hourly.map((_,i)=>(i<9||i>=18)?'rgba(239,68,68,0.6)':'rgba(99,102,241,0.7)'),borderRadius:2}]},options:{...base,plugins:{legend:{display:false}},scales:{x:{grid:noGrid,border:{display:false},ticks:{maxRotation:0,font:{size:9}}},y:{grid,border:{display:false},beginAtZero:true}}}});

const totM=meetingMins.reduce((a,b)=>a+b,0)/60,totF=focusMins.reduce((a,b)=>a+b,0)/60,totO=Math.max(0,40-totM-totF);
new Chart(document.getElementById('donutChart'),{type:'doughnut',data:{labels:['Meetings','Focus','Other'],datasets:[{data:[+totM.toFixed(1),+totF.toFixed(1),+totO.toFixed(1)],backgroundColor:['#6366f1','#10b981',isDark?'#1e293b':'#e2e8f0'],borderWidth:0,hoverOffset:4}]},options:{...base,cutout:'68%',plugins:{legend:{position:'bottom',labels:{boxWidth:8,padding:12}},tooltip:{callbacks:{label:ctx=>` ${ctx.parsed.toFixed(1)} hrs`}}}}});

new Chart(document.getElementById('scoreChart'),{type:'line',data:{labels,datasets:[{data:scores,borderColor:'#a78bfa',backgroundColor:'rgba(167,139,250,0.07)',fill:true,tension:.4,pointBackgroundColor:'#a78bfa',pointRadius:3}]},options:{...base,plugins:{legend:{display:false}},scales:{x:{grid:noGrid,border:{display:false}},y:{grid,border:{display:false},min:0,max:100}}}});

const tEl=document.getElementById('trendChart');
if(tEl&&trendLabels.length>0){new Chart(tEl,{type:'line',data:{labels:trendLabels,datasets:[{data:trendScores,borderColor:'#34d399',backgroundColor:'rgba(52,211,153,0.06)',fill:true,tension:.4,pointBackgroundColor:'#34d399',pointRadius:4}]},options:{...base,plugins:{legend:{display:false}},scales:{x:{grid:noGrid,border:{display:false}},y:{grid,border:{display:false},min:0,max:100}}}});}
</script>
@endpush