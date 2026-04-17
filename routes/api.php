<?php
use App\Http\Controllers\Api\V1\{AuthController, EventController};
use Illuminate\Support\Facades\Route;

// Health check
Route::get('v1/health', fn () => response()->json(['status' => 'ok', 'service' => 'sse-platform']));

Route::prefix('v1/auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

Route::prefix('v1')->middleware(['auth:api'])->group(function () {
    Route::get('auth/me', [AuthController::class, 'me']);

    // SSE Stream (long-lived connection)
    Route::get('events', [EventController::class, 'stream']);

    // REST endpoints
    Route::post('events/dispatch', [EventController::class, 'dispatch']);
    Route::get('events/recent', [EventController::class, 'recent']);
    Route::get('events/connections', [EventController::class, 'connections']);
});
