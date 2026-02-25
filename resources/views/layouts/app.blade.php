<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeaGuard Panjang - @yield('title', 'Dashboard')</title>
    
    <!-- Load Tailwind dari CDN -->
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
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Leaflet -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    
    <style>
        /* Custom Styles */
        body {
            font-family: system-ui, -apple-system, 'Segoe UI', sans-serif;
        }
        .time-card {
            background: linear-gradient(135deg, #1e3a8a 0%, #0ea5e9 100%);
            color: white;
        }
        .tide-animation {
            animation: tideFlow 3s ease-in-out infinite;
        }
        
        /* Digital Clock Style */
        .digital-clock {
            font-family: 'Courier New', monospace;
            font-weight: bold;
            letter-spacing: 2px;
        }
        
        /* Wave background effect */
        .wave-bg {
            background: linear-gradient(180deg, 
                rgba(14, 165, 233, 0.1) 0%, 
                rgba(14, 165, 233, 0.05) 50%, 
                transparent 100%);
        }
        
        /* Blink effect for seconds */
        .blink {
            animation: blink 1s step-start infinite;
        }
        
        @keyframes blink {
            50% { opacity: 0.4; }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <div class="bg-gradient-to-r from-blue-900 to-cyan-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 py-4">
            <div class="flex justify-between items-center">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <div class="tide-animation">
                        <i class="fas fa-water text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold">SeaGuard Panjang</h1>
                        <p class="text-xs text-blue-200">Prediksi Pasang Surut</p>
                    </div>
                </div>
                
                <!-- Live Time WIB -->
                <div class="hidden md:flex items-center space-x-4">
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <div class="flex items-center">
                            <i class="fas fa-clock mr-2 text-blue-200"></i>
                            <div class="text-center">
                                <div class="digital-clock text-lg" id="liveTime">
                                    --:--:--
                                </div>
                                <div class="text-xs text-blue-200 mt-1">
                                    Waktu Indonesia Barat (WIB)
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white/10 backdrop-blur-sm px-4 py-2 rounded-lg">
                        <div class="text-center">
                            <div class="text-sm">Tanggal</div>
                            <div class="font-semibold" id="liveDate">
                                -- --- ----
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CSRF Token -->
                <meta name="csrf-token" content="{{ csrf_token() }}">

                <!-- Menu -->
                <div class="flex space-x-2">
                    <a href="/dashboard" class="px-4 py-2 rounded-lg hover:bg-white/10 transition flex items-center">
                        <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                    </a>
                    <a href="/weekly-prediction" class="px-4 py-2 rounded-lg hover:bg-white/10 transition flex items-center">
                        <i class="fas fa-chart-line mr-2"></i>Prediksi
                    </a>
                    <a href="/maps" class="px-4 py-2 rounded-lg hover:bg-white/10 transition flex items-center">
                        <i class="fas fa-map mr-2"></i>Peta
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-12 border-t bg-white py-6 wave-bg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center md:text-left">
                    <h4 class="font-bold text-gray-800">SeaGuard Panjang v1.0</h4>
                    <p class="text-sm text-gray-600 mt-2">
                        Sistem Prediksi Pasang Surut Air Laut<br>
                        Panjang, Bandar Lampung
                    </p>
                </div>
                
                <div class="text-center">
                    <div class="inline-flex items-center space-x-2 bg-blue-50 px-4 py-2 rounded-lg">
                        <i class="fas fa-sync-alt text-blue-500"></i>
                        <span class="text-sm text-gray-700">Data diperbarui secara real-time</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">Waktu Server: <span id="serverTime">{{ now('Asia/Jakarta')->format('H:i:s') }}</span> WIB</p>
                </div>
                
                <div class="text-center md:text-right">
                    <p class="text-gray-600">&copy; {{ date('Y') }} SeaGuard Panjang</p>
                    <p class="text-xs text-gray-500 mt-1">Algoritma KNN | Data BMKG</p>
                </div>
            </div>
        </div>
    </footer>

    @yield('scripts')
    
    <!-- Live Time Script -->
    <script>
        // Function to convert local time to WIB (UTC+7)
        function getWIBTime() {
            const now = new Date();
            
            // Get local time in milliseconds
            const localTime = now.getTime();
            
            // Get local timezone offset in minutes
            const localOffset = now.getTimezoneOffset() * 60000;
            
            // Convert to UTC
            const utcTime = localTime + localOffset;
            
            // WIB is UTC+7 (7 hours = 25200000 milliseconds)
            const wibOffset = 7 * 60 * 60 * 1000;
            const wibTime = new Date(utcTime + wibOffset);
            
            return wibTime;
        }
        
        // Format time to HH:MM:SS
        function formatTime(date) {
            const hours = date.getHours().toString().padStart(2, '0');
            const minutes = date.getMinutes().toString().padStart(2, '0');
            const seconds = date.getSeconds().toString().padStart(2, '0');
            
            return {
                time: `${hours}:${minutes}:<span class="blink">${seconds}</span>`,
                timePlain: `${hours}:${minutes}:${seconds}`,
                hour: hours,
                minute: minutes,
                second: seconds
            };
        }
        
        // Format date to Indonesian format
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
        
        // Update all time elements
        function updateAllTimes() {
            const wibTime = getWIBTime();
            const time = formatTime(wibTime);
            const date = formatDate(wibTime);
            
            // Update time elements
            const timeElements = document.querySelectorAll('.digital-clock, #liveTime, #dashboardTime, #serverTime, #currentTimeWIB');
            timeElements.forEach(el => {
                if (el.classList.contains('digital-clock') || el.id === 'currentTimeWIB') {
                    el.innerHTML = time.time;
                } else {
                    el.textContent = time.timePlain;
                }
            });
            
            // Update date elements
            document.getElementById('liveDate').textContent = date.short;
            
            // Update progress bar
            const totalMinutes = wibTime.getHours() * 60 + wibTime.getMinutes();
            const progressPercentage = (totalMinutes / (24 * 60)) * 100;
            const progressBar = document.getElementById('timeProgress');
            if (progressBar) {
                progressBar.style.width = `${progressPercentage}%`;
            }
            
            // Update current hour display
            const currentHourElement = document.getElementById('currentHour');
            if (currentHourElement) {
                currentHourElement.textContent = `${time.hour}:${time.minute}`;
            }
        }
        
        // Initialize time update
        updateAllTimes();
        setInterval(updateAllTimes, 1000);
    </script>
</body>
</html>