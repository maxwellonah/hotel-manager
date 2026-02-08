<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Quickly create a new guest (AJAX) for bookings flow.
     * Only accessible to admin and receptionist (route protected).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickCreateGuest(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'identification_type' => ['required', 'string', 'in:passport,national_id,driving_license'],
            'identification_number' => ['required', 'string', 'max:50'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'identification_type' => $validated['identification_type'],
            'identification_number' => $validated['identification_number'],
            // Generate a random password; guest can reset later
            'password' => \Illuminate\Support\Str::random(32),
            'role' => 'guest',
        ]);

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'identification_type' => $user->identification_type,
            'identification_number' => $user->identification_number,
        ], 201);
    }
}
