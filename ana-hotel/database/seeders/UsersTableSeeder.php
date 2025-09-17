<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1234567890',
            'address' => '123 Admin St',
            'city' => 'Admin City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => '12345',
            'email_verified_at' => now(),
        ]);

        // Create receptionist user
        User::create([
            'name' => 'Receptionist User',
            'email' => 'receptionist@example.com',
            'password' => Hash::make('password'),
            'role' => 'receptionist',
            'phone' => '+1234567891',
            'address' => '456 Front Desk St',
            'city' => 'Hotel City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => '12346',
            'email_verified_at' => now(),
        ]);

        // Create housekeeping user
        User::create([
            'name' => 'Housekeeping User',
            'email' => 'housekeeping@example.com',
            'password' => Hash::make('password'),
            'role' => 'housekeeping',
            'phone' => '+1234567892',
            'address' => '789 Service St',
            'city' => 'Hotel City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => '12347',
            'email_verified_at' => now(),
        ]);

        // Create guest user
        User::create([
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'password' => Hash::make('password'),
            'role' => 'guest',
            'phone' => '+1234567893',
            'address' => '321 Guest St',
            'city' => 'Visitor City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => '12348',
            'email_verified_at' => now(),
        ]);

        // Create additional guest users
        User::factory(10)->create([
            'role' => 'guest',
            'city' => 'Visitor City',
            'state' => 'State',
            'country' => 'Country',
            'postal_code' => '12349',
            'email_verified_at' => now(),
        ]);
    }
}
