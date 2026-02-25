<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TideHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌊 Memulai seeding data historis pasang surut...');
        
        // Kosongkan tabel terlebih dahulu
        DB::table('tide_history')->truncate();
        
        // Generate data untuk 2 tahun terakhir (cukup untuk training)
        $startDate = Carbon::now()->subYears(2);
        $endDate = Carbon::now();
        $currentDate = $startDate->copy();
        
        $totalRecords = 0;
        $locations = ['PANJANG_PORT', 'MARINA_BEACH', 'KERANG_BEACH'];
        
        $bar = $this->command->getOutput()->createProgressBar(
            $startDate->diffInDays($endDate) * 8 // 8 data per hari
        );
        
        $this->command->info("\n📊 Generating historical data for 2 years...");
        
        while ($currentDate <= $endDate) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            $day = $currentDate->day;
            $dayOfYear = $currentDate->dayOfYear;
            
            // Generate data 8x sehari (setiap 3 jam)
            for ($hour = 0; $hour < 24; $hour += 3) {
                foreach ($locations as $location) {
                    // Hitung tinggi air berdasarkan pola pasang surut
                    $height = $this->calculateTideHeight($currentDate, $hour, $location);
                    
                    // Tentukan tipe pasang surut
                    $type = $this->determineTideType($height, $hour);
                    
                    // Data cuaca berdasarkan BMKG
                    $weather = $this->generateWeatherData($currentDate, $hour);
                    
                    DB::table('tide_history')->insert([
                        'date' => $currentDate->toDateString(),
                        'time' => sprintf('%02d:00:00', $hour),
                        'height' => $height,
                        'type' => $type,
                        'temperature' => $weather['temperature'],
                        'wind_speed' => $weather['wind_speed'],
                        'wind_direction' => $weather['wind_direction'],
                        'pressure' => $weather['pressure'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    $totalRecords++;
                }
                
                $bar->advance();
            }
            
            $currentDate->addDay();
        }
        
        $bar->finish();
        
        $this->command->newLine(2);
        $this->command->info('✅ Data historis berhasil dibuat!');
        $this->command->info('📈 Total records: ' . number_format($totalRecords));
        $this->command->info('📅 Periode: ' . $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'));
    }
    
    /**
     * Hitung tinggi air berdasarkan pola pasang surut
     */
    private function calculateTideHeight(Carbon $date, int $hour, string $location): float
    {
        $month = $date->month;
        $dayOfYear = $date->dayOfYear;
        
        // Base height per lokasi
        $baseHeights = [
            'PANJANG_PORT' => 1.8,
            'MARINA_BEACH' => 1.5,
            'KERANG_BEACH' => 2.1,
        ];
        
        $baseHeight = $baseHeights[$location] ?? 1.5;
        
        // 1. Pola musiman
        $seasonalFactor = 0;
        
        // November-Desember: periode pasang tinggi di Bandar Lampung
        if ($month == 11 || $month == 12) {
            $seasonalFactor = 0.4;
        }
        // Juni-Juli: periode surut
        elseif ($month == 6 || $month == 7) {
            $seasonalFactor = -0.2;
        }
        
        // 2. Pola harian (tidal cycle)
        $hourInRadians = ($hour * 15) * pi() / 180;
        $tidalCycle = sin($hourInRadians) * 0.8;
        
        // Tambah faktor untuk waktu pasang
        if (($hour >= 5 && $hour <= 7) || ($hour >= 17 && $hour <= 19)) {
            $tidalCycle *= 1.2;
        }
        
        // 3. Pengaruh fase bulan
        $moonPhase = $this->calculateMoonPhase($date);
        $moonFactor = 0;
        
        if ($moonPhase < 0.1 || $moonPhase > 0.9) {
            $moonFactor = 0.3;
        } elseif ($moonPhase > 0.4 && $moonPhase < 0.6) {
            $moonFactor = -0.2;
        }
        
        // 4. Faktor acak
        $randomFactor = (rand(-15, 15) / 100);
        
        // 5. Hitung total tinggi air
        $totalHeight = $baseHeight + $seasonalFactor + $tidalCycle + $moonFactor + $randomFactor;
        
        // Pastikan dalam range yang wajar
        $totalHeight = max(0.2, min(3.5, $totalHeight));
        
        return round($totalHeight, 2);
    }
    
    /**
     * Hitung fase bulan
     */
    private function calculateMoonPhase(Carbon $date): float
    {
        $daysInLunarCycle = 29.530588853;
        $referenceNewMoon = Carbon::create(2020, 1, 13);
        
        $daysSinceReference = $referenceNewMoon->diffInDays($date);
        $phase = fmod($daysSinceReference, $daysInLunarCycle) / $daysInLunarCycle;
        
        return $phase;
    }
    
    /**
     * Tentukan tipe pasang surut
     */
    private function determineTideType(float $height, int $hour): string
    {
        $hourInDay = $hour % 24;
        
        if ($height > 2.0) {
            return 'HIGH_TIDE';
        }
        
        if ($height < 0.8) {
            return 'LOW_TIDE';
        }
        
        if (($hourInDay >= 5 && $hourInDay <= 8) || ($hourInDay >= 17 && $hourInDay <= 20)) {
            return $height > 1.5 ? 'HIGH_TIDE' : 'MEDIUM_TIDE';
        }
        
        if (($hourInDay >= 11 && $hourInDay <= 14) || ($hourInDay >= 23 || $hourInDay <= 2)) {
            return $height < 1.0 ? 'LOW_TIDE' : 'MEDIUM_TIDE';
        }
        
        return 'MEDIUM_TIDE';
    }
    
    /**
     * Generate data cuaca
     */
    private function generateWeatherData(Carbon $date, int $hour): array
    {
        $month = $date->month;
        $dayOfYear = $date->dayOfYear;
        
        // Base temperature untuk Bandar Lampung
        $baseTemp = 28.0;
        
        // Suhu lebih tinggi di November-Desember
        if ($month == 11 || $month == 12) {
            $baseTemp = 29.5;
        }
        
        // Variasi harian
        $dailyVariation = cos(($hour - 14) * 15 * pi() / 180) * 3;
        
        // Variasi musiman
        $seasonalVariation = sin($dayOfYear * 0.0172) * 1.5;
        
        $temperature = $baseTemp + $dailyVariation + $seasonalVariation + (rand(-10, 10) / 10);
        $temperature = round(max(25.0, min(35.0, $temperature)), 1);
        
        // Kecepatan angin (m/s)
        $windSpeed = 2.5;
        if ($month >= 11 && $month <= 12) {
            $windSpeed = 3.5;
        }
        $windSpeed += (rand(0, 20) / 10);
        $windSpeed = round($windSpeed, 1);
        
        // Arah angin dominan di Bandar Lampung
        $windDirections = [
            'Timur', 'Timur Laut', 'Timur Laut', 'Timur',
            'Barat Daya', 'Barat Daya', 'Selatan', 'Selatan',
            'Tenggara', 'Tenggara'
        ];
        $windDirection = $windDirections[array_rand($windDirections)];
        
        // Tekanan udara (hPa)
        $pressure = 1010.0 + rand(-5, 5);
        $pressure = round($pressure, 1);
        
        return [
            'temperature' => $temperature,
            'wind_speed' => $windSpeed,
            'wind_direction' => $windDirection,
            'pressure' => $pressure,
        ];
    }
}