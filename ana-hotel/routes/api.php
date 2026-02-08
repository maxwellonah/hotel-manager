<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomAvailabilityController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    // Public routes
    Route::get('/rooms/availability', [RoomAvailabilityController::class, 'checkAvailability']);
    Route::get('/rooms/{room}/availability', [RoomAvailabilityController::class, 'getRoomAvailability']);
    Route::post('/check-availability', [RoomAvailabilityController::class, 'checkRoomAvailability']);

    // Guest search
    Route::get('/guests/search', function (Request $request) {
        // ... (existing implementation)
    })->name('api.guests.search');

    Route::get('/guests/{id}', function ($id) {
        // ... (existing implementation)
    })->where('id', '\d+');

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/admin/rooms/check-availability', [RoomAvailabilityController::class, 'adminCheckAvailability']);

        Route::get('/user', function (Request $request) {
            return $request->user();
        });

        // Reports API
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/quick-stats', [ReportController::class, 'quickStats'])->name('quick-stats');
            Route::get('/occupancy-data', [ReportController::class, 'occupancy'])->name('occupancy-data');
            Route::get('/revenue-data', [ReportController::class, 'revenue'])->name('revenue-data');
            Route::get('/bookings-data', [ReportController::class, 'bookings'])->name('bookings-data');
            Route::get('/guests-data', [ReportController::class, 'guests'])->name('guests-data');
        });
    });
});
