<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoomAvailabilityController;

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

// Public routes
Route::prefix('v1')->group(function () {
    // Room availability endpoints
    Route::get('/rooms/availability', [RoomAvailabilityController::class, 'checkAvailability']);
    Route::get('/rooms/{room}/availability', [RoomAvailabilityController::class, 'getRoomAvailability']);
    
    // Check room availability with filters
    Route::post('/check-availability', [RoomAvailabilityController::class, 'checkRoomAvailability']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    // Protected room availability endpoints (for admin use)
    Route::post('/admin/rooms/check-availability', [RoomAvailabilityController::class, 'adminCheckAvailability']);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Room Availability Routes
Route::prefix('rooms')->group(function () {
    // Check room availability for a date range
    Route::get('/availability', [RoomAvailabilityController::class, 'checkAvailability']);
    
    // Get availability for a specific room for the next 30 days
    Route::get('/{room}/availability', [RoomAvailabilityController::class, 'getRoomAvailability']);
});

// Guest search endpoint
Route::get('/guests/search', function (Request $request) {
    try {
        $search = $request->input('search');
        
        \Log::info('Guest search initiated', ['search' => $search]);
        
        if (empty($search)) {
            return response()->json([]);
        }
        
        $guests = \App\Models\User::where('role', 'guest')
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
            })
            ->select('id', 'name', 'email', 'phone')
            ->limit(10)
            ->get();
        
        \Log::info('Guest search results', ['count' => $guests->count()]);
        
        return response()->json($guests);
    } catch (\Exception $e) {
        \Log::error('Guest search error', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error' => 'An error occurred while searching for guests',
            'details' => config('app.debug') ? $e->getMessage() : null
        ], 500);
    }
})->name('api.guests.search');

// Get single guest endpoint
Route::get('/guests/{id}', function ($id) {
    $guest = \App\Models\User::where('role', 'guest')
        ->select('id', 'name', 'email', 'phone')
        ->findOrFail($id);
    
    return response()->json($guest);
})->where('id', '\d+');
