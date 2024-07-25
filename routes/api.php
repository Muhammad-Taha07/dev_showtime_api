<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\CommentController;

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

// On-boarding Routes
Route::controller(AuthController::class)->prefix('auth')->group(function () {
    Route::post('/register', 'register')->name('user-register');
    Route::post('/login', 'login')->name('user-login');
    Route::post('/forgot-pass', 'forgotPassword')->name('user-forgot-password');
    Route::post('/verify-code', 'verifyCode')->name('user-verify-account');
});

// Authenticated Routes
Route::middleware("auth:api")->prefix('user')->group(function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('/logout', 'logout')->name('logout');
        Route::post('/reset-password', 'resetPassword')->name('user-reset-password');
    });

// User Profile Routes
    Route::controller(UserController::class)->group(function () {
        Route::post('/update-profile', 'updateProfile')->name('user-update-profile');
        Route::get('/get-user-details/{user}', 'getUserDetails')->name('user-details');
    });

// Video Routes
    Route::controller(MediaController::class)->group(function () {
        Route::get('/get-all-medias', 'getAllMedias')->name('get-all-medias');
        Route::get('/get-media/{id}', 'getMedia')->name('open-media');
        Route::get('/get-user-medias', 'getCurrentUserMedias')->name('user-own-medias');
        Route::post('/save-media', 'saveMedia')->name('user-save-media');
        Route::delete('/delete-media/{id}', 'deleteMedia')->middleware('check.video.ownership');
        Route::post('/view-video/{id}', 'viewVideo')->name('view-video');
        Route::post('/like-video', 'toggleLikeDislike')->name('user-like-video');
    });

// Comment Routes
    Route::controller(CommentController::class)->group(function () {
        Route::get('/get-comments/{id}', 'getComments')->name('get-comments');
        Route::post('/post-comment', 'postComment')->name('post-comment');
        Route::delete('/delete-comment/{id}', 'deleteComment')->name('user-delete-comment');
        Route::post('/report-comment', 'reportComment')->name('report-comment');
    });
});

// Admin Routes
Route::middleware(['auth:api', 'check.admin'])->prefix('admin')->group(function () {
    Route::controller(AdminController::class)->group(function () {
        Route::get('/get-admin-mediafiles', 'getMediaFiles')->name('admin-get-pending-media');
        Route::post('/update-media-status', 'updateMediaStatus')->name('admin-approve-media');
        Route::get('/get-reported-comments', 'getReportedComments')->name('admin-get-reports');
    });
});


