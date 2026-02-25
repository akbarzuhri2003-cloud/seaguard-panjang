@extends('layouts.app')

@section('title', 'Prediksi Mingguan')

@section('content')
<div class="mb-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">Prediksi Ketinggian Air Laut</h1>
            <p class="text-gray-600 dark:text-gray-300 mt-2">
                <i class="fas fa-chart-line mr-2"></i>
                Prediksi 7 hari ke depan berdasarkan data historis dan algoritma KNN
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="px-4 py-2 bg-blue-100 dark:bg-blue-900 text-blue-600 dark:text-blue-300 rounded-lg">
                <i class="fas fa-database mr-2"></i>
                Data: <span id="dataPoints">0</span> records
            </div>
            <button onclick="refreshPredictions()" id="refreshButton" class="px-4 py-2 bg-seaguard-500 hover:bg-seaguard-600 text-white rounded-lg transition flex items-center">
                <i class="fas fa-redo-alt mr-2"></i>
                <span id="refreshText">Refresh</span>
            </button>
        </div>
    </div>
</div>

<!-- Loading State -->
<div id="loadingState" class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-8 mb-8 text-center">
    <div class="flex flex-col items-center justify-center">
        <div class="w-16 h-16 border-4 border-seaguard-500 border-t-transparent rounded-full animate-spin mb-4"></div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Memuat Prediksi</h3>
        <p class="text-gray-600 dark:text-gray-300">Menganalisis data historis menggunakan algoritma KNN...</p>
    </div>
</div>

<!-- Content (akan diisi oleh JavaScript) -->
<div id="content" class="hidden">
    <!-- Status Indikator -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <!-- Status Saat Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4" id="currentStatus">
            <!-- Diisi oleh JS -->
        </div>

        <!-- Tinggi Air Saat Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-300 text-sm">Tinggi Air Saat Ini</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-2" id="currentWaterLevel">-- m</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Panjang, Bandar Lampung</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                    <i class="fas fa-water text-blue-500 dark:text-blue-300 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Prediksi Pasang Tertinggi -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-300 text-sm">Prediksi Pasang Tertinggi</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-2" id="highestPrediction">-- m</h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300" id="highestPredictionTime">--:--</p>
                </div>
                <div class="w-12 h-12 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-500 dark:text-red-300 text-xl"></i>
                </div>
            </div>
        </div>

        <!-- Akurasi Prediksi -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-300 text-sm">Akurasi Prediksi</p>
                    <h3 class="text-2xl font-bold text-gray-800 dark:text-white mt-2" id="accuracy">--%</h3>
                    <p class="text-sm text-green-600 dark:text-green-400">
                        <i class="fas fa-brain mr-1"></i>Algoritma KNN
                    </p>
                </div>
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                    <i class="fas fa-brain text-green-500 dark:text-green-300 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Prediksi Mingguan -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white">Grafik Prediksi 7 Hari</h3>
                <p class="text-sm text-gray-600 dark:text-gray-300" id="chartSubtitle">Data prediksi ketinggian air laut harian</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-sm text-gray-600 dark:text-gray-300">Update:</span>
                <span class="text-sm font-semibold text-seaguard-600" id="lastUpdated">--:--</span>
            </div>
        </div>
        
        <div class="h-96">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>

    <!-- Tabel Prediksi Detail -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 mb-8">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6">
            <i class="fas fa-table mr-2"></i>Detail Prediksi Harian
        </h3>
        
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b dark:border-gray-700">
                        <th class="text-left py-3 px-4 text-gray-600 dark:text-gray-300">Hari/Tanggal</th>
                        <th class="text-left py-3 px-4 text-gray-600 dark:text-gray-300">Prediksi (m)</th>
                        <th class="text-left py-3 px-4 text-gray-600 dark:text-gray-300">Status</th>
                        <th class="text-left py-3 px-4 text-gray-600 dark:text-gray-300">Pasang Tertinggi</th>
                        <th class="text-left py-3 px-4 text-gray-600 dark:text-gray-300">Surut Terendah</th>
                        <th class="text-left py-3 px-4 text-gray-600 dark:text-gray-300">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody id="predictionTable">
                    <!-- Diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Algoritma -->
    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 mb-8">
        <h4 class="text-lg font-semibold text-blue-800 dark:text-blue-300 mb-4">
            <i class="fas fa-cogs mr-2"></i>Informasi Algoritma KNN
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-search text-purple-500 dark:text-purple-300"></i>
                    </div>
                    <h5 class="font-semibold text-gray-800 dark:text-white">Nearest Neighbor</h5>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Mencari pola tanggal yang sama dalam data historis untuk prediksi.
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar text-green-500 dark:text-green-300"></i>
                    </div>
                    <h5 class="font-semibold text-gray-800 dark:text-white">Data Training</h5>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Menggunakan <span id="trainingDataCount">0</span> data historis dari database.
                </p>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4">
                <div class="flex items-center mb-3">
                    <div class="w-10 h-10 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-balance-scale text-orange-500 dark:text-orange-300"></i>
                    </div>
                    <h5 class="font-semibold text-gray-800 dark:text-white">Similarity Weight</h5>
                </div>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Memberi bobot lebih pada data dengan pola yang paling mirip.
                </p>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    // Status configuration
    const statusConfig = {
        aman: {
            text: 'AMAN',
            color: 'green',
            border: 'border-green-500',
            icon: 'fa-check-circle',
            desc: 'Ketinggian air normal, aman untuk aktivitas laut'
        },
        waspada: {
            text: 'WASPADA',
            color: 'yellow',
            border: 'border-yellow-500',
            icon: 'fa-exclamation',
            desc: 'Ketinggian air cukup tinggi, waspada di area pantai'
        },
        siaga: {
            text: 'SIAGA',
            color: 'orange',
            border: 'border-orange-500',
            icon: 'fa-exclamation-triangle',
            desc: 'Ketinggian air tinggi, batasi aktivitas laut'
        },
        bahaya: {
            text: 'BAHAYA',
            color: 'red',
            border: 'border-red-500',
            icon: 'fa-times-circle',
            desc: 'Ketinggian air sangat tinggi, hindari area pantai'
        }
    };

    // Chart instance
    let weeklyChart = null;
    
    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        loadPredictions();
    });

    // Load predictions from API
    async function loadPredictions() {
        try {
            const response = await fetch('/api/predictions');
            const data = await response.json();

            if (data.empty || !data.predictions || data.predictions.length === 0) {
                showEmptyState(data.message || 'Belum ada data. Silakan import data Excel terlebih dahulu.');
                return;
            }

            if (data.success) {
                displayPredictions(data);
                initChart(data.predictions);
                document.getElementById('loadingState').classList.add('hidden');
                document.getElementById('content').classList.remove('hidden');
            } else {
                showErrorState(data.message);
            }
        } catch (error) {
            console.error('Error loading predictions:', error);
            showErrorState();
        }
    }

    // Display predictions data
    function displayPredictions(data) {
        const predictions = data.predictions;
        const currentData = data.current_data;
        
        // Update data points
        document.getElementById('dataPoints').textContent = 
            data.total_training_data.toLocaleString();
        document.getElementById('trainingDataCount').textContent = 
            data.total_training_data.toLocaleString();
        
        // Update accuracy
        document.getElementById('accuracy').textContent = 
            `${data.accuracy}%`;
        
        // Update last updated
        document.getElementById('lastUpdated').textContent = 
            data.last_updated;
        
        // Update current water level
        if (currentData) {
            document.getElementById('currentWaterLevel').textContent = 
                `${currentData.height} m`;
            
            // Determine current status
            const currentStatus = determineStatusFromHeight(currentData.height);
            updateCurrentStatus(currentStatus);
        }
        
        // Find highest prediction
        let highestPrediction = { max_height: 0 };
        predictions.forEach(pred => {
            if (pred.max_height > highestPrediction.max_height) {
                highestPrediction = pred;
            }
        });
        
        // Update highest prediction display
        document.getElementById('highestPrediction').textContent = 
            `${highestPrediction.max_height} m`;
        document.getElementById('highestPredictionTime').textContent = 
            `${highestPrediction.high_tide_time} WIB`;
        
        // Update prediction table
        updatePredictionTable(predictions);
    }

    // Update current status display
    function updateCurrentStatus(status) {
        const config = statusConfig[status];
        const element = document.getElementById('currentStatus');
        
        element.className = `bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6 border-l-4 ${config.border}`;
        element.innerHTML = `
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-300 text-sm">Status Saat Ini</p>
                    <h3 class="text-2xl font-bold mt-2 text-${config.color}-600 dark:text-${config.color}-400">
                        ${config.text}
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                        ${config.desc}
                    </p>
                </div>
                <div class="text-3xl text-${config.color}-500">
                    <i class="fas ${config.icon}"></i>
                </div>
            </div>
        `;
    }

    // Determine status from height
    function determineStatusFromHeight(height) {
        if (height > 3.0) return 'bahaya';
        if (height > 2.5) return 'siaga';
        if (height > 1.8) return 'waspada';
        return 'aman';
    }

    // Update prediction table
    function updatePredictionTable(predictions) {
        const tableBody = document.getElementById('predictionTable');
        tableBody.innerHTML = '';
        
        predictions.forEach(pred => {
            const config = statusConfig[pred.status];
            
            const row = document.createElement('tr');
            row.className = 'border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700/50';
            
            row.innerHTML = `
                <td class="py-4 px-4">
                    <div class="font-medium text-gray-800 dark:text-white">${pred.day_name}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">${pred.formatted_date}</div>
                </td>
                <td class="py-4 px-4">
                    <div class="font-bold text-lg text-gray-800 dark:text-white">${pred.avg_height} m</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Min: ${pred.min_height}m | Max: ${pred.max_height}m
                    </div>
                </td>
                <td class="py-4 px-4">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                        ${pred.status === 'aman' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : ''}
                        ${pred.status === 'waspada' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' : ''}
                        ${pred.status === 'siaga' ? 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300' : ''}
                        ${pred.status === 'bahaya' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : ''}">
                        <i class="fas ${config.icon} mr-2"></i>
                        ${config.text}
                    </span>
                </td>
                <td class="py-4 px-4">
                    <div class="font-medium text-gray-800 dark:text-white">${pred.high_tide_time}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">${pred.high_tide_height} m</div>
                </td>
                <td class="py-4 px-4">
                    <div class="font-medium text-gray-800 dark:text-white">${pred.low_tide_time}</div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">${pred.low_tide_height} m</div>
                </td>
                <td class="py-4 px-4">
                    <div class="text-sm text-gray-600 dark:text-gray-300">${pred.recommendation}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <i class="fas fa-chart-line mr-1"></i>
                        ${pred.similar_data_points} data similar
                    </div>
                </td>
            `;
            
            tableBody.appendChild(row);
        });
    }

    // Initialize chart
    function initChart(predictions) {
        const ctx = document.getElementById('weeklyChart').getContext('2d');
        
        // Destroy existing chart
        if (weeklyChart) {
            weeklyChart.destroy();
        }
        
        // Prepare data
        const labels = predictions.map(p => p.short_date);
        const avgData = predictions.map(p => p.avg_height);
        const maxData = predictions.map(p => p.max_height);
        const minData = predictions.map(p => p.min_height);
        
        // Status colors
        const backgroundColors = predictions.map(p => {
            switch(p.status) {
                case 'bahaya': return 'rgba(239, 68, 68, 0.1)';
                case 'siaga': return 'rgba(249, 115, 22, 0.1)';
                case 'waspada': return 'rgba(234, 179, 8, 0.1)';
                default: return 'rgba(34, 197, 94, 0.1)';
            }
        });
        
        // Update chart subtitle
        const highest = Math.max(...maxData);
        const lowest = Math.min(...minData);
        document.getElementById('chartSubtitle').textContent = 
            `Rentang prediksi: ${lowest.toFixed(2)}m - ${highest.toFixed(2)}m`;
        
        // Create chart
        weeklyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Rata-rata',
                        data: avgData,
                        backgroundColor: 'rgba(14, 165, 233, 0.7)',
                        borderColor: '#0ea5e9',
                        borderWidth: 2,
                        type: 'line',
                        tension: 0.4,
                        fill: false
                    },
                    {
                        label: 'Maksimum (Pasang)',
                        data: maxData,
                        backgroundColor: backgroundColors,
                        borderColor: predictions.map(p => {
                            switch(p.status) {
                                case 'bahaya': return '#ef4444';
                                case 'siaga': return '#f97316';
                                case 'waspada': return '#eab308';
                                default: return '#22c55e';
                            }
                        }),
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 0,
                        max: Math.ceil(Math.max(...maxData) * 1.1),
                        title: {
                            display: true,
                            text: 'Tinggi Air (meter)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Tanggal'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toFixed(2) + ' m';
                                
                                // Add status info for max data
                                if (context.datasetIndex === 1) {
                                    const pred = predictions[context.dataIndex];
                                    label += ` (${statusConfig[pred.status].text})`;
                                }
                                
                                return label;
                            }
                        }
                    }
                }
            }
        });
    }

    // Refresh predictions
    async function refreshPredictions() {
        const button = document.getElementById('refreshButton');
        const refreshText = document.getElementById('refreshText');
        const originalText = refreshText.textContent;
        
        // Show loading state
        button.disabled = true;
        refreshText.textContent = 'Memperbarui...';
        button.querySelector('i').className = 'fas fa-spinner fa-spin mr-2';
        
        try {
            // Simulate API call to refresh
            const response = await fetch('/api/predictions/refresh', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Show success message
                showToast('Data prediksi berhasil diperbarui!', 'success');
                
                // Reload predictions
                document.getElementById('loadingState').classList.remove('hidden');
                document.getElementById('content').classList.add('hidden');
                
                setTimeout(() => {
                    loadPredictions();
                }, 1000);
            }
        } catch (error) {
            console.error('Error refreshing:', error);
            showToast('Gagal memperbarui data', 'error');
        } finally {
            // Reset button
            setTimeout(() => {
                button.disabled = false;
                refreshText.textContent = originalText;
                button.querySelector('i').className = 'fas fa-redo-alt mr-2';
            }, 1000);
        }
    }

    // Show toast notification
    function showToast(message, type = 'info') {
        // Remove existing toast
        const existingToast = document.getElementById('predictionToast');
        if (existingToast) existingToast.remove();
        
        // Create toast
        const toast = document.createElement('div');
        toast.id = 'predictionToast';
        toast.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg shadow-lg transform transition-all duration-300
            ${type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'}`;
        toast.innerHTML = `
            <div class="flex items-center">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Show empty state (no data imported yet)
    function showEmptyState(message) {
        document.getElementById('loadingState').innerHTML = `
            <div class="flex flex-col items-center justify-center py-4">
                <div class="w-20 h-20 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-database text-blue-500 dark:text-blue-300 text-3xl"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-800 dark:text-white mb-2">Belum Ada Data Prediksi</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-6 max-w-md text-center">${message}</p>
                <a href="/dashboard" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition flex items-center font-medium">
                    <i class="fas fa-file-upload mr-2"></i>Import Data Excel
                </a>
            </div>
        `;
    }

    // Show error state
    function showErrorState(message) {
        document.getElementById('loadingState').innerHTML = `
            <div class="flex flex-col items-center justify-center">
                <div class="w-16 h-16 bg-red-100 dark:bg-red-900 rounded-full flex items-center justify-center mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 dark:text-red-300 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-2">Gagal Memuat Data</h3>
                <p class="text-gray-600 dark:text-gray-300 mb-4">${message || 'Terjadi kesalahan saat memuat prediksi.'}</p>
                <button onclick="loadPredictions()" class="px-4 py-2 bg-seaguard-500 hover:bg-seaguard-600 text-white rounded-lg transition">
                    <i class="fas fa-redo-alt mr-2"></i>Coba Lagi
                </button>
            </div>
        `;
    }

    // Auto-refresh every 5 minutes
    setInterval(() => {
        if (!document.getElementById('refreshButton').disabled) {
            loadPredictions();
        }
    }, 5 * 60 * 1000); // 5 minutes
</script>
@endsection
@endsection