<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Models\Booking;
use App\Models\Service;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $rooms = Room::with('roomType')->paginate(10);
        return view('admin.rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new room.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $roomTypes = RoomType::all();
        return view('admin.rooms.create', compact('roomTypes'));
    }

    /**
     * Store a newly created room in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:10|unique:rooms',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('room_images', 'public');
            $validated['image_path'] = $path;
        }

        Room::create($validated);

        return redirect()->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    /**
     * Display the specified room.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\View\View
     */
    public function show(Room $room)
    {
        return view('admin.rooms.show', compact('room'));
    }

    /**
     * Show the form for editing the specified room.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\View\View
     */
    public function edit(Room $room)
    {
        $roomTypes = RoomType::all();
        $housekeepers = User::where('role', 'housekeeping')->orderBy('name')->get(['id','name']);
        return view('admin.rooms.edit', compact('room', 'roomTypes', 'housekeepers'));
    }

    /**
     * Update the specified room in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'room_number' => 'required|string|max:10|unique:rooms,room_number,' . $room->id,
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,maintenance,cleaning',
            'image' => 'nullable|image|max:2048',
            'housekeeping_user_id' => 'nullable|exists:users,id',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($room->image_path) {
                Storage::disk('public')->delete($room->image_path);
            }
            $path = $request->file('image')->store('room_images', 'public');
            $validated['image_path'] = $path;
        }

        $room->update($validated);

        return redirect()->route('rooms.index')
            ->with('success', 'Room updated successfully');
    }

    /**
     * Remove the specified room from storage.
     *
     * @param  \App\Models\Room  $room
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Room $room)
    {
        if ($room->image_path) {
            Storage::disk('public')->delete($room->image_path);
        }
        
        $room->delete();
        
        return redirect()->route('rooms.index')
            ->with('success', 'Room deleted successfully');
    }

    /**
     * Update room type price.
     *
     * @param  \App\Models\RoomType  $roomType
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRoomTypePrice(Request $request, RoomType $roomType): JsonResponse
    {
        $validated = $request->validate([
            'price' => 'required|numeric|min:0|max:99999.99',
        ]);

        $roomType->update([
            'price_per_night' => $validated['price'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room type price updated successfully.',
            'price' => $roomType->price_per_night,
        ]);
    }

    /**
     * Update room type name.
     *
     * @param  \App\Models\RoomType  $roomType
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateRoomTypeName(Request $request, RoomType $roomType): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:room_types,name,' . $roomType->id,
        ]);

        $roomType->update([
            'name' => $validated['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Room type name updated successfully.',
            'name' => $roomType->name,
        ]);
    }

    /**
     * Clear all rooms from database.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearAllRooms(): \Illuminate\Http\RedirectResponse
    {
        try {
            DB::transaction(function () {
                // Delete all room images from storage
                $rooms = Room::all();
                foreach ($rooms as $room) {
                    if ($room->image_path) {
                        Storage::disk('public')->delete($room->image_path);
                    }
                }
                
                // Disable foreign key checks and delete all related data
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                
                // Delete in correct order:
                // 1. booking_services (pivot table)
                // 2. bookings (references rooms)
                // 3. rooms 
                // 4. services (referenced by booking_services)
                DB::table('booking_services')->truncate();
                Booking::truncate();
                Room::truncate();
                Service::truncate();
                
                // Re-enable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            });
            
            return redirect()->route('admin.rooms.index')
                ->with('success', 'All rooms, bookings, and related services have been cleared from the database.');
                
        } catch (\Exception $e) {
            return redirect()->route('admin.rooms.index')
                ->with('error', 'Failed to clear rooms: ' . $e->getMessage());
        }
    }
}
