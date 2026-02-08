<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;

class RoomAvailabilityController extends Controller
{
    public function checkRoomAvailability(Request $request)
    {
        $validated = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'room_type_id' => ['nullable', 'exists:room_types,id'],
            'ignore_booking_id' => ['nullable', 'exists:bookings,id'],
        ]);

        $query = Room::query()->where('status', 'available');

        if ($request->room_type_id) {
            $query->where('room_type_id', $request->room_type_id);
        }

        $query->whereDoesntHave('bookings', function ($q) use ($request) {
            $q->where(function ($q2) use ($request) {
                $q2->where('check_in', '<', $request->check_out)
                   ->where('check_out', '>', $request->check_in);
            });

            if ($request->ignore_booking_id) {
                $q->where('id', '!=', $request->ignore_booking_id);
            }
        });

        $rooms = $query->get();

        return response()->json([
            'data' => $rooms,
            'meta' => [
                'check_in' => $request->check_in,
                'check_out' => $request->check_out,
                'total_available' => $rooms->count(),
            ],
        ]);
    }
}
