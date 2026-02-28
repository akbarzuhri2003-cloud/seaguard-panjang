<?php
// app/Http/Controllers/MapController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

use App\Services\KNNTidePredictor;
use App\Models\HistoricalTide;

class MapController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        // ... (keeping existing index logic but using real status from predictor if possible)
        $locations = [
            'main_port' => [
                'lat' => -5.4755,
                'lng' => 105.3147,
                'name' => 'Pelabuhan Panjang',
                'description' => 'Pelabuhan utama dengan aktivitas bongkar muat',
                'sensor_id' => 'SENSOR-001',
            ],
            'beach_1' => [
                'lat' => -5.4720,
                'lng' => 105.3190,
                'name' => 'Pantai Marina',
                'description' => 'Area wisata pantai dengan ombak tenang',
                'sensor_id' => 'SENSOR-002',
            ],
            'beach_2' => [
                'lat' => -5.4780,
                'lng' => 105.3080,
                'name' => 'Pantai Kerang',
                'description' => 'Pantai dengan ombak tinggi, area nelayan',
                'sensor_id' => 'SENSOR-003',
            ],
        ];
        
        $predictor = new KNNTidePredictor();
        $now = Carbon::now('Asia/Jakarta');
        
        foreach ($locations as $key => &$location) {
            $prediction = $predictor->predictForDate($now->toDateString(), [
                'temperature' => 28.5,
                'pressure' => 1010.5,
                'wind_speed' => 3.2,
                'moon_phase' => 0.5,
                'day_of_year' => $now->dayOfYear,
                'hour' => $now->hour
            ]);
            
            $variation = 0;
            if ($key == 'beach_1') $variation = -0.3;
            if ($key == 'beach_2') $variation = 0.3;
            
            $location['current_height'] = $prediction ? round($prediction['predicted_height'] + $variation, 2) : 1.5;
            $location['status'] = $prediction ? $prediction['tide_type'] : 'NORMAL';
            $location['last_update'] = $now->format('H:i:s');
        }
        
        $heatmapData = [];
        foreach ($locations as $location) {
            $heatmapData[] = [$location['lat'], $location['lng'], $location['current_height'] * 10];
        }
        
        $stats = [
            'total_sensors' => count($locations),
            'high_tide_locations' => count(array_filter($locations, function($loc) { return $loc['status'] == 'TINGGI'; })),
            'avg_height' => round(array_sum(array_column($locations, 'current_height')) / count($locations), 2),
            'last_data_update' => $now->format('Y-m-d H:i:s'),
            'map_center' => ['lat' => -5.4755, 'lng' => 105.3147],
            'zoom_level' => 14
        ];
        
        return view('maps.maps', compact('locations', 'heatmapData', 'stats'));
    }

    public function exportKnnData()
    {
        $predictor = new KNNTidePredictor();
        $today = Carbon::now('Asia/Jakarta');
        $fileName = 'SeaGuard_KNN_Predictions_' . $today->format('Y-m-d') . '.csv';

        return response()->streamDownload(function() use ($predictor, $today) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'No', 
                'Tanggal', 
                'Jam (WIB)', 
                'Prediksi Tinggi Air (m)', 
                'Status Pasang Surut', 
                'Tingkat Kepercayaan',
                'Keterangan Kondisi'
            ]);

            $counter = 1;
            // Generate hourly predictions for 30 days
            for ($d = 0; $d < 30; $d++) {
                $curDate = $today->copy()->addDays($d);
                
                for ($h = 0; $h < 24; $h++) {
                    $prediction = $predictor->predictForDate($curDate->toDateString(), [
                        'temperature' => 28.5,
                        'pressure'    => 1010.5,
                        'wind_speed'  => 3.2,
                        'moon_phase'  => 0.5, 
                        'day_of_year' => $curDate->dayOfYear,
                        'hour'        => $h,
                    ]);

                    if ($prediction) {
                        $height = $prediction['predicted_height'];
                        $status = $prediction['tide_type'];
                        
                        // Detailed Status Descriptions based on SeaGuard logic
                        $description = 'NORMAL - Kondisi permukaan air stabil dan aman untuk aktivitas dermaga serta pelayaran ringan.';
                        if ($status === 'HIGH_TIDE' || $status === 'PASANG') {
                            if ($height > 2.5) {
                                $description = 'BAHAYA - Terdeteksi pasang sangat tinggi. Waspada banjir rob atau luapan air di dermaga.';
                            } else {
                                $description = 'WASPADA - Terdeteksi pasang tinggi. Harap berhati-hati saat melakukan aktivitas di bibir pantai.';
                            }
                        } elseif ($status === 'LOW_TIDE' || $status === 'SURUT') {
                            $description = 'SURUT - Permukaan air rendah. Kapal dengan draf dalam disarankan menunda manuver sandar.';
                        }

                        fputcsv($file, [
                            $counter++,
                            $curDate->format('d/m/Y'),
                            sprintf('%02d:00', $h),
                            $height,
                            $status,
                            round($prediction['confidence'] * 100, 1) . '%',
                            $description
                        ]);
                    }
                }
            }
            
            fclose($file);
        }, $fileName);
    }
}