<?php

namespace App\Exports;

use App\Models\Plan;
use App\Models\Project;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProjectsExport implements FromArray, WithStyles, ShouldAutoSize
{
    private function parseContentToValues($htmlContent)
    {
        if (empty($htmlContent)) return ['0', '0', '0'];
        preg_match_all('/data-icon-type="([^"]+)"/', $htmlContent, $matches);
        $values = [];
        foreach ($matches[1] ?? [] as $iconType) {
            $values[] = $iconType === 'checkmark' ? '1' : '0';
        }
        while (count($values) < 3) $values[] = '0';
        return array_slice($values, 0, 3);
    }

    public function array(): array
    {
        $projects = Project::withRelations()->get();
        $data = [];
        $firstDay = now()->startOfMonth()->format('d/m/Y');

        $sum_columns = array_fill(0, 9, 0);
        $sum_view_month = 0;
        $sum_view_total = 0;
        $sum_register_month = 0;
        $sum_register_total = 0;

        $project_data = [];
        $stt = 1;

        foreach ($projects as $project) {
            $districts = $project->districts
                ->map(fn($d) => method_exists($d,'getTranslation') ? ($d->getTranslation('name','vi')??'') : ($d->name['vi']??''))
                ->filter()
                ->implode(', ');

            $unit = $project->unit == 1 ? 'km' : 'ha';

            $plan = Plan::where('vrtour_id', $project->id)->first();
            $content1_values = $this->parseContentToValues($plan->content1 ?? null);
            $content2_values = $this->parseContentToValues($plan->content2 ?? null);
            $content3_values = $this->parseContentToValues($plan->content3 ?? null);

            $all_values = array_merge($content1_values, $content2_values, $content3_values);
            foreach ($all_values as $i => $v) { if ($v==='1') $sum_columns[$i]++; }

            $view_month = $project->views_month ?? 0;
            $view_total = $project->view_num ?? 0;
            $register_month = $project->interests()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

            $register_total = $project->interests()->count();

            $sum_view_month += $view_month;
            $sum_view_total += $view_total;
            $sum_register_month += $register_month;
            $sum_register_total += $register_total;

            $project_data[] = [
                $stt++,
                $project->name ?? '',
                $districts,
                ($project->area ?? '0,00').' '.$unit,
                $project->price ? $project->price.' tỷ đồng' : '',
                ...$content1_values,
                ...$content2_values,
                ...$content3_values,
                (string) ($view_total ?? '0'),
                (string) ($view_month ?? '0'),
                (string) ($register_total ?? '0'),
                (string) ($register_month ?? '0')
            ];
        }

        // Header Level 1
        $data[] = [
            'STT','THÔNG TIN CƠ BẢN','','','','KẾ HOẠCH TRIỂN KHAI','','','','','','','','','THỐNG KÊ TRUY CẬP','','','',''
        ];
        // Header Level 2
        $data[] = [
            '', 'Tên dự án', 'Địa điểm (Phường, xã)', 'Quy mô (ha hoặc km)', 'Tổng mức đầu tư (tỷ đồng)',
            'Chuẩn bị dự án','','','Xúc tiến đầu tư','','','Giám sát thực hiện','','','Truy cập dự án','','','Đăng ký nhận thông tin','',''
        ];
        // Header Level 3
        $data[] = [
            '', '', '', '', '',
            'Quy hoạch tổng thể','Quy hoạch chi tiết','Thuộc danh mục','Số hóa','Chấp thuận chủ trương',
            'Lựa chọn nhà đầu tư','Giải phóng mặt bằng','Phương án kỹ thuật','Hoàn thành',
            'Tổng lượt xem',
            "Lượt xem\n(Tính từ $firstDay đến nay)",
            'Tổng đăng ký',
            "Đăng ký\n(Tính từ $firstDay đến nay)"
        ];        

        // Row tổng cộng
        $data[] = [
            '', '', '', '', '',
            $sum_columns[0],$sum_columns[1],$sum_columns[2],$sum_columns[3],$sum_columns[4],$sum_columns[5],$sum_columns[6],$sum_columns[7],$sum_columns[8],
            $sum_view_total,$sum_view_month,$sum_register_total,$sum_register_month
        ];

        $data = array_merge($data, $project_data);
        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        // Merge header
        $sheet->mergeCells('A1:A3');
        $sheet->mergeCells('B1:E1');
        $sheet->mergeCells('F1:N1');
        $sheet->mergeCells('O1:R1');

        // Gán text cho ô merge
        $sheet->setCellValue('A1', 'STT');
        $sheet->setCellValue('B1', 'THÔNG TIN CƠ BẢN');
        $sheet->setCellValue('F1', 'KẾ HOẠCH TRIỂN KHAI');
        $sheet->setCellValue('O1', 'THỐNG KÊ TRUY CẬP');

        // Merge sub header
        $sheet->mergeCells('B2:B3');
        $sheet->mergeCells('C2:C3');
        $sheet->mergeCells('D2:D3');
        $sheet->mergeCells('E2:E3');
        $sheet->mergeCells('F2:H2');
        $sheet->mergeCells('I2:K2');
        $sheet->mergeCells('L2:N2');
        $sheet->mergeCells('O2:P2');
        $sheet->mergeCells('Q2:R2');

        $sheet->setCellValue('O2', 'Truy cập dự án');
        $sheet->setCellValue('Q2', 'Đăng ký nhận thông tin');

        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(25);
        $sheet->getRowDimension(3)->setRowHeight(45);
        $sheet->getRowDimension(4)->setRowHeight(25);

        // Header style
        $sheet->getStyle('A1:'.$highestColumn.'3')->applyFromArray([
            'font'=>['bold'=>true],
            'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER,'wrapText'=>true],
            'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'D9E1F2']]
        ]);

        // Tổng row style
        $sheet->getStyle('A4:'.$highestColumn.'4')->applyFromArray([
            'font'=>['bold'=>true],
            'alignment'=>['horizontal'=>Alignment::HORIZONTAL_CENTER,'vertical'=>Alignment::VERTICAL_CENTER],
            'fill'=>['fillType'=>Fill::FILL_SOLID,'startColor'=>['rgb'=>'FFFF00']]
        ]);

        // Format text F→R
        $sheet->getStyle('F5:R'.$highestRow)->getNumberFormat()->setFormatCode('@');

        // Borders
        $sheet->getStyle('A1:'.$highestColumn.$highestRow)->applyFromArray([
            'borders'=>['allBorders'=>['borderStyle'=>Border::BORDER_THIN,'color'=>['rgb'=>'000000']]]
        ]);

        $sheet->getStyle('P3:R3')->applyFromArray([
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical'   => Alignment::VERTICAL_CENTER,
                'wrapText'   => true
            ],
        ]);

        return [];
    }
}