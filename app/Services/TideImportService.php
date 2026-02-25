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
        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            array_shift($rows);
            
            $count = 0;
            $errors = [];
            
            DB::beginTransaction();
            
            foreach ($rows as $index => $row) {
                // Skip empty rows
                if (empty($row[0])) {
                    continue;
                }
                
                try {
                    $this->processRow($row);
                    $count++;
                } catch (\Exception $e) {
                    $rowNum = $index + 2; // +1 for 0-index, +1 for header
                    $errors[] = "Row {$rowNum}: " . $e->getMessage();
                    Log::warning("Error importing row {$rowNum}: " . $e->getMessage());
                }
            }
            
            DB::commit();
            
            return [
                'success' => true,
                'count' => $count,
                'errors' => $errors,
                'message' => "Successfully imported {$count} data points." . (count($errors) > 0 ? " with " . count($errors) . " errors." : "")
            ];
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Import failed: " . $e->getMessage());
            
            return [
                'success' => false,
                'message' => "Import failed: " . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ];
        }
    }
    
    private function processRow($row)
    {
        // Expected columns:
        // 0: Date (Y-m-d or Excel date format)
        // 1: Time (H:i:s or Excel time format)
        // 2: Height (numeric)
        // 3: Temperature (optional)
        // 4: Wind Speed (optional)
        // 5: Pressure (optional)
        
        $date = $this->parseDate($row[0]);
        $time = $this->parseTime($row[1] ?? '00:00:00');
        $height = floatval($row[2] ?? 0);
        
        $data = [
            'date' => $date,
            'time' => $time,
            'height' => $height,
            'type' => $this->determineTideType($height, $time),
            'temperature' => isset($row[3]) && $row[3] !== '' ? floatval($row[3]) : 28.5, // Default average temp
            'wind_speed' => isset($row[4]) && $row[4] !== '' ? floatval($row[4]) : 3.2,   // Default average wind speed
            'pressure' => isset($row[5]) && $row[5] !== '' ? floatval($row[5]) : 1010.5, // Default average pressure
            'wind_direction' => isset($row[6]) && $row[6] !== '' ? $row[6] : 'Utara',    // Default direction
        ];
        
        // Update or create based on date and time
        HistoricalTide::updateOrCreate(
            ['date' => $date, 'time' => $time],
            $data
        );
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
