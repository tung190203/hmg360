<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ReportVisits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'visit:report {--days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report website visit statistics and bot activity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');
        $this->info("--- Website Visit Report (Last $days days) ---");

        $stats = \App\Models\VisitLog::selectRaw('DATE(created_at) as date, count(*) as total, count(distinct ip_address) as unique_ips, sum(is_bot) as bots')
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $headers = ['Date', 'Total Visits', 'Unique IPs', 'Bot Visits', 'Real Visits'];
        $data = $stats->map(function($stat) {
            return [
                $stat->date,
                $stat->total,
                $stat->unique_ips,
                $stat->bots,
                $stat->total - $stat->bots
            ];
        });

        $this->table($headers, $data);

        $this->info("\n--- Top Bot User Agents ---");
        $topBots = \App\Models\VisitLog::where('is_bot', true)
            ->select('user_agent', \DB::raw('count(*) as count'))
            ->groupBy('user_agent')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        foreach ($topBots as $bot) {
            $this->line("- ({$bot->count}) " . substr($bot->user_agent, 0, 100));
        }
    }
}
