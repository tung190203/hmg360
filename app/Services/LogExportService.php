<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Spatie\Activitylog\Models\Activity;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class LogExportService
{
    protected string $exportDirectory;

    public function __construct()
    {
        $this->exportDirectory = storage_path('app/exports');

        if (!File::exists($this->exportDirectory)) {
            File::makeDirectory($this->exportDirectory, 0755, true);
        }
    }

    /**
     * Export activity logs to a zipped file.
     *
     * @param int $months
     * @param bool $recentOnly
     * @param string|null $password
     * @param string $format
     * @return array|false
     */
    public function exportToZip(int $months = 3, bool $recentOnly = false, ?string $password = null, string $format = 'csv')
    {
        $query = Activity::query()->orderBy('created_at', 'desc');

        if ($months > 0) {
            $query->where('created_at', '>=', now()->subMonths($months));
        }

        $activities = $query->get();

        if ($activities->isEmpty()) {
            return false;
        }

        $format = strtolower($format);
        $allowedFormats = ['csv', 'excel', 'xlsx'];
        if (!in_array($format, $allowedFormats, true)) {
            $format = 'csv';
        }

        $timeSuffix = now()->format('Ymd_His');
        $baseName = "activity_logs_{$timeSuffix}";
        $exportFileName = $format === 'excel' || $format === 'xlsx' ? "{$baseName}.xlsx" : "{$baseName}.csv";
        $exportFilePath = $this->exportDirectory . DIRECTORY_SEPARATOR . $exportFileName;
        $zipFileName = "{$baseName}.zip";
        $zipFilePath = $this->exportDirectory . DIRECTORY_SEPARATOR . $zipFileName;

        $rows = $this->buildRows($activities);
        $this->writeExportFile($exportFilePath, $rows, $format);
        $this->createZip($zipFilePath, $exportFilePath, $password);

        return [
            'path' => $zipFilePath,
            'name' => $zipFileName,
        ];
    }

    protected function buildRows($activities): array
    {
        $rows = [];
        $rows[] = [
            'ID',
            'Log Name',
            'Description',
            'Subject Type',
            'Subject ID',
            'Causer Type',
            'Causer ID',
            'Event',
            'Properties',
            'Created At',
            'Updated At',
        ];

        foreach ($activities as $activity) {
            $properties = $activity->properties;
            if ($properties instanceof \Illuminate\Support\Collection) {
                $properties = $properties->toJson(JSON_UNESCAPED_UNICODE);
            } elseif (is_array($properties) || is_object($properties)) {
                $properties = json_encode($properties, JSON_UNESCAPED_UNICODE);
            } else {
                $properties = (string) $properties;
            }

            $rows[] = [
                $activity->id,
                $activity->log_name,
                $activity->description,
                $activity->subject_type,
                $activity->subject_id,
                $activity->causer_type,
                $activity->causer_id,
                $activity->event,
                $properties,
                optional($activity->created_at)->format('Y-m-d H:i:s'),
                optional($activity->updated_at)->format('Y-m-d H:i:s'),
            ];
        }

        return $rows;
    }

    protected function writeExportFile(string $filePath, array $rows, string $format): void
    {
        if ($format === 'csv') {
            $handle = fopen($filePath, 'w');
            if ($handle === false) {
                throw new \RuntimeException("Không thể tạo file xuất dữ liệu: {$filePath}");
            }

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
            return;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($rows as $rowIndex => $row) {
            foreach ($row as $colIndex => $value) {
                $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    protected function createZip(string $zipPath, string $sourceFilePath, ?string $password = null): void
    {
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            throw new \RuntimeException("Không thể tạo file ZIP: {$zipPath}");
        }

        $fileName = basename($sourceFilePath);
        $zip->addFile($sourceFilePath, $fileName);

        if ($password && method_exists($zip, 'setPassword')) {
            $zip->setPassword($password);
            if (method_exists($zip, 'setEncryptionName') && defined('ZipArchive::EM_AES_256')) {
                $zip->setEncryptionName($fileName, \ZipArchive::EM_AES_256);
            }
        }

        $zip->close();

        // Delete the original export file after creating the ZIP
        if (File::exists($sourceFilePath)) {
            File::delete($sourceFilePath);
        }
    }
}
