<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Models\RoomType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->endOfMonth()->format('Y-m-d'));
        $stats = $this->getQuickStats($startDate, $endDate);
        return view('admin.reports.index', array_merge(compact('startDate', 'endDate'), $stats));
    }

    private function getQuickStats($startDate, $endDate)
    {
        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])->count();
        $totalRevenue = Booking::where('payment_status', 'paid')->whereBetween('payment_confirmed_at', [$startDate, $endDate])->sum('total_price');
        $totalRooms = Room::count();
        $totalGuests = User::where('role', 'guest')->count();

        $availableNights = $totalRooms * Carbon::parse($startDate)->diffInDays($endDate);
        $bookings = Booking::where(function($q) use ($startDate, $endDate) {
            $q->where('check_in', '<=', $endDate)->where('check_out', '>=', $startDate);
        })->get();

        $totalNights = 0;
        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in)->max($startDate);
            $checkOut = Carbon::parse($booking->check_out)->min($endDate);
            $totalNights += $checkIn->diffInDays($checkOut);
        }

        $occupancyRate = $availableNights > 0 ? ($totalNights / $availableNights) * 100 : 0;

        return [
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'totalRooms' => $totalRooms,
            'totalGuests' => $totalGuests,
            'occupancyRate' => round($occupancyRate, 2)
        ];
    }

    public function quickStats()
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        $stats = $this->getQuickStats($startDate, $endDate);
        return response()->json($stats);
    }

    public function occupancy(Request $request)
    {
        // ... (existing occupancy method is fine, leaving it as is)
    }
    
    public function revenue(Request $request)
    {
        $data = $this->getRevenueData($request);
        if ($request->ajax()) {
            return response()->json($data);
        }
        return view('admin.reports.revenue', $data);
    }

    public function revenueCsv(Request $request)
    {
        $data = $this->getRevenueData($request);

        $callback = function () use ($data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Period', 'Room Revenue', 'Other Revenue', 'Total Revenue', 'Bookings', 'ADR']);

            foreach ($data['tableData'] as $row) {
                fputcsv($file, [
                    $row['label'],
                    $row['room_revenue'],
                    $row['other_revenue'],
                    $row['revenue'],
                    $row['bookings'],
                    $row['adr'],
                ]);
            }
            fclose($file);
        };

        $filename = 'revenue_report_' . $data['startDate'] . '_to_' . $data['endDate'] . '_' . $data['groupBy'] . '.csv';

        return new StreamedResponse($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function getRevenueData(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:day,week,month,year',
            'room_type' => 'nullable|exists:room_types,id',
        ]);

        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : now()->subDays(29)->startOfDay();
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : now()->endOfDay();
        $groupBy = $request->input('group_by', 'day');

        $payments = Payment::with(['booking.room.roomType'])
            ->where('status', 'completed')
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->when($request->filled('room_type'), function ($q) use ($request) {
                $q->whereHas('booking.room', function ($qq) use ($request) {
                    $qq->where('room_type_id', $request->room_type);
                });
            })
            ->get()
            ->groupBy(function ($payment) use ($groupBy) {
                return $this->getPeriod($payment->paid_at, $groupBy);
            });

        $periods = [];
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $period = $this->getPeriod($currentDate, $groupBy);
            $periods[$period] = [
                'date' => $currentDate->format('Y-m-d'),
                'label' => $this->getPeriodLabel($currentDate, $groupBy),
                'revenue' => 0,
                'room_revenue' => 0,
                'other_revenue' => 0,
                'bookings' => 0,
                'adr' => 0,
            ];

            switch ($groupBy) {
                case 'day': $currentDate->addDay(); break;
                case 'week': $currentDate->addWeek(); break;
                case 'month': $currentDate->addMonth(); break;
                case 'year': $currentDate->addYear(); break;
            }
        }

        foreach ($payments as $period => $periodPayments) {
            if (!isset($periods[$period])) continue;
            
            $periodData = &$periods[$period];
            foreach ($periodPayments as $payment) {
                $periodData['revenue'] += $payment->amount;
                if ($payment->type === 'room') {
                    $periodData['room_revenue'] += $payment->amount;
                } else {
                    $periodData['other_revenue'] += $payment->amount;
                }
            }
            $periodData['bookings'] = $periodPayments->pluck('booking_id')->unique()->count();
            $periodData['adr'] = $periodData['bookings'] > 0 ? $periodData['room_revenue'] / $periodData['bookings'] : 0;
        }

        $totalRevenue = array_sum(array_column($periods, 'revenue'));
        $totalRoomRevenue = array_sum(array_column($periods, 'room_revenue'));
        $totalOtherRevenue = array_sum(array_column($periods, 'other_revenue'));
        $totalBookings = array_sum(array_column($periods, 'bookings'));

        $paymentMethods = Payment::select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
            ->where('status', 'completed')->whereBetween('paid_at', [$startDate, $endDate])->groupBy('payment_method')->get()
            ->mapWithKeys(function ($item) use ($totalRevenue) {
                return [$item->payment_method => [
                    'amount' => $item->total_amount,
                    'percentage' => $totalRevenue > 0 ? ($item->total_amount / $totalRevenue) * 100 : 0,
                    'count' => $item->count
                ]];
            });

        $roomTypeRevenue = Booking::select('room_types.name', DB::raw('SUM(bookings.total_price) as amount'), DB::raw('COUNT(bookings.id) as bookings'))
            ->join('payments', 'bookings.id', '=', 'payments.booking_id')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
            ->where('payments.status', 'completed')->whereBetween('payments.paid_at', [$startDate, $endDate])
            ->when($request->filled('room_type'), function ($q) use ($request) {
                $q->where('rooms.room_type_id', $request->room_type);
            })
            ->groupBy('room_types.name')->get()
            ->mapWithKeys(function ($item) use ($totalRevenue) {
                return [$item->name => [
                    'amount' => $item->amount,
                    'bookings' => $item->bookings,
                    'percentage' => $totalRevenue > 0 ? ($item->amount / $totalRevenue) * 100 : 0
                ]];
            });
            
        return [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'groupBy' => $groupBy,
            'selectedRoomType' => $request->room_type,
            'totalRevenue' => $totalRevenue,
            'totalRoomRevenue' => $totalRoomRevenue,
            'totalOtherRevenue' => $totalOtherRevenue,
            'totalBookings' => $totalBookings,
            'averageAdr' => $totalBookings > 0 ? $totalRoomRevenue / $totalBookings : 0,
            'averageRate' => $totalBookings > 0 ? $totalRevenue / $totalBookings : 0,
            'roomTypes' => RoomType::orderBy('name')->get(),
            'chartLabels' => array_column($periods, 'label'),
            'chartData' => ['Room Revenue' => array_column($periods, 'room_revenue'), 'Other Revenue' => array_column($periods, 'other_revenue')],
            'tableData' => array_values($periods),
            'paymentMethods' => $paymentMethods,
            'roomTypeRevenue' => $roomTypeRevenue,
        ];
    }
    
    // ... (keep other existing helper methods like getPeriod, getPeriodLabel, etc.)
    protected function getPeriod($date, $groupBy)
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        switch ($groupBy) {
            case 'day': return $date->format('Y-m-d');
            case 'week': return $date->startOfWeek()->format('Y-m-d');
            case 'month': return $date->startOfMonth()->format('Y-m');
            case 'year': return $date->startOfYear()->format('Y');
            default: return $date->format('Y-m-d');
        }
    }

    protected function getPeriodLabel($date, $groupBy)
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        switch ($groupBy) {
            case 'day': return $date->format('M d, Y');
            case 'week': return 'Week of ' . $date->startOfWeek()->format('M d, Y');
            case 'month': return $date->format('M Y');
            case 'year': return $date->format('Y');
            default: return $date->format('M d, Y');
        }
    }
}
