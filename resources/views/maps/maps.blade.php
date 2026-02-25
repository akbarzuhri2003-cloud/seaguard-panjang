<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeaGuard Panjang - Peta Prediksi</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        seaguard: {
                            50: '#f0f9ff',
                            100: '#e0f2fe',
                            500: '#0ea5e9',
                            600: '#0284c7',
                        },
                        tide: {
                            high: '#ef4444',
                            medium: '#3b82f6',
                            low: '#10b981',
                        }
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                        'tide-flow': 'tideFlow 3s ease-in-out infinite',
                    },
                    keyframes: {
                        tideFlow: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' },
                        }
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    
    <style>
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        .digital-clock {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 2px;
        }
        .blink {
            animation: blink 1s step-start infinite;
        }
        @keyframes blink {
            50% { opacity: 0.4; }
        }
        .custom-marker {
            background: transparent;
            border: none;
        }
        .legend-gradient {
            height: 10px;
            width: 100%;
            background: linear-gradient(to right, blue, cyan, lime, yellow, red);
            border-radius: 3px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <div class="bg-gradient-to-r from-blue-900 to-cyan-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="tide-animation">
                        <i class="fas fa-water text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">SeaGuard Panjang</h1>
                        <p class="text-xs text-blue-200">Peta Prediksi Pasang Surut</p>
                    </div>
                </div>
                
                <div class="hidden md:flex items-center space-x-4">
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2 text-blue-200"></i>
                            <div class="text-center">
                                <div class="digital-clock text-lg" id="liveTime">--:--:--</div>
                                <div class="text-xs text-blue-200 mt-1">Waktu Indonesia Barat (WIB)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <div class="text-center">
                            <div class="text-sm">Tanggal</div>
                            <div class="font-semibold" id="liveDate">-- --- ----</div>
                        </div>
                    </div>
                </div>

                <div class="flex space-x-2">
                    <a href="/dashboard" class="px-4 py-2 rounded-lg hover:bg-white/10 transition flex items-center">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="/weekly-prediction" class="px-4 py-2 rounded-lg hover:bg-white/10 transition flex items-center">
                        <i class="fas fa-chart-line mr-2"></i>Prediksi
                    </a>
                    <a href="/maps" class="px-4 py-2 rounded-lg bg-white/20 hover:bg-white/30 transition flex items-center">
                        <i class="fas fa-map mr-2"></i>Peta
                    </a>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-4 py-2 rounded-lg hover:bg-white/10 transition flex items-center text-red-200 hover:text-white">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Peta Prediksi Pasang Surut Panjang</h1>
            <p class="text-gray-600 mt-2">
                <i class="fas fa-map-marked-alt mr-2"></i>
                Koordinat: -5.4755°, 105.3147° | 
                <span class="font-semibold" id="mapTime">--:--:--</span> WIB |
                <span id="totalSensorsDisplay" class="text-blue-600">{{ count($locations) }} Sensor Aktif</span>
            </p>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-satellite-dish text-blue-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Sensor</p>
                        <p class="text-xl font-bold text-gray-800">{{ $stats['total_sensors'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-wave-square text-red-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Lokasi Pasang Tinggi</p>
                        <p class="text-xl font-bold text-gray-800">{{ $stats['high_tide_locations'] }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-ruler-vertical text-green-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tinggi Rata-rata</p>
                        <p class="text-xl font-bold text-gray-800">{{ $stats['avg_height'] }} m</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow p-4 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-sync-alt text-purple-500"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Update Terakhir</p>
                        <p class="text-sm font-bold text-gray-800">{{ $stats['last_data_update'] }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Peta Interaktif Panjang, Bandar Lampung</h3>
                    <p class="text-sm text-gray-600">Street View & Heatmap Data Real-time</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button id="streetViewBtn" class="px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition flex items-center">
                        <i class="fas fa-street-view mr-2"></i>Street View
                    </button>
                    <button id="heatmapToggle" class="px-4 py-2 bg-red-500 text-white rounded-lg text-sm font-medium hover:bg-red-600 transition flex items-center">
                        <i class="fas fa-fire mr-2"></i>Heatmap
                    </button>
                    <button id="refreshData" class="px-4 py-2 bg-green-500 text-white rounded-lg text-sm font-medium hover:bg-green-600 transition flex items-center">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                    <button id="downloadData" class="px-4 py-2 bg-purple-500 text-white rounded-lg text-sm font-medium hover:bg-purple-600 transition flex items-center">
                        <i class="fas fa-download mr-2"></i>Download Data
                    </button>
                </div>
            </div>
            
            <div id="map" style="height: 500px; width: 100%; border-radius: 10px;" class="z-0"></div>
            
            <!-- Map Controls & Legend -->
            <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3">Kontrol Peta</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Zoom Level:</span>
                            <span id="zoomLevel" class="font-mono text-sm bg-gray-200 px-2 py-1 rounded">{{ $stats['zoom_level'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Koordinat Tengah:</span>
                            <span id="currentCoords" class="font-mono text-xs">{{ $stats['map_center']['lat'] }}, {{ $stats['map_center']['lng'] }}</span>
                        </div>
                        <div class="flex space-x-2 mt-4">
                            <button id="zoomIn" class="flex-1 px-4 py-2 bg-blue-100 text-blue-600 rounded-lg text-sm hover:bg-blue-200 flex items-center justify-center">
                                <i class="fas fa-plus mr-2"></i>Zoom In
                            </button>
                            <button id="zoomOut" class="flex-1 px-4 py-2 bg-blue-100 text-blue-600 rounded-lg text-sm hover:bg-blue-200 flex items-center justify-center">
                                <i class="fas fa-minus mr-2"></i>Zoom Out
                            </button>
                            <button id="resetView" class="flex-1 px-4 py-2 bg-blue-100 text-blue-600 rounded-lg text-sm hover:bg-blue-200 flex items-center justify-center">
                                <i class="fas fa-home mr-2"></i>Reset
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="p-4 bg-gray-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3">Legenda Peta</h4>
                    <div class="space-y-2 mb-4">
                        <div class="flex items-center">
                            <div class="w-5 h-5 bg-blue-500 rounded-full mr-3 flex items-center justify-center">
                                <i class="fas fa-water text-white text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-700">Lokasi Sensor Aktif</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-5 h-5 bg-red-500 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-700">Area Pasang Tinggi (≥ 1.8m)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-5 h-5 bg-green-500 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-700">Area Normal (1.2-1.8m)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-5 h-5 bg-yellow-500 rounded-full mr-3"></div>
                            <span class="text-sm text-gray-700">Area Siaga (≤ 1.2m)</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-sm text-gray-700 mb-2">Heatmap Intensitas Pasang:</div>
                        <div class="legend-gradient"></div>
                        <div class="flex justify-between text-xs text-gray-600 mt-1">
                            <span>Rendah</span>
                            <span>Sedang</span>
                            <span>Tinggi</span>
                        </div>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-semibold text-gray-800 mb-3">Informasi Sistem</h4>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Status Sistem:</span>
                            <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold animate-pulse">LIVE</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Update Data:</span>
                            <span class="font-semibold text-green-600 text-sm" id="lastUpdateTime">{{ $stats['last_data_update'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Mode Peta:</span>
                            <span id="mapMode" class="font-semibold text-blue-600 text-sm">Normal</span>
                        </div>
                        <div class="pt-3 border-t border-blue-100">
                            <button onclick="showRealTimeData()" class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition">
                                <i class="fas fa-database mr-2"></i>Lihat Data Real-time
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Points Grid -->
        <div class="mb-8">
            <h3 class="text-xl font-bold text-gray-800 mb-4">Data Sensor Lokasi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($locations as $key => $location)
                <div class="bg-white rounded-xl shadow-lg p-5 border-l-4 
                    @if($location['status'] == 'TINGGI') border-red-500 
                    @elseif($location['status'] == 'NORMAL') border-green-500 
                    @else border-yellow-500 @endif">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center">
                                <div class="w-10 h-10 rounded-full 
                                    @if($location['status'] == 'TINGGI') bg-red-100 text-red-600
                                    @elseif($location['status'] == 'NORMAL') bg-green-100 text-green-600
                                    @else bg-yellow-100 text-yellow-600 @endif 
                                    flex items-center justify-center mr-3">
                                    <i class="fas fa-water"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-gray-800">{{ $location['name'] }}</h4>
                                    <p class="text-xs text-gray-500">{{ $location['sensor_id'] ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="inline-block px-3 py-1 text-xs font-semibold rounded-full
                                @if($location['status'] == 'TINGGI') bg-red-100 text-red-800
                                @elseif($location['status'] == 'NORMAL') bg-green-100 text-green-800
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ $location['status'] }}
                            </span>
                        </div>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4">{{ $location['description'] }}</p>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Koordinat:</span>
                            <span class="font-mono text-sm">{{ number_format($location['lat'], 4) }}, {{ number_format($location['lng'], 4) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Tinggi Air:</span>
                            <div class="flex items-center">
                                <span class="font-bold text-lg mr-2">{{ $location['current_height'] }}</span>
                                <span class="text-sm text-gray-500">m</span>
                            </div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Update Terakhir:</span>
                            <span class="text-green-600 text-sm font-semibold" id="updateTime-{{ $key }}">{{ $location['last_update'] ?? '--:--:--' }}</span>
                        </div>
                    </div>
                    
                    <div class="mt-5 flex space-x-2">
                        <button onclick="flyToLocation({{ $location['lat'] }}, {{ $location['lng'] }})" 
                                class="flex-1 px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-medium hover:bg-blue-100 transition flex items-center justify-center">
                            <i class="fas fa-map-marker-alt mr-2"></i>Lihat di Peta
                        </button>
                        <button onclick="showLocationDetails('{{ $key }}')" 
                                class="flex-1 px-4 py-2 bg-gray-50 text-gray-600 rounded-lg text-sm font-medium hover:bg-gray-100 transition flex items-center justify-center">
                            <i class="fas fa-info-circle mr-2"></i>Detail
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-12 border-t bg-white py-6">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center md:text-left">
                    <h4 class="font-bold text-gray-800">SeaGuard Panjang Maps v1.0</h4>
                    <p class="text-sm text-gray-600 mt-2">
                        Sistem Peta Prediksi Pasang Surut<br>
                        Panjang, Bandar Lampung - Lampung
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center space-x-2 bg-blue-50 px-4 py-2 rounded-lg">
                        <i class="fas fa-sync-alt text-blue-500 animate-spin"></i>
                        <span class="text-sm text-gray-700">Data real-time updating</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Powered by Leaflet.js & OpenStreetMap</p>
                </div>
                
                <div class="text-center md:text-right">
                    <p class="text-gray-600">&copy; {{ date('Y') }} SeaGuard Panjang</p>
                    <p class="text-xs text-gray-500 mt-1">Koordinat: 5°28'31.8"S 105°18'52.9"E</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        let map;
        let heatmapLayer;
        let markers = {};
        let streetViewActive = false;
        let satelliteLayer;

        const locations = @json($locations);
        const heatmapData = @json($heatmapData);
        const stats = @json($stats);

        // Time functions
        function getWIBTime() {
            const now = new Date();
            const localTime = now.getTime();
            const localOffset = now.getTimezoneOffset() * 60000;
            const utcTime = localTime + localOffset;
            const wibOffset = 7 * 60 * 60 * 1000;
            return new Date(utcTime + wibOffset);
        }
        
        function formatTime(date) {
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const seconds = date.getSeconds().toString().padStart(2, '0');
            
            return {
                time: `${hours}:${minutes}:<span class="blink">${seconds}</span>`,
                timePlain: `${hours}:${minutes}:${seconds}`
            };
        }
        
        function formatDate(date) {
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 
                          'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            
            const dayName = days[date.getDay()];
            const dateNum = date.getDate();
            const monthName = months[date.getMonth()];
            const year = date.getFullYear();
            
            return {
                full: `${dayName}, ${dateNum} ${monthName} ${year}`,
                short: `${dateNum} ${monthName} ${year}`
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            updateAllTimes();
            setInterval(updateAllTimes, 1000);
            initMap();
        });
        
        function initMap() {
            map = L.map('map').setView([stats.map_center.lat, stats.map_center.lng], stats.zoom_level);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles © Esri',
                maxZoom: 19
            });
            
            Object.keys(locations).forEach(key => {
                addMarker(locations[key], key);
            });
            
            initHeatmap();
            
            map.on('zoomend', updateMapControls);
            map.on('moveend', updateMapControls);
            
            initMapControls();
            updateMapControls();
        }
        
        function addMarker(location, key) {
            const iconColor = location.status === 'TINGGI' ? 'red' : 
                             location.status === 'NORMAL' ? 'green' : 'yellow';
            
            const markerIcon = L.divIcon({
                html: `<div class="w-10 h-10 bg-${iconColor}-500 border-3 border-white rounded-full flex items-center justify-center shadow-lg">
                          <i class="fas fa-water text-white"></i>
                       </div>`,
                className: 'custom-marker',
                iconSize: [40, 40],
                iconAnchor: [20, 40]
            });
            
            const marker = L.marker([location.lat, location.lng], { icon: markerIcon }).addTo(map);
            
            marker.bindPopup(`
                <div class="p-3">
                    <h4 class="font-bold text-gray-800">${location.name}</h4>
                    <p class="text-sm text-gray-600">${location.description}</p>
                    <div class="mt-2 space-y-1">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Tinggi Air:</span>
                            <span class="font-bold">${location.current_height} m</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500">Status:</span>
                            <span class="font-semibold ${location.status === 'TINGGI' ? 'text-red-600' : 
                                location.status === 'NORMAL' ? 'text-green-600' : 'text-yellow-600'}">${location.status}</span>
                        </div>
                    </div>
                </div>
            `);
            
            markers[key] = marker;
        }
        
        function initHeatmap() {
            const points = heatmapData.map(point => [point[0], point[1], point[2]]);
            
            heatmapLayer = L.heatLayer(points, {
                radius: 25,
                blur: 15,
                maxZoom: 17,
                gradient: {0.1: 'blue', 0.3: 'cyan', 0.5: 'lime', 0.7: 'yellow', 1.0: 'red'}
            }).addTo(map);
        }
        
        function initMapControls() {
            document.getElementById('streetViewBtn').addEventListener('click', function() {
                if (!streetViewActive) {
                    const center = map.getCenter();
                    showStreetView(center.lat, center.lng);
                    this.innerHTML = '<i class="fas fa-map mr-2"></i>Map View';
                    streetViewActive = true;
                } else {
                    hideStreetView();
                    this.innerHTML = '<i class="fas fa-street-view mr-2"></i>Street View';
                    streetViewActive = false;
                }
            });
            
            document.getElementById('heatmapToggle').addEventListener('click', function() {
                if (map.hasLayer(heatmapLayer)) {
                    map.removeLayer(heatmapLayer);
                    this.innerHTML = '<i class="fas fa-fire mr-2"></i>Show Heatmap';
                } else {
                    map.addLayer(heatmapLayer);
                    this.innerHTML = '<i class="fas fa-fire mr-2"></i>Hide Heatmap';
                }
            });
            
            document.getElementById('refreshData').addEventListener('click', function() {
                refreshMapData();
            });
            
            document.getElementById('downloadData').addEventListener('click', function() {
                downloadMapData();
            });
            
            document.getElementById('zoomIn').addEventListener('click', () => map.zoomIn());
            document.getElementById('zoomOut').addEventListener('click', () => map.zoomOut());
            document.getElementById('resetView').addEventListener('click', () => {
                map.flyTo([stats.map_center.lat, stats.map_center.lng], stats.zoom_level, { duration: 1 });
            });
        }
        
        function updateAllTimes() {
            const wibTime = getWIBTime();
            const time = formatTime(wibTime);
            const date = formatDate(wibTime);
            
            document.getElementById('liveTime').innerHTML = time.time;
            document.getElementById('mapTime').innerHTML = time.time;
            document.getElementById('liveDate').textContent = date.short;
        }
        
        function updateMapControls() {
            const zoom = map.getZoom();
            const center = map.getCenter();
            document.getElementById('zoomLevel').textContent = zoom;
            document.getElementById('currentCoords').textContent = `${center.lat.toFixed(4)}, ${center.lng.toFixed(4)}`;
        }
        
        window.flyToLocation = function(lat, lng) {
            map.flyTo([lat, lng], 16, { duration: 1.5 });
        };
        
        window.showStreetView = function(lat, lng) {
            map.eachLayer(function(layer) {
                if (layer instanceof L.TileLayer && layer._url.includes('openstreetmap')) {
                    map.removeLayer(layer);
                }
            });
            
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                subdomains: ['mt0','mt1','mt2','mt3'],
                maxZoom: 20
            }).addTo(map);
            
            satelliteLayer.addTo(map);
            map.setView([lat, lng], 18);
            streetViewActive = true;
            document.getElementById('mapMode').textContent = 'Street View';
        };
        
        function hideStreetView() {
            map.eachLayer(function(layer) {
                if (layer instanceof L.TileLayer) {
                    map.removeLayer(layer);
                }
            });
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors',
                maxZoom: 19
            }).addTo(map);
            
            map.setView([stats.map_center.lat, stats.map_center.lng], stats.zoom_level);
            streetViewActive = false;
            document.getElementById('mapMode').textContent = 'Normal';
        }
        
        function refreshMapData() {
            Object.keys(locations).forEach(key => {
                const now = new Date().toLocaleTimeString('id-ID');
                document.getElementById(`updateTime-${key}`).textContent = now;
                locations[key].last_update = now;
            });
            
            document.getElementById('lastUpdateTime').textContent = new Date().toLocaleString('id-ID');
            
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 px-4 py-2 bg-green-500 text-white rounded-lg shadow-lg z-50';
            notification.textContent = 'Data berhasil diperbarui!';
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }
        
        function downloadMapData() {
            const data = {
                export_time: new Date().toISOString(),
                locations: locations,
                stats: stats,
                heatmap_points: heatmapData.length
            };
            
            const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `seaguard-map-data-${new Date().toISOString().slice(0,10)}.json`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
        
        window.showLocationDetails = function(key) {
            const loc = locations[key];
            if (!loc) return;
            
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
                    <div class="p-6 border-b">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold text-gray-800">${loc.name}</h3>
                            <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <p class="text-gray-600 mb-4">${loc.description}</p>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Koordinat:</span>
                                <span class="font-mono">${loc.lat.toFixed(6)}, ${loc.lng.toFixed(6)}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tinggi Air:</span>
                                <span class="font-bold">${loc.current_height} m</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Status:</span>
                                <span class="font-semibold ${loc.status === 'TINGGI' ? 'text-red-600' : 
                                    loc.status === 'NORMAL' ? 'text-green-600' : 'text-yellow-600'}">${loc.status}</span>
                            </div>
                        </div>
                        <button onclick="flyToLocation(${loc.lat}, ${loc.lng}); this.closest('.fixed').remove()" 
                                class="w-full mt-6 px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition">
                            <i class="fas fa-map-marker-alt mr-2"></i>Lihat di Peta
                        </button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        };
        
        window.showRealTimeData = function() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full">
                    <div class="p-6 border-b">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold text-gray-800">Data Real-time Sensor</h3>
                            <button onclick="this.closest('.fixed').remove()" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6 max-h-[60vh] overflow-y-auto">
                        <div class="space-y-4">
                            ${Object.keys(locations).map(key => {
                                const loc = locations[key];
                                return `
                                    <div class="border rounded-lg p-4">
                                        <div class="flex justify-between items-center">
                                            <h4 class="font-semibold text-gray-800">${loc.name}</h4>
                                            <span class="px-3 py-1 text-xs rounded-full ${loc.status === 'TINGGI' ? 'bg-red-100 text-red-800' :
                                                loc.status === 'NORMAL' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'}">
                                                ${loc.status}
                                            </span>
                                        </div>
                                        <div class="mt-3 grid grid-cols-2 gap-4">
                                            <div>
                                                <p class="text-sm text-gray-500">Tinggi Air</p>
                                                <p class="text-lg font-bold">${loc.current_height} m</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-500">Update</p>
                                                <p class="text-sm">${loc.last_update}</p>
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }).join('')}
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        };
    </script>
</body>
</html>