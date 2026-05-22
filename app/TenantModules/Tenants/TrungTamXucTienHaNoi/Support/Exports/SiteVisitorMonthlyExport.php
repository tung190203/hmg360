<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Exports;

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models\SiteVisitor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SiteVisitorMonthlyExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(
        protected Carbon $month
    ) {
        $this->month = $month->copy()->startOfMonth();
    }

    public function collection()
    {
        return SiteVisitor::select(
                'ip_address',
                DB::raw('SUM(hits) as total_hits'),
                DB::raw('COUNT(DISTINCT visit_date) as visit_days'),
                DB::raw('MIN(visit_date) as first_visit_date'),
                DB::raw('MAX(visit_date) as last_visit_date')
            )
            ->whereBetween('visit_date', [
                $this->month->copy()->startOfMonth()->toDateString(),
                $this->month->copy()->endOfMonth()->toDateString(),
            ])
            ->groupBy('ip_address')
            ->orderByDesc('total_hits')
            ->get();
    }

    public function headings(): array
    {
        return [
            'IP',
            'Tổng lượt truy cập',
            'Số ngày truy cập',
            'Có quay lại',
            'Ngày truy cập đầu tiên trong tháng',
            'Ngày truy cập gần nhất trong tháng',
        ];
    }

    public function map($visitor): array
    {
        return [
            $visitor->ip_address,
            (int) $visitor->total_hits,
            (int) $visitor->visit_days,
            $visitor->visit_days >= 2 ? 'Có' : 'Không',
            Carbon::parse($visitor->first_visit_date)->format('d/m/Y'),
            Carbon::parse($visitor->last_visit_date)->format('d/m/Y'),
        ];
    }
}
