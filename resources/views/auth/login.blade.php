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
    <div class="login-card rounded-[2rem] shadow-2xl p-6 md:p-10 w-full max-w-md relative overflow-hidden group">
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-blue-400 via-cyan-400 to-blue-400"></div>
        
        <!-- Logo -->
        <div class="text-center mb-10 relative z-10">
            <div class="wave-animation inline-block mb-6">
                <div class="w-24 h-24 bg-white dark:bg-gray-800 rounded-3xl flex items-center justify-center mx-auto shadow-2xl border border-white/20 transform rotate-12 group-hover:rotate-0 transition-all duration-700">
                    <i class="fas fa-water text-4xl text-blue-500"></i>
                </div>
            </div>
            <h1 class="text-3xl md:text-4xl font-black text-gray-800 leading-tight tracking-tight uppercase">SeaGuard</h1>
            <p class="text-[10px] md:text-xs font-black text-blue-500 uppercase tracking-[0.3em] mt-2">Panjang Prediksi System</p>
        </div>
        
        <!-- Error Messages -->
        @if($errors->any())
            <div class="bg-red-500/10 border border-red-500/20 text-red-600 px-4 py-4 rounded-2xl mb-8 backdrop-blur-md animate-shake">
                <div class="flex items-center text-sm font-black uppercase tracking-widest mb-2">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    <span>Akses Ditolak</span>
                </div>
                <ul class="text-[10px] font-bold space-y-1">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @if(session('status'))
            <div class="bg-green-500/10 border border-green-500/20 text-green-600 px-4 py-4 rounded-2xl mb-8 backdrop-blur-md">
                <div class="flex items-center text-sm font-black uppercase tracking-widest">
                    <i class="fas fa-check-circle mr-2"></i>
                    <span>{{ session('status') }}</span>
                </div>
            </div>
        @endif
        
        <!-- Login Form -->
        <form method="POST" action="{{ route('login.post') }}" class="relative z-10">
            @csrf
            
            <div class="space-y-6">
                <!-- Email -->
                <div>
                    <label class="block text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2 px-1" for="email">
                        Alamat Email
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <input type="email" id="email" name="email" 
                               value="{{ old('email') }}"
                               class="w-full pl-11 pr-4 py-4 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold text-gray-800 placeholder-gray-400"
                               placeholder="admin@seaguard.id"
                               required
                               autofocus>
                    </div>
                </div>
                
                <!-- Password -->
                <div>
                    <label class="block text-gray-500 text-[10px] font-black uppercase tracking-widest mb-2 px-1" for="password">
                        Kata Sandi
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-blue-500 transition-colors">
                            <i class="fas fa-key"></i>
                        </div>
                        <input type="password" id="password" name="password" 
                               class="w-full pl-11 pr-4 py-4 bg-gray-50/50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold text-gray-800 placeholder-gray-400"
                               placeholder="••••••••"
                               required>
                    </div>
                </div>
                
                <!-- Remember Me -->
                <div class="flex items-center justify-between px-1">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" id="remember" name="remember" class="w-5 h-5 text-blue-600 rounded-lg border-gray-300 focus:ring-blue-500 transition-all">
                        <span class="ml-3 text-xs font-bold text-gray-500 group-hover:text-gray-700 transition-colors">Ingat saya</span>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-black py-4 rounded-2xl shadow-xl shadow-blue-500/20 transition-all hover:scale-[1.02] active:scale-95 flex items-center justify-center text-sm uppercase tracking-widest">
                    <span>Otorisasi Sistem</span>
                    <i class="fas fa-chevron-right ml-3 text-xs opacity-50 group-hover:translate-x-1 transition-transform"></i>
                </button>
            </div>
        </form>
        
        <!-- Demo Credentials -->
        <div class="mt-10 pt-8 border-t border-gray-100/50 relative z-10">
            <div class="text-center">
                <p class="text-[9px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">
                    Kredensial Otoritas
                </p>
                <div class="bg-blue-50/50 rounded-2xl p-4 border border-blue-100 flex flex-col gap-3">
                    <div class="flex items-center justify-between bg-white px-4 py-2 rounded-xl shadow-sm border border-blue-50">
                        <span class="text-[10px] font-black text-gray-400 uppercase">User</span>
                        <code class="text-[10px] font-black text-blue-600">admin@seaguard.id</code>
                    </div>
                    <div class="flex items-center justify-between bg-white px-4 py-2 rounded-xl shadow-sm border border-blue-50">
                        <span class="text-[10px] font-black text-gray-400 uppercase">Pass</span>
                        <code class="text-[10px] font-black text-blue-600">password123</code>
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