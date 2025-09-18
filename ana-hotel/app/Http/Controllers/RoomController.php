<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

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
}
