<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', function () {
    return view('welcome');
});
Route::get('/vssadmin', [AdminController::class,'index'])->middleware('guest')->name('login');
Route::post('/login', [AdminController::class, 'login'])->name('authcheck');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});
// Logout route
Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
