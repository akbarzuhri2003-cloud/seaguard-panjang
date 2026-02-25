<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoricalTideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌊 Memulai seeding data historis pasang surut...');
        
        // Kosongkan tabel terlebih dahulu
        DB::table('historical_tides')->truncate();
        
        // Data dasar untuk Bandar Lampung - Panjang
        $locations = [
            'Pelabuhan Panjang' => [
                'lat' => -5.4755,
                'lng' => 105.3147,
                'base_height' => 1.8,
            ],
            'Pantai Marina' => [
                'lat' => -5.4720,
                'lng' => 105.3190,
                'base_height' => 1.5,
            ],
            'Pantai Kerang' => [
                'lat' => -5.4780,
                'lng' => 105.3080,
                'base_height' => 2.1,
            ],
        ];
        
        // Generate data 1 tahun terakhir (2024) agar lebih cepat
        $startDate = Carbon::create(2024, 1, 1);
        $endDate = Carbon::create(2024, 12, 31);
        $currentDate = $startDate->copy();
        
        $totalRecords = 0;
        $locationsData = ['PANJANG_PORT', 'MARINA_BEACH', 'KERANG_BEACH'];
        
        $bar = $this->command->getOutput()->createProgressBar(
            $startDate->diffInDays($endDate) * 24 // per jam selama 5 tahun
        );
        
        $this->command->info("\n📊 Generating historical data for 5 years (2020-2024)...");
        
        while ($currentDate <= $endDate) {
            $year = $currentDate->year;
            $month = $currentDate->month;
            $day = $currentDate->day;
            $dayOfYear = $currentDate->dayOfYear;
            
            // Data BMKG untuk November-Desember 2024 (periode pasang tinggi)
            $isHighTidePeriod = ($year == 2024 && ($month == 11 || $month == 12));
            
            // Generate data per jam (00:00 sampai 23:00)
            for ($hour = 0; $hour < 24; $hour++) {
                // Skip beberapa data untuk mempercepat (ambil sample 4x sehari)
                if ($hour % 6 != 0 && rand(1, 3) != 1) {
                    continue;
                }
                
                foreach ($locationsData as $location) {
                    // Hitung tinggi air berdasarkan pola pasang surut
                    $height = $this->calculateTideHeight($currentDate, $hour, $location, $isHighTidePeriod);
                    
                    // Tentukan tipe pasang surut
                    $type = $this->determineTideType($height, $hour);
                    
                    // Data cuaca berdasarkan BMKG
                    $weather = $this->generateWeatherData($currentDate, $hour, $isHighTidePeriod);
                    
                    DB::table('historical_tides')->insert([
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
            
            // Tampilkan progress setiap bulan
            if ($currentDate->day == 1) {
                $this->command->info("\n📅 Processed: " . $currentDate->copy()->subDay()->format('F Y'));
            }
        }
        
        $bar->finish();
        
        $this->command->newLine(2);
        $this->command->info('✅ Data historis berhasil dibuat!');
        $this->command->info('📈 Total records: ' . number_format($totalRecords));
        $this->command->info('📅 Periode: ' . $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y'));
        $this->command->info('📍 Lokasi: Pelabuhan Panjang, Pantai Marina, Pantai Kerang');
    }
    
    /**
     * Hitung tinggi air berdasarkan pola pasang surut
     */
    private function calculateTideHeight(Carbon $date, int $hour, string $location, bool $isHighTidePeriod): float
    {
        $year = $date->year;
        $month = $date->month;
        $day = $date->day;
        $dayOfYear = $date->dayOfYear;
        
        // Base height per lokasi
        $baseHeights = [
            'PANJANG_PORT' => 1.8,
            'MARINA_BEACH' => 1.5,
            'KERANG_BEACH' => 2.1,
        ];
        
        $baseHeight = $baseHeights[$location] ?? 1.5;
        
        // 1. Pola musiman (seasonal pattern)
        $seasonalFactor = 0;
        
        // November-Desember: periode pasang tinggi di Bandar Lampung
        if ($month == 11 || $month == 12) {
            $seasonalFactor = 0.4; // Tambah 40cm
            if ($isHighTidePeriod) {
                $seasonalFactor = 0.6; // Tambah 60cm untuk 2024
            }
        }
        // Januari-Februari: periode normal
        elseif ($month == 1 || $month == 2) {
            $seasonalFactor = 0.1;
        }
        // Juni-Juli: periode surut
        elseif ($month == 6 || $month == 7) {
            $seasonalFactor = -0.2;
        }
        
        // 2. Pola harian (tidal cycle - 2 pasang 2 surut per hari)
        // Waktu pasang: ~06:00 dan ~18:00
        // Waktu surut: ~12:00 dan ~00:00
        $hourInRadians = ($hour * 15) * pi() / 180; // 15 derajat per jam
        $tidalCycle = sin($hourInRadians) * 0.8;
        
        // Tambah faktor untuk waktu pasang tertentu
        if (($hour >= 5 && $hour <= 7) || ($hour >= 17 && $hour <= 19)) {
            $tidalCycle *= 1.2; // Amplify high tide
        }
        
        // 3. Pengaruh fase bulan
        $moonPhase = $this->calculateMoonPhase($date);
        $moonFactor = 0;
        
        if ($moonPhase < 0.1 || $moonPhase > 0.9) { // Bulan baru/purnama
            $moonFactor = 0.3; // Spring tide
        } elseif ($moonPhase > 0.4 && $moonPhase < 0.6) { // Kuarter pertama/ketiga
            $moonFactor = -0.2; // Neap tide
        }
        
        // 4. Faktor acak (variasi alam)
        $randomFactor = (rand(-15, 15) / 100); // ±15cm
        
        // 5. Hitung total tinggi air
        $totalHeight = $baseHeight + $seasonalFactor + $tidalCycle + $moonFactor + $randomFactor;
        
        // Pastikan dalam range yang wajar untuk Bandar Lampung
        $totalHeight = max(0.2, min(3.5, $totalHeight)); // Min 20cm, Max 3.5m
        
        return round($totalHeight, 2);
    }
    
    /**
     * Hitung fase bulan (0 = new moon, 0.5 = full moon, 1 = new moon)
     */
    private function calculateMoonPhase(Carbon $date): float
    {
        $daysInLunarCycle = 29.530588853;
        $referenceNewMoon = Carbon::create(2020, 1, 13); // Tanggal new moon referensi
        
        $daysSinceReference = $referenceNewMoon->diffInDays($date);
        $phase = fmod($daysSinceReference, $daysInLunarCycle) / $daysInLunarCycle;
        
        return $phase;
    }
    
    /**
     * Tentukan tipe pasang surut berdasarkan tinggi dan waktu
     */
    private function determineTideType(float $height, int $hour): string
    {
        $hourInDay = $hour % 24;
        
        // Kriteria untuk Bandar Lampung
        if ($height > 2.0) {
            return 'HIGH_TIDE';
        }
        
        if ($height < 0.8) {
            return 'LOW_TIDE';
        }
        
        // Berdasarkan waktu (pola pasang surut harian)
        if (($hourInDay >= 5 && $hourInDay <= 8) || ($hourInDay >= 17 && $hourInDay <= 20)) {
            // Waktu pasang
            return $height > 1.5 ? 'HIGH_TIDE' : 'MEDIUM_TIDE';
        }
        
        if (($hourInDay >= 11 && $hourInDay <= 14) || ($hourInDay >= 23 || $hourInDay <= 2)) {
            // Waktu surut
            return $height < 1.0 ? 'LOW_TIDE' : 'MEDIUM_TIDE';
        }
        
        return 'MEDIUM_TIDE';
    }
    
    /**
     * Generate data cuaca berdasarkan BMKG
     */
    private function generateWeatherData(Carbon $date, int $hour, bool $isHighTidePeriod): array
    {
        $month = $date->month;
        $dayOfYear = $date->dayOfYear;
        
        // Base temperature untuk Bandar Lampung
        $baseTemp = 28.0;
        
        // Suhu lebih tinggi di November-Desember
        if ($month == 11 || $month == 12) {
            $baseTemp = 29.5;
            if ($isHighTidePeriod) {
                $baseTemp = 30.0; // Lebih panas di 2024
            }
        }
        
        // Variasi harian (lebih dingin malam, panas siang)
        $dailyVariation = cos(($hour - 14) * 15 * pi() / 180) * 3; // Puncak jam 14:00
        
        // Variasi musiman
        $seasonalVariation = sin($dayOfYear * 0.0172) * 1.5; // 0.0172 = 2π/365
        
        $temperature = $baseTemp + $dailyVariation + $seasonalVariation + (rand(-10, 10) / 10);
        $temperature = round(max(25.0, min(35.0, $temperature)), 1);
        
        // Kecepatan angin (m/s)
        $windSpeed = 2.5;
        if ($month >= 11 && $month <= 12) {
            $windSpeed = 3.5; // Angin lebih kencang di musim penghujan
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
        $pressure = 1010.0;
        if ($isHighTidePeriod) {
            $pressure = 1008.0; // Sedikit lebih rendah saat pasang tinggi
        }
        $pressure += rand(-5, 5);
        $pressure = round($pressure, 1);
        
        return [
            'temperature' => $temperature,
            'wind_speed' => $windSpeed,
            'wind_direction' => $windDirection,
            'pressure' => $pressure,
        ];
    }
}