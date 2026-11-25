<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

// REST API VSM publik (read-only) untuk integrasi dengan website lain.
Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::get('/ping', fn () => response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]));
    Route::get('/projects', [\App\Http\Controllers\Api\ProjectApiController::class, 'index']);
    Route::get('/projects/{code}', [\App\Http\Controllers\Api\ProjectApiController::class, 'show'])->where('code', '.*');
});

// Ringkasan VSM publik, serupa endpoint /api/qc/summary pada sistem QC.
Route::get('/vsm/summary', [\App\Http\Controllers\Api\VsmApiController::class, 'summary']);

// Endpoint VSM publik; tetap dibatasi oleh rate limit API.
Route::prefix('vsm')->middleware('throttle:api')->group(function () {
    Route::get('/projects', [\App\Http\Controllers\Api\VsmApiController::class, 'projects']);
    Route::get('/projects/{code}', [\App\Http\Controllers\Api\VsmApiController::class, 'project'])->where('code', '.*');
    Route::get('/material', [\App\Http\Controllers\Api\VsmApiController::class, 'material']);
});