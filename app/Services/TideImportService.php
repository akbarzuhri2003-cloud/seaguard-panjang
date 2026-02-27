<?php
// app/Services/TideImportService.php

namespace App\Services;

use App\Models\HistoricalTide;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TideImportService
{
    public function import(UploadedFile $file)
    {
        // Prevent timeout for large files
        set_time_limit(0);
        
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            array_shift($rows);
            
            $dataToUpsert = [];
            $errors = [];
            
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty($row[0])) {
                    continue;
                }
                
                try {
                    $dataToUpsert[] = $this->prepareRowData($row);
                } catch (\Exception $e) {
                    $rowNum = $index + 2; // +1 for 0-index, +1 for header
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                    Log::warning("Error processing row {$rowNum}: " . $e->getMessage());
                }
            }
            
            // Batch upsert in chunks to prevent memory/query length issues
            $chunks = array_chunk($dataToUpsert, 500);
            $count = 0;
            
            foreach ($chunks as $chunk) {
                HistoricalTide::upsert(
                    $chunk,
                    ['date', 'time'], // Unique keys
                    ['height', 'type', 'temperature', 'wind_speed', 'pressure', 'wind_direction', 'updated_at'] // Columns to update
                );
                $count += count($chunk);
            }
            
            return [
                'success' => true,
                'count' => $count,
                'errors' => $errors,
                'message' => "Successfully imported {$count} data points." . (count($errors) > 0 ? " with " . count($errors) . " errors." : "")
            ];
            
        } catch (\Exception $e) {
            Log::error("Import failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => "Import failed: " . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    private function prepareRowData($row)
    {
        $date = $this->parseDate($row[0]);
        $time = $this->parseTime($row[1] ?? '00:00:00');
        $height = floatval($row[2] ?? 0);
        
        return [
            'date' => $date,
            'time' => $time,
            'height' => $height,
            'type' => $this->determineTideType($height, $time),
            'temperature' => isset($row[3]) && $row[3] !== '' ? floatval($row[3]) : 28.5,
            'wind_speed' => isset($row[4]) && $row[4] !== '' ? floatval($row[4]) : 3.2,
            'pressure' => isset($row[5]) && $row[5] !== '' ? floatval($row[5]) : 1010.5,
            'wind_direction' => isset($row[6]) && $row[6] !== '' ? $row[6] : 'Utara',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
    
    private function parseDate($value)
    {
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        
        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            throw new \Exception("Invalid date format: {$value}");
        }
    }
    
    private function parseTime($value)
    {
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('H:i:s');
        }
        
        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Exception $e) {
            // Try to parse partial time or default
            return '00:00:00';
        }
    }
    
    private function determineTideType($height, $timeStr)
    {
        // Simple logic mainly based on height, can be improved with time context if needed
        // Assuming height in meters
        
        if ($height > 1.5) {
            return 'HIGH_TIDE';
        } elseif ($height < 0.6) {
            return 'LOW_TIDE';
        }
        
        return 'MEDIUM_TIDE';
    }
}
