<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// On-boarding Routes
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/register', 'register')->name('user-register');
    Route::post('/login', 'login')->name('user-login');
    Route::post('/forgot-pass', 'forgotPassword')->name('user-forgot-password');
    Route::post('/verify-code', 'verifyCode')->name('user-verify-account');
});

Route::middleware("auth:api")->prefix('user')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::post('/reset-password', 'resetPassword')->name('user-reset-password');
    });

    Route::controller(UserController::class)->group(function () {
        Route::post('/update-profile', 'updateProfile')->name('user-update-profile');
        Route::get('/get-user-details/{user}', 'getUserDetails')->name('user-details');
    });
    Route::controller(VideoController::class)->group(function () {
        Route::post('/save-video', 'saveVideo')->name('user-save-video');
    });
});


