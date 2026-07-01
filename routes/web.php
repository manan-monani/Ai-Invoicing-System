<?php

use App\Http\Controllers\CaptchaController;
use App\Http\Controllers\Guest\AuthController;
use App\Http\Controllers\Guest\ContactController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('captcha', [CaptchaController::class, 'generate'])->name('captcha');
Route::get('captcha-code', [CaptchaController::class, 'getCode'])->name('captcha.code');

Route::get('/', function () {
    $user = Auth::user();

    if (! $user) {
        return redirect('login');
    }

    if ($user->isClient()) {
        return redirect()->route('customer.dashboard');
    }

    if ($user->isEmployee()) {
        return redirect()->route('employee.dashboard');
    }

    return redirect()->route('admin.dashboard');
});

Route::get('contact', [ContactController::class, 'show'])->name('contact');

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'storeLogin']);
    Route::get('register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('register', [AuthController::class, 'storeRegister']);
});

Route::middleware('auth')->group(function () {
    Route::get('logout-page', function () {
        return Inertia::render('Logout');
    })->name('logout-page');
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
});

// Load admin routes
require __DIR__.'/admin.php';

// Load customer routes
require __DIR__.'/customer.php';
