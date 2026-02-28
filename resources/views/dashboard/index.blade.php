@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 px-1">
    <div class="w-full">
        <div class="flex items-center space-x-2 mb-1">
            <span class="w-2 h-8 bg-blue-600 rounded-full"></span>
            <h1 class="text-2xl md:text-4xl font-black text-gray-800 dark:text-white leading-tight tracking-tight uppercase">Dashboard Prediksi</h1>
        </div>
        <p class="text-[10px] md:text-sm text-gray-500 dark:text-gray-400 flex items-center font-medium">
            <i class="fas fa-map-marker-alt mr-2 text-blue-500"></i>
            Pelabuhan Panjang • Bandar Lampung, Indonesia
        </p>
    </div>
    
    <div class="w-full md:w-auto">
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
                class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-black py-3 px-6 rounded-2xl shadow-xl shadow-blue-500/20 flex items-center justify-center transition-all hover:scale-[1.02] active:scale-95 text-xs uppercase tracking-widest">
            <i class="fas fa-file-upload mr-3 text-sm"></i>
            Import Data
        </button>
    </div>
</div>

{{-- Flash Messages --}}
@if(session('success'))
<div class="bg-green-50 dark:bg-green-900/30 border-l-4 border-green-500 text-green-700 dark:text-green-300 p-4 mb-6 rounded-xl shadow-md animate-bounce-short" role="alert">
    <div class="flex items-center">
        <div class="w-10 h-10 bg-green-100 dark:bg-green-800 rounded-full flex items-center justify-center mr-3">
            <i class="fas fa-check-circle text-green-600 dark:text-green-400 text-xl"></i>
        </div>
        <div>
            <p class="font-bold">Berhasil!</p>
            <p class="text-sm">{{ session('success') }}</p>
        </div>
    </div>
</div>
@endif

@if(session('error'))
<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
    <div class="flex items-center">
        <i class="fas fa-exclamation-circle mr-2"></i>
        <p>{{ session('error') }}</p>
    </div>
</div>
@endif

@if(session('import_errors'))
<div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mb-6 rounded shadow-sm" role="alert">
    <div class="flex items-center mb-2">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <p class="font-bold">Beberapa data gagal diimport:</p>
    </div>
    <ul class="list-disc ml-8 text-sm max-h-40 overflow-y-auto">
        @foreach(session('import_errors') as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

{{-- Import Modal --}}
<div id="importModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('importModal').classList.add('hidden')"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <form action="{{ route('dashboard.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-cloud-upload-alt text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white" id="modal-title">
                                Import Data Pasang Surut
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                    Upload file Excel (.xlsx, .xls) berisi data historis pasang surut.
                                    Pastikan format kolom sesuai: Date, Time, Height, dll.
                                </p>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Pilih File Excel
                                    </label>
                                    <input type="file" name="file" accept=".xlsx, .xls" required
                                           class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Upload
                    </button>
                    <button type="button" onclick="document.getElementById('importModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(!$hasData)
    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-8 rounded-xl shadow-lg mb-8 text-center">
        <div class="bg-blue-100 dark:bg-blue-800 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-database text-4xl text-blue-600 dark:text-blue-300"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">Belum Ada Data Prediksi</h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6 max-w-lg mx-auto">
            Data pasang surut belum tersedia. Silakan import data Excel terlebih dahulu untuk melihat prediksi dan statistik.
        </p>
        <button onclick="document.getElementById('importModal').classList.remove('hidden')" 
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg shadow-lg inline-flex items-center transition duration-200">
            <i class="fas fa-file-upload mr-2"></i>
            Import Data Sekarang
        </button>
    </div>
@else
<!-- Stats Grid -->
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-6 gap-3 md:gap-4 mb-8">
    <!-- Time Card -->
    <div class="time-card rounded-2xl shadow-xl p-5 col-span-2 lg:col-span-2 relative overflow-hidden group">
        <div class="absolute -top-4 -right-4 p-8 opacity-10 group-hover:scale-110 transition-transform">
            <i class="fas fa-clock text-7xl"></i>
        </div>
        <div class="relative z-10 flex flex-col justify-between h-full">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <p class="text-[9px] md:text-xs font-black uppercase tracking-[0.2em] text-blue-200/80">Waktu Live (WIB)</p>
                    <div class="digital-clock text-3xl md:text-4xl font-black mt-1 drop-shadow-lg" id="currentTimeWIB">--:--:--</div>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-2xl backdrop-blur-md flex items-center justify-center border border-white/20 shadow-inner">
                    <i class="fas fa-water text-2xl text-white"></i>
                </div>
            </div>
            
            <div>
                <div class="flex justify-between text-[9px] font-bold text-blue-200/60 mb-2 uppercase tracking-widest">
                    <span>00:00</span>
                    <span id="currentHour" class="text-white px-2 bg-black/20 rounded text-[10px]">--:--</span>
                    <span>23:59</span>
                </div>
                <div class="h-2.5 bg-black/30 rounded-full overflow-hidden border border-white/10 p-[1px]">
                    <div class="h-full bg-gradient-to-r from-blue-300 to-white rounded-full shadow-[0_0_12px_rgba(255,255,255,0.8)] transition-all duration-1000" id="timeProgress"></div>
                </div>
                <p class="text-blue-100 text-[10px] sm:text-xs mt-4 font-bold tracking-wide flex items-center" id="currentDateWIB">
                    <i class="fas fa-calendar-day mr-2 opacity-60"></i>-- --- ----
                </p>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <!-- Tinggi Air -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 border-b-4 border-blue-500 hover:shadow-2xl transition-all group overflow-hidden relative">
        <div class="absolute -bottom-2 -right-2 opacity-5 text-4xl group-hover:scale-125 transition-transform"><i class="fas fa-water"></i></div>
        <div class="flex flex-col h-full justify-between relative z-10">
            <div class="flex items-center justify-between mb-3">
                <p class="text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">Tinggi Air</p>
                <div class="w-8 h-8 bg-blue-50 dark:bg-blue-900/30 rounded-xl flex items-center justify-center border border-blue-100 dark:border-blue-800">
                    <i class="fas fa-tint text-blue-500 text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white truncate" id="liveHeight">-- m</h3>
                <div class="mt-2 inline-flex items-center px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-[9px] font-black text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-800" id="liveStatus">
                    --
                </div>
            </div>
        </div>
    </div>

    <!-- Suhu -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 border-b-4 border-green-500 hover:shadow-2xl transition-all group overflow-hidden relative">
        <div class="absolute -bottom-2 -right-2 opacity-5 text-4xl group-hover:scale-125 transition-transform"><i class="fas fa-thermometer-half"></i></div>
        <div class="flex flex-col h-full justify-between relative z-10">
            <div class="flex items-center justify-between mb-3">
                <p class="text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">Suhu</p>
                <div class="w-8 h-8 bg-green-50 dark:bg-green-900/30 rounded-xl flex items-center justify-center border border-green-100 dark:border-green-800">
                    <i class="fas fa-sun text-green-500 text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white truncate" id="liveTemp">--°C</h3>
                <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase" id="liveHumidity">H: --%</p>
            </div>
        </div>
    </div>

    <!-- Angin -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 border-b-4 border-purple-500 hover:shadow-2xl transition-all group overflow-hidden relative">
        <div class="absolute -bottom-2 -right-2 opacity-5 text-4xl group-hover:scale-125 transition-transform"><i class="fas fa-wind"></i></div>
        <div class="flex flex-col h-full justify-between relative z-10">
            <div class="flex items-center justify-between mb-3">
                <p class="text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">Angin</p>
                <div class="w-8 h-8 bg-purple-50 dark:bg-purple-900/30 rounded-xl flex items-center justify-center border border-purple-100 dark:border-purple-800">
                    <i class="fas fa-wind text-purple-500 text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white truncate" id="liveWind">-- m/s</h3>
                <p class="text-[9px] font-bold text-gray-400 mt-1 uppercase truncate" id="liveWindDir">--</p>
            </div>
        </div>
    </div>

    <!-- Tekanan -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-4 border-b-4 border-orange-500 hover:shadow-2xl transition-all group overflow-hidden relative">
        <div class="absolute -bottom-2 -right-2 opacity-5 text-4xl group-hover:scale-125 transition-transform"><i class="fas fa-tachometer-alt"></i></div>
        <div class="flex flex-col h-full justify-between relative z-10">
            <div class="flex items-center justify-between mb-3">
                <p class="text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">Tekanan</p>
                <div class="w-8 h-8 bg-orange-50 dark:bg-orange-900/30 rounded-xl flex items-center justify-center border border-orange-100 dark:border-orange-800">
                    <i class="fas fa-compress-alt text-orange-500 text-sm"></i>
                </div>
            </div>
            <div>
                <h3 class="text-xl md:text-2xl font-black text-gray-800 dark:text-white truncate" id="livePressure">-- hPa</h3>
                <div class="mt-2 flex items-center space-x-1" id="weatherSource">
                    <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                    <span class="text-[8px] font-bold text-gray-400 uppercase tracking-tighter">Live Open-Meteo</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Condition Insight (New Section for Description) -->
<div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-2xl shadow-xl p-5 md:p-6 mb-8 text-white relative overflow-hidden group">
    <div class="absolute top-0 right-0 p-8 opacity-10 group-hover:rotate-12 transition-transform">
        <i class="fas fa-info-circle text-8xl"></i>
    </div>
    <div class="relative z-10">
        <div class="flex items-center space-x-3 mb-3">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-md border border-white/20">
                <i class="fas fa-comment-alt text-white"></i>
            </div>
            <h4 class="text-base md:text-lg font-black uppercase tracking-widest">Insight Kondisi Air Laut</h4>
        </div>
        <div class="bg-white/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
            <p class="text-xs md:text-sm leading-relaxed font-medium text-blue-50" id="conditionDescription">
                Menganalisis data pasang surut...
            </p>
        </div>
    </div>
</div>
@endif

<!-- Grafik Prediksi -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg p-4 md:p-6 mb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
        <div>
            <h3 class="text-lg font-bold text-gray-800 dark:text-white">Grafik Prediksi 30 Hari</h3>
            <p class="text-[10px] md:text-sm text-gray-500">Update terakhir: <span id="lastUpdate">--:--:--</span></p>
        </div>
        <div class="flex flex-wrap gap-2">
            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-300 rounded-full text-[10px] font-bold uppercase tracking-wider">
                <i class="fas fa-brain mr-1"></i>KNN Prediction
            </span>
            <span class="px-3 py-1 bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 rounded-full text-[10px] font-bold uppercase tracking-wider">
                <i class="fas fa-database mr-1 text-xs"></i>Verified
            </span>
        </div>
    </div>
    
    <div class="h-64 md:h-96 w-full">
        <canvas id="tideChart"></canvas>
    </div>
</div>

@if(!empty($predictions))
{{-- Jadwal Pasang Surut Hari Ini dari data KNN --}}
<div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 border border-blue-100 dark:border-blue-800/50 rounded-2xl p-4 md:p-6 mb-8 overflow-hidden relative">
    <div class="absolute top-0 right-0 p-8 opacity-5">
        <i class="fas fa-calendar-alt text-8xl"></i>
    </div>
    <h4 class="text-sm md:text-lg font-black text-blue-900 dark:text-blue-300 mb-5 flex items-center">
        <i class="fas fa-water mr-3 text-blue-500"></i>
        PREDIKSI PASANG SURUT TERKINI (KNN)
    </h4>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 relative z-10">
        @foreach(collect($predictions)->take(4) as $pred)
        @php
            $isHigh = in_array($pred['tide_type'], ['HIGH_TIDE', 'PASANG']);
            $bgColor = $isHigh ? 'bg-blue-600' : 'bg-green-600';
            $textColor = $isHigh ? 'text-blue-600' : 'text-green-600';
            $label = $isHigh ? 'Pasang' : 'Surut';
            $icon = $isHigh ? 'fa-arrow-up' : 'fa-arrow-down';
        @endphp
        <div class="bg-white dark:bg-gray-800 p-4 rounded-xl shadow-sm border border-black/5 hover:border-blue-300 transition-colors">
            <div class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">{{ \Carbon\Carbon::parse($pred['date'])->format('d M') }}</div>
            <div class="flex items-end justify-between mt-3">
                <div class="text-xl font-black text-gray-800 dark:text-white">{{ number_format($pred['predicted_height'], 2) }}<span class="text-xs font-normal ml-1 text-gray-400">m</span></div>
                <div class="w-6 h-6 rounded-lg {{ $bgColor }} bg-opacity-10 flex items-center justify-center">
                    <i class="fas {{ $icon }} {{ $textColor }} text-[10px]"></i>
                </div>
            </div>
            <div class="text-[10px] font-bold {{ $textColor }} uppercase tracking-tighter mt-1">{{ $label }}</div>
        </div>
        @endforeach
    </div>
</div>
@endif

@section('scripts')
<script>
    // Function to update date display
    function updateDateDisplay() {
        const wibTime = getWIBTime();
        const date = formatDate(wibTime);

        // Update date elements in dashboard
        const currentDateElement = document.getElementById('currentDateWIB');
        if (currentDateElement) {
            currentDateElement.textContent = date.full;
        }

        const time = formatTime(wibTime);
        const lastUpdateElement = document.getElementById('lastUpdate');
        if (lastUpdateElement) {
            lastUpdateElement.textContent = time.timePlain;
        }

        // Update tide prediction based on time
        updateTidePrediction(parseInt(time.hour), parseInt(time.minute));
    }

    // Function to fetch real-time data (weather + tide)
    function updateLiveStats() {
        console.log('Fetching live stats...');
        fetch('/api/realtime-data', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                return response.json();
            })
            .then(data => {
                console.log('Live data received:', data);
                
                if (!data || data.status === 'empty') {
                    console.warn('No real-time data available');
                    return;
                }

                // Update Tide Height
                const liveHeightEl = document.getElementById('liveHeight');
                if (liveHeightEl && data.tide_height !== undefined) {
                    liveHeightEl.textContent = data.tide_height + ' m';
                }

                // Update Tide Status Badge
                const statusEl = document.getElementById('liveStatus');
                const descEl = document.getElementById('conditionDescription');
                if (statusEl && data.tide_type) {
                    const typeMap = {
                        'HIGH_TIDE': { 
                            label: 'Pasang Tinggi', 
                            icon: 'fa-arrow-up', 
                            color: 'text-red-600 dark:text-red-400',
                            desc: 'WASPADA - Terdeteksi pasang tinggi. Harap berhati-hati saat melakukan aktivitas di bibir pantai atau dermaga.'
                        },
                        'LOW_TIDE':  { 
                            label: 'Surut',         
                            icon: 'fa-arrow-down', 
                            color: 'text-blue-600 dark:text-blue-400',
                            desc: 'SURUT - Permukaan air rendah. Kapal dengan draf dalam disarankan menunda manuver sandar.'
                        },
                        'MEDIUM_TIDE': { 
                            label: 'Normal',      
                            icon: 'fa-minus', 
                            color: 'text-green-600 dark:text-green-400',
                            desc: 'NORMAL - Kondisi permukaan air stabil dan aman untuk aktivitas dermaga serta pelayaran ringan.'
                        },
                        'PASANG': { 
                            label: 'Pasang', 
                            icon: 'fa-arrow-up', 
                            color: 'text-red-600 dark:text-red-400',
                            desc: 'PASANG - Air laut sedang naik. Perhatikan batas aman di pelabuhan.'
                        },
                        'SURUT': { 
                            label: 'Surut', 
                            icon: 'fa-arrow-down', 
                            color: 'text-blue-600 dark:text-blue-400',
                            desc: 'SURUT - Air laut sedang turun. Kondisi draf kapal perlu dipantau.'
                        }
                    };
                    const t = typeMap[data.tide_type] || typeMap['MEDIUM_TIDE'];
                    statusEl.innerHTML = `<i class="fas ${t.icon} mr-1"></i>${t.label}`;
                    statusEl.className = `mt-2 inline-flex items-center px-2 py-0.5 rounded-lg bg-blue-50 dark:bg-blue-900/30 text-[9px] font-black ${t.color}`;
                    
                    if (descEl) {
                        descEl.textContent = t.desc;
                    }
                }

                // Update Temperature
                const liveTempEl = document.getElementById('liveTemp');
                if (liveTempEl) {
                    liveTempEl.textContent = (data.temperature !== null && data.temperature !== undefined) 
                        ? data.temperature + '°C' : '--°C';
                }

                // Update Humidity
                const liveHumidityEl = document.getElementById('liveHumidity');
                if (liveHumidityEl) {
                    liveHumidityEl.textContent = (data.humidity !== null && data.humidity !== undefined)
                        ? 'Kelembaban: ' + data.humidity + '%' : 'Kelembaban: --%';
                }

                // Update Wind
                const liveWindEl = document.getElementById('liveWind');
                if (liveWindEl) {
                    liveWindEl.textContent = (data.wind_speed !== null && data.wind_speed !== undefined)
                        ? data.wind_speed + ' m/s' : '-- m/s';
                }

                const liveWindDirEl = document.getElementById('liveWindDir');
                if (liveWindDirEl) {
                    liveWindDirEl.textContent = 'Arah: ' + (data.wind_direction || '--');
                }

                // Update Pressure
                const livePressureEl = document.getElementById('livePressure');
                if (livePressureEl) {
                    livePressureEl.textContent = (data.pressure !== null && data.pressure !== undefined)
                        ? Math.round(data.pressure) + ' hPa' : '-- hPa';
                }

                // Weather source badge
                const sourceEl = document.getElementById('weatherSource');
                if (sourceEl) {
                    if (data.weather_source === 'open-meteo') {
                        sourceEl.innerHTML = '<i class="fas fa-satellite-dish mr-1 text-green-500"></i>Open-Meteo Live';
                    } else if (data.weather_source === 'unavailable' || data.weather_source === 'error') {
                        sourceEl.innerHTML = '<i class="fas fa-exclamation-triangle mr-1 text-yellow-500"></i>Offline Mode';
                    }
                }
            })
            .catch(err => {
                console.error('Error fetching live stats:', err);
                const statusEl = document.getElementById('liveStatus');
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-exclamation-circle mr-1 text-red-500"></i>Gagal Update';
                }
            });
    }

    // Update tide prediction based on current time
    function updateTidePrediction(hour, minute) {
        let tideStatus = "Normal";
        let tideColor = "text-green-600";
        let tideIcon = "fa-water";
        
        if ((hour >= 5 && hour <= 8) || (hour >= 17 && hour <= 20)) {
            tideStatus = "Pasang Tinggi";
            tideColor = "text-red-600";
            tideIcon = "fa-wave-square";
        } else if ((hour >= 11 && hour <= 14) || hour >= 23 || hour <= 2) {
            tideStatus = "Surut";
            tideColor = "text-blue-600";
            tideIcon = "fa-water";
        }
        
        // Find and update all tide status elements
        const tideElements = document.querySelectorAll('.tide-status');
        tideElements.forEach(element => {
            element.innerHTML = `<i class="fas ${tideIcon} mr-1"></i>${tideStatus}`;
            element.className = `text-sm ${tideColor} tide-status`;
        });
    }
    
    // Initialize Chart
    document.addEventListener('DOMContentLoaded', function() {
        // Update date display
        updateDateDisplay();

        // Update date every minute
        setInterval(updateDateDisplay, 60000);

        // Fetch weather data immediately on load
        updateLiveStats();

        // Auto-refresh weather data every 5 minutes (300000ms)
        setInterval(updateLiveStats, 300000);

        // Create tide chart using KNN prediction data from backend
        const ctx = document.getElementById('tideChart');
        if (ctx) {
            // Data dari backend (KNN processed)
            const chartLabels = @json($chartData['dates'] ?? []);
            const chartHeights = @json($chartData['heights'] ?? []);
            const chartTypes = @json($chartData['tide_types'] ?? []);

            // Color points based on tide type
            const pointColors = chartTypes.map(type => 
                (type === 'HIGH_TIDE' || type === 'PASANG') ? '#ef4444' : '#22c55e'
            );

            new Chart(ctx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Tinggi Air (m) - KNN Prediction',
                        data: chartHeights,
                        borderColor: '#0ea5e9',
                        backgroundColor: 'rgba(14, 165, 233, 0.1)',
                        pointBackgroundColor: pointColors,
                        pointRadius: 4,
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: false,
                            min: 0.5,
                            title: {
                                display: true,
                                text: 'Tinggi (meter)'
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
                                afterLabel: function(context) {
                                    const type = chartTypes[context.dataIndex];
                                    return 'Status: ' + (type === 'HIGH_TIDE' ? 'Pasang' : type === 'LOW_TIDE' ? 'Surut' : type);
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endsection
@endsection