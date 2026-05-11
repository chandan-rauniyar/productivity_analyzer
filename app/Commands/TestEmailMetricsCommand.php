<?php

namespace App\Commands;

use App\Services\GraphApiService;
use Illuminate\Console\Command;

class TestEmailMetricsCommand extends Command
{
    protected $signature = 'test:email-metrics';

    protected $description = 'Test fetching email metrics from Graph API';

    public function handle(GraphApiService $graphApiService)
    {
        $this->info('Testing GraphApiService::getTodayEmailMetrics()');
        
        $user = auth()->user();
        
        if (!$user) {
            $this->error('No authenticated user. Please login first.');
            return 1;
        }
        
        try {
            $metrics = $graphApiService->getTodayEmailMetrics($user->oauthToken);
            $this->info('Email Metrics:');
            $this->table(['Metric', 'Value'], [
                ['Received', $metrics['received']],
                ['Sent', $metrics['sent']],
                ['After Hours', $metrics['after_hours']],
            ]);
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());
            return 1;
        }
    }
}
