<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function createBooking(array $validatedData): Booking
    {
        $isGuestBooking = isset($validatedData['is_guest_booking']) && $validatedData['is_guest_booking'] == '1';
        if (!$isGuestBooking) {
            $validatedData['user_id'] = auth()->id();
        }

        $isEarlyCheckin = !empty($validatedData['is_early_checkin']);
        $checkInDate = $isEarlyCheckin ? now() : $validatedData['check_in'];

        return DB::transaction(function () use ($validatedData, $checkInDate, $isEarlyCheckin, $isGuestBooking) {
            $room = $this->resolveRoomForBooking(
                $validatedData['room_type_id'],
                $validatedData['room_id'] ?? null,
                $checkInDate,
                $validatedData['check_out']
            );

            $totalPrice = $this->calculateTotalPrice($validatedData['room_type_id'], $validatedData['check_in'], $validatedData['check_out']);

            $bookingData = $this->prepareBookingData($validatedData, $room->id, $totalPrice, $isEarlyCheckin);

            $booking = Booking::create($bookingData);

            // Debug logging to track booking creation
            \Log::info('Booking created', [
                'booking_id' => $booking->id,
                'room_id' => $room->id,
                'room_number' => $room->room_number,
                'room_type_id' => $validatedData['room_type_id'],
                'room_type_name' => $room->roomType->name,
                'user_id' => $validatedData['user_id'],
                'check_in' => $validatedData['check_in'],
                'check_out' => $validatedData['check_out'],
                'total_price' => $totalPrice,
                'is_guest_booking' => $isGuestBooking,
            ]);

            $this->updateRoomStatus($booking);

            // Update guest identification details if provided
            if ($isGuestBooking && isset($validatedData['identification_type']) && isset($validatedData['identification_number'])) {
                $this->updateGuestIdentification($validatedData['user_id'], $validatedData['identification_type'], $validatedData['identification_number']);
            }

            return $booking;
        });
    }

    public function updateBooking(Booking $booking, array $validatedData): Booking
    {
        $isEarlyCheckin = !empty($validatedData['is_early_checkin']);
        $checkInDate = $isEarlyCheckin ? now() : $validatedData['check_in'];

        if ($this->isBookingDetailsChanged($booking, $validatedData)) {
            if (!$this->isRoomAvailable($validatedData['room_id'], $checkInDate, $validatedData['check_out'], $booking->id)) {
                throw new \Exception('The selected room is not available for the selected dates.');
            }
            $validatedData['total_price'] = $this->calculateTotalPrice($booking->room->room_type_id, $validatedData['check_in'], $validatedData['check_out']);
        }

        if ($isEarlyCheckin && $booking->status === 'confirmed') {
            $validatedData['status'] = 'checked_in';
            $validatedData['checked_in_at'] = now();
        }

        DB::transaction(function () use ($booking, $validatedData) {
            $booking->update($validatedData);
            $this->updateRoomStatus($booking);
        });

        return $booking;
    }
    
    public function processEarlyCheckIn(Booking $booking): Booking
    {       
        if ($booking->status !== 'confirmed') {
            throw new \Exception('Only confirmed bookings can be checked in early.');
        }
        
        DB::transaction(function() use ($booking) {
            $booking->update([
                'status' => 'checked_in',
                'checked_in_at' => now(),
                'is_early_checkin' => true,
            ]);
    
            if ($booking->room) {
                $booking->room->update(['status' => 'occupied']);
            }
        });

        return $booking;
    }

    private function findAvailableRoom(int $roomTypeId, $checkIn, $checkOut): Room
    {
        $room = Room::where('room_type_id', $roomTypeId)
            ->bookableForDates($checkIn)
            ->whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
                $query->where('status', '!=', 'cancelled')
                    ->where(function ($q) use ($checkIn, $checkOut) {
                        $q->whereBetween('check_in', [$checkIn, $checkOut])
                            ->orWhereBetween('check_out', [$checkIn, $checkOut])
                            ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                $q->where('check_in', '<', $checkIn)
                                    ->where('check_out', '>', $checkIn);
                            });
                    });
            })
            ->lockForUpdate()
            ->first();

        if (!$room) {
            throw new \Exception('No rooms of this type are available for the selected dates.');
        }

        // Debug logging to track room selection
        \Log::info('Room selected for booking', [
            'requested_room_type_id' => $roomTypeId,
            'selected_room_id' => $room->id,
            'selected_room_number' => $room->room_number,
            'room_type_name' => $room->roomType->name,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
        ]);

        return $room;
    }

    private function resolveRoomForBooking(int $roomTypeId, ?int $selectedRoomId, $checkIn, $checkOut): Room
    {
        if (!$selectedRoomId) {
            return $this->findAvailableRoom($roomTypeId, $checkIn, $checkOut);
        }

        $room = Room::where('id', $selectedRoomId)
            ->where('room_type_id', $roomTypeId)
            ->first();

        if (!$room) {
            throw new \Exception('The selected room does not belong to the chosen room type.');
        }

        $roomIsBookableForDates = Room::query()
            ->whereKey($room->id)
            ->bookableForDates($checkIn)
            ->exists();

        if (!$roomIsBookableForDates || $room->hasActiveBookings($checkIn, $checkOut)) {
            throw new \Exception('The selected room is not available for the selected dates.');
        }

        return $room;
    }

    private function calculateTotalPrice(int $roomTypeId, string $checkIn, string $checkOut): float
    {
        $roomType = \App\Models\RoomType::findOrFail($roomTypeId);
        $nights = (new Carbon($checkIn))->diffInDays(new Carbon($checkOut));
        return $roomType->price_per_night * $nights;
    }

    private function prepareBookingData(array $validatedData, int $roomId, float $totalPrice, bool $isEarlyCheckin): array
    {
        $bookingData = [
            'room_id' => $roomId,
            'user_id' => $validatedData['user_id'],
            'check_in' => $validatedData['check_in'],
            'check_out' => $validatedData['check_out'],
            'adults' => $validatedData['adults'],
            'children' => $validatedData['children'] ?? 0,
            'status' => $isEarlyCheckin ? 'checked_in' : 'confirmed',
            'payment_status' => 'pending',
            'special_requests' => $validatedData['special_requests'] ?? null,
            'total_price' => $totalPrice,
            'is_early_checkin' => $isEarlyCheckin,
        ];

        if ($isEarlyCheckin) {
            $bookingData['checked_in_at'] = now();
        }

        return $bookingData;
    }

    private function isBookingDetailsChanged(Booking $booking, array $validatedData): bool
    {
        return $booking->room_id != $validatedData['room_id'] ||
            $booking->check_in != $validatedData['check_in'] ||
            $booking->check_out != $validatedData['check_out'];
    }

    private function isRoomAvailable(int $roomId, $checkIn, $checkOut, int $bookingId): bool
    {
        return !Booking::where('room_id', $roomId)
            ->where('id', '!=', $bookingId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<', $checkIn)
                            ->where('check_out', '>', $checkOut);
                    });
            })
            ->exists();
    }

    private function updateRoomStatus(Booking $booking): void
    {
        $room = $booking->room;

        if ($booking->status === 'checked_in' && $room->status !== 'occupied') {
            $room->update(['status' => 'occupied']);
            return;
        }

        if (in_array($booking->status, ['confirmed', 'pending', 'cancelled', 'checked_out']) && $room->status === 'occupied') {
            if (!Booking::where('room_id', $room->id)->where('status', 'checked_in')->where('id', '!=', $booking->id)->exists()) {
                $room->update(['status' => 'available']);
            }
        }
    }

    private function updateGuestIdentification(int $userId, string $identificationType, string $identificationNumber): void
    {
        if ($identificationType === 'national_id') {
            $identificationType = 'id_card';
        }

        $guest = User::where('id', $userId)->where('role', 'guest')->first();
        
        if ($guest) {
            $guest->update([
                'identification_type' => $identificationType,
                'identification_number' => $identificationNumber,
            ]);
        }
    }
}
