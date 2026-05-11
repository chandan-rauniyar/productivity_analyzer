{{-- resources/views/history.blade.php --}}
@extends('layouts.app')
@section('title', 'History & Trends')
@php $active = 'history'; @endphp

@section('content')
<style>
.card { background:var(--bg-card); border:1px solid var(--border); border-radius:16px; padding:24px; }
.stat-chip { background:var(--bg-card); border:1px solid var(--border); border-radius:14px; padding:20px; text-align:center; }
.chart-wrap { position:relative; height:200px; }
.badge { display:inline-block; font-size:11px; font-weight:500; padding:2px 8px; border-radius:20px; }
.badge-good    { background:rgba(52,211,153,0.12); color:#34d399; }
.badge-fair    { background:rgba(251,191,36,0.12); color:#fbbf24; }
.badge-poor    { background:rgba(239,68,68,0.12);  color:#f87171; }
.week-row { display:grid; grid-template-columns:1fr 60px 80px 80px 80px; gap:12px; align-items:center;
            padding:12px 0; border-bottom:1px solid var(--border); font-size:13px; }
.week-row:last-child { border-bottom:none; }
</style>

{{-- ── Header ─────────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:var(--text-pri);">History & Trends</h1>
        <p style="font-size:13px;color:var(--text-sec);margin-top:2px;">Last 30 days of productivity data</p>
    </div>
    <a href="{{ route('dashboard') }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--text-sec);text-decoration:none;
              background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:7px 14px;transition:all .15s;"
       onmouseover="this.style.color='var(--text-pri)'" onmouseout="this.style.color='var(--text-sec)'">
        ← Dashboard
    </a>
</div>

{{-- ── Summary stat cards ─────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;">
    @foreach([
        ['Avg Score',        $avgScore,        '/100', $avgScore >= 70 ? '#4ade80' : ($avgScore >= 40 ? '#fbbf24' : '#f87171')],
        ['Best Score',       $bestScore,       '/100', '#4ade80'],
        ['Emails (30 days)', $totalReceived,   '',     '#60a5fa'],
        ['Meeting Hours',    $totalMeetingHrs, 'hrs',  '#a78bfa'],
    ] as [$label, $val, $unit, $color])
    <div class="stat-chip">
        <p style="font-size:11px;color:var(--text-ter);text-transform:uppercase;letter-spacing:.05em;margin-bottom:8px;">{{ $label }}</p>
        <p style="font-family:'Syne',sans-serif;font-size:26px;font-weight:700;color:{{ $color }};">
            {{ $val }}<span style="font-size:14px;font-weight:400;color:var(--text-ter);">{{ $unit }}</span>
        </p>
    </div>
    @endforeach
</div>

{{-- ── Charts row ──────────────────────────────────────────── --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">

    {{-- Score trend --}}
    <div class="card">
        <h3 style="font-size:14px;font-weight:600;color:var(--text-pri);margin-bottom:4px;">Productivity Score — 30 days</h3>
        <p style="font-size:12px;color:var(--text-ter);margin-bottom:16px;">Daily score trend</p>
        <div class="chart-wrap"><canvas id="scoreChart"></canvas></div>
    </div>

    {{-- Email volume --}}
    <div class="card">
        <h3 style="font-size:14px;font-weight:600;color:var(--text-pri);margin-bottom:4px;">Email Volume — 30 days</h3>
        <p style="font-size:12px;color:var(--text-ter);margin-bottom:16px;">Emails received per day</p>
        <div class="chart-wrap"><canvas id="emailChart"></canvas></div>
    </div>

    {{-- Meeting load --}}
    <div class="card">
        <h3 style="font-size:14px;font-weight:600;color:var(--text-pri);margin-bottom:4px;">Meeting Load — 30 days</h3>
        <p style="font-size:12px;color:var(--text-ter);margin-bottom:16px;">Minutes in meetings per day</p>
        <div class="chart-wrap"><canvas id="meetingChart"></canvas></div>
    </div>

    {{-- Weekly scores --}}
    <div class="card">
        <h3 style="font-size:14px;font-weight:600;color:var(--text-pri);margin-bottom:4px;">Weekly Score — 8 weeks</h3>
        <p style="font-size:12px;color:var(--text-ter);margin-bottom:16px;">Average score per week</p>
        <div class="chart-wrap"><canvas id="weekChart"></canvas></div>
    </div>
</div>

{{-- ── Weekly scores table ─────────────────────────────────── --}}
<div class="card">
    <h3 style="font-size:14px;font-weight:600;color:var(--text-pri);margin-bottom:16px;">Weekly Breakdown</h3>

    {{-- Header --}}
    <div class="week-row" style="border-bottom:1px solid var(--border);">
        <span style="font-size:11px;font-weight:600;color:var(--text-ter);text-transform:uppercase;letter-spacing:.05em;">Week</span>
        <span style="font-size:11px;font-weight:600;color:var(--text-ter);text-transform:uppercase;letter-spacing:.05em;text-align:center;">Score</span>
        <span style="font-size:11px;font-weight:600;color:var(--text-ter);text-transform:uppercase;letter-spacing:.05em;text-align:center;">Email</span>
        <span style="font-size:11px;font-weight:600;color:var(--text-ter);text-transform:uppercase;letter-spacing:.05em;text-align:center;">Calendar</span>
        <span style="font-size:11px;font-weight:600;color:var(--text-ter);text-transform:uppercase;letter-spacing:.05em;text-align:center;">Balance</span>
    </div>

    @forelse($weeklyScores as $ws)
    @php
        $badge = $ws->score >= 70 ? 'badge-good' : ($ws->score >= 40 ? 'badge-fair' : 'badge-poor');
    @endphp
    <div class="week-row">
        <div>
            <p style="font-size:13px;font-weight:500;color:var(--text-pri);">
                {{ $ws->week_start->format('M j') }} — {{ $ws->week_start->addDays(6)->format('M j, Y') }}
            </p>
            @if($ws->best_day)
                <p style="font-size:11px;color:var(--text-ter);margin-top:2px;">
                    Best: {{ $ws->best_day }} · Worst: {{ $ws->worst_day }}
                </p>
            @endif
        </div>
        <div style="text-align:center;">
            <span class="badge {{ $badge }}">{{ $ws->score }}</span>
        </div>
        <div style="text-align:center;font-size:13px;color:var(--text-sec);">{{ $ws->email_score ?? '—' }}</div>
        <div style="text-align:center;font-size:13px;color:var(--text-sec);">{{ $ws->calendar_score ?? '—' }}</div>
        <div style="text-align:center;font-size:13px;color:var(--text-sec);">{{ $ws->balance_score ?? '—' }}</div>
    </div>
    @empty
    <div style="text-align:center;padding:32px;color:var(--text-ter);">
        <p>No weekly data yet. Sync your data daily to build history.</p>
        <a href="{{ route('dashboard') }}" style="color:#60a5fa;font-size:13px;text-decoration:none;margin-top:8px;display:inline-block;">
            Go to dashboard →
        </a>
    </div>
    @endforelse
</div>

@endsection

@push('scripts')
<script>
const isDark  = document.documentElement.getAttribute('data-theme') !== 'light';
const gridClr = isDark ? 'rgba(255,255,255,0.04)' : 'rgba(0,0,0,0.06)';
Chart.defaults.color = isDark ? '#64748b' : '#6b7280';
Chart.defaults.font  = { family:"'DM Sans',sans-serif", size:11 };

const labels      = @json($chartLabels ?? []);
const scores      = @json($chartScores ?? []);
const received    = @json($chartReceived ?? []);
const meetingMins = @json($chartMeetingMins ?? []);
const weekLabels  = @json($weekLabels ?? []);
const weekScores  = @json($weekScores ?? []);

const noGrid = { display:false };
const grid   = { color: gridClr, drawBorder:false };
const base   = { responsive:true, maintainAspectRatio:false,
                 plugins:{ legend:{ display:false } } };

// Score line
new Chart(document.getElementById('scoreChart'), {
    type:'line', data:{
        labels,
        datasets:[{ data:scores, borderColor:'#a78bfa', backgroundColor:'rgba(167,139,250,0.08)',
                    fill:true, tension:.4, pointRadius:3, pointBackgroundColor:'#a78bfa' }]
    },
    options:{ ...base, scales:{
        x:{ grid:noGrid, border:{display:false} },
        y:{ grid, border:{display:false}, min:0, max:100 }
    }}
});

// Email bar
new Chart(document.getElementById('emailChart'), {
    type:'bar', data:{
        labels,
        datasets:[{ data:received, backgroundColor:'rgba(59,130,246,0.65)',
                    borderRadius:3, borderSkipped:false }]
    },
    options:{ ...base, scales:{
        x:{ grid:noGrid, border:{display:false} },
        y:{ grid, border:{display:false}, beginAtZero:true }
    }}
});

// Meeting bar
new Chart(document.getElementById('meetingChart'), {
    type:'bar', data:{
        labels,
        datasets:[{ data:meetingMins, backgroundColor:'rgba(99,102,241,0.65)',
                    borderRadius:3, borderSkipped:false }]
    },
    options:{ ...base, scales:{
        x:{ grid:noGrid, border:{display:false} },
        y:{ grid, border:{display:false}, beginAtZero:true }
    }}
});

// Weekly line
new Chart(document.getElementById('weekChart'), {
    type:'line', data:{
        labels: weekLabels,
        datasets:[{ data:weekScores, borderColor:'#34d399', backgroundColor:'rgba(52,211,153,0.08)',
                    fill:true, tension:.4, pointRadius:5, pointBackgroundColor:'#34d399' }]
    },
    options:{ ...base, scales:{
        x:{ grid:noGrid, border:{display:false} },
        y:{ grid, border:{display:false}, min:0, max:100 }
    }}
});
</script>
@endpush