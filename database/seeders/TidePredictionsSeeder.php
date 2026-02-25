<?php
// database/seeders/TidePredictionsSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HistoricalTide;
use Carbon\Carbon;

class TidePredictionsSeeder extends Seeder
{
    public function run()
    {
        // Data historis BMKG (sample untuk November-Desember 2020-2024)
        $startDate = Carbon::create(2020, 11, 1);
        $endDate = Carbon::create(2024, 12, 31);
        
        $currentDate = $startDate->copy();
        
        while ($currentDate <= $endDate) {
            // Skip some days to make seeding faster
            if (rand(1, 3) === 1) {
                $currentDate->addDay();
                continue;
            }
            
            // Create 4 records per day (every 6 hours)
            for ($i = 0; $i < 4; $i++) {
                $hour = $i * 6;
                
                // Base height with seasonal variation
                $baseHeight = $this->getBaseHeight($currentDate);
                
                // Add random variation
                $height = $baseHeight + (rand(-20, 20) / 100);
                
                // Determine tide type based on time and height
                $type = $this->getTideType($hour, $height);
                
                HistoricalTide::create([
                    'date' => $currentDate->toDateString(),
                    'time' => Carbon::createFromTime($hour, 0, 0)->format('H:i:s'),
                    'height' => $height,
                    'type' => $type,
                    'temperature' => $this->getTemperature($currentDate),
                    'wind_speed' => rand(10, 50) / 10,
                    'wind_direction' => $this->getWindDirection(),
                    'pressure' => 1010 + rand(-10, 10),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $currentDate->addDay();
        }
        
        $this->command->info('Historical tide data seeded successfully!');
    }
    
    private function getBaseHeight($date)
    {
        $month = $date->month;
        $day = $date->day;
        
        // Higher tides in November-December for Bandar Lampung
        if ($month == 11 || $month == 12) {
            return 1.8 + (sin($day * 0.2) * 0.5);
        }
        
        return 1.2 + (sin($day * 0.2) * 0.3);
    }
    
    private function getTideType($hour, $height)
    {
        // High tide around 6 AM and 6 PM
        if (($hour >= 5 && $hour <= 7) || ($hour >= 17 && $hour <= 19)) {
            return $height > 1.5 ? 'HIGH_TIDE' : 'MEDIUM_TIDE';
        }
        // Low tide around 12 PM and 12 AM
        elseif (($hour >= 11 && $hour <= 13) || ($hour == 0 || $hour == 23)) {
            return $height < 0.8 ? 'LOW_TIDE' : 'MEDIUM_TIDE';
        }
        
        return 'MEDIUM_TIDE';
    }
    
    private function getTemperature($date)
    {
        $month = $date->month;
        
        // Warmer in November-December
        if ($month == 11 || $month == 12) {
            return 28.5 + rand(0, 15) / 10;
        }
        
        return 27.0 + rand(0, 20) / 10;
    }
    
    private function getWindDirection()
    {
        $directions = ['Timur', 'Timur Laut', 'Utara', 'Barat Laut', 'Barat', 'Barat Daya', 'Selatan', 'Tenggara'];
        return $directions[array_rand($directions)];
    }
}