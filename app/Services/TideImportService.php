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
            
            // The format from user screenshot shows headers (JAM) in row 1, 
            // hours (1-24) in row 2, and data starting row 3.
            $dataToUpsert = [];
            $errors = [];
            
            // Skip first two rows (headers)
            $dataRows = array_slice($rows, 2);
            
            foreach ($dataRows as $rowIndex => $row) {
                // Skip empty rows
                if (empty($row[0])) {
                    continue;
                }
                
                try {
                    $date = $this->parseDate($row[0]);
                    
                    // Iterate columns 1 to 24 (index 1 to 24)
                    for ($hour = 1; $hour <= 24; $hour++) {
                        $value = $row[$hour] ?? null;
                        
                        if ($value === null || $value === '') {
                            continue;
                        }

                        $height = floatval($value);
                        $time = sprintf('%02d:00:00', $hour % 24);
                        if ($hour == 24) $time = '00:00:00'; 

                        $dataToUpsert[] = [
                            'date' => $date,
                            'time' => $time,
                            'height' => $height,
                            'type' => $this->determineTideType($height, $time),
                            'temperature' => 28.5,
                            'wind_speed' => 3.2,
                            'pressure' => 1010.5,
                            'wind_direction' => 'Utara',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                } catch (\Exception $e) {
                    $rowNum = $rowIndex + 3; 
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
            
            // Clear dashboard cache after import
            \Illuminate\Support\Facades\Cache::forget('dashboard_predictions_30_days');
            
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

    private function parseDate($value)
    {
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }
        
        // Handle "01-Nov" formats
        try {
            // Assume 2025 as per screenshot "november-desember 2025"
            $date = Carbon::parse($value . ' 2025');
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            try {
                return Carbon::parse($value)->format('Y-m-d');
            } catch (\Exception $e2) {
                throw new \Exception("Invalid date format: {$value}");
            }
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
