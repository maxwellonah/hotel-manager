<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get booking 30
$booking = \App\Models\Booking::with(['user', 'room', 'payments'])->find(30);

if (!$booking) {
    echo "Booking 30 not found!\n";
    exit;
}

echo "=== BOOKING 30 DEBUG INFO ===\n";
echo "ID: " . $booking->id . "\n";
echo "Status: " . $booking->status . "\n";
echo "Check-in Date: " . $booking->check_in . "\n";
echo "Check-out Date: " . $booking->check_out . "\n";
echo "Room ID: " . $booking->room_id . "\n";
echo "User ID: " . $booking->user_id . "\n";

echo "\n=== USER INFO ===\n";
echo "Name: " . $booking->user->name . "\n";
echo "Email: " . $booking->user->email . "\n";
echo "Phone: " . ($booking->user->phone ?? 'N/A') . "\n";
echo "ID Type: " . ($booking->user->identification_type ?? 'NULL') . "\n";
echo "ID Number: " . ($booking->user->identification_number ?? 'NULL') . "\n";

echo "\n=== ROOM INFO ===\n";
echo "Room Number: " . $booking->room->room_number . "\n";
echo "Room Type: " . $booking->room->roomType->name . "\n";
echo "Room Status: " . $booking->room->status . "\n";

echo "\n=== PAYMENTS ===\n";
foreach ($booking->payments as $payment) {
    echo "Payment ID: " . $payment->id . ", Status: " . $payment->status . ", Amount: $" . $payment->amount . "\n";
}

echo "\n=== CHECK-IN ELIGIBILITY ===\n";
$canCheckIn = $booking->status === 'confirmed';
echo "Can check in (status confirmed): " . ($canCheckIn ? 'YES' : 'NO') . "\n";

if (!$canCheckIn) {
    echo "Current status '" . $booking->status . "' prevents check-in. Must be 'confirmed'.\n";
}

$requireId = empty($booking->user->identification_type) || empty($booking->user->identification_number);
echo "ID required: " . ($requireId ? 'YES' : 'NO') . "\n";

echo "\n=== DEBUG COMPLETE ===\n";
