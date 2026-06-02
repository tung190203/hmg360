<?php

namespace App\Console\Commands;

use App\Core\Tenant\Tenant;
use App\Core\Tenant\TenantDatabaseManager;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Exports\SiteVisitorMonthlyExport;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Excel as ExcelFormat;

class ExportMonthlySiteVisitors extends Command
{
    protected $signature = 'site-visitors:monthly-export
        {--month= : Month to export, format YYYY-MM}
        {--tenant= : Tenant slug to export. Defaults to all active tenants.}';

    protected $description = 'Export monthly returning visitor statistics and send to LOG_ROTATE_EMAIL.';

    public function handle(TenantDatabaseManager $databaseManager): int
    {
        $targetEmails = $this->targetEmails();
        if (empty($targetEmails)) {
            return self::FAILURE;
        }

        $month = $this->targetMonth();
        if (! $month) {
            return self::FAILURE;
        }

        $tenants = $this->tenants();
        if ($tenants->isEmpty()) {
            $this->error($this->option('tenant')
                ? "Tenant '{$this->option('tenant')}' not found."
                : 'No active tenants found.');

            return self::FAILURE;
        }

        $hasFailure = false;
        foreach ($tenants as $tenant) {
            try {
                $databaseManager->connect($tenant);
                $this->exportTenant($tenant, $month, $targetEmails);
            } catch (\Throwable $e) {
                $hasFailure = true;
                $this->error("Failed to export site visitors for '{$tenant->slug}': {$e->getMessage()}");
            }
        }

        return $hasFailure ? self::FAILURE : self::SUCCESS;
    }

    private function targetEmails(): array
    {
        $targetEmailString = env('LOG_ROTATE_EMAIL');
        if (! $targetEmailString) {
            $this->error('Missing LOG_ROTATE_EMAIL in .env');

            return [];
        }

        $targetEmails = array_filter(array_map('trim', explode(',', $targetEmailString)));
        if (empty($targetEmails)) {
            $this->error('LOG_ROTATE_EMAIL does not contain a valid email address');
        }

        return $targetEmails;
    }

    private function targetMonth(): ?Carbon
    {
        try {
            return $this->option('month')
                ? Carbon::createFromFormat('Y-m', $this->option('month'))->startOfMonth()
                : now()->startOfMonth();
        } catch (\Throwable) {
            $this->error('Invalid --month value. Expected format: YYYY-MM');

            return null;
        }
    }

    private function tenants()
    {
        $tenantSlug = $this->option('tenant');

        return $tenantSlug
            ? Tenant::where('slug', $tenantSlug)->get()
            : Tenant::where('status', Tenant::STATUS_ACTIVE)->get();
    }

    private function exportTenant(Tenant $tenant, Carbon $month, array $targetEmails): void
    {
        $export = new SiteVisitorMonthlyExport($month);
        $rows = $export->collection();
        if ($rows->isEmpty()) {
            $this->info("No site visitor data found for {$tenant->slug} in {$month->format('m/Y')}");

            return;
        }

        $uniqueVisitors = $rows->count();
        $returningVisitors = $rows->where('visit_days', '>=', 2)->count();
        $totalHits = $rows->sum('total_hits');

        $directory = "site_visitor_reports/{$tenant->slug}";
        $fileName = 'site_visitors_' . $tenant->slug . '_' . $month->format('Y_m') . '.csv';
        $storagePath = storage_path('app/private/' . $directory);
        $filePath = $storagePath . '/' . $fileName;

        if (! File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        Excel::store($export, $directory . '/' . $fileName, 'local', ExcelFormat::CSV);

        if (! File::exists($filePath)) {
            throw new \RuntimeException('Export file was not created: ' . $filePath);
        }

        Mail::raw(
            "Chào bạn,\n\nĐây là file thống kê lượt truy cập quay lại tháng " . $month->format('m/Y') . ".\n\n" .
            "Tenant: {$tenant->name} ({$tenant->slug})\n" .
            "Tổng IP riêng biệt: {$uniqueVisitors}\n" .
            "IP quay lại: {$returningVisitors}\n" .
            "Tổng lượt truy cập: {$totalHits}\n\n" .
            "File đính kèm: {$fileName}\n\nTrân trọng.",
            function ($message) use ($targetEmails, $filePath, $fileName, $month, $tenant) {
                $message->to($targetEmails)
                    ->subject('[Visitor Report] ' . $tenant->slug . ' ' . $month->format('Y-m'))
                    ->attach($filePath, [
                        'as' => $fileName,
                        'mime' => 'text/csv',
                    ]);
            }
        );

        $this->info("Monthly site visitor report for {$tenant->slug} sent to: " . implode(', ', $targetEmails));
    }
}
