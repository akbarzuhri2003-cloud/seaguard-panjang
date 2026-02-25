<?php
// app/Http\Controllers\Auth\LoginController.php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

class LoginController extends Controller
{
    /**
     * Show login form
     */
    public function showLoginForm()
    {
        // Jika sudah login, redirect ke dashboard
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        
        // Waktu live WIB
        $currentTime = Carbon::now('Asia/Jakarta')->format('H:i:s');
        $currentDate = Carbon::now('Asia/Jakarta')->format('l, d F Y');
        
        return view('auth.login', compact('currentTime', 'currentDate'));
    }
    
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        
        // Coba login
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }
        
        // Jika gagal
        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->except('password'));
    }
    
    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}