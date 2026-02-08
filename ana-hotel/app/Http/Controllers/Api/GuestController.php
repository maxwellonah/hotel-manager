<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GuestController extends Controller
{
    /**
     * Search for guests by name or email
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request): JsonResponse
    {
        $search = $request->get('search');
        
        if (empty($search) || strlen($search) < 2) {
            return response()->json([]);
        }

        $guests = User::where('role', 'guest')
            ->where(function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%")
                      ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'identification_type', 'identification_number']);

        return response()->json($guests);
    }

    /**
     * Get a specific guest by ID
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $guest = User::where('role', 'guest')
            ->where('id', $id)
            ->first(['id', 'name', 'email', 'phone', 'identification_type', 'identification_number']);

        if (!$guest) {
            return response()->json(['error' => 'Guest not found'], 404);
        }

        return response()->json($guest);
    }
}
