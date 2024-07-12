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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// On-boarding Routes
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/signup', 'signUp')->name('user-signup');
    Route::post('/send-code', 'sendCode')->name('user-send-code');
    Route::post('/verifyCode', 'verifyCode')->name('user-verify-account');
    Route::post('/resetPassword', 'resetPassword')->name('user-resetPass');
});
