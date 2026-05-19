<?php

namespace App\Console\Commands;

use App\Exports\SiteVisitorMonthlyExport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ExportMonthlySiteVisitors extends Command
{
    protected $signature = 'site-visitors:monthly-export {--month= : Month to export, format YYYY-MM}';

    protected $description = 'Export monthly returning visitor statistics and send to LOG_ROTATE_EMAIL';

    public function handle(): int
    {
        $targetEmailString = env('LOG_ROTATE_EMAIL');
        if (!$targetEmailString) {
            $this->error('Missing LOG_ROTATE_EMAIL in .env');
            return self::FAILURE;
        }

        try {
            $month = $this->option('month')
                ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
                : now()->startOfMonth();
        } catch (\Exception $e) {
            $this->error('Invalid --month value. Expected format: YYYY-MM');
            return self::FAILURE;
        }

        $targetEmails = array_filter(array_map('trim', explode(',', $targetEmailString)));
        if (empty($targetEmails)) {
            $this->error('LOG_ROTATE_EMAIL does not contain a valid email address');
            return self::FAILURE;
        }

        $export = new SiteVisitorMonthlyExport($month);
        $rows = $export->collection();
        if ($rows->isEmpty()) {
            $this->info('No site visitor data found for ' . $month->format('m/Y'));
            return self::SUCCESS;
        }

        $uniqueVisitors = $rows->count();
        $returningVisitors = $rows->where('visit_days', '>=', 2)->count();
        $totalHits = $rows->sum('total_hits');

        $directory = 'site_visitor_reports';
        $fileName = 'site_visitors_' . $month->format('Y_m') . '.csv';
        $storagePath = storage_path('app/private/' . $directory);
        $filePath = $storagePath . '/' . $fileName;

        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        Excel::store($export, $directory . '/' . $fileName, 'local', ExcelFormat::CSV);

        if (!File::exists($filePath)) {
            $this->error('Export file was not created: ' . $filePath);
            return self::FAILURE;
        }

        try {
            Mail::raw(
                "Chào bạn,\n\nĐây là file thống kê lượt truy cập quay lại tháng " . $month->format('m/Y') . ".\n\n" .
                "Tổng IP riêng biệt: {$uniqueVisitors}\n" .
                "IP quay lại: {$returningVisitors}\n" .
                "Tổng lượt truy cập: {$totalHits}\n\n" .
                "File đính kèm: {$fileName}\n\nTrân trọng.",
                function ($message) use ($targetEmails, $filePath, $fileName, $month) {
                    $message->to($targetEmails)
                        ->subject('[Visitor Report] ' . $month->format('Y-m'))
                        ->attach($filePath, [
                            'as' => $fileName,
                            'mime' => 'text/csv',
                        ]);
                }
            );
        } catch (\Exception $e) {
            $this->error('Failed to send email: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Monthly site visitor report sent to: ' . implode(', ', $targetEmails));

        return self::SUCCESS;
    }
}
