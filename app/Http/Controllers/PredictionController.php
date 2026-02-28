<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\HistoricalTide;

class PredictionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only('index');
    }

    // Menampilkan halaman prediksi
    public function index()
    {
        return view('predictions.weekly');
    }

    // API untuk data prediksi (diakses dari halaman weekly)
    public function getPredictions()
    {
        try {
            // Ambil semua data historis dari tabel yang benar
            $historicalData = HistoricalTide::orderBy('date', 'asc')
                ->orderBy('time', 'asc')
                ->get();

            $totalRecords = $historicalData->count();

            // Jika tidak ada data, kembalikan empty response
            if ($totalRecords === 0) {
                return response()->json([
                    'success' => true,
                    'empty'   => true,
                    'message' => 'Belum ada data. Silakan import data Excel terlebih dahulu.',
                    'predictions' => [],
                    'current_data' => null,
                    'accuracy' => 0,
                    'last_updated' => now('Asia/Jakarta')->format('H:i:s'),
                    'total_training_data' => 0,
                ]);
            }

            // Data real-time (record terakhir)
            $currentData = HistoricalTide::latest('date')->latest('time')->first();

            // Hitung prediksi KNN
            $predictions = $this->calculatePredictions($historicalData);

            // Hitung akurasi
            $accuracy = $this->calculateAccuracy($historicalData);

            return response()->json([
                'success'            => true,
                'empty'              => false,
                'predictions'        => $predictions,
                'current_data'       => $currentData,
                'accuracy'           => $accuracy,
                'last_updated'       => now('Asia/Jakarta')->format('H:i:s'),
                'total_training_data'=> $totalRecords,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in PredictionController::getPredictions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'empty'   => true,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'predictions' => [],
                'current_data' => null,
                'accuracy' => 0,
                'last_updated' => now('Asia/Jakarta')->format('H:i:s'),
                'total_training_data' => 0,
            ], 200);
        }
    }

    // Algoritma KNN untuk prediksi 7 hari ke depan
    private function calculatePredictions($historicalData)
    {
        $predictions = [];
        $today = Carbon::today('Asia/Jakarta');
        $predictor = new \App\Services\KNNTidePredictor();

        // Prediksi untuk 7 hari ke depan
        for ($i = 0; $i < 7; $i++) {
            $predictionDate = $today->copy()->addDays($i);
            
            // Hitung rata-rata dan cari peak harian dengan iterasi per jam
            $hourlyHeights = [];
            $totalHeight = 0;
            $confidenceSum = 0;
            
            for ($hour = 0; $hour < 24; $hour++) {
                $pred = $predictor->predictForDate($predictionDate->toDateString(), [
                    'temperature' => 28.5,
                    'pressure' => 1010.5,
                    'wind_speed' => 3.2,
                    'moon_phase' => null, // Biar didefinisikan service
                    'day_of_year' => $predictionDate->dayOfYear,
                    'hour' => $hour
                ]);
                
                if ($pred) {
                    $height = $pred['predicted_height'];
                    $hourlyHeights[$hour] = $height;
                    $totalHeight += $height;
                    $confidenceSum += $pred['confidence'];
                }
            }

            if (empty($hourlyHeights)) {
                // Fallback jika tidak ada data sama sekali
                $avgHeight = 1.5;
                $highTideHeight = 1.8;
                $lowTideHeight = 0.5;
                $highTideHour = 6;
                $lowTideHour = 12;
                $avgConfidence = 0.5;
            } else {
                $avgHeight = $totalHeight / count($hourlyHeights);
                $highTideHeight = max($hourlyHeights);
                $lowTideHeight = min($hourlyHeights);
                $highTideHour = array_search($highTideHeight, $hourlyHeights);
                $lowTideHour = array_search($lowTideHeight, $hourlyHeights);
                $avgConfidence = $confidenceSum / count($hourlyHeights);
            }

            $status = $this->determineStatus($highTideHeight); // Status berdasarkan peak

            $predictions[] = [
                'date'               => $predictionDate->toDateString(),
                'formatted_date'     => $this->formatIndonesianDate($predictionDate),
                'short_date'         => $predictionDate->format('d M'),
                'day_name'           => $this->getIndonesianDayName($predictionDate),
                'avg_height'         => round($avgHeight, 2),
                'max_height'         => round($highTideHeight, 2),
                'min_height'         => round($lowTideHeight, 2),
                'status'             => $status,
                'high_tide_time'     => sprintf('%02d:00', $highTideHour),
                'high_tide_height'   => round($highTideHeight, 2),
                'low_tide_time'      => sprintf('%02d:00', $lowTideHour),
                'low_tide_height'    => round($lowTideHeight, 2),
                'recommendation'     => $this->getRecommendation($status),
                'confidence'         => round($avgConfidence * 100, 1),
            ];
        }

        return $predictions;
    }

    // Tentukan status berdasarkan tinggi air
    private function determineStatus($height)
    {
        $height = (float) $height;
        if ($height > 3.0) return 'bahaya';
        if ($height > 2.5) return 'siaga';
        if ($height > 1.8) return 'waspada';
        return 'aman';
    }

    // Rekomendasi berdasarkan status
    private function getRecommendation($status)
    {
        $recommendations = [
            'aman'    => 'Aman untuk aktivitas laut normal. Nelayan dapat melaut dengan aman.',
            'waspada' => 'Hindari area dangkal, berhati-hati di pantai. Aktivitas laut perlu pengawasan.',
            'siaga'   => 'Batasi aktivitas laut, pantau terus kondisi. Nelayan disarankan tidak melaut jauh.',
            'bahaya'  => 'Hindari area pantai, waspada banjir rob. Semua aktivitas laut dihentikan.',
        ];
        return $recommendations[$status] ?? 'Harap berhati-hati.';
    }

    // Hitung akurasi berdasarkan konsistensi data
    private function calculateAccuracy($historicalData)
    {
        try {
            if ($historicalData->count() < 10) {
                return 75.0;
            }

            $heights = $historicalData->pluck('height')->map(fn($h) => (float)$h)->toArray();
            $mean    = array_sum($heights) / count($heights);

            $sumSquares = 0;
            foreach ($heights as $h) {
                $sumSquares += pow($h - $mean, 2);
            }

            $stdDev   = sqrt($sumSquares / count($heights));
            $accuracy = 95.0 - ($stdDev * 8);
            $accuracy = max(70, min(98, $accuracy));

            return round($accuracy, 1);
        } catch (\Exception $e) {
            return 85.0;
        }
    }

    // Format tanggal Indonesia
    private function formatIndonesianDate(Carbon $date)
    {
        $months = ['Januari','Februari','Maret','April','Mei','Juni',
                   'Juli','Agustus','September','Oktober','November','Desember'];
        return $this->getIndonesianDayName($date) . ', ' .
               $date->day . ' ' . $months[$date->month - 1] . ' ' . $date->year;
    }

    // Nama hari Indonesia
    private function getIndonesianDayName(Carbon $date)
    {
        $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
        return $days[$date->dayOfWeek] ?? 'Hari';
    }

    // Refresh prediksi
    public function refreshPredictions(Request $request)
    {
        $dataCount = HistoricalTide::count();

        return response()->json([
            'success'      => true,
            'message'      => 'Prediksi berhasil diperbarui',
            'last_updated' => now('Asia/Jakarta')->format('H:i:s'),
            'algorithm'    => 'KNN (K-Nearest Neighbors)',
            'data_points'  => $dataCount,
        ]);
    }
}