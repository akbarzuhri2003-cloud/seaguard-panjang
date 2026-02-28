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
    <nav class="bg-gradient-to-r from-blue-900 to-cyan-800 text-white shadow-lg sticky top-0 z-[1000]">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center h-16">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="tide-animation">
                        <i class="fas fa-water text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-lg md:text-xl font-bold leading-tight">SeaGuard</h1>
                        <p class="text-[10px] md:text-xs text-blue-200 uppercase tracking-wider">Panjang Prediksi</p>
                    </div>
                </div>
                
                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="/dashboard" class="px-3 py-2 rounded-lg hover:bg-white/10 transition flex items-center text-sm">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="/weekly-prediction" class="px-3 py-2 rounded-lg hover:bg-white/10 transition flex items-center text-sm">
                        <i class="fas fa-chart-line mr-2"></i>Prediksi
                    </a>
                    <a href="/maps" class="px-3 py-2 rounded-lg bg-white/20 transition flex items-center text-sm">
                        <i class="fas fa-map mr-2"></i>Peta
                    </a>
                    <form action="/logout" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-3 py-2 rounded-lg hover:bg-white/10 transition flex items-center text-sm text-red-200 hover:text-white">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </form>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" class="text-white hover:text-blue-200 focus:outline-none p-2 rounded-lg hover:bg-white/10">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-blue-900/95 backdrop-blur-md border-t border-white/10">
            <div class="px-4 pt-2 pb-6 space-y-2">
                <a href="/dashboard" class="block px-4 py-3 rounded-lg hover:bg-white/10 transition flex items-center">
                    <i class="fas fa-tachometer-alt w-8"></i>Dashboard
                </a>
                <a href="/weekly-prediction" class="block px-4 py-3 rounded-lg hover:bg-white/10 transition flex items-center">
                    <i class="fas fa-chart-line w-8"></i>Prediksi Mingguan
                </a>
                <a href="/maps" class="block px-4 py-3 rounded-lg bg-white/20 flex items-center">
                    <i class="fas fa-map w-8"></i>Peta Interaktif
                </a>
                <form action="/logout" method="POST" class="block pt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 rounded-lg hover:bg-white/10 transition flex items-center text-red-300">
                        <i class="fas fa-sign-out-alt w-8"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6 md:py-8">
        <!-- Header -->
        <div class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl font-black text-gray-800 leading-tight">Peta Lokasi & Heatmap</h1>
            <p class="text-xs md:text-sm text-gray-500 mt-1 flex items-center">
                <i class="fas fa-map-marked-alt mr-2 text-blue-500"></i>
                <span id="totalSensorsDisplay" class="font-bold">{{ count($locations) }} Sensor Aktif</span>
                <span class="mx-2">•</span>
                <span class="font-medium" id="mapTime">--:--:--</span> WIB
            </p>
        </div>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-blue-500">
                <p class="text-[10px] md:text-xs text-gray-400 font-black uppercase tracking-widest">Total Sensor</p>
                <p class="text-lg md:text-xl font-black text-gray-800 mt-1">{{ $stats['total_sensors'] }}</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-red-500">
                <p class="text-[10px] md:text-xs text-gray-400 font-black uppercase tracking-widest">Pasang Tinggi</p>
                <p class="text-lg md:text-xl font-black text-gray-800 mt-1">{{ $stats['high_tide_locations'] }}</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-green-500">
                <p class="text-[10px] md:text-xs text-gray-400 font-black uppercase tracking-widest">Tinggi Rerata</p>
                <p class="text-lg md:text-xl font-black text-gray-800 mt-1">{{ $stats['avg_height'] }}m</p>
            </div>
            
            <div class="bg-white rounded-xl shadow-md p-4 border-l-4 border-purple-500">
                <p class="text-[10px] md:text-xs text-gray-400 font-black uppercase tracking-widest">Data Update</p>
                <p class="text-[10px] md:text-sm font-bold text-gray-600 mt-1 leading-tight">{{ \Carbon\Carbon::parse($stats['last_data_update'])->format('H:i:s') }}</p>
            </div>
        </div>

        <!-- Map Container -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="p-4 md:p-6 bg-gray-50/50 border-b flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h3 class="text-base md:text-lg font-black text-gray-800 uppercase tracking-tight">Interactive Map View</h3>
                    <p class="text-[10px] md:text-sm text-gray-500">Panjang, Bandar Lampung (Real-time)</p>
                </div>
                <div class="flex flex-wrap gap-2 w-full md:w-auto">
                    <button id="streetViewBtn" class="flex-1 md:flex-none px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center">
                        <i class="fas fa-street-view mr-2"></i>Street View
                    </button>
                    <button id="heatmapToggle" class="flex-1 md:flex-none px-3 py-2 bg-red-600 hover:bg-red-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center">
                        <i class="fas fa-fire mr-2"></i>Heatmap
                    </button>
                    <button id="refreshData" class="flex-1 md:flex-none px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center">
                        <i class="fas fa-sync-alt mr-2"></i>Refresh
                    </button>
                    <button id="downloadData" class="flex-1 md:flex-none px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-xl text-[10px] font-black uppercase tracking-widest transition flex items-center justify-center">
                        <i class="fas fa-download mr-2"></i>Data
                    </button>
                </div>
            </div>
            
            <div id="map" style="height: 500px; width: 100%; min-height: 400px;" class="z-0 border-b bg-gray-200"></div>
            
            <!-- Map Controls & Legend -->
            <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                        <i class="fas fa-sliders-h mr-2"></i>Kontrol Peta
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-tighter">Zoom Level:</span>
                            <span id="zoomLevel" class="font-black text-xs text-blue-600 px-2 py-1 bg-white rounded-lg shadow-sm">{{ $stats['zoom_level'] }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] md:text-xs font-bold text-gray-500 uppercase tracking-tighter">Koordinat:</span>
                            <span id="currentCoords" class="font-mono text-[9px] text-gray-600 bg-white px-2 py-1 rounded-lg truncate ml-4">{{ $stats['map_center']['lat'] }}, {{ $stats['map_center']['lng'] }}</span>
                        </div>
                        <div class="grid grid-cols-3 gap-2 mt-4">
                            <button id="zoomIn" class="p-2 bg-white text-blue-600 rounded-lg shadow-sm hover:bg-blue-50 transition border border-gray-100">
                                <i class="fas fa-plus"></i>
                            </button>
                            <button id="zoomOut" class="p-2 bg-white text-blue-600 rounded-lg shadow-sm hover:bg-blue-50 transition border border-gray-100">
                                <i class="fas fa-minus"></i>
                            </button>
                            <button id="resetView" class="p-2 bg-white text-blue-600 rounded-lg shadow-sm hover:bg-blue-50 transition border border-gray-100">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Legend -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                        <i class="fas fa-list-ul mr-2"></i>Legenda
                    </h4>
                    <div class="bg-gray-50 rounded-xl p-4 space-y-3">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-3 shadow-sm border border-white"></div>
                            <span class="text-[10px] md:text-xs font-bold text-gray-700">PASANG TINGGI (≥ 1.8m)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3 shadow-sm border border-white"></div>
                            <span class="text-[10px] md:text-xs font-bold text-gray-700">NORMAL (1.2m - 1.8m)</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3 shadow-sm border border-white"></div>
                            <span class="text-[10px] md:text-xs font-bold text-gray-700">SIAGA (≤ 1.2m)</span>
                        </div>
                        <div class="mt-4 pt-3 border-t border-gray-200">
                            <div class="text-[9px] font-black text-gray-400 mb-2 uppercase italic">Heatmap Gradient</div>
                            <div class="legend-gradient shadow-sm"></div>
                        </div>
                    </div>
                </div>
                
                <!-- System Info -->
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>Info
                    </h4>
                    <div class="bg-blue-50/50 rounded-xl p-4 space-y-3 border border-blue-100">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-blue-900/60 uppercase">System Status</span>
                            <span class="px-2 py-0.5 bg-green-100 text-green-700 rounded-lg text-[9px] font-black animate-pulse uppercase tracking-widest border border-green-200">Live</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-bold text-blue-900/60 uppercase">Mode Peta</span>
                            <span id="mapMode" class="font-black text-blue-600 text-[10px] uppercase">Normal</span>
                        </div>
                        <div class="pt-2">
                             <button onclick="showRealTimeData()" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition shadow-md">
                                <i class="fas fa-table mr-2"></i>Real-time Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Data Points Grid -->
        <div class="mb-8">
            <h3 class="text-lg font-black text-gray-800 mb-6 flex items-center uppercase tracking-tight">
                <i class="fas fa-microchip mr-3 text-blue-500"></i>Sensor Lokasi
            </h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($locations as $key => $location)
                <div class="bg-white rounded-2xl shadow-lg p-5 border border-gray-100 group hover:border-blue-500/30 transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center mr-3 shadow-inner 
                                @if($location['status'] == 'TINGGI') bg-red-50 text-red-600
                                @elseif($location['status'] == 'NORMAL') bg-green-50 text-green-600
                                @else bg-yellow-50 text-yellow-600 @endif transition-transform group-hover:scale-110">
                                <i class="fas fa-water"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-gray-800 leading-tight uppercase tracking-tighter">{{ $location['name'] }}</h4>
                                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest mt-0.5">{{ $location['sensor_id'] ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                             <p class="text-[8px] text-gray-400 font-black uppercase tracking-widest mb-1">Tinggi Air</p>
                             <div class="flex items-end">
                                 <span class="text-xl font-black text-gray-800 leading-none">{{ $location['current_height'] }}</span>
                                 <span class="text-[10px] text-gray-500 font-bold ml-1 mb-0.5">m</span>
                             </div>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-3 border border-gray-100">
                             <p class="text-[8px] text-gray-400 font-black uppercase tracking-widest mb-1">Status</p>
                             <span class="text-[10px] font-black uppercase tracking-widest
                                 @if($location['status'] == 'TINGGI') text-red-600 @elseif($location['status'] == 'NORMAL') text-green-600 @else text-yellow-600 @endif">
                                 {{ $location['status'] }}
                             </span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between gap-2">
                        <button onclick="flyToLocation({{ $location['lat'] }}, {{ $location['lng'] }})" 
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-blue-700 transition shadow-md">
                            <i class="fas fa-map-marker-alt mr-2"></i>Map
                        </button>
                        <button onclick="showLocationDetails('{{ $key }}')" 
                                class="px-4 py-2 bg-gray-100 text-gray-600 rounded-lg text-[10px] font-black uppercase tracking-widest hover:bg-gray-200 transition">
                            <i class="fas fa-info-circle"></i>
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
            
            // Mobile Menu Toggle
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                });
            }
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