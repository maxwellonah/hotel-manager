<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\RoomAvailabilityResource;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RoomAvailabilityController extends Controller
{
    /**
     * Check room availability for the given date range.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'room_type_id' => 'nullable|exists:room_types,id',
            'ignore_booking_id' => 'nullable|exists:bookings,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        
        // Get all rooms of the specified type (or all rooms if no type specified)
        $query = Room::with('roomType');
        
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        
        // Get all rooms that don't have conflicting bookings
        $availableRooms = $query->get()->filter(function ($room) use ($checkIn, $checkOut, $request) {
            return $room->isAvailableForDates($checkIn, $checkOut, $request->ignore_booking_id);
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'total_available' => $availableRooms->count(),
                'rooms' => $availableRooms->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'room_type' => $room->roomType->name,
                        'floor' => $room->floor,
                        'capacity' => $room->capacity,
                        'price_per_night' => $room->roomType->price_per_night,
                        'image_url' => $room->image_path ? asset('storage/' . $room->image_path) : null,
                    ];
                })->values(),
            ]
        ]);
    }
    
    /**
     * Get room availability for the next 30 days.
     *
     * @param  int  $roomId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoomAvailability($roomId)
    {
        $room = Room::with(['activeBookings'])->findOrFail($roomId);
        
        $startDate = now();
        $endDate = now()->addDays(30);
        
        $availability = [];
        $currentDate = $startDate->copy();
        
        while ($currentDate->lte($endDate)) {
            $isAvailable = !$room->hasActiveBookings(
                $currentDate->format('Y-m-d'),
                $currentDate->copy()->addDay()->format('Y-m-d')
            );
            
            $availability[] = [
                'date' => $currentDate->format('Y-m-d'),
                'day' => $currentDate->format('D'),
                'is_available' => $isAvailable,
                'status' => $isAvailable ? 'Available' : 'Booked',
            ];
            
            $currentDate->addDay();
        }
        
        return response()->json([
            'success' => true,
            'data' => [
                'room' => [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'room_type' => $room->roomType->name,
                ],
                'availability' => $availability,
            ]
        ]);
    }
    
    /**
     * Admin endpoint to check room availability with more detailed information.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminCheckAvailability(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'room_type_id' => 'nullable|exists:room_types,id',
            'ignore_booking_id' => 'nullable|exists:bookings,id',
            'include_room_details' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $checkIn = Carbon::parse($request->check_in);
        $checkOut = Carbon::parse($request->check_out);
        
        // Get all rooms of the specified type (or all rooms if no type specified)
        $query = Room::with(['roomType', 'bookings' => function($q) use ($checkIn, $checkOut) {
            $q->where('status', '!=', 'cancelled')
              ->where(function($query) use ($checkIn, $checkOut) {
                  $query->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function($q) use ($checkIn, $checkOut) {
                            $q->where('check_in', '<', $checkIn)
                              ->where('check_out', '>', $checkOut);
                        });
              });
        }]);
        
        if ($request->filled('room_type_id')) {
            $query->where('room_type_id', $request->room_type_id);
        }
        
        // Get all rooms with their availability status
        $rooms = $query->get()->map(function($room) use ($checkIn, $checkOut, $request) {
            // Check if room is available for the given dates
            $isAvailable = $room->isAvailableForDates(
                $checkIn, 
                $checkOut, 
                $request->ignore_booking_id
            );
            
            // Get conflicting bookings if any
            $conflictingBookings = $room->bookings->map(function($booking) {
                return [
                    'id' => $booking->id,
                    'check_in' => $booking->check_in->format('Y-m-d H:i'),
                    'check_out' => $booking->check_out->format('Y-m-d H:i'),
                    'status' => $booking->status,
                    'guest_name' => $booking->user->name,
                    'is_early_checkin' => (bool)$booking->is_early_checkin,
                ];
            });
            
            $result = [
                'id' => $room->id,
                'room_number' => $room->room_number,
                'room_type' => $room->roomType->name,
                'floor' => $room->floor,
                'capacity' => $room->capacity,
                'status' => $room->status,
                'is_available' => $isAvailable,
                'conflicting_bookings' => $conflictingBookings->isNotEmpty() ? $conflictingBookings : null,
            ];
            
            // Include additional room details if requested
            if ($request->boolean('include_room_details')) {
                $result['room_details'] = [
                    'description' => $room->description,
                    'amenities' => $room->amenities,
                    'price_per_night' => $room->roomType->price_per_night,
                    'image_url' => $room->image_path ? asset('storage/' . $room->image_path) : null,
                ];
            }
            
            return $result;
        });
        
        return response()->json([
            'success' => true,
            'data' => [
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'total_available' => $rooms->where('is_available', true)->count(),
                'total_rooms' => $rooms->count(),
                'rooms' => $rooms,
            ]
        ]);
    }
    
    /**
     * Check room availability with filters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkRoomAvailability(Request $request)
    {
        \Log::info('checkRoomAvailability called', [
            'request_data' => $request->all(),
            'user_agent' => $request->header('User-Agent')
        ]);

        $validator = Validator::make($request->all(), [
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'room_type_id' => 'nullable|exists:room_types,id',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'beds' => 'nullable|integer|min:1',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
            'sort_by' => 'nullable|in:price_low_to_high,price_high_to_low,rating',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
            'ignore_booking_id' => 'nullable|exists:bookings,id',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $checkIn = Carbon::parse($request->check_in);
            $checkOut = Carbon::parse($request->check_out);
            
            \Log::info('Parsed dates', [
                'check_in' => $checkIn->toDateTimeString(),
                'check_out' => $checkOut->toDateTimeString(),
                'ignore_booking_id' => $request->ignore_booking_id
            ]);
            
            // Log the ignore_booking_id for debugging
            \Log::info('Checking room availability with ignore_booking_id', [
                'ignore_booking_id' => $request->ignore_booking_id,
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d')
            ]);
            
            // Get the ignored booking if provided
            $ignoredBooking = null;
            $ignoredRoomId = null;
            if ($request->filled('ignore_booking_id')) {
                $ignoredBooking = \App\Models\Booking::find($request->ignore_booking_id);
                if ($ignoredBooking) {
                    $ignoredRoomId = $ignoredBooking->room_id;
                }
            }
            
            // Get all room IDs that have conflicting bookings (excluding the ignored booking)
            $conflictingRoomIds = \App\Models\Booking::where('status', '!=', 'cancelled')
                ->where('check_in', '<', $checkOut->format('Y-m-d'))
                ->where('check_out', '>', $checkIn->format('Y-m-d'))
                ->when($ignoredRoomId, function($query) use ($ignoredRoomId) {
                    return $query->where('room_id', '!=', $ignoredRoomId);
                })
                ->pluck('room_id')
                ->unique()
                ->toArray();
            
            // Log the conflicting room IDs for debugging
            \Log::info('Conflicting room IDs', [
                'ignored_room_id' => $ignoredRoomId,
                'conflicting_room_ids' => $conflictingRoomIds,
                'check_in' => $checkIn->format('Y-m-d'),
                'check_out' => $checkOut->format('Y-m-d'),
                'all_bookings' => \App\Models\Booking::all()->map(function($booking) {
                    return [
                        'id' => $booking->id,
                        'room_id' => $booking->room_id,
                        'check_in' => $booking->check_in,
                        'check_out' => $booking->check_out,
                        'status' => $booking->status
                    ];
                })
            ]);
            
            // Start building the main query
            $query = Room::with('roomType')
                ->where('status', 'available')
                ->where(function($q) use ($conflictingRoomIds, $ignoredRoomId) {
                    // If we have an ignored booking, include its room
                    if ($ignoredRoomId) {
                        $q->where('id', $ignoredRoomId);
                    }
                    
                    // Also include rooms that don't have any conflicting bookings
                    if (!empty($conflictingRoomIds)) {
                        $q->orWhereNotIn('id', $conflictingRoomIds);
                    }
                });
                
            // Log the SQL query for debugging
            \Log::info('Availability query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
            
            // Apply filters
            if ($request->filled('room_type_id')) {
                $query->where('room_type_id', $request->room_type_id);
            }
            
            if ($request->filled('adults')) {
                $query->whereHas('roomType', function($q) use ($request) {
                    $q->where('capacity', '>=', $request->adults + ($request->children ?? 0));
                });
            }
            
            if ($request->filled('beds')) {
                $query->whereHas('roomType', function($q) use ($request) {
                    $q->where('beds', '>=', $request->beds);
                });
            }
            
            if ($request->filled('price_min')) {
                $query->whereHas('roomType', function($q) use ($request) {
                    $q->where('price_per_night', '>=', $request->price_min);
                });
            }
            
            if ($request->filled('price_max')) {
                $query->whereHas('roomType', function($q) use ($request) {
                    $q->where('price_per_night', '<=', $request->price_max);
                });
            }
            
            // Apply sorting
            $sortBy = $request->input('sort_by');
            if ($sortBy) {
                switch ($sortBy) {
                    case 'price_low_to_high':
                        $query->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                              ->orderBy('room_types.price_per_night')
                              ->select('rooms.*');
                        break;
                    case 'price_high_to_low':
                        $query->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                              ->orderBy('room_types.price_per_night', 'desc')
                              ->select('rooms.*');
                        break;
                    case 'rating':
                        $query->withAvg('reviews as avg_rating', 'rating')
                              ->orderBy('avg_rating', 'desc');
                        break;
                }
            } else {
                $query->latest();
            }
            
            // Pagination
            $perPage = $request->per_page ?? 10;
            $page = $request->page ?? 1;
            
            $rooms = $query->paginate($perPage, ['*'], 'page', $page);
            
            // Log the results for debugging
            \Log::info('Available rooms', [
                'total' => $rooms->total(),
                'room_ids' => $rooms->pluck('id')->toArray(),
                'room_numbers' => $rooms->pluck('room_number')->toArray()
            ]);
            
            // Format the response using the resource
            return RoomAvailabilityResource::collection($rooms)
                ->additional([
                    'meta' => [
                        'check_in' => $checkIn->format('Y-m-d'),
                        'check_out' => $checkOut->format('Y-m-d'),
                        'total_nights' => $checkIn->diffInDays($checkOut),
                        'total_available' => $rooms->total(),
                        'current_page' => $rooms->currentPage(),
                        'last_page' => $rooms->lastPage(),
                        'per_page' => $rooms->perPage(),
                    ]
                ]);
                
        } catch (\Exception $e) {
            \Log::error('Error in checkRoomAvailability', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while checking room availability.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
