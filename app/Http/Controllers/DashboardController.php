<?php
// app/Http\Controllers\DashboardController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\KNNTidePredictor;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct()
    {
        // Middleware di constructor
        $this->middleware('auth');
    }
    
    public function index()
    {
        $cacheKey = 'dashboard_predictions_30_days';
        
        $data = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addHours(1), function () {
            $predictor = new KNNTidePredictor();
            $today = Carbon::now('Asia/Jakarta');
            
            // Prediksi untuk 30 hari ke depan
            $predictions = [];
            $hasData = false;
            
            // Cek dulu apakah ada data histori sama sekali
            if (\App\Models\HistoricalTide::exists()) {
                for ($i = 0; $i < 30; $i++) {
                    $date = $today->copy()->addDays($i)->toDateString();
                    $prediction = $predictor->predictForDate($date);
                    if ($prediction) {
                        $predictions[] = $prediction;
                        $hasData = true;
                    }
                }
            }
            
            // Jika tidak ada data valid, kosongkan array
            if (!$hasData) {
                $predictions = [];
            }
            
            // Data untuk chart
            $chartData = [
                'dates' => !empty($predictions) ? array_map(function($prediction) {
                    return \Carbon\Carbon::parse($prediction['date'])->format('d M');
                }, $predictions) : [],
                'heights' => !empty($predictions) ? array_map(function($prediction) {
                    return (float) $prediction['predicted_height'];
                }, $predictions) : [],
                'tide_types' => !empty($predictions) ? array_column($predictions, 'tide_type') : [],
            ];

            return compact('predictions', 'chartData');
        });
        
        return view('dashboard.index', $data);
    }
    
    public function weeklyPrediction()
    {
        $predictor = new KNNTidePredictor();
        $today = Carbon::now('Asia/Jakarta');
        
        // Prediksi untuk 7 hari ke depan dengan detail per jam
        $weeklyPredictions = [];
        for ($day = 0; $day < 7; $day++) {
            $date = $today->copy()->addDays($day);
            $dailyPredictions = [];
            
            for ($hour = 0; $hour < 24; $hour += 3) {
                $predictionDate = $date->copy()->setTime($hour, 0);
                
                try {
                    $prediction = $predictor->predictForDate(
                        $predictionDate->toDateString()
                    );
                    
                    if ($prediction) {
                        $dailyPredictions[] = [
                            'time' => $predictionDate->format('H:i'),
                            'height' => $prediction['predicted_height'],
                            'type' => $prediction['tide_type'],
                        ];
                    }
                } catch (\Exception $e) {
                    // Ignore errors
                }
            }
            
            if (!empty($dailyPredictions)) {
                $weeklyPredictions[] = [
                    'date' => $date->format('Y-m-d'),
                    'day_name' => $date->translatedFormat('l'),
                    'date_formatted' => $date->format('d M Y'),
                    'predictions' => $dailyPredictions,
                ];
            }
        }
        
        return view('dashboard.weekly', compact('weeklyPredictions'));
    }
    
    public function maps()
    {
        // Koordinat Panjang, Bandar Lampung
        $locations = [
            'main_port' => [
                'lat' => -5.4755,
                'lng' => 105.3147,
                'name' => 'Pelabuhan Panjang',
                'description' => 'Pelabuhan utama dengan aktivitas bongkar muat'
            ],
            'beach_1' => [
                'lat' => -5.4720,
                'lng' => 105.3190,
                'name' => 'Pantai Marina',
                'description' => 'Area wisata pantai dengan ombak tenang'
            ],
            'beach_2' => [
                'lat' => -5.4780,
                'lng' => 105.3080,
                'name' => 'Pantai Kerang',
                'description' => 'Pantai dengan ombak tinggi, area nelayan'
            ],
        ];
        
        // Cek data terbaru dari DB
        $latest = \App\Models\HistoricalTide::latest('date')->latest('time')->first();
        
        if ($latest) {
             foreach ($locations as $key => &$location) {
                // Variasi kecil antar lokasi based on real data
                $variation = 0; 
                if ($key == 'beach_1') $variation = -0.3;
                if ($key == 'beach_2') $variation = 0.3;
                
                $location['current_height'] = $latest->height + $variation;
                $location['status'] = $latest->type;
             }
        }
        
        // Data heatmap hanya jika ada data real
        $heatmapData = [];
        if ($latest) {
            foreach ($locations as $location) {
                if (isset($location['current_height'])) {
                    $heatmapData[] = [
                        $location['lat'], 
                        $location['lng'], 
                        $location['current_height'] * 10
                    ];
                }
            }
        }
        
        return view('maps.maps', compact('locations', 'heatmapData'));
    }
    
    // Method untuk data real-time (API)
    public function getRealTimeData()
    {
        try {
            $currentTime = Carbon::now('Asia/Jakarta');

            $latestTide = \App\Models\HistoricalTide::orderBy('date', 'desc')
                ->orderBy('time', 'desc')
                ->first();

            if (!$latestTide) {
                return response()->json([
                    'status' => 'empty',
                    'message' => 'No data available'
                ]);
            }

            // KNN Prediction
            try {
                $predictor  = new \App\Services\KNNTidePredictor();
                $prediction = $predictor->predictForDate($currentTime->toDateString());
                $tideHeight = $prediction ? $prediction['predicted_height'] : (float)$latestTide->height;
                $tideType   = $prediction ? $prediction['tide_type'] : ($latestTide->type ?? 'MEDIUM_TIDE');
            } catch (\Exception $e) {
                $tideHeight = (float)$latestTide->height;
                $tideType   = $latestTide->type ?? 'MEDIUM_TIDE';
            }

            // Weather Data
            $weatherData = $this->fetchOpenMeteoWeather();

            // Date formatting
            $datePart = $latestTide->date instanceof \Carbon\Carbon ? $latestTide->date->toDateString() : $latestTide->date;
            $timePart = $latestTide->time instanceof \Carbon\Carbon ? $latestTide->time->toTimeString() : $latestTide->time;

            return response()->json([
                'status'         => 'ok',
                'time'           => $currentTime->format('H:i:s'),
                'date'           => $currentTime->format('d M Y'),
                'tide_height'    => $tideHeight,
                'tide_type'      => $tideType,
                'temperature'    => $weatherData['temperature'] ?? null,
                'wind_speed'     => $weatherData['wind_speed'] ?? null,
                'wind_direction' => $weatherData['wind_direction'] ?? null,
                'humidity'       => $weatherData['humidity'] ?? null,
                'pressure'       => $weatherData['pressure'] ?? null,
                'weather_source' => $weatherData['source'] ?? 'unknown',
                'last_update'    => Carbon::parse($datePart . ' ' . $timePart)->diffForHumans(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ambil data cuaca real-time dari Open-Meteo API
     * Koordinat Pelabuhan Panjang, Bandar Lampung: -5.4755, 105.3147
     */
    private function fetchOpenMeteoWeather()
    {
        try {
            $url = 'https://api.open-meteo.com/v1/forecast'
                . '?latitude=-5.4755'
                . '&longitude=105.3147'
                . '&current=temperature_2m,relative_humidity_2m,wind_speed_10m,wind_direction_10m,pressure_msl,weather_code'
                . '&wind_speed_unit=ms'
                . '&timezone=Asia%2FJakarta';

            $context = stream_context_create([
                'http' => [
                    'timeout'       => 5,
                    'method'        => 'GET',
                    'ignore_errors' => true,
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false,
                ],
            ]);

            $response = @file_get_contents($url, false, $context);

            if ($response === false) {
                return ['source' => 'unavailable'];
            }

            $json = json_decode($response, true);

            if (!isset($json['current'])) {
                return ['source' => 'parse_error'];
            }

            $current = $json['current'];

            // Konversi arah angin dari derajat ke nama arah
            $windDeg = $current['wind_direction_10m'] ?? 0;
            $windDir = $this->degreeToWindDirection($windDeg);

            return [
                'temperature'    => $current['temperature_2m'] ?? null,
                'wind_speed'     => $current['wind_speed_10m'] ?? null,
                'wind_direction' => $windDir,
                'wind_degree'    => $windDeg,
                'humidity'       => $current['relative_humidity_2m'] ?? null,
                'pressure'       => $current['pressure_msl'] ?? null,
                'weather_code'   => $current['weather_code'] ?? null,
                'source'         => 'open-meteo',
            ];

        } catch (\Exception $e) {
            \Log::warning('Open-Meteo API error: ' . $e->getMessage());
            return ['source' => 'error'];
        }
    }

    /**
     * Konversi derajat angin ke nama arah (8 mata angin)
     */
    private function degreeToWindDirection($degree)
    {
        $directions = ['Utara', 'Timur Laut', 'Timur', 'Tenggara',
                       'Selatan', 'Barat Daya', 'Barat', 'Barat Laut'];
        $index = (int)(($degree + 22.5) / 45) % 8;
        return $directions[$index];
    }


    public function import(Request $request, \App\Services\TideImportService $importService)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048'
        ]);

        $result = $importService->import($request->file('file'));

        if ($result['success']) {
            return redirect()->route('dashboard')->with('success', $result['message'])->with('import_errors', $result['errors']);
        } else {
            return redirect()->route('dashboard')->with('error', $result['message']);
        }
    }
}