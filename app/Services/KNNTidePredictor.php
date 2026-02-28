<?php
// app/Services/KNNTidePredictor.php

namespace App\Services;

use App\Models\HistoricalTide;
use Illuminate\Support\Carbon;

class KNNTidePredictor
{
    private $k = 3;
    private $historicalDataCache = null;
    
    public function predictForDate($date, $features = null)
    {
        try {
            if (!$features) {
                $features = $this->getDefaultFeatures($date);
            }
            
            $historicalData = $this->getHistoricalData($date);
            
            if ($historicalData->isEmpty()) {
                return $this->getDefaultPrediction($date);
            }
            
            $distances = [];
            $weights = [
                'temperature' => 0.2,
                'pressure'    => 0.3,
                'wind_speed'  => 0.2,
                'moon_phase'  => 0.15,
                'day_of_year' => 0.1,
                'hour'        => 0.05,
            ];
            
            foreach ($historicalData as $data) {
                // Pre-calculate data features to avoid excessive Carbon calls in loop
                $dataCarbon = Carbon::parse($data->date);
                $dataTime = Carbon::parse($data->time);
                
                $dataFeatures = [
                    'temperature' => $data->temperature,
                    'pressure' => $data->pressure,
                    'wind_speed' => $data->wind_speed,
                    'moon_phase' => $this->getMoonPhase($dataCarbon),
                    'day_of_year' => $dataCarbon->dayOfYear,
                    'hour' => $dataTime->hour,
                ];

                $sum = 0;
                foreach ($weights as $key => $weight) {
                    $val1 = $features[$key] ?? 0;
                    $val2 = $dataFeatures[$key] ?? 0;
                    $diff = $val1 - $val2;
                    $sum += $weight * ($diff * $diff);
                }
                $distance = sqrt($sum);
                
                $distances[] = [
                    'distance' => $distance,
                    'data' => $data
                ];
            }
            
            usort($distances, function($a, $b) {
                return $a['distance'] <=> $b['distance'];
            });
            
            $nearestNeighbors = array_slice($distances, 0, $this->k);
            
            return $this->calculatePrediction($nearestNeighbors, $date);
            
        } catch (\Exception $e) {
            // Fallback jika ada error
            return $this->getDefaultPrediction($date);
        }
    }
    
    private function getHistoricalData($date)
    {
        // Simple internal cache for the request duration
        if ($this->historicalDataCache !== null) {
            return $this->historicalDataCache;
        }

        try {
            if (!\Schema::hasTable('historical_tides')) {
                return collect();
            }

            $carbonDate = Carbon::parse($date);

            // Strategi 1: Cari data bulan yang sama (±1 bulan)
            $data = HistoricalTide::where(function($q) use ($carbonDate) {
                    $q->whereMonth('date', $carbonDate->month)
                      ->orWhereMonth('date', $carbonDate->copy()->subMonth()->month)
                      ->orWhereMonth('date', $carbonDate->copy()->addMonth()->month);
                })
                ->orderBy('date', 'desc')
                ->take(500) // Increased for better accuracy now that it's cached
                ->get();

            // Strategi 2: Jika kurang dari 3 record, pakai semua data yang ada
            if ($data->count() < 3) {
                $data = HistoricalTide::orderBy('date', 'desc')
                    ->take(1000)
                    ->get();
            }

            $this->historicalDataCache = $data;
            return $data;

        } catch (\Exception $e) {
            return collect();
        }
    }

    private function getDefaultFeatures($date)
    {
        $carbonDate = Carbon::parse($date);
        return [
            'temperature' => 28.5,
            'pressure'    => 1010.5,
            'wind_speed'  => 3.2,
            'moon_phase'  => $this->getMoonPhase($carbonDate),
            'day_of_year' => $carbonDate->dayOfYear,
            'hour'        => 12,
        ];
    }

    private function getMoonPhase($date)
    {
        $daysInLunarCycle  = 29.530588853;
        $referenceNewMoon  = Carbon::create(2024, 1, 11);
        $daysSinceReference = $referenceNewMoon->diffInDays($date);
        return fmod($daysSinceReference, $daysInLunarCycle) / $daysInLunarCycle;
    }

    private function calculatePrediction($neighbors, $date)
    {
        if (empty($neighbors)) {
            return $this->getDefaultPrediction($date);
        }

        $totalHeight      = 0;
        $totalTemperature = 0;
        $totalWindSpeed   = 0;
        $totalWeight      = 0;

        foreach ($neighbors as $neighbor) {
            $weight       = 1 / ($neighbor['distance'] + 0.0001);
            $totalWeight  += $weight;
            $data          = $neighbor['data'];
            $totalHeight      += (float)$data->height * $weight;
            $totalTemperature += (float)($data->temperature ?? 28.5) * $weight;
            $totalWindSpeed   += (float)($data->wind_speed ?? 3.2) * $weight;
        }

        $carbonDate  = Carbon::parse($date);
        $tidalFactor = $this->calculateTidalFactor($carbonDate);
        $avgHeight   = $totalWeight > 0 ? ($totalHeight / $totalWeight) * $tidalFactor : 1.5;
        $avgHeight   = max(0.1, min(4.0, $avgHeight));

        return [
            'date'                 => $date,
            'predicted_height'     => round($avgHeight, 2),
            'predicted_temperature'=> $totalWeight > 0 ? round($totalTemperature / $totalWeight, 1) : 28.5,
            'predicted_wind_speed' => $totalWeight > 0 ? round($totalWindSpeed / $totalWeight, 1) : 3.2,
            'tide_type'            => $this->predictTideType($avgHeight, $carbonDate->hour),
            'confidence'           => min(0.95, $totalWeight / (count($neighbors) * 10)),
        ];
    }

    private function calculateTidalFactor($date)
    {
        $moonPhase = $this->getMoonPhase($date);
        if ($moonPhase < 0.1 || $moonPhase > 0.9) return 1.2;
        if ($moonPhase > 0.4 && $moonPhase < 0.6) return 0.8;
        return 1.0;
    }

    private function predictTideType($height, $hour)
    {
        $hourInDay = $hour % 24;
        if (($hourInDay >= 5 && $hourInDay <= 8) || ($hourInDay >= 17 && $hourInDay <= 20)) {
            return $height > 1.5 ? 'HIGH_TIDE' : 'MEDIUM_TIDE';
        } elseif (($hourInDay >= 11 && $hourInDay <= 14) || ($hourInDay >= 23 || $hourInDay <= 2)) {
            return $height < 0.5 ? 'LOW_TIDE' : 'MEDIUM_TIDE';
        }
        return 'MEDIUM_TIDE';
    }

    private function getDefaultPrediction($date)
    {
        // Jika ada data di DB, gunakan rata-rata sebagai fallback
        try {
            $avg = HistoricalTide::avg('height');
            if ($avg !== null) {
                $carbonDate = Carbon::parse($date);
                $avgHeight  = max(0.1, min(4.0, (float)$avg));
                return [
                    'date'                  => $date,
                    'predicted_height'      => round($avgHeight, 2),
                    'predicted_temperature' => round((float)(HistoricalTide::avg('temperature') ?? 28.5), 1),
                    'predicted_wind_speed'  => round((float)(HistoricalTide::avg('wind_speed') ?? 3.2), 1),
                    'tide_type'             => $this->predictTideType($avgHeight, 12),
                    'confidence'            => 0.3,
                ];
            }
        } catch (\Exception $e) {
            // ignore
        }
        return null;
    }
}
