<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SeaGuard Panjang - Login</title>
    
    <!-- Tailwind CSS -->
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
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #1e3a8a 0%, #0ea5e9 50%, #22d3ee 100%);
            min-height: 100vh;
            font-family: system-ui, -apple-system, sans-serif;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .wave-animation {
            animation: wave 3s ease-in-out infinite;
        }
        
        @keyframes wave {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <!-- Live Time Display -->
    <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm rounded-lg px-4 py-2 shadow-lg">
        <div class="text-sm font-semibold text-gray-700">
            <i class="far fa-clock mr-2"></i>
            WIB: <span id="currentTime">{{ $currentTime ?? '--:--:--' }}</span>
        </div>
        <div class="text-xs text-gray-600">{{ $currentDate ?? '-- --- ----' }}</div>
    </div>
    
    <!-- Login Card -->
    <div class="login-card rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="wave-animation inline-block mb-4">
                <div class="w-20 h-20 bg-gradient-to-r from-blue-500 to-cyan-400 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-water text-3xl text-white"></i>
                </div>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">SeaGuard Panjang</h1>
            <p class="text-gray-600">Sistem Prediksi Pasang Surut Air Laut</p>
            <p class="text-sm text-blue-500 mt-2">Bandar Lampung - Panjang</p>
        </div>
        
        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <span class="font-medium">Login gagal!</span>
                </div>
                <ul class="mt-2 text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if(session('status'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                <div class="flex items-center">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif
        
        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            
            <div class="space-y-6">
                <!-- Email -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="email">
                        <i class="fas fa-envelope mr-2"></i>Email Address
                    </label>
                    <input type="email" id="email" name="email" 
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="admin@seaguard.id"
                           required
                           autofocus>
                </div>
                
                <!-- Password -->
                <div>
                    <label class="block text-gray-700 text-sm font-medium mb-2" for="password">
                        <i class="fas fa-lock mr-2"></i>Password
                    </label>
                    <input type="password" id="password" name="password" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                           placeholder="••••••••"
                           required>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="w-4 h-4 text-blue-600 rounded">
                    <label for="remember" class="ml-2 text-sm text-gray-600">Ingat saya</label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-cyan-500 text-white font-semibold py-3 rounded-lg hover:from-blue-600 hover:to-cyan-600 transition duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-sign-in-alt mr-2"></i>Masuk ke Sistem
                </button>
            </div>
        </form>
        
        <!-- Demo Credentials -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="text-center">
                <p class="text-sm text-gray-600 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>
                    Gunakan Username dan Password Ini:
                </p>
                <div class="bg-gray-50 rounded-lg p-3 text-left">
                    <div class="text-sm">
                        <div class="flex items-center mb-1">
                            <i class="fas fa-user text-gray-500 mr-2 w-4"></i>
                            <span class="font-medium">Email:</span>
                            <code class="ml-2 bg-gray-100 px-2 py-1 rounded">admin@seaguard.id</code>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-key text-gray-500 mr-2 w-4"></i>
                            <span class="font-medium">Password:</span>
                            <code class="ml-2 bg-gray-100 px-2 py-1 rounded">password123</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Update time script -->
    <script>
        function updateTime() {
            const now = new Date();
            const options = { 
                timeZone: 'Asia/Jakarta',
                hour12: false,
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            };
            const timeString = now.toLocaleTimeString('id-ID', options);
            document.getElementById('currentTime').textContent = timeString;
        }
        
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>