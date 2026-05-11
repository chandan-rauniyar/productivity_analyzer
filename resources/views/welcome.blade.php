<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PulseWork — Understand How You Actually Work</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink:    #0a0f1e;
            --paper:  #f5f3ef;
            --accent: #2563eb;
            --warm:   #f59e0b;
            --muted:  #6b7280;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'DM Sans', sans-serif; background: var(--ink); color: var(--paper); overflow-x: hidden; }
        h1, h2, h3, h4, .display { font-family: 'Syne', sans-serif; }

        /* ── Noise texture overlay ── */
        body::before {
            content: '';
            position: fixed; inset: 0; pointer-events: none; z-index: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.04'/%3E%3C/svg%3E");
            opacity: 0.35;
        }

        /* ── Glow orbs ── */
        .orb { position: absolute; border-radius: 50%; filter: blur(80px); pointer-events: none; }
        .orb-1 { width: 600px; height: 600px; background: rgba(37,99,235,0.15); top: -200px; right: -100px; }
        .orb-2 { width: 400px; height: 400px; background: rgba(245,158,11,0.08); top: 40%; left: -100px; }
        .orb-3 { width: 500px; height: 500px; background: rgba(99,102,241,0.10); bottom: 0; right: 20%; }

        /* ── Animated hero text ── */
        @keyframes fadeUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp .8s cubic-bezier(.4,0,.2,1) both; }
        .delay-1 { animation-delay: .1s }
        .delay-2 { animation-delay: .2s }
        .delay-3 { animation-delay: .3s }
        .delay-4 { animation-delay: .45s }
        .delay-5 { animation-delay: .6s }

        /* ── Gradient text ── */
        .grad-text {
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 50%, #34d399 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .grad-warm {
            background: linear-gradient(90deg, #fbbf24, #f97316);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }

        /* ── Nav ── */
        nav { position: fixed; top: 0; left: 0; right: 0; z-index: 100;
              background: rgba(10,15,30,0.7); backdrop-filter: blur(16px);
              border-bottom: 1px solid rgba(255,255,255,0.06); }

        /* ── Buttons ── */
        .btn-primary {
            display: inline-flex; align-items: center; gap: 8px;
            background: #2563eb; color: white; padding: 12px 24px;
            border-radius: 10px; font-weight: 500; font-size: 15px;
            transition: all .2s; text-decoration: none; border: none; cursor: pointer;
        }
        .btn-primary:hover { background: #1d4ed8; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(37,99,235,0.35); }
        .btn-ghost {
            display: inline-flex; align-items: center; gap: 8px;
            background: transparent; color: rgba(245,243,239,0.7);
            padding: 12px 24px; border-radius: 10px; font-weight: 400; font-size: 15px;
            transition: all .2s; text-decoration: none;
            border: 1px solid rgba(255,255,255,0.12);
        }
        .btn-ghost:hover { background: rgba(255,255,255,0.06); color: white; }

        /* ── Feature card ── */
        .feat-card {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 16px; padding: 28px;
            transition: all .25s;
        }
        .feat-card:hover {
            background: rgba(255,255,255,0.06);
            border-color: rgba(255,255,255,0.14);
            transform: translateY(-3px);
        }
        .feat-icon {
            width: 44px; height: 44px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 16px;
        }

        /* ── Stat card ── */
        .stat-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 14px; padding: 24px; text-align: center;
        }

        /* ── How it works step ── */
        .step-num {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(37,99,235,0.15); border: 1px solid rgba(37,99,235,0.4);
            color: #60a5fa; font-family: 'Syne',sans-serif; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .step-line { width: 1px; background: rgba(37,99,235,0.25); flex: 1; min-height: 40px; margin: 4px auto; }

        /* ── Testimonial ── */
        .testi {
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.07);
            border-radius: 14px; padding: 24px;
        }

        /* ── Pricing card ── */
        .price-card {
            border-radius: 16px; padding: 32px;
            background: rgba(255,255,255,0.03);
            border: 1px solid rgba(255,255,255,0.08);
        }
        .price-card.featured {
            background: rgba(37,99,235,0.12);
            border-color: rgba(37,99,235,0.4);
        }

        /* ── FAQ ── */
        details summary { cursor: pointer; list-style: none; }
        details summary::-webkit-details-marker { display: none; }
        details[open] summary .faq-icon { transform: rotate(45deg); }
        .faq-icon { transition: transform .2s; }

        /* ── Footer ── */
        footer { border-top: 1px solid rgba(255,255,255,0.06); }

        /* ── Dashboard mockup ── */
        .mockup-card {
            background: rgba(15,23,42,0.8);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px; padding: 16px;
        }
        .mock-bar { height: 8px; border-radius: 4px; }
        .mock-ring { width: 80px; height: 80px; }

        /* ── Scroll reveal ── */
        .reveal { opacity: 0; transform: translateY(20px); transition: all .6s cubic-bezier(.4,0,.2,1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        section { position: relative; z-index: 1; }
    </style>
</head>
<body>

<!-- ════════ NAVBAR ════════════════════════════════════════════ -->
<nav>
    <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Logo -->
        <a href="/" class="flex items-center gap-2.5">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:8px;display:flex;align-items:center;justify-content:center;">
                <svg width="18" height="18" fill="none" stroke="white" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <span style="font-family:'Syne',sans-serif;font-weight:700;font-size:18px;color:white;">PulseWork</span>
        </a>

        <!-- Desktop nav -->
        <div class="hidden md:flex items-center gap-8">
            <a href="#features"    class="text-sm text-slate-400 hover:text-white transition">Features</a>
            <a href="#how-it-works"class="text-sm text-slate-400 hover:text-white transition">How it works</a>
            <a href="#pricing"     class="text-sm text-slate-400 hover:text-white transition">Pricing</a>
            <a href="#faq"         class="text-sm text-slate-400 hover:text-white transition">FAQ</a>
        </div>

        <!-- CTA buttons -->
        <div class="flex items-center gap-3">
            @auth
                <a href="{{ route('dashboard') }}" class="btn-primary" style="padding:9px 18px;font-size:14px;">
                    Go to Dashboard →
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost" style="padding:9px 18px;font-size:14px;">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary" style="padding:9px 18px;font-size:14px;">Get started free</a>
            @endauth
        </div>
    </div>
</nav>

<!-- ════════ HERO ══════════════════════════════════════════════ -->
<section style="min-height:100vh;display:flex;align-items:center;padding:120px 24px 80px;overflow:hidden;position:relative;">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    <div class="max-w-6xl mx-auto w-full">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center;">

            <!-- Left: Copy -->
            <div>
                <div class="fade-up delay-1" style="display:inline-flex;align-items:center;gap:8px;background:rgba(37,99,235,0.12);border:1px solid rgba(37,99,235,0.3);border-radius:100px;padding:6px 14px;margin-bottom:24px;">
                    <span style="width:6px;height:6px;background:#60a5fa;border-radius:50%;display:inline-block;"></span>
                    <span style="font-size:13px;color:#93c5fd;font-weight:500;">Privacy-first · Metadata only · No email content</span>
                </div>

                <h1 class="fade-up delay-2" style="font-size:clamp(40px,5vw,64px);font-weight:800;line-height:1.05;letter-spacing:-1.5px;margin-bottom:20px;">
                    Understand how<br>
                    you <span class="grad-text">actually work</span>
                </h1>

                <p class="fade-up delay-3" style="font-size:18px;color:rgba(245,243,239,0.6);line-height:1.7;max-width:480px;margin-bottom:36px;font-weight:300;">
                    PulseWork analyses your Microsoft Outlook email and calendar patterns — not content — to reveal hidden productivity insights, burnout signals, and your best focus windows.
                </p>

                <div class="fade-up delay-4" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:40px;">
                    @auth
                        <a href="{{ route('dashboard') }}" class="btn-primary">
                            Open Dashboard
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @else
                        <a href="{{ route('register') }}" class="btn-primary">
                            Start for free
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                        <a href="{{ route('login') }}" class="btn-ghost">Sign in with Microsoft</a>
                    @endauth
                </div>

                <div class="fade-up delay-5" style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                    @foreach(['No email content read','Free to start','Disconnect anytime'] as $trust)
                        <div style="display:flex;align-items:center;gap:6px;">
                            <svg width="14" height="14" fill="#34d399" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            <span style="font-size:13px;color:rgba(245,243,239,0.5);">{{ $trust }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Right: Dashboard mockup -->
            <div class="fade-up delay-3" style="position:relative;">
                <div style="background:rgba(15,23,42,0.9);border:1px solid rgba(255,255,255,0.1);border-radius:20px;padding:20px;box-shadow:0 40px 80px rgba(0,0,0,0.5);">
                    <!-- Mockup header -->
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:24px;height:24px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:6px;"></div>
                            <span style="font-family:'Syne',sans-serif;font-weight:600;font-size:13px;">PulseWork</span>
                        </div>
                        <div style="background:#2563eb;border-radius:6px;padding:4px 10px;font-size:11px;font-weight:500;">Sync Now</div>
                    </div>

                    <!-- Score + stats grid -->
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:12px;">
                        <div style="grid-column:span 1;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:14px;text-align:center;">
                            <div style="font-size:10px;color:#64748b;margin-bottom:8px;text-transform:uppercase;letter-spacing:.05em;">Score</div>
                            <div style="position:relative;width:56px;height:56px;margin:0 auto 6px;">
                                <svg viewBox="0 0 60 60" style="transform:rotate(-90deg)">
                                    <circle cx="30" cy="30" r="24" fill="none" stroke="#1e293b" stroke-width="6"/>
                                    <circle cx="30" cy="30" r="24" fill="none" stroke="#4ade80" stroke-width="6" stroke-linecap="round" stroke-dasharray="150.8" stroke-dashoffset="37.7"/>
                                </svg>
                                <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-family:'Syne',sans-serif;font-weight:700;font-size:14px;">78</div>
                            </div>
                            <div style="font-size:11px;color:#4ade80;font-weight:500;">Excellent</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:14px;">
                            <div style="font-size:10px;color:#64748b;margin-bottom:4px;">Emails</div>
                            <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:22px;">34</div>
                            <div style="font-size:10px;color:#64748b;margin-top:2px;">12 sent</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);border-radius:12px;padding:14px;">
                            <div style="font-size:10px;color:#64748b;margin-bottom:4px;">Meetings</div>
                            <div style="font-family:'Syne',sans-serif;font-weight:700;font-size:22px;">2.5<span style="font-size:13px;font-weight:400;color:#64748b;">h</span></div>
                            <div style="font-size:10px;color:#64748b;margin-top:2px;">3 meetings</div>
                        </div>
                    </div>

                    <!-- Mini bar chart -->
                    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px;padding:12px;margin-bottom:10px;">
                        <div style="font-size:11px;color:#64748b;margin-bottom:10px;">Email Load — This Week</div>
                        <div style="display:flex;align-items:flex-end;gap:5px;height:40px;">
                            @foreach([60,85,45,30,75,55,90] as $h)
                                <div style="flex:1;height:{{ $h }}%;background:rgba(59,130,246,{{ $h > 70 ? '0.7' : '0.4' }});border-radius:3px;"></div>
                            @endforeach
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:6px;">
                            @foreach(['M','T','W','T','F','S','S'] as $d)
                                <span style="font-size:9px;color:#475569;">{{ $d }}</span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Insights -->
                    <div style="space-y:6px;">
                        <div style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.2);border-radius:8px;padding:8px 10px;font-size:11px;color:#86efac;margin-bottom:6px;">
                            🎯 3.5hrs of focus time available today
                        </div>
                        <div style="background:rgba(251,191,36,0.08);border:1px solid rgba(251,191,36,0.2);border-radius:8px;padding:8px 10px;font-size:11px;color:#fde68a;">
                            ⚡ 2 back-to-back meetings this afternoon
                        </div>
                    </div>
                </div>

                <!-- Floating badges -->
                <div style="position:absolute;top:-16px;right:-16px;background:rgba(34,197,94,0.15);border:1px solid rgba(34,197,94,0.3);border-radius:10px;padding:8px 12px;backdrop-filter:blur(8px);">
                    <div style="font-size:11px;color:#4ade80;font-weight:500;">↑ +12pts this week</div>
                </div>
                <div style="position:absolute;bottom:-16px;left:-16px;background:rgba(37,99,235,0.15);border:1px solid rgba(37,99,235,0.3);border-radius:10px;padding:8px 12px;backdrop-filter:blur(8px);">
                    <div style="font-size:11px;color:#93c5fd;font-weight:500;">🔒 Email content never read</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ════════ STATS BAR ══════════════════════════════════════════ -->
<section style="padding:40px 24px;border-top:1px solid rgba(255,255,255,0.05);border-bottom:1px solid rgba(255,255,255,0.05);">
    <div class="max-w-5xl mx-auto">
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px;" class="reveal">
            @foreach([['10,000+','professionals tracked'],['94%','report better focus'],['3.2hrs','avg focus time gained'],['0','email contents stored']] as [$num,$label])
            <div class="stat-card">
                <div style="font-family:'Syne',sans-serif;font-size:28px;font-weight:800;" class="grad-text">{{ $num }}</div>
                <div style="font-size:13px;color:rgba(245,243,239,0.5);margin-top:4px;">{{ $label }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════ FEATURES ══════════════════════════════════════════ -->
<section id="features" style="padding:100px 24px;">
    <div class="max-w-6xl mx-auto">
        <div style="text-align:center;margin-bottom:64px;" class="reveal">
            <div style="display:inline-block;background:rgba(167,139,250,0.1);border:1px solid rgba(167,139,250,0.25);border-radius:100px;padding:5px 14px;font-size:12px;color:#c4b5fd;font-weight:500;margin-bottom:16px;text-transform:uppercase;letter-spacing:.08em;">Features</div>
            <h2 style="font-size:clamp(28px,4vw,44px);font-weight:800;letter-spacing:-1px;margin-bottom:16px;">Everything you need to work smarter</h2>
            <p style="font-size:17px;color:rgba(245,243,239,0.5);max-width:520px;margin:0 auto;font-weight:300;">Built around your actual work patterns — not what you think your patterns are.</p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;" class="reveal">
            @php
            $features = [
                ['bg'=>'rgba(37,99,235,0.15)','stroke'=>'#60a5fa','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10','title'=>'Productivity Score','desc'=>'A 0–100 score calculated daily from email load, meeting density, focus time, and after-hours activity. Track your trend week over week.'],
                ['bg'=>'rgba(99,102,241,0.15)','stroke'=>'#a78bfa','icon'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z','title'=>'Email Pattern Analysis','desc'=>'Discover your peak email hours, average response times, email volume trends, and after-hours activity — all from metadata only.'],
                ['bg'=>'rgba(245,158,11,0.15)','stroke'=>'#fbbf24','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','title'=>'Calendar Intelligence','desc'=>'Track meeting hours, detect back-to-back overload, measure true focus time between meetings, and flag after-hours calendar events.'],
                ['bg'=>'rgba(239,68,68,0.15)','stroke'=>'#f87171','icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z','title'=>'Burnout Detection','desc'=>'Automatically flags consecutive heavy-meeting days and excessive after-hours activity before burnout sets in. Early warning = prevention.'],
                ['bg'=>'rgba(52,211,153,0.15)','stroke'=>'#34d399','icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z','title'=>'Focus Window Finder','desc'=>'Analyses your weekly calendar patterns to recommend your best 2-hour deep-work blocks. Block them before meetings fill your day.'],
                ['bg'=>'rgba(248,113,113,0.15)','stroke'=>'#fb923c','icon'=>'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z','title'=>'Weekly Insights Report','desc'=>'Best day, worst day, sub-scores for email, calendar and work-life balance. Personalised tips that update as your patterns change.'],
            ];
            @endphp
            @foreach($features as $f)
            <div class="feat-card">
                <div class="feat-icon" style="background:{{ $f['bg'] }};">
                    <svg width="22" height="22" fill="none" stroke="{{ $f['stroke'] }}" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $f['icon'] }}"/>
                    </svg>
                </div>
                <h3 style="font-size:16px;font-weight:700;margin-bottom:10px;">{{ $f['title'] }}</h3>
                <p style="font-size:14px;color:rgba(245,243,239,0.5);line-height:1.7;font-weight:300;">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════ HOW IT WORKS ═══════════════════════════════════════ -->
<section id="how-it-works" style="padding:100px 24px;background:rgba(255,255,255,0.02);">
    <div class="max-w-4xl mx-auto">
        <div style="text-align:center;margin-bottom:64px;" class="reveal">
            <div style="display:inline-block;background:rgba(52,211,153,0.1);border:1px solid rgba(52,211,153,0.25);border-radius:100px;padding:5px 14px;font-size:12px;color:#6ee7b7;font-weight:500;margin-bottom:16px;text-transform:uppercase;letter-spacing:.08em;">How it works</div>
            <h2 style="font-size:clamp(28px,4vw,44px);font-weight:800;letter-spacing:-1px;margin-bottom:16px;">Up and running in 60 seconds</h2>
            <p style="font-size:17px;color:rgba(245,243,239,0.5);font-weight:300;">No setup. No configuration. No API keys. Just connect and go.</p>
        </div>

        @php $steps = [
            ['num'=>'01','title'=>'Create your account','desc'=>'Register with email and password, or jump straight to connecting your Microsoft account. Your account is free to create.','color'=>'#60a5fa'],
            ['num'=>'02','title'=>'Connect Microsoft Outlook','desc'=>'Click "Connect Outlook" and sign in with your Microsoft account. You choose what permissions to grant — we only request read access to email timestamps and calendar events.','color'=>'#a78bfa'],
            ['num'=>'03','title'=>'Sync your data','desc'=>'Hit the Sync button. PulseWork fetches metadata from the past 7 days — no email subject lines or content, ever. Analysis runs in seconds.','color'=>'#34d399'],
            ['num'=>'04','title'=>'See your insights','desc'=>'Your productivity score, meeting load, focus windows, burnout signals, and personalised insights appear instantly on your dashboard. Sync daily for trend data.','color'=>'#fbbf24'],
        ]; @endphp

        <div class="reveal">
            @foreach($steps as $i => $step)
            <div style="display:flex;gap:24px;margin-bottom:{{ $i < count($steps)-1 ? '8px' : '0' }};">
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <div class="step-num" style="background:rgba({{ $step['color'] === '#60a5fa' ? '96,165,250' : ($step['color']==='#a78bfa'?'167,139,250':($step['color']==='#34d399'?'52,211,153':'251,191,36')) }},0.12);border-color:rgba({{ $step['color'] === '#60a5fa' ? '96,165,250' : ($step['color']==='#a78bfa'?'167,139,250':($step['color']==='#34d399'?'52,211,153':'251,191,36')) }},0.35);color:{{ $step['color'] }};">
                        {{ $step['num'] }}
                    </div>
                    @if($i < count($steps)-1)
                        <div class="step-line"></div>
                    @endif
                </div>
                <div style="padding-bottom:{{ $i < count($steps)-1 ? '32px' : '0' }};">
                    <h3 style="font-size:18px;font-weight:700;margin-bottom:8px;">{{ $step['title'] }}</h3>
                    <p style="font-size:14px;color:rgba(245,243,239,0.5);line-height:1.7;font-weight:300;max-width:540px;">{{ $step['desc'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════ PRIVACY ════════════════════════════════════════════ -->
<section style="padding:100px 24px;">
    <div class="max-w-5xl mx-auto reveal">
        <div style="background:rgba(37,99,235,0.06);border:1px solid rgba(37,99,235,0.2);border-radius:24px;padding:56px;display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:center;">
            <div>
                <div style="width:48px;height:48px;background:rgba(37,99,235,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:20px;">
                    <svg width="24" height="24" fill="none" stroke="#60a5fa" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <h2 style="font-size:28px;font-weight:800;letter-spacing:-.5px;margin-bottom:16px;">Privacy by design</h2>
                <p style="font-size:15px;color:rgba(245,243,239,0.6);line-height:1.8;font-weight:300;">We built PulseWork with a single constraint: never touch email content. We analyse how you work, not what you write.</p>
            </div>
            <div style="display:flex;flex-direction:column;gap:14px;">
                @foreach(['Email body and subject lines are never fetched','Only timestamps, counts, and durations are stored','Tokens encrypted at rest in the database','Disconnect anytime — all data deleted on request','No data sold or shared with third parties','GDPR-aligned data handling throughout'] as $p)
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:20px;height:20px;background:rgba(52,211,153,0.12);border:1px solid rgba(52,211,153,0.3);border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="10" height="10" fill="#34d399" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    </div>
                    <span style="font-size:14px;color:rgba(245,243,239,0.65);">{{ $p }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<!-- ════════ PRICING ════════════════════════════════════════════ -->
<section id="pricing" style="padding:100px 24px;background:rgba(255,255,255,0.02);">
    <div class="max-w-5xl mx-auto">
        <div style="text-align:center;margin-bottom:64px;" class="reveal">
            <div style="display:inline-block;background:rgba(251,191,36,0.1);border:1px solid rgba(251,191,36,0.25);border-radius:100px;padding:5px 14px;font-size:12px;color:#fde68a;font-weight:500;margin-bottom:16px;text-transform:uppercase;letter-spacing:.08em;">Pricing</div>
            <h2 style="font-size:clamp(28px,4vw,44px);font-weight:800;letter-spacing:-1px;margin-bottom:16px;">Simple, honest pricing</h2>
            <p style="font-size:17px;color:rgba(245,243,239,0.5);font-weight:300;">Start free, upgrade when you need more.</p>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;" class="reveal">
            @php $plans = [
                ['name'=>'Free','price'=>'$0','period'=>'forever','features'=>['7-day data sync','Email pattern analysis','Calendar analysis','Productivity score','Basic insights','1 Outlook account'],'cta'=>'Get started','featured'=>false],
                ['name'=>'Pro','price'=>'$9','period'=>'per month','features'=>['30-day data history','Full analytics suite','Burnout detection','Focus window finder','Weekly email report','Export data as CSV','Priority support'],'cta'=>'Start free trial','featured'=>true],
                ['name'=>'Team','price'=>'$29','period'=>'per month','features'=>['Everything in Pro','Up to 10 team members','Anonymous team insights','Department dashboards','Admin controls','SSO / Azure AD login','Dedicated support'],'cta'=>'Contact us','featured'=>false],
            ]; @endphp
            @foreach($plans as $plan)
            <div class="price-card {{ $plan['featured'] ? 'featured' : '' }}" style="position:relative;">
                @if($plan['featured'])
                    <div style="position:absolute;top:-12px;left:50%;transform:translateX(-50%);background:linear-gradient(90deg,#2563eb,#6366f1);border-radius:100px;padding:4px 14px;font-size:11px;font-weight:600;white-space:nowrap;">Most popular</div>
                @endif
                <div style="font-size:13px;color:rgba(245,243,239,0.5);font-weight:500;margin-bottom:8px;text-transform:uppercase;letter-spacing:.06em;">{{ $plan['name'] }}</div>
                <div style="display:flex;align-items:baseline;gap:4px;margin-bottom:4px;">
                    <span style="font-family:'Syne',sans-serif;font-size:40px;font-weight:800;">{{ $plan['price'] }}</span>
                    <span style="font-size:13px;color:rgba(245,243,239,0.4);">{{ $plan['period'] }}</span>
                </div>
                <div style="border-top:1px solid rgba(255,255,255,0.07);margin:20px 0;padding-top:20px;display:flex;flex-direction:column;gap:10px;">
                    @foreach($plan['features'] as $feat)
                    <div style="display:flex;align-items:center;gap:8px;font-size:14px;color:rgba(245,243,239,0.7);">
                        <svg width="14" height="14" fill="{{ $plan['featured'] ? '#60a5fa' : '#34d399' }}" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $feat }}
                    </div>
                    @endforeach
                </div>
                <a href="{{ route('register') }}" class="{{ $plan['featured'] ? 'btn-primary' : 'btn-ghost' }}" style="width:100%;justify-content:center;margin-top:8px;">{{ $plan['cta'] }}</a>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════ TESTIMONIALS ═══════════════════════════════════════ -->
<section style="padding:100px 24px;">
    <div class="max-w-5xl mx-auto">
        <div style="text-align:center;margin-bottom:64px;" class="reveal">
            <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;letter-spacing:-1px;margin-bottom:16px;">Trusted by productive people</h2>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;" class="reveal">
            @php $testis = [
                ['quote'=>'Finally understand why Wednesdays feel so draining — 4 hours of back-to-back meetings with zero focus time. PulseWork showed me in 30 seconds what I hadn\'t noticed in months.','name'=>'Priya S.','role'=>'Product Manager'],
                ['quote'=>'The burnout alert caught me before I crashed. Three consecutive heavy weeks and it flagged it immediately. I rescheduled two recurring meetings and feel human again.','name'=>'James T.','role'=>'Engineering Lead'],
                ['quote'=>'My focus window recommendation is 10–12am on Tuesdays. I blocked it, turned off Slack, and shipped more in those 2 hours than in entire afternoons before.','name'=>'Mei L.','role'=>'Senior Designer'],
            ]; @endphp
            @foreach($testis as $t)
            <div class="testi">
                <div style="display:flex;gap:2px;margin-bottom:14px;">
                    @for($i=0;$i<5;$i++)
                        <svg width="14" height="14" fill="#fbbf24" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    @endfor
                </div>
                <p style="font-size:14px;color:rgba(245,243,239,0.65);line-height:1.7;margin-bottom:16px;font-style:italic;font-weight:300;">"{{ $t['quote'] }}"</p>
                <div>
                    <div style="font-size:14px;font-weight:600;">{{ $t['name'] }}</div>
                    <div style="font-size:12px;color:rgba(245,243,239,0.4);">{{ $t['role'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════ FAQ ═════════════════════════════════════════════════ -->
<section id="faq" style="padding:100px 24px;background:rgba(255,255,255,0.02);">
    <div class="max-w-3xl mx-auto">
        <div style="text-align:center;margin-bottom:56px;" class="reveal">
            <h2 style="font-size:clamp(28px,4vw,40px);font-weight:800;letter-spacing:-1px;">Frequently asked questions</h2>
        </div>
        <div style="display:flex;flex-direction:column;gap:12px;" class="reveal">
            @php $faqs = [
                ['q'=>'Does PulseWork read my emails?','a'=>'Never. We only access metadata — timestamps, sender count, and whether emails were sent inside or outside work hours. We explicitly exclude email subjects, bodies, and attachments from every API request.'],
                ['q'=>'What Microsoft permissions does it need?','a'=>'Four delegated permissions: User.Read (your profile), Mail.Read (email timestamps only), Calendars.Read (event start/end times), and offline_access (to refresh your token without re-logging in). You grant these yourself during login.'],
                ['q'=>'What happens to my data if I disconnect?','a'=>'Visit Settings and click "Delete my data" — all computed metrics are permanently deleted from our database. Your Microsoft token is revoked immediately. We retain nothing.'],
                ['q'=>'How is my productivity score calculated?','a'=>'It is a weighted combination of three sub-scores: email score (volume, response time, after-hours), calendar score (meeting load, back-to-back, focus time), and balance score (work-life boundary metrics). Each is 0–100, combined into one overall score.'],
                ['q'=>'Does it work with personal Microsoft / Outlook accounts?','a'=>'Yes. PulseWork supports personal Microsoft accounts (Outlook.com, Hotmail) as well as work and school accounts. Use the "consumers" OAuth flow — this is already configured.'],
                ['q'=>'Can my employer see my data?','a'=>'No. Each user account is private. Your productivity data is only visible to you. Even if multiple people from the same organisation sign up, they cannot see each other\'s individual data.'],
            ]; @endphp
            @foreach($faqs as $faq)
            <details style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:12px;overflow:hidden;">
                <summary style="padding:18px 20px;display:flex;align-items:center;justify-content:space-between;font-size:15px;font-weight:500;">
                    {{ $faq['q'] }}
                    <span class="faq-icon" style="color:#64748b;flex-shrink:0;margin-left:16px;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </span>
                </summary>
                <div style="padding:0 20px 18px;font-size:14px;color:rgba(245,243,239,0.55);line-height:1.8;font-weight:300;border-top:1px solid rgba(255,255,255,0.05);padding-top:14px;margin-top:0;">
                    {{ $faq['a'] }}
                </div>
            </details>
            @endforeach
        </div>
    </div>
</section>

<!-- ════════ CTA BANNER ══════════════════════════════════════════ -->
<section style="padding:100px 24px;">
    <div class="max-w-3xl mx-auto text-center reveal">
        <h2 style="font-size:clamp(32px,5vw,52px);font-weight:800;letter-spacing:-1.5px;margin-bottom:20px;line-height:1.1;">
            Ready to understand<br>
            <span class="grad-warm">how you work?</span>
        </h2>
        <p style="font-size:17px;color:rgba(245,243,239,0.5);margin-bottom:36px;font-weight:300;">Free to start. No credit card. Connect and get your first insights in under a minute.</p>
        <a href="{{ route('register') }}" class="btn-primary" style="font-size:16px;padding:14px 32px;">
            Get started free
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
    </div>
</section>

<!-- ════════ FOOTER ══════════════════════════════════════════════ -->
<footer style="padding:48px 24px;">
    <div class="max-w-6xl mx-auto">
        <div style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:48px;margin-bottom:48px;">
            <div>
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;">
                    <div style="width:28px;height:28px;background:linear-gradient(135deg,#2563eb,#6366f1);border-radius:7px;display:flex;align-items:center;justify-content:center;">
                        <svg width="15" height="15" fill="none" stroke="white" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span style="font-family:'Syne',sans-serif;font-weight:700;">PulseWork</span>
                </div>
                <p style="font-size:13px;color:rgba(245,243,239,0.4);line-height:1.7;max-width:240px;font-weight:300;">Privacy-first productivity analytics for Microsoft Outlook users.</p>
            </div>
            <div>
                <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:rgba(245,243,239,0.4);margin-bottom:16px;">Product</p>
                @foreach(['Features','How it works','Pricing','Changelog'] as $link)
                    <a href="#" style="display:block;font-size:14px;color:rgba(245,243,239,0.5);text-decoration:none;margin-bottom:10px;transition:color .15s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(245,243,239,0.5)'">{{ $link }}</a>
                @endforeach
            </div>
            <div>
                <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:rgba(245,243,239,0.4);margin-bottom:16px;">Account</p>
                @foreach([['Log in',route('login')],['Sign up',route('register')],['Dashboard',route('dashboard')]] as [$l,$r])
                    <a href="{{ $r }}" style="display:block;font-size:14px;color:rgba(245,243,239,0.5);text-decoration:none;margin-bottom:10px;transition:color .15s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(245,243,239,0.5)'">{{ $l }}</a>
                @endforeach
            </div>
            <div>
                <p style="font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:rgba(245,243,239,0.4);margin-bottom:16px;">Legal</p>
                @foreach(['Privacy Policy','Terms of Service','Cookie Policy','GDPR'] as $link)
                    <a href="#" style="display:block;font-size:14px;color:rgba(245,243,239,0.5);text-decoration:none;margin-bottom:10px;transition:color .15s;" onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(245,243,239,0.5)'">{{ $link }}</a>
                @endforeach
            </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.06);padding-top:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
            <p style="font-size:13px;color:rgba(245,243,239,0.3);">© {{ date('Y') }} PulseWork. All rights reserved.</p>
            <p style="font-size:13px;color:rgba(245,243,239,0.3);">Built with Laravel · Microsoft Graph API · Privacy first</p>
        </div>
    </div>
</footer>

<script>
// Scroll reveal
const observer = new IntersectionObserver(entries => {
    entries.forEach(e => { if(e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1 });
document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
</script>
</body>
</html>