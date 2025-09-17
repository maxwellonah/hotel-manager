<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\CheckOutController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Profile Routes
Route::middleware(['auth'])->group(function () {
    // Show profile
    Route::get('/profile', [ProfileController::class, 'show'])
        ->name('profile.show');
        
    // Edit profile
    Route::get('/profile/edit', [ProfileController::class, 'edit'])
        ->name('profile.edit');
        
    // Update profile
    Route::put('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
        
    // Update password
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password.update');
        
    // User bookings
    Route::get('/profile/bookings', [ProfileController::class, 'bookings'])
        ->name('profile.bookings');
});

// Admin Dashboard
Route::get('/admin', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])
    ->middleware(['auth', 'role:admin'])
    ->name('admin.dashboard');

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    // User Management
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    
    // Room Management
    Route::resource('rooms', RoomController::class)->names('rooms');
    
    // Booking Management
    Route::resource('bookings', BookingController::class)->names('bookings');
    
    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
    Route::get('/reports/guests', [ReportController::class, 'guests'])->name('reports.guests');
});

// Room Routes (for non-admin)
Route::resource('rooms', RoomController::class)
    ->middleware(['auth']);

// Guest Services
Route::get('/services', [\App\Http\Controllers\Guest\ServicesController::class, 'index'])
    ->name('guest.services.index');

// Booking Routes
Route::resource('bookings', BookingController::class)
    ->middleware(['auth', 'role:admin,receptionist']);

// Early Check-in Route
Route::post('/bookings/{booking}/early-checkin', [BookingController::class, 'earlyCheckIn'])
    ->name('bookings.early-checkin')
    ->middleware(['auth', 'role:admin,receptionist']);

// Check-in and Check-out Routes
Route::prefix('check-in')->name('check-in.')->middleware(['auth'])->group(function () {
    Route::get('/', [CheckInController::class, 'index'])->name('index');
    Route::get('/{booking}/process', [CheckInController::class, 'process'])->name('process');
    Route::put('/{booking}/complete', [CheckInController::class, 'complete'])->name('complete');
    Route::put('/{booking}/cancel', [CheckInController::class, 'cancel'])->name('cancel');
    
    // Extend stay routes
    Route::get('/{booking}/extend', [CheckInController::class, 'showExtendStayForm'])->name('extend');
    Route::post('/{booking}/extend', [CheckInController::class, 'extendStay'])->name('extend.store');
});

// Report Routes
Route::prefix('reports')->name('reports.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [ReportController::class, 'index'])->name('index');
    Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
    Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
    Route::get('/bookings', [ReportController::class, 'bookings'])->name('bookings');
    Route::get('/guests', [ReportController::class, 'guests'])->name('guests');
    
    // API endpoints for AJAX requests
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/quick-stats', [ReportController::class, 'quickStats'])->name('quick-stats');
        Route::get('/occupancy-data', [ReportController::class, 'occupancy'])->name('occupancy-data');
        Route::get('/revenue-data', [ReportController::class, 'revenue'])->name('revenue-data');
        Route::get('/bookings-data', [ReportController::class, 'bookings'])->name('bookings-data');
        Route::get('/guests-data', [ReportController::class, 'guests'])->name('guests-data');
    });
});

Route::prefix('check-out')->name('check-out.')->middleware(['auth'])->group(function () {
    Route::get('/', [CheckOutController::class, 'index'])->name('index');
    Route::get('/{booking}/process', [CheckOutController::class, 'process'])->name('process');
    Route::put('/{booking}/complete', [CheckOutController::class, 'complete'])->name('complete');
});

// Auth Routes (from auth.php)
require __DIR__.'/auth.php';
