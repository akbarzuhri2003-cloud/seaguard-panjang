<?php
// app/Http/Controllers/MapController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MapController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function index()
    {
        $locations = [
            'main_port' => [
                'lat' => -5.4755,
                'lng' => 105.3147,
                'name' => 'Pelabuhan Panjang',
                'current_height' => 1.8,
                'status' => 'NORMAL',
                'description' => 'Pelabuhan utama dengan aktivitas bongkar muat',
                'sensor_id' => 'SENSOR-001',
                'last_update' => now()->format('H:i:s')
            ],
            'beach_1' => [
                'lat' => -5.4720,
                'lng' => 105.3190,
                'name' => 'Pantai Marina',
                'current_height' => 1.5,
                'status' => 'RENDAH',
                'description' => 'Area wisata pantai dengan ombak tenang',
                'sensor_id' => 'SENSOR-002',
                'last_update' => now()->format('H:i:s')
            ],
            'beach_2' => [
                'lat' => -5.4780,
                'lng' => 105.3080,
                'name' => 'Pantai Kerang',
                'current_height' => 2.1,
                'status' => 'TINGGI',
                'description' => 'Pantai dengan ombak tinggi, area nelayan',
                'sensor_id' => 'SENSOR-003',
                'last_update' => now()->format('H:i:s')
            ],
        ];
        
        $heatmapData = [];
        foreach ($locations as $location) {
            $heatmapData[] = [
                $location['lat'], 
                $location['lng'], 
                $location['current_height'] * 10
            ];
        }
        
        for ($i = 0; $i < 20; $i++) {
            $heatmapData[] = [
                -5.475 + (rand(-100, 100) / 10000),
                105.315 + (rand(-100, 100) / 10000),
                rand(5, 20)
            ];
        }
        
        $stats = [
            'total_sensors' => count($locations),
            'high_tide_locations' => count(array_filter($locations, function($loc) { 
                return $loc['status'] == 'TINGGI'; 
            })),
            'avg_height' => round(array_sum(array_column($locations, 'current_height')) / count($locations), 2),
            'last_data_update' => now()->format('Y-m-d H:i:s'),
            'map_center' => ['lat' => -5.4755, 'lng' => 105.3147],
            'zoom_level' => 14
        ];
        
        return view('maps.maps', compact('locations', 'heatmapData', 'stats'));
    }
}