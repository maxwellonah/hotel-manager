<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoomAvailabilityResource extends JsonResource
{
    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'room';
    
    /**
     * The check-in date.
     *
     * @var string|null
     */
    protected $checkIn;
    
    /**
     * The check-out date.
     *
     * @var string|null
     */
    protected $checkOut;
    
    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @param  string|null  $checkIn
     * @param  string|null  $checkOut
     * @return void
     */
    public function __construct($resource, $checkIn = null, $checkOut = null)
    {
        parent::__construct($resource);
        $this->checkIn = $checkIn;
        $this->checkOut = $checkOut;
    }
    
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $roomType = $this->roomType;
        $totalNights = $this->checkIn && $this->checkOut 
            ? \Carbon\Carbon::parse($this->checkIn)->diffInDays($this->checkOut)
            : 0;
        
        return [
            'id' => $this->id,
            'room_number' => $this->room_number,
            'floor' => $this->floor,
            'status' => $this->status,
            'description' => $this->description,
            'amenities' => $this->amenities ?? [],
            'image_url' => $this->image_path ? asset('storage/' . $this->image_path) : null,
            'room_type' => [
                'id' => $roomType->id,
                'name' => $roomType->name,
                'description' => $roomType->description,
                'capacity' => $roomType->capacity,
                'beds' => $roomType->beds,
                'price_per_night' => (float) $roomType->price_per_night,
                'size' => $roomType->size,
                'max_occupancy' => $roomType->max_occupancy,
            ],
            'availability' => [
                'is_available' => $this->when(isset($this->is_available), $this->is_available),
                'check_in' => $this->checkIn,
                'check_out' => $this->checkOut,
                'total_nights' => $totalNights,
                'total_price' => $totalNights > 0 ? $totalNights * $roomType->price_per_night : 0,
            ],
            'links' => [
                // Remove the self link since we don't have an API endpoint for showing a single room
                'book' => url('/bookings/create?room_id=' . $this->id),
            ],
        ];
    }
    
    /**
     * Customize the response for a request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Http\JsonResponse  $response
     * @return void
     */
    public function withResponse($request, $response)
    {
        $response->header('Cache-Control', 'public, max-age=300');
    }
}
