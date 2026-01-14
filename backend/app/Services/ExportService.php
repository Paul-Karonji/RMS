<?php

namespace App\Services;

use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    /**
     * Export data to CSV
     * 
     * @param array $data Array of data rows
     * @param array $headers Column headers
     * @param string $filename Output filename
     * @return string File path
     */
    public function exportToCsv(array $data, array $headers, string $filename): string
    {
        $filename = $filename . '_' . now()->format('Y-m-d_His') . '.csv';
        $path = 'exports/' . $filename;
        
        // Create CSV content
        $csvContent = [];
        
        // Add headers
        $csvContent[] = $headers;
        
        // Add data rows
        foreach ($data as $row) {
            $csvRow = [];
            foreach ($headers as $header) {
                // Convert header to snake_case key
                $key = strtolower(str_replace(' ', '_', $header));
                $csvRow[] = $row[$key] ?? '';
            }
            $csvContent[] = $csvRow;
        }
        
        // Write to storage
        $handle = fopen('php://temp', 'r+');
        foreach ($csvContent as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);
        
        Storage::put($path, $csv);
        
        return Storage::path($path);
    }
    
    /**
     * Export data to PDF
     * 
     * @param array $data Report data
     * @param string $template Blade template name
     * @param string $filename Output filename
     * @return string File path
     */
    public function exportToPdf(array $data, string $template, string $filename): string
    {
        $filename = $filename . '_' . now()->format('Y-m-d_His') . '.pdf';
        $path = 'exports/' . $filename;
        
        // Generate PDF
        $pdf = Pdf::loadView('reports.' . $template, ['data' => $data])
            ->setPaper('a4', 'portrait');
        
        // Save to storage
        Storage::put($path, $pdf->output());
        
        return Storage::path($path);
    }
    
    /**
     * Get download URL for exported file
     * 
     * @param string $filePath Full file path
     * @return string Download URL
     */
    public function getDownloadUrl(string $filePath): string
    {
        $filename = basename($filePath);
        return url('api/exports/download/' . $filename);
    }
}
