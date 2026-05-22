<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Spatie\Activitylog\Models\Activity;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $months;
    protected $olderThan;

    public function __construct($months = 3, $olderThan = false)
    {
        $this->months = $months;
        $this->olderThan = $olderThan;
    }

    public function collection()
    {
        $query = Activity::with('causer')->whereNotNull('causer_id');
        
        if ($this->months != 0 && $this->months !== null) {
            $cutoffDate = now()->subMonths($this->months);
            if ($this->olderThan) {
                $query->where('created_at', '<', $cutoffDate);
            } else {
                $query->where('created_at', '>=', $cutoffDate);
            }
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'Thời gian',
            'Người thực hiện',
            'Hành động',
            'Đối tượng',
            'Chi tiết thay đổi'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'E' => [
                'alignment' => [
                    'wrapText' => true,
                    'vertical' => Alignment::VERTICAL_TOP
                ]
            ],
        ];
    }

    private function formatValue($value)
    {
        if (is_array($value)) {
            $value = json_encode($value, JSON_UNESCAPED_UNICODE);
        }
        
        if (is_string($value)) {
            // Replace various line-breaking tags with actual newlines
            $value = preg_replace('/<(p|br|div|li|h[1-6])(\s+[^>]*)?\/?>/i', "\n", $value);
            // Clean up remaining tags
            $value = strip_tags($value);
            // Decode HTML entities (like &nbsp;)
            $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Clean up multiple newlines
            $value = preg_replace("/\n\s*\n+/", "\n", $value);
            $value = trim($value);
        }
        
        return $value;
    }

    public function map($log): array
    {
        $performer = $log->causer ? $log->causer->name : 'System';
        $action = ucfirst($log->description);
        $subject = $log->subject_type ? class_basename($log->subject_type) . " (ID: {$log->subject_id})" : '-';
        
        $details = [];
        if ($log->properties && count($log->properties) > 0) {
            $attributes = $log->properties['attributes'] ?? null;
            $old = $log->properties['old'] ?? null;
            
            if ($attributes) {
                foreach ($attributes as $key => $value) {
                    if ($key == 'updated_at' || $key == 'created_at') continue;
                    
                    $rawOld = $old[$key] ?? '';
                    $oldVal = $this->formatValue($rawOld);
                    $newVal = $this->formatValue($value);

                    if ($oldVal !== '') {
                        $details[] = "• [$key]: $oldVal -> $newVal";
                    } else {
                        $details[] = "• [$key]: $newVal";
                    }
                }
            } else {
                $otherProps = collect($log->properties)->except(['attributes', 'old']);
                foreach ($otherProps as $key => $value) {
                    $val = $this->formatValue($value);
                    $details[] = "• [$key]: $val";
                }
            }
        }

        return [
            $log->created_at->format('H:i d/m/Y'),
            $performer,
            $action,
            $subject,
            count($details) > 0 ? implode("\n", $details) : '-'
        ];
    }
}
