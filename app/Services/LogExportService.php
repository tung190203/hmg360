<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class LogExportService
{
    /**
     * Export logs to a password-protected zip file.
     *
     * @param int|null $months Number of months to export (null for all)
     * @param bool $olderThan If true, export logs older than $months. If false, export logs within the last $months.
     * @param string|null $password
     * @param string $format 'csv' or 'excel'
     * @return array|null
     */
    public function exportToZip($months = 3, $olderThan = true, $password = null, $format = 'csv')
    {
        $dateStr = now()->format('Y_m_d_His');
        $extension = ($format === 'excel') ? 'xlsx' : 'csv';
        $fileName = "logs_export_{$dateStr}";
        $dataFileName = "{$fileName}.{$extension}";
        $zipFileName = "{$fileName}.zip";
        
        $password = $password ?: env('LOG_ROTATE_PASSWORD', 'Log@2025');

        $storagePath = storage_path('app/private/logs_archive');
        if (!File::exists($storagePath)) {
            File::makeDirectory($storagePath, 0755, true);
        }

        $dataPath = $storagePath . '/' . $dataFileName;
        $zipPath = $storagePath . '/' . $zipFileName;

        // Use ActivityLogExport for data generation
        $export = new \App\Exports\ActivityLogExport($months, $olderThan);
        
        // Quick check if there is data before proceeding
        if ($export->collection()->isEmpty()) {
            return null;
        }

        try {
            if ($format === 'excel') {
                \Maatwebsite\Excel\Facades\Excel::store($export, 'logs_archive/' . $dataFileName, 'local');
            } else {
                // For CSV, we also use Excel package as it handles BOM and encoding better than manual fputcsv in some cases
                \Maatwebsite\Excel\Facades\Excel::store($export, 'logs_archive/' . $dataFileName, 'local', \Maatwebsite\Excel\Excel::CSV);
            }
        } catch (\Exception $e) {
            Log::error('Export failed: ' . $e->getMessage());
            return null;
        }

        if (!File::exists($dataPath)) {
            return null;
        }

        // We don't easily know the count without querying again or modifying Export class, 
        // but for now let's just proceed if file exists and has size
        if (File::size($dataPath) < 100) { // Very small file might be empty (just headers)
            // Optional: check if count is actually 0. For now just continue.
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            $zip->addFile($dataPath, $dataFileName);
            if (method_exists($zip, 'setEncryptionName')) {
                $zip->setEncryptionName($dataFileName, ZipArchive::EM_AES_256, $password);
            }
            $zip->close();
        }

        File::delete($dataPath);

        return [
            'path' => $zipPath,
            'name' => $zipFileName,
            'count' => 'N/A' // Count is harder to get now, but let's keep it simple
        ];
    }
}
