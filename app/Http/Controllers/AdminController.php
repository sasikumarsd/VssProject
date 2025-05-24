<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminController extends Controller
{
      public function index()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard'); // redirect if already logged in
        }
        $users = User::all();
        return view("admin.auth.login", compact("users"));
    }

    public function login(Request $request){

         $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $throttleKey = Str::lower($request->email) . '|' . $request->ip();

        // Check if the user is rate limited
        if (RateLimiter::tooManyAttempts($throttleKey, 3)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'email' => "Too many login attempts. Try again in " . ceil($seconds / 60) . " minutes.",
            ])->withInput();
        }
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password)) {
            RateLimiter::clear($throttleKey); // Reset attempts on success
            Auth::login($user);
            return redirect()->route('dashboard');
        }

        // Increment failed login attempts
        RateLimiter::hit($throttleKey, 600); // Lockout for 600 seconds (10 minutes)

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput();

    }

    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'You have been logged out.');
    }

}
