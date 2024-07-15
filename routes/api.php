<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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

Route::prefix('user')->middleware("auth:api")->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout');
        Route::post('/reset-password', 'resetPassword');
    });
});



Route::post('/logout',[AuthController::class,'logout'])
  ->middleware('auth:sanctum');
