<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeaGuard Panjang - @yield('title', 'Dashboard')</title>
    
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <style>
        /* Fallback jika CSS belum load */
        body { margin: 0; padding: 0; font-family: system-ui, sans-serif; }
        .nav-active { border-bottom: 2px solid white; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Navigation Bar -->
    <nav class="bg-gradient-to-r from-blue-900 to-cyan-800 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <!-- Logo & Brand -->
                <div class="flex items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas fa-water text-xl"></i>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold">SeaGuard Panjang</h1>
                            <p class="text-xs text-blue-200">Prediksi Pasang Surut</p>
                        </div>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden md:flex ml-10 space-x-1">
                        <a href="{{ route('dashboard') }}" 
                           class="px-4 py-2 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-white/20' : 'hover:bg-white/10' }} transition">
                            <i class="fas fa-tachometer-alt mr-2"></i>Dashboard
                        </a>
                        <a href="{{ route('weekly') }}" 
                           class="px-4 py-2 rounded-lg {{ request()->routeIs('weekly') ? 'bg-white/20' : 'hover:bg-white/10' }} transition">
                            <i class="fas fa-chart-line mr-2"></i>Prediksi Mingguan
                        </a>
                        <a href="{{ route('maps') }}" 
                           class="px-4 py-2 rounded-lg {{ request()->routeIs('maps') ? 'bg-white/20' : 'hover:bg-white/10' }} transition">
                            <i class="fas fa-map-marked-alt mr-2"></i>Peta Panjang
                        </a>
                    </div>
                </div>
                
                <!-- Right Section -->
                <div class="flex items-center space-x-4">
                    <!-- Time Display -->
                    <div class="hidden sm:block bg-white/10 px-3 py-1 rounded-lg">
                        <i class="far fa-clock mr-2"></i>
                        <span id="currentTime">{{ now('Asia/Jakarta')->format('H:i') }}</span> WIB
                    </div>
                    
                    <!-- User & Logout -->
                    <div class="relative">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded-lg flex items-center transition">
                                <i class="fas fa-sign-out-alt mr-2"></i>
                                <span class="hidden md:inline">Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-6">
        <!-- Page Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">@yield('page-title', 'Dashboard')</h1>
            <p class="text-gray-600 mt-2">@yield('page-description', 'Sistem prediksi pasang surut air laut menggunakan algoritma KNN')</p>
        </div>
        
        <!-- Content -->
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-12 border-t bg-white py-6">
        <div class="max-w-7xl mx-auto px-4 text-center text-gray-600">
            <p class="font-semibold">SeaGuard Panjang v1.0</p>
            <p class="text-sm mt-2">Sistem Prediksi Pasang Surut Air Laut &copy; {{ date('Y') }}</p>
            <p class="text-xs mt-1">Bandar Lampung - Panjang | Algoritma KNN | Data BMKG</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet.heat@0.2.0/dist/leaflet-heat.js"></script>
    
    <!-- Update Waktu -->
    <script>
        function updateWaktu() {
            const sekarang = new Date();
            const options = { 
                timeZone: 'Asia/Jakarta',
                hour12: false,
                hour: '2-digit',
                minute: '2-digit'
            };
            const waktu = sekarang.toLocaleTimeString('id-ID', options);
            document.getElementById('currentTime').textContent = waktu;
        }
        
        // Update setiap menit
        setInterval(updateWaktu, 60000);
        updateWaktu();
    </script>
    
    @yield('scripts')
</body>
</html>