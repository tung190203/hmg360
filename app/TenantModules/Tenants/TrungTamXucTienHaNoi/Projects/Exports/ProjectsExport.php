<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Exports;

use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Plan;
use App\TenantModules\Tenants\TrungTamXucTienHaNoi\Projects\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProjectsExport implements FromArray, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        $rows = [[
            'STT',
            'Tên dự án',
            'Địa điểm',
            'Quy mô',
            'Tổng mức đầu tư',
            'Lượt xem tổng',
            'Lượt xem tháng',
            'Đăng ký tổng',
            'Đăng ký tháng',
        ]];

        Project::withRelations()->orderBy('updated_at', 'desc')->get()->each(function (Project $project, int $index) use (&$rows) {
            $unit = Project::UNIT_OPTIONS[$project->unit] ?? '';
            $plan = Plan::where('vrtour_id', $project->id)->first();

            $rows[] = [
                $index + 1,
                $project->name ?? '',
                $project->districts->pluck('name')->filter()->implode(', '),
                trim(($project->area ?? '') . ' ' . $unit),
                $project->price ? $project->price . ' tỷ đồng' : '',
                (int) ($project->view_num ?? 0),
                (int) ($project->views_month ?? 0),
                $project->interests()->count(),
                $project->interests()
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                $plan?->name,
            ];
        });

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $highestColumn = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        $sheet->getStyle('A1:' . $highestColumn . '1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
        ]);

        $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']]],
        ]);

        return [];
    }
}
