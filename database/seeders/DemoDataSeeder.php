<?php
// ════════════════════════════════════════════════════════════════
// database/seeders/DemoDataSeeder.php
//
// Creates 3 demo accounts with 90 days of realistic data each.
// Passwords are all: Demo@1234
//
// Run with:
//   php artisan db:seed --class=DemoDataSeeder
//
// To RESET and re-seed:
//   php artisan db:seed --class=DemoDataSeeder --force
//
// Login at /login with:
//   priya@pulsework.demo   / Demo@1234  (Heavy meetings, burnout risk)
//   james@pulsework.demo   / Demo@1234  (Balanced, high scorer)
//   mei@pulsework.demo     / Demo@1234  (Email overload, improving)
// ════════════════════════════════════════════════════════════════

namespace Database\Seeders;

use App\Models\CalendarMetric;
use App\Models\EmailMetric;
use App\Models\OAuthToken;
use App\Models\ProductivityScore;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    // ── Demo account profiles ──────────────────────────────────────────────
    private array $accounts = [
        [
            'name'         => 'Priya Sharma',
            'email'        => 'priya@pulsework.demo',
            'organisation' => 'pulsework.demo',
            'ms_id'        => 'demo-ms-id-priya-001',
            'avatar_url'   => null,
            'profile'      => 'heavy_meetings',  // lots of meetings, burnout risk
        ],
        [
            'name'         => 'James Thompson',
            'email'        => 'james@pulsework.demo',
            'organisation' => 'pulsework.demo',
            'ms_id'        => 'demo-ms-id-james-002',
            'avatar_url'   => null,
            'profile'      => 'balanced',        // healthy balance, high scores
        ],
        [
            'name'         => 'Mei Lin',
            'email'        => 'mei@pulsework.demo',
            'organisation' => 'pulsework.demo',
            'ms_id'        => 'demo-ms-id-mei-003',
            'avatar_url'   => null,
            'profile'      => 'email_heavy',     // email overload, improving
        ],
    ];

    public function run(): void
    {
        $this->command->info('🌱 Seeding demo accounts...');

        foreach ($this->accounts as $accountData) {
            $this->seedAccount($accountData);
        }

        $this->command->info('✅ Demo seeding complete!');
        $this->command->info('');
        $this->command->info('Login credentials (password: Demo@1234):');
        foreach ($this->accounts as $a) {
            $this->command->info("  → {$a['email']}  ({$a['profile']})");
        }
    }

    private function seedAccount(array $accountData): void
    {
        $this->command->info("  Creating {$accountData['name']}...");

        // ── Create or update user ──────────────────────────────────────────
        $user = User::updateOrCreate(
            ['email' => $accountData['email']],
            [
                'name'           => $accountData['name'],
                'password'       => Hash::make('Demo@1234'),
                'ms_id'          => $accountData['ms_id'],
                'organisation'   => $accountData['organisation'],
                'avatar_url'     => $accountData['avatar_url'],
                'last_synced_at' => now()->subMinutes(rand(5, 60)),
                'settings'       => [
                    'work_start'           => 9,
                    'work_end'             => 18,
                    'timezone'             => 'Asia/Kolkata',
                    'sync_frequency'       => 'daily',
                    'weekly_report'        => true,
                    'burnout_alerts'       => true,
                    'email_notifications'  => false,
                ],
            ]
        );

        // ── Fake OAuth token (won't work for real sync — that's fine) ──────
        OAuthToken::updateOrCreate(
            ['user_id' => $user->id, 'provider' => 'azure'],
            [
                'access_token'  => 'demo-fake-token-' . $user->id . '-' . str_repeat('x', 40),
                'refresh_token' => 'demo-fake-refresh-' . $user->id . '-' . str_repeat('y', 40),
                'expires_at'    => now()->addDays(30),
                'scopes'        => ['User.Read', 'Mail.Read', 'Calendars.Read', 'offline_access'],
            ]
        );

        // ── Delete existing metrics for this user ──────────────────────────
        EmailMetric::where('user_id', $user->id)->delete();
        CalendarMetric::where('user_id', $user->id)->delete();
        ProductivityScore::where('user_id', $user->id)->delete();

        // ── Generate 90 days of data ───────────────────────────────────────
        $this->generateDailyMetrics($user->id, $accountData['profile']);

        // ── Generate weekly scores ─────────────────────────────────────────
        $this->generateWeeklyScores($user->id);

        $this->command->info("  ✓ {$accountData['name']} seeded.");
    }

    private function generateDailyMetrics(int $userId, string $profile): void
    {
        for ($daysAgo = 90; $daysAgo >= 0; $daysAgo--) {
            $date = now()->subDays($daysAgo)->startOfDay();

            // Skip most weekends (but keep some for realism)
            $dayOfWeek = $date->dayOfWeek; // 0=Sun, 6=Sat
            if (($dayOfWeek === 0 || $dayOfWeek === 6) && rand(1, 10) > 2) {
                continue;
            }

            // Generate day data based on profile
            $emailData    = $this->generateEmailMetrics($profile, $daysAgo, $dayOfWeek);
            $calendarData = $this->generateCalendarMetrics($profile, $daysAgo, $dayOfWeek);
            $score        = $this->calculateScore($emailData, $calendarData);

            EmailMetric::create([
                'user_id'             => $userId,
                'metric_date'         => $date->toDateString(),
                'received_count'      => $emailData['received'],
                'sent_count'          => $emailData['sent'],
                'after_hours_count'   => $emailData['after_hours'],
                'avg_response_hours'  => $emailData['avg_response'],
                'peak_hour'           => $emailData['peak_hour'],
                'hourly_distribution' => $emailData['hourly'],
                'productivity_score'  => $score,
            ]);

            CalendarMetric::create([
                'user_id'              => $userId,
                'metric_date'          => $date->toDateString(),
                'total_meetings'       => $calendarData['total_meetings'],
                'meeting_minutes'      => $calendarData['meeting_minutes'],
                'focus_time_minutes'   => $calendarData['focus_minutes'],
                'back_to_back_count'   => $calendarData['back_to_back'],
                'after_hours_meetings' => $calendarData['after_hours_meetings'],
                'meeting_slots'        => $calendarData['slots'],
            ]);
        }
    }

    // ── Email metric generation per profile ────────────────────────────────

    private function generateEmailMetrics(string $profile, int $daysAgo, int $dayOfWeek): array
    {
        $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;

        // Base values per profile
        $bases = match ($profile) {
            'heavy_meetings' => [
                'recv' => [25, 45],   'sent' => [8, 18],
                'after' => [3, 12],  'resp' => [1.5, 4.0],
                'peak'  => [10, 14],
            ],
            'balanced' => [
                'recv' => [18, 35],   'sent' => [10, 22],
                'after' => [0, 4],   'resp' => [0.8, 2.5],
                'peak'  => [9, 11],
            ],
            'email_heavy' => [
                // Gets better over time (improving trend)
                'recv' => $daysAgo > 45
                    ? [55, 90] : ($daysAgo > 20 ? [40, 65] : [25, 45]),
                'sent' => $daysAgo > 45
                    ? [20, 40] : ($daysAgo > 20 ? [15, 30] : [10, 22]),
                'after' => $daysAgo > 45
                    ? [8, 20] : ($daysAgo > 20 ? [4, 12] : [0, 5]),
                'resp' => $daysAgo > 45
                    ? [4.0, 8.0] : ($daysAgo > 20 ? [2.0, 5.0] : [0.5, 2.0]),
                'peak'  => [8, 12],
            ],
            default => ['recv'=>[20,40],'sent'=>[8,15],'after'=>[2,8],'resp'=>[1,3],'peak'=>[10,13]],
        };

        if ($isWeekend) {
            // Weekend: much lower activity
            $received   = rand(2, 10);
            $sent       = rand(0, 4);
            $afterHours = rand(0, 3);
            $avgResp    = round(rand(20, 80) / 10, 1);
            $peakHour   = rand(10, 16);
        } else {
            $received   = rand($bases['recv'][0], $bases['recv'][1]);
            $sent       = rand($bases['sent'][0], $bases['sent'][1]);
            $afterHours = rand($bases['after'][0], $bases['after'][1]);
            $avgResp    = round(rand((int)($bases['resp'][0]*10), (int)($bases['resp'][1]*10)) / 10, 1);
            $peakHour   = rand($bases['peak'][0], $bases['peak'][1]);
        }

        // Build realistic hourly distribution
        $hourly = array_fill(0, 24, 0);
        $remaining = $received;

        // Morning peak around 9-11
        $morningPct = rand(30, 45) / 100;
        $morningCount = (int) ($received * $morningPct);
        for ($i = 0; $i < $morningCount && $remaining > 0; $i++) {
            $h = $this->weightedHour([9=>4, 10=>5, 11=>4, 8=>2]);
            $hourly[$h]++;
            $remaining--;
        }

        // Afternoon 13-16
        $afternoonPct = rand(25, 40) / 100;
        $afternoonCount = (int) ($received * $afternoonPct);
        for ($i = 0; $i < $afternoonCount && $remaining > 0; $i++) {
            $h = $this->weightedHour([13=>3, 14=>5, 15=>4, 16=>3]);
            $hourly[$h]++;
            $remaining--;
        }

        // After-hours emails
        $ahCount = min($afterHours, $remaining);
        for ($i = 0; $i < $ahCount && $remaining > 0; $i++) {
            $h = rand(0, 1) ? rand(19, 22) : rand(6, 8);
            $hourly[$h]++;
            $remaining--;
        }

        // Spread remaining randomly through work day
        while ($remaining > 0) {
            $hourly[rand(9, 17)]++;
            $remaining--;
        }

        $peakHour = array_search(max($hourly), $hourly);

        return [
            'received'    => $received,
            'sent'        => $sent,
            'after_hours' => $afterHours,
            'avg_response'=> $avgResp,
            'peak_hour'   => $peakHour,
            'hourly'      => $hourly,
        ];
    }

    // ── Calendar metric generation per profile ─────────────────────────────

    private function generateCalendarMetrics(string $profile, int $daysAgo, int $dayOfWeek): array
    {
        $isWeekend = $dayOfWeek === 0 || $dayOfWeek === 6;

        if ($isWeekend) {
            return [
                'total_meetings'       => 0,
                'meeting_minutes'      => 0,
                'focus_minutes'        => 480,
                'back_to_back'         => 0,
                'after_hours_meetings' => 0,
                'slots'                => [],
            ];
        }

        // Meeting counts per profile
        $meetingCount = match ($profile) {
            'heavy_meetings' => rand(4, 8),
            'balanced'       => rand(1, 4),
            'email_heavy'    => rand(0, 3),
            default          => rand(1, 4),
        };

        // For heavy_meetings: make some days extra heavy (burnout scenario)
        if ($profile === 'heavy_meetings' && $daysAgo <= 21 && rand(1, 3) === 1) {
            $meetingCount = rand(6, 10); // 3 consecutive weeks of heavy meetings
        }

        $slots        = [];
        $totalMinutes = 0;
        $backToBack   = 0;
        $afterHoursM  = 0;
        $prevEndMin   = null;

        // Work hours 9am-6pm in minutes from midnight
        $workStart = 9 * 60;
        $workEnd   = 18 * 60;
        $cursor    = $workStart;

        // Generate meeting slots sorted through the day
        $meetingStarts = [];
        for ($i = 0; $i < $meetingCount; $i++) {
            $start = $cursor + rand(0, 60);
            if ($start >= $workEnd - 30) break;
            $meetingStarts[] = $start;
            $cursor = $start + rand(30, 90) + rand(0, 30);
        }

        sort($meetingStarts);

        $prevEndMin = null;
        foreach ($meetingStarts as $startMin) {
            $duration  = $this->randomMeetingDuration();
            $endMin    = min($startMin + $duration, $workEnd + 30);

            // Back-to-back: gap less than 5 minutes
            if ($prevEndMin !== null && ($startMin - $prevEndMin) < 5) {
                $backToBack++;
            }

            $startH   = intdiv($startMin, 60);
            $startM   = $startMin % 60;
            $endH     = intdiv($endMin, 60);
            $endM     = $endMin % 60;

            if ($startH >= 18) $afterHoursM++;

            $slots[] = [
                'title'    => $this->randomMeetingTitle($profile),
                'start'    => sprintf('%02d:%02d', $startH, $startM),
                'end'      => sprintf('%02d:%02d', $endH, $endM),
                'duration' => $endMin - $startMin,
                'online'   => rand(1, 3) > 1, // 2/3 chance online
            ];

            $totalMinutes += ($endMin - $startMin);
            $prevEndMin    = $endMin;
        }

        // Calculate focus time (gaps >= 30min between meetings during work hours)
        $focusMinutes = $this->calcFocusTime($slots);

        return [
            'total_meetings'       => count($slots),
            'meeting_minutes'      => $totalMinutes,
            'focus_minutes'        => $focusMinutes,
            'back_to_back'         => $backToBack,
            'after_hours_meetings' => $afterHoursM,
            'slots'                => $slots,
        ];
    }

    // ── Score calculation (mirrors ProductivityAnalyzer logic) ─────────────

    private function calculateScore(array $em, array $cm): int
    {
        $score = 100;

        // Email penalties
        $recv = $em['received'];
        if ($recv > 80)     $score -= 20;
        elseif ($recv > 50) $score -= 10;

        $after = $em['after_hours'];
        if ($after > 10)    $score -= 20;
        elseif ($after > 5) $score -= 10;

        $resp = $em['avg_response'];
        if ($resp > 12)        $score -= 15;
        elseif ($resp > 6)     $score -= 7;
        elseif ($resp > 0 && $resp < 2) $score += 5;

        // Calendar penalties
        $mHrs = $cm['meeting_minutes'] / 60;
        if ($mHrs > 5)      $score -= 25;
        elseif ($mHrs > 3)  $score -= 12;

        $b2b = $cm['back_to_back'];
        if ($b2b > 3)      $score -= 15;
        elseif ($b2b > 1)  $score -= 7;

        $fHrs = $cm['focus_minutes'] / 60;
        if ($fHrs > 3)                     $score += 10;
        elseif ($fHrs < 1 && $mHrs > 0)   $score -= 12;

        if ($cm['after_hours_meetings'] > 2) $score -= 10;

        return max(0, min(100, $score));
    }

    // ── Weekly scores ──────────────────────────────────────────────────────

    private function generateWeeklyScores(int $userId): void
    {
        // Find all Mondays in the last 90 days
        $monday = now()->subDays(90)->startOfWeek();

        while ($monday->lte(now())) {
            $weekEnd = $monday->copy()->endOfWeek();

            $weekEmails    = EmailMetric::where('user_id', $userId)
                ->whereBetween('metric_date', [$monday->toDateString(), $weekEnd->toDateString()])
                ->get();

            $weekCalendars = CalendarMetric::where('user_id', $userId)
                ->whereBetween('metric_date', [$monday->toDateString(), $weekEnd->toDateString()])
                ->get();

            if ($weekEmails->isNotEmpty()) {
                $avgScore = (int) $weekEmails->avg('productivity_score');

                // Find best and worst day
                $scored = $weekEmails->map(function ($em) use ($weekCalendars) {
                    $cm = $weekCalendars->first(
                        fn($c) => $c->metric_date->toDateString() === $em->metric_date->toDateString()
                    );
                    $score = $this->calculateScore(
                        ['received' => $em->received_count, 'sent' => $em->sent_count,
                         'after_hours' => $em->after_hours_count, 'avg_response' => $em->avg_response_hours ?? 2],
                        ['meeting_minutes' => $cm?->meeting_minutes ?? 0,
                         'focus_minutes' => $cm?->focus_time_minutes ?? 240,
                         'back_to_back' => $cm?->back_to_back_count ?? 0,
                         'after_hours_meetings' => $cm?->after_hours_meetings ?? 0]
                    );
                    return ['day' => $em->metric_date->format('l'), 'score' => $score];
                });

                $bestDay  = $scored->sortByDesc('score')->first()['day'] ?? null;
                $worstDay = $scored->sortBy('score')->first()['day'] ?? null;

                $sampleInsights = $this->generateSampleInsights($avgScore, $weekEmails, $weekCalendars);

                ProductivityScore::updateOrCreate(
                    ['user_id' => $userId, 'week_start' => $monday->toDateString()],
                    [
                        'score'          => $avgScore,
                        'email_score'    => min(100, max(0, $avgScore + rand(-10, 10))),
                        'calendar_score' => min(100, max(0, $avgScore + rand(-15, 15))),
                        'balance_score'  => min(100, max(0, $avgScore + rand(-8, 8))),
                        'insights'       => $sampleInsights,
                        'best_day'       => $bestDay,
                        'worst_day'      => $worstDay,
                    ]
                );
            }

            $monday->addWeek();
        }
    }

    // ── Helpers ────────────────────────────────────────────────────────────

    private function randomMeetingDuration(): int
    {
        return match (rand(1, 5)) {
            1 => 15,   // quick 15-min
            2 => 30,   // half hour
            3 => 45,   // 45 min
            4 => 60,   // 1 hour
            5 => rand(1,2) === 1 ? 90 : 120, // 1.5 or 2 hours
        };
    }

    private function randomMeetingTitle(string $profile): string
    {
        $titles = match ($profile) {
            'heavy_meetings' => [
                'Daily Standup', 'Sprint Planning', 'Design Review',
                'Executive Sync', 'Product Roadmap', 'Stakeholder Update',
                'Team Retrospective', '1:1 with Manager', 'Architecture Review',
                'All Hands Meeting', 'Client Call', 'Budget Review',
                'Quarterly Planning', 'Team Sync', 'Project Status Update',
            ],
            'balanced' => [
                'Weekly 1:1', 'Team Standup', 'Project Kickoff',
                'Client Check-in', 'Code Review', 'Design Feedback',
                'Monthly Review', 'Interview', 'Training Session',
            ],
            'email_heavy' => [
                'Weekly Sync', '1:1', 'Project Update',
                'Team Meeting', 'Client Call', 'Review Session',
            ],
            default => ['Team Meeting', '1:1', 'Sync', 'Review'],
        };

        return $titles[array_rand($titles)];
    }

    private function calcFocusTime(array $slots): int
    {
        if (empty($slots)) return 480; // full day focus if no meetings

        $workStart  = 9 * 60;
        $workEnd    = 18 * 60;
        $focus      = 0;
        $cursor     = $workStart;

        foreach ($slots as $slot) {
            $startH   = (int) explode(':', $slot['start'])[0];
            $startM   = (int) explode(':', $slot['start'])[1];
            $endH     = (int) explode(':', $slot['end'])[0];
            $endM     = (int) explode(':', $slot['end'])[1];
            $startMin = max($startH * 60 + $startM, $workStart);
            $endMin   = min($endH * 60 + $endM, $workEnd);

            $gap = $startMin - $cursor;
            if ($gap >= 30) $focus += $gap;

            $cursor = max($cursor, $endMin);
        }

        $remaining = $workEnd - $cursor;
        if ($remaining >= 30) $focus += $remaining;

        return max(0, $focus);
    }

    private function weightedHour(array $weights): int
    {
        $total  = array_sum($weights);
        $rand   = rand(1, $total);
        $cumulative = 0;

        foreach ($weights as $hour => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) return $hour;
        }

        return array_key_first($weights);
    }

    private function generateSampleInsights(int $score, $weekEmails, $weekCalendars): array
    {
        $insights = [];

        if ($score >= 80) {
            $insights[] = "Excellent week! Score {$score}/100 — keep up the great work.";
        } elseif ($score >= 60) {
            $insights[] = "Good week overall. Score {$score}/100. Small improvements can push you higher.";
        } else {
            $insights[] = "Challenging week. Score {$score}/100. Review meeting load and email patterns.";
        }

        $avgMeetingMins = $weekCalendars->avg('meeting_minutes') ?? 0;
        if ($avgMeetingMins > 240) {
            $hrs = round($avgMeetingMins / 60, 1);
            $insights[] = "Heavy meeting load — averaged {$hrs}hrs of meetings per day.";
        }

        $totalAfterHours = $weekEmails->sum('after_hours_count');
        if ($totalAfterHours > 10) {
            $insights[] = "{$totalAfterHours} after-hours emails this week — consider setting boundaries.";
        }

        $avgFocus = $weekCalendars->avg('focus_time_minutes') ?? 0;
        if ($avgFocus > 180) {
            $focusHrs = round($avgFocus / 60, 1);
            $insights[] = "Great focus time — averaged {$focusHrs}hrs of uninterrupted work per day.";
        }

        return $insights;
    }
}