<?php

use App\Http\Controllers\Api\Admin\AuthController;
use App\Http\Controllers\Api\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Api\Admin\MessageController;
use App\Http\Controllers\Api\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SiteContentController;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Route;

Route::get('/projects', [ProjectController::class, 'index']);
Route::get('/projects/{slug}', [ProjectController::class, 'show']);
Route::get('/gallery', [ProjectController::class, 'gallery']);
Route::get('/site-content', [SiteContentController::class, 'index']);
Route::get('/site-settings', fn () => response()->json(array_merge(
    SiteSetting::DEFAULTS, SiteSetting::pluck('value', 'key')->all(),
))->header('Cache-Control', 'no-store'));
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1');

Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::prefix('admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::apiResource('projects', AdminProjectController::class);
    Route::post('/gallery', [AdminGalleryController::class, 'store']);
    Route::delete('/gallery/{image}', [AdminGalleryController::class, 'destroy']);
    Route::get('/messages', [MessageController::class, 'index']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    Route::delete('/messages/{message}', [MessageController::class, 'destroy']);
});
