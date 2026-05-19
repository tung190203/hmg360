<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use App\Services\LogExportService;

class RotateLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'logs:rotate {--password= : Password for the zip file} {--now : Rotate all logs instead of just 3 months old} {--test : Send email with data but do NOT delete any records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive logs older than 3 months, zip with password, and send to email';

    /**
     * Execute the console command.
     */
    public function handle(LogExportService $logExportService)
    {
        $isNow = $this->option('now');
        $isTest = $this->option('test');
        $months = $isNow ? null : 3;
        
        $password = $this->option('password') ?: env('LOG_ROTATE_PASSWORD');
        $targetEmailString = env('LOG_ROTATE_EMAIL');

        if (!$password || !$targetEmailString) {
            $this->error("Missing LOG_ROTATE_PASSWORD or LOG_ROTATE_EMAIL in .env");
            return;
        }

        $targetEmails = array_map('trim', explode(',', $targetEmailString));
        $targetEmails = array_filter($targetEmails);

        $this->info("Starting log rotation for " . ($isNow ? "ALL logs" : "logs older than 3 months") . ($isTest ? " [TEST MODE - no data will be deleted]" : ""));

        $result = $logExportService->exportToZip($months, true, $password);

        if (!$result) {
            $this->info("No logs found to rotate.");
            return;
        }

        $zipPath = $result['path'];
        $zipFileName = $result['name'];
        $count = $result['count'];

        // 3. Send Email
        try {
            Mail::raw("Chào bạn,\n\nĐây là file backup log cũ " . ($isNow ? "(tất cả)" : "(hơn 3 tháng)") . " tính đến tháng " . now()->format('m/Y') . ".\nFile được nén và đặt mật khẩu bảo mật.\n\nTên file: {$zipFileName}\n\nTrân trọng.", function ($message) use ($targetEmails, $zipPath, $zipFileName) {
                $message->to($targetEmails)
                    ->subject("[Log Backup] " . now()->format('Y-m'))
                    ->attach($zipPath, [
                        'as' => $zipFileName,
                        'mime' => 'application/zip',
                    ]);
            });
            $this->info("Email sent successfully to: " . implode(', ', $targetEmails));
        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return;
        }

        // 4. Cleanup DB
        if ($isTest) {
            $this->warn("[TEST MODE] Skipping database cleanup. No records were deleted.");
        } else {
            $cutoffDate = $isNow ? now()->addDay() : now()->subMonths(3);
            Activity::where('created_at', '<', $cutoffDate)->delete();
        }

        $this->info("Rotation completed successfully. $count records processed.");
    }
}
