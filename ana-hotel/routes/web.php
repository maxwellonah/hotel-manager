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
use App\Http\Controllers\Reception\GuestManagementController;
use App\Http\Controllers\HousekeepingTaskController;
use App\Http\Controllers\HousekeepingController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use Illuminate\Http\Request;

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
    return redirect()->route('login');
});

// Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// Profile Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/profile/bookings', [ProfileController::class, 'bookings'])->name('profile.bookings');
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');

    // User Management
    Route::resource('users', AdminUserController::class);

    // Room Management
    Route::resource('rooms', RoomController::class);
    
    // Room Type Management
    Route::post('/room-types/{roomType}/update-price', [\App\Http\Controllers\RoomController::class, 'updateRoomTypePrice'])
        ->name('room-types.update-price');
    Route::post('/room-types/{roomType}/update-name', [\App\Http\Controllers\RoomController::class, 'updateRoomTypeName'])
        ->name('room-types.update-name');
    
    // Clear All Rooms
    Route::post('/rooms/clear-all', [\App\Http\Controllers\RoomController::class, 'clearAllRooms'])
        ->name('rooms.clear-all');

    // Booking Management
    Route::resource('bookings', BookingController::class);

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports');
    Route::get('/reports/occupancy', [ReportController::class, 'occupancy'])->name('reports.occupancy');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
    Route::get('/reports/bookings', [ReportController::class, 'bookings'])->name('reports.bookings');
    Route::get('/reports/guests', [ReportController::class, 'guests'])->name('reports.guests');
});

// Room Routes (for non-admin)
Route::resource('rooms', RoomController::class)->middleware(['auth'])->except(['create', 'store', 'edit', 'update', 'destroy']);

// Guest Services
Route::get('/services', [\App\Http\Controllers\Guest\ServicesController::class, 'index'])
    ->name('guest.services.index');

// Booking Routes
Route::resource('bookings', BookingController::class)->middleware(['auth', 'role:admin,receptionist']);

// Early Check-in Route
Route::post('/bookings/{booking}/early-checkin', [BookingController::class, 'earlyCheckIn'])
    ->name('bookings.early-checkin')
    ->middleware(['auth', 'role:admin,receptionist']);

// Accept Payment Route
Route::post('/bookings/{booking}/accept-payment', [BookingController::class, 'acceptPayment'])
    ->name('bookings.accept-payment')
    ->middleware(['auth', 'role:admin,receptionist']);

// Admin: Create a pending payment for a booking
Route::post('/admin/bookings/{booking}/payments/pending', [BookingController::class, 'createPendingPayment'])
    ->name('admin.bookings.create-pending-payment')
    ->middleware(['auth', 'role:admin']);


// Housekeeping Routes
Route::prefix('housekeeping')->name('housekeeping.')->middleware(['auth', 'role:housekeeping,admin'])->group(function () {
    Route::get('/rooms', [HousekeepingController::class, 'rooms'])->name('rooms');
    Route::get('/dashboard', [HousekeepingController::class, 'dashboard'])->name('dashboard')->middleware('role:admin');
    Route::get('/dashboard/export', [HousekeepingController::class, 'exportTasks'])->name('dashboard.export')->middleware('role:admin');
    Route::get('/unassigned-rooms', [HousekeepingController::class, 'unassignedRooms'])->name('unassigned-rooms')->middleware('role:admin');
    Route::post('/tasks/{room}/{user}/complete', [HousekeepingController::class, 'completeTodayTask'])->name('tasks.complete-today')->middleware('role:admin');
});

// Housekeeping Task Routes
Route::resource('housekeeping-tasks', HousekeepingTaskController::class)->middleware(['auth']);
Route::post('/housekeeping-tasks/{housekeeping_task}/complete', [HousekeepingTaskController::class, 'complete'])->name('housekeeping-tasks.complete')->middleware(['auth']);
Route::post('/housekeeping-tasks/{housekeeping_task}/cancel', [HousekeepingTaskController::class, 'cancel'])->name('housekeeping-tasks.cancel')->middleware(['auth', 'role:admin']);

// Check-in and Check-out Routes
Route::prefix('check-in')->name('check-in.')->middleware(['auth'])->group(function () {
    Route::get('/', [CheckInController::class, 'index'])->name('index');
    Route::get('/{booking}/process', [CheckInController::class, 'process'])->name('process');
    Route::put('/{booking}/complete', [CheckInController::class, 'complete'])->name('complete');
    Route::put('/{booking}/cancel', [CheckInController::class, 'cancel'])->name('cancel');
    Route::get('/{booking}/extend', [CheckInController::class, 'showExtendStayForm'])->name('extend');
    Route::post('/{booking}/extend', [CheckInController::class, 'extendStay'])->name('extend.store');
});

Route::prefix('check-out')->name('check-out.')->middleware(['auth'])->group(function () {
    Route::get('/', [CheckOutController::class, 'index'])->name('index');
    Route::get('/{booking}/process', [CheckOutController::class, 'process'])->name('process');
    Route::put('/{booking}/complete', [CheckOutController::class, 'complete'])->name('complete');
});

// Receptionist Guest Management
Route::prefix('reception')->name('reception.')->middleware(['auth', 'role:admin,receptionist'])->group(function () {
    Route::get('/guests', [GuestManagementController::class, 'index'])->name('guests.index');
});

// Quick-create Guest (AJAX, returns JSON)
Route::post('/guests/quick-create', [UserController::class, 'quickCreateGuest'])
    ->name('guests.quick-create')
    ->middleware(['auth', 'role:admin,receptionist']);

// Auth Routes
require __DIR__.'/auth.php';
