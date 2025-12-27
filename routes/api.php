<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\SecurityController;

// Batasi akses: Maksimal 5 kali request per menit untuk endpoint register
Route::middleware('throttle:5,1')->group(function () {
Route::post('/register', [AuthController::class, 'register']);
});

// Route login (lebih longgar sedikit)
Route::post('/login', [AuthController::class, 'login']);

// Route yang butuh login
Route::middleware('auth:sanctum')->group(function () {
Route::get('/user', [AuthController::class, 'profile']);
Route::post('/logout', [AuthController::class, 'logout']);
});

// Route OTP User
Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/register', [AuthController::class, 'register']);

// Route Update Profil User
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);
});

// Route Login Akun Google
Route::post('/google-login', [AuthController::class, 'googleLogin']);

// Route Notifikasi
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
});

// Route History Login Aplikasi zona app mobile
Route::middleware('auth:sanctum')->group(function () {
    // Grouping fitur keamanan
    Route::prefix('security')->group(function () {
        Route::get('/login-history', [SecurityController::class, 'getLoginHistory']);
        // Route::post('/change-email', [SecurityController::class, 'updateEmail']);
    });
});

// Route Login Histori
Route::middleware('auth:sanctum')->group(function () {
    // ==================== SECURITY & DEVICES ====================
    Route::prefix('security')->group(function () {
        Route::get('/login-history', [SecurityController::class, 'getLoginHistory']);
        Route::delete('/logout-device/{id}', [SecurityController::class, 'logoutDevice']);
    });
});

// Route Ubah Email
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/security/request-email-otp', [SecurityController::class, 'requestEmailChange']);
    Route::post('/security/verify-email-change', [SecurityController::class, 'verifyEmailChange']);
});

// Route 2FA security
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/security/2fa/request', [SecurityController::class, 'requestTwoFactor']);
    Route::post('/security/2fa/enable', [SecurityController::class, 'enableTwoFactor']);
    Route::post('/security/2fa/disable', [SecurityController::class, 'disableTwoFactor']);
    Route::get('/security/2fa/status', [SecurityController::class, 'getTwoFactorStatus']);
});