<?php

namespace App\Console\Commands;

use App\Services\AiUsageAlertService;
use Illuminate\Console\Command;

class CheckAiUsageCostAlert extends Command
{
    protected $signature = 'ai:usage-cost-alert {--force : Send even if an alert was already sent today}';

    protected $description = 'Check AI usage cost threshold and send configured alert emails';

    public function handle(AiUsageAlertService $alertService): int
    {
        $result = $alertService->checkCostThreshold((bool) $this->option('force'));

        if ($result['sent'] ?? false) {
            $this->info('AI usage alert sent to: ' . implode(', ', $result['emails'] ?? []));
            return self::SUCCESS;
        }

        $this->info('No alert sent: ' . ($result['reason'] ?? 'unknown'));
        if (isset($result['cost_24h'], $result['threshold'])) {
            $this->line('Cost 24h: $' . number_format((float) $result['cost_24h'], 6));
            $this->line('Threshold: $' . number_format((float) $result['threshold'], 6));
        }

        return self::SUCCESS;
    }
}
