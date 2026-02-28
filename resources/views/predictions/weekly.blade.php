@extends('layouts.app')

@section('title', 'Prediksi Mingguan')

@section('content')
<div class="mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 dark:text-white leading-tight">Prediksi Air Laut</h1>
            <p class="text-xs md:text-sm text-gray-600 dark:text-gray-300 mt-1">
                <i class="fas fa-chart-line mr-2 text-blue-500"></i>
                Prediksi 7 hari ke depan (Algoritma KNN)
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3 w-full md:w-auto">
            <div class="px-4 py-2 bg-blue-100 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 rounded-xl text-sm font-medium flex items-center justify-center">
                <i class="fas fa-database mr-2"></i>
                <span id="dataPoints">0</span> <span class="ml-1">records</span>
            </div>
            <button onclick="refreshPredictions()" id="refreshButton" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl shadow-md transition flex items-center justify-center font-bold">
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
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <!-- Status Saat Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6 border-l-4 col-span-2 md:col-span-1" id="currentStatus">
            <!-- Diisi oleh JS -->
        </div>

        <!-- Tinggi Air Saat Ini -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6">
            <div class="flex flex-col h-full justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-300 text-[10px] md:text-sm font-bold uppercase">Tinggi Air</p>
                    <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white mt-1" id="currentWaterLevel">-- m</h3>
                </div>
                <div class="flex items-center mt-2">
                    <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900/40 rounded-lg flex items-center justify-center mr-2">
                        <i class="fas fa-water text-blue-500 text-sm"></i>
                    </div>
                    <span class="text-[10px] text-gray-400">Live</span>
                </div>
            </div>
        </div>

        <!-- Pasang Tertinggi -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6">
            <div class="flex flex-col h-full justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-300 text-[10px] md:text-sm font-bold uppercase">Pasang Max</p>
                    <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white mt-1" id="highestPrediction">-- m</h3>
                </div>
                <div class="flex items-center mt-2">
                    <div class="w-8 h-8 bg-red-100 dark:bg-red-900/40 rounded-lg flex items-center justify-center mr-2">
                        <i class="fas fa-arrow-up text-red-500 text-sm"></i>
                    </div>
                    <span class="text-[10px] text-gray-400" id="highestPredictionTime">--:--</span>
                </div>
            </div>
        </div>

        <!-- Akurasi -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6">
            <div class="flex flex-col h-full justify-between">
                <div>
                    <p class="text-gray-500 dark:text-gray-300 text-[10px] md:text-sm font-bold uppercase">Akurasi</p>
                    <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white mt-1" id="accuracy">--%</h3>
                </div>
                <div class="flex items-center mt-2">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center mr-2">
                        <i class="fas fa-brain text-green-500 text-sm"></i>
                    </div>
                    <span class="text-[10px] text-gray-400">KNN ML</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Grafik Prediksi Mingguan -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-2">
            <div>
                <h3 class="text-lg font-bold text-gray-800 dark:text-white">Grafik Prediksi 7 Hari</h3>
                <p class="text-[10px] md:text-sm text-gray-500" id="chartSubtitle">Trend ketinggian air laut</p>
            </div>
            <div class="flex items-center space-x-2">
                <span class="text-[10px] text-gray-400 uppercase font-bold">Update:</span>
                <span class="text-[10px] font-black text-blue-600 dark:text-blue-400" id="lastUpdated">--:--</span>
            </div>
        </div>
        
        <div class="h-64 md:h-96">
            <canvas id="weeklyChart"></canvas>
        </div>
    </div>

    <!-- Tabel Prediksi Detail -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6 mb-8 overflow-hidden">
        <h3 class="text-lg font-bold text-gray-800 dark:text-white mb-6 flex items-center">
            <i class="fas fa-table mr-3 text-blue-500"></i>Detail Prediksi Harian
        </h3>
        
        <div class="overflow-x-auto -mx-4 px-4 md:mx-0 md:px-0">
            <table class="w-full text-xs md:text-sm">
                <thead>
                    <tr class="border-b dark:border-gray-700 text-gray-400 uppercase tracking-tighter">
                        <th class="text-left py-4 px-2 font-black">Hari/Tanggal</th>
                        <th class="text-left py-4 px-2 font-black">Prediksi (m)</th>
                        <th class="text-left py-4 px-2 font-black">Status</th>
                        <th class="text-left py-4 px-2 font-black">Pasang/Surut</th>
                        <th class="text-left py-4 px-2 font-black">Rekomendasi</th>
                    </tr>
                </thead>
                <tbody id="predictionTable" class="divide-y divide-gray-100 dark:divide-gray-700">
                    <!-- Diisi oleh JavaScript -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Algoritma -->
    <div class="bg-gradient-to-br from-gray-50 to-blue-50 dark:from-gray-900/40 dark:to-blue-900/40 border border-gray-100 dark:border-gray-800 rounded-2xl p-6 mb-8">
        <h4 class="text-sm md:text-lg font-black text-gray-800 dark:text-blue-300 mb-6 flex items-center uppercase tracking-widest">
            <i class="fas fa-microchip mr-3 text-blue-500"></i>Engine: KNN Analysis
        </h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-4 border border-white/40 dark:border-gray-700/40">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-purple-100 dark:bg-purple-900/40 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-search text-purple-500 text-sm"></i>
                    </div>
                    <h5 class="text-xs font-black text-gray-800 dark:text-white uppercase">Nearest Neighbor</h5>
                </div>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-relaxed">
                    Mencari pola tanggal yang sama dalam data historis untuk akurasi prediksi.
                </p>
            </div>
            
            <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-4 border border-white/40 dark:border-gray-700/40">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900/40 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-chart-bar text-green-500 text-sm"></i>
                    </div>
                    <h5 class="text-xs font-black text-gray-800 dark:text-white uppercase">Training Data</h5>
                </div>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-relaxed">
                    Menggunakan <span id="trainingDataCount" class="font-black">0</span> data historis yang tersimpan aman di database.
                </p>
            </div>
            
            <div class="bg-white/60 dark:bg-gray-800/60 backdrop-blur-sm rounded-xl p-4 border border-white/40 dark:border-gray-700/40">
                <div class="flex items-center mb-3">
                    <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900/40 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-balance-scale text-orange-500 text-sm"></i>
                    </div>
                    <h5 class="text-xs font-black text-gray-800 dark:text-white uppercase">Similarity Weight</h5>
                </div>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 leading-relaxed">
                    Memberi bobat dinamis pada data dengan pola yang paling relevan.
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
                <td class="py-4 px-2">
                    <div class="font-black text-gray-800 dark:text-white uppercase tracking-tighter">${pred.day_name}</div>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">${pred.short_date}</div>
                </td>
                <td class="py-4 px-2">
                    <div class="font-black text-base text-gray-800 dark:text-white">${pred.avg_height} m</div>
                    <div class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">
                        Rerata
                    </div>
                </td>
                <td class="py-4 px-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-black uppercase tracking-widest
                        ${pred.status === 'aman' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : ''}
                        ${pred.status === 'waspada' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' : ''}
                        ${pred.status === 'siaga' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300' : ''}
                        ${pred.status === 'bahaya' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : ''}">
                        <i class="fas ${config.icon} mr-1.5"></i>
                        ${config.text}
                    </span>
                </td>
                <td class="py-4 px-2">
                    <div class="flex flex-col gap-1">
                        <div class="flex items-center text-blue-600 dark:text-blue-400 font-black">
                             <i class="fas fa-arrow-up text-[8px] mr-1.5"></i> ${pred.high_tide_height}m
                             <span class="text-[9px] ml-2 text-gray-400 font-medium">${pred.high_tide_time}</span>
                        </div>
                        <div class="flex items-center text-green-600 dark:text-green-400 font-black">
                             <i class="fas fa-arrow-down text-[8px] mr-1.5"></i> ${pred.low_tide_height}m
                             <span class="text-[9px] ml-2 text-gray-400 font-medium">${pred.low_tide_time}</span>
                        </div>
                    </div>
                </td>
                <td class="py-4 px-2 min-w-[150px]">
                    <div class="text-[10px] text-gray-600 dark:text-gray-300 leading-normal italic line-clamp-2">${pred.recommendation}</div>
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