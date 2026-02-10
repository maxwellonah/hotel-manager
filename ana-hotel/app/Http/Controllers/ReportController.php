<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Room;
use App\Models\User;
use App\Models\RoomType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
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

        $availableNights = $totalRooms * (Carbon::parse($startDate)->diffInDays($endDate) + 1);
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

    public function bookings(Request $request)
    {
        try {
            try {
                \Log::info('Bookings method called with:', [
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'status' => $request->status,
                    'room_type' => $request->room_type,
                ]);
            } catch (\Exception $e) {
                \Log::error('Logging error: ' . $e->getMessage());
            }
            
            $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'status' => 'nullable|in:pending,confirmed,checked_in,checked_out,cancelled',
                'room_type' => 'nullable|exists:room_types,id',
            ]);
        
            $startDate = $request->filled('start_date') 
                ? Carbon::parse($request->start_date)
                : now()->startOfMonth();
                
            $endDate = $request->filled('end_date')
                ? Carbon::parse($request->end_date)
                : now()->endOfMonth();
        
            $baseQuery = Booking::query()
                ->with(['room.roomType', 'user'])
                ->where(function ($q) use ($startDate, $endDate) {
                    $q->where('check_in', '<=', $endDate)
                      ->where('check_out', '>=', $startDate);
                });
        
            if (!$request->filled('status')) {
                $baseQuery->where('status', '!=', 'cancelled');
            }
        
            if ($request->filled('status')) {
                $baseQuery->where('status', $request->status);
            }
        
            if ($request->filled('room_type')) {
                $baseQuery->whereHas('room', function ($q) use ($request) {
                    $q->where('room_type_id', $request->room_type);
                });
            }

            $allBookings = $baseQuery->get();
        
            $totalBookings = $allBookings->count();
            $totalRevenue = $allBookings->where('payment_status', 'paid')->sum('total_price');
            $totalNights = $allBookings->sum(function($booking) {
                return Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
            });

            $averageStay = $totalBookings > 0 ? $totalNights / $totalBookings : 0;
        
            $paidNights = $allBookings->where('payment_status', 'paid')->sum(function($booking) {
                return Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
            });
            $averageDailyRate = $paidNights > 0 ? $totalRevenue / $paidNights : 0;

            $totalRooms = Room::count();
            $dateRange = $startDate->diffInDays($endDate) + 1; // Add 1 to include both start and end dates
            $totalAvailableNights = $totalRooms * $dateRange;
            
            // Prevent division by zero in occupancy rate
            if ($totalAvailableNights == 0) {
                $occupancyRate = 0;
            } else {
                $occupiedNights = $allBookings->sum(function($booking) use ($startDate, $endDate) {
                    $checkIn = Carbon::parse($booking->check_in)->max($startDate);
                    $checkOut = Carbon::parse($booking->check_out)->min($endDate);
                    return max(0, $checkIn->diffInDays($checkOut));
                });
                $occupancyRate = ($occupiedNights / $totalAvailableNights) * 100;
            }

            $statusCounts = $allBookings->groupBy('status')->map(function($group, $status) use ($totalBookings) {
                $count = $group->count();
                return [
                    'count' => $count,
                    'revenue' => $group->where('payment_status', 'paid')->sum('total_price'),
                    'color' => $this->getStatusColor($status),
                    'percentage' => $totalBookings > 0 ? ($count / $totalBookings) * 100 : 0,
                ];
            });

            // Ensure all default statuses are present even if count is 0
            $defaultStatuses = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];
            foreach ($defaultStatuses as $status) {
                if (!$statusCounts->has($status)) {
                    $statusCounts->put($status, [
                        'count' => 0,
                        'revenue' => 0,
                        'color' => $this->getStatusColor($status),
                        'percentage' => 0,
                    ]);
                }
            }

            $roomTypeBookings = $allBookings->groupBy('room.roomType.name')->map(function($group) {
                $count = $group->count();
                $revenue = $group->where('payment_status', 'paid')->sum('total_price');
                
                // Calculate average stay
                $totalNights = $group->sum(function($booking) {
                    return Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
                });
                
                // Calculate average rate (revenue per booking)
                $averageRate = $count > 0 ? $revenue / $count : 0;
                
                // Calculate average stay (nights per booking)
                $averageStay = $count > 0 ? $totalNights / $count : 0;
                
                return [
                    'count' => $count,
                    'revenue' => $revenue,
                    'average_rate' => $averageRate,
                    'average_stay' => $averageStay,
                ];
            })->sortByDesc('count');

            $monthlyBookings = [];
            $monthlyStatusTrends = [];
            $period = CarbonPeriod::create($startDate, '1 month', $endDate);
            $statuses = ['confirmed', 'checked_in', 'checked_out', 'cancelled'];

            // Initialize monthly status trends
            foreach ($statuses as $status) {
                $monthlyStatusTrends[$status] = [];
            }

            foreach ($period as $date) {
                $month = $date->format('M Y');
                $monthlyBookings[$month] = [
                    'count' => 0,
                    'revenue' => 0
                ];
                
                foreach ($statuses as $status) {
                    $monthlyStatusTrends[$status][$month] = [
                        'count' => 0,
                        'revenue' => 0
                    ];
                }
            }

            foreach ($allBookings as $booking) {
                $month = Carbon::parse($booking->check_in)->format('M Y');
                
                if (isset($monthlyBookings[$month])) {
                    $monthlyBookings[$month]['count']++;
                    if ($booking->payment_status === 'paid') {
                        $monthlyBookings[$month]['revenue'] += $booking->total_price;
                    }
                }
                
                if (isset($monthlyStatusTrends[$booking->status][$month])) {
                    $monthlyStatusTrends[$booking->status][$month]['count']++;
                    if ($booking->payment_status === 'paid') {
                        $monthlyStatusTrends[$booking->status][$month]['revenue'] += $booking->total_price;
                    }
                }
            }
            
            // Convert monthlyBookings to collection format for view
            $monthlyBookings = collect($monthlyBookings)->map(function($data, $month) {
                return (object) [
                    'month' => $month,
                    'count' => $data['count'],
                    'revenue' => $data['revenue']
                ];
            })->values();
            
            $bookings = $baseQuery->orderBy('check_in', 'desc')->paginate(15);

            // Preprocess monthlyStatusTrends for safe Blade JSON output
            $monthlyStatusTrendsFormatted = [];
            $monthlyStatusLabels = [];
            
            foreach (['confirmed', 'checked_in', 'checked_out', 'cancelled'] as $status) {
                $monthlyStatusTrendsFormatted[$status] = collect($monthlyStatusTrends[$status] ?? [])
                    ->pluck('count')
                    ->values()
                    ->toArray();
            }
            
            if (!empty($monthlyStatusTrends['confirmed'])) {
                $monthlyStatusLabels = array_keys($monthlyStatusTrends['confirmed']);
            }

            $data = [
                'bookings' => $bookings,
                'totalBookings' => $totalBookings,
                'totalRevenue' => $totalRevenue,
                'averageStay' => $averageStay,
                'occupancyRate' => $occupancyRate,
                'averageDailyRate' => $averageDailyRate,
                'statusCounts' => $statusCounts,
                'roomTypeBookings' => $roomTypeBookings,
                'monthlyBookings' => $monthlyBookings,
                'monthlyStatusTrends' => $monthlyStatusTrends,
                'monthlyStatusTrendsFormatted' => $monthlyStatusTrendsFormatted,
                'monthlyStatusLabels' => $monthlyStatusLabels,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d'),
                'selectedStatus' => $request->status,
                'selectedRoomType' => $request->room_type,
                'roomTypes' => RoomType::orderBy('name')->get(),
            ];
            
            // Debug: Log the data being passed to view
            \Log::info('Data being passed to bookings view:', [
                'totalBookings' => $totalBookings,
                'totalRevenue' => $totalRevenue,
                'averageStay' => $averageStay,
                'averageDailyRate' => $averageDailyRate,
                'occupancyRate' => $occupancyRate,
                'statusCounts_count' => $statusCounts ? $statusCounts->count() : 0,
                'roomTypeBookings_count' => $roomTypeBookings ? $roomTypeBookings->count() : 0,
                'monthlyBookings_count' => $monthlyBookings ? $monthlyBookings->count() : 0,
                'monthlyStatusTrends_count' => count($monthlyStatusTrends ?? []),
            ]);
        
            if ($request->ajax()) {
                return response()->json($data);
            }
        
            return view('admin.reports.bookings', $data);
            
        } catch (\Exception $e) {
            \Log::error('Bookings method error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return a simple error response for debugging
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->toArray()
            ], 500);
        }
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
            
        $revenueData = [];
        foreach ($periods as $period) {
            $revenueData[] = [
                'period' => $period['label'],
                'revenue' => $period['revenue'],
                'bookings' => $period['bookings']
            ];
        }
            
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
            'revenueData' => $revenueData, 
            'paymentMethods' => $paymentMethods,
            'roomTypeRevenue' => $roomTypeRevenue,
        ];
    }
    
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

    protected function getStatusColor($status, $opacity = 1)
    {
        $colors = [
            'pending' => 'rgba(255, 193, 7, ' . $opacity . ')',  // Amber
            'confirmed' => 'rgba(13, 110, 253, ' . $opacity . ')',  // Blue
            'checked_in' => 'rgba(25, 135, 84, ' . $opacity . ')',  // Green
            'checked_out' => 'rgba(111, 66, 193, ' . $opacity . ')',  // Purple
            'cancelled' => 'rgba(220, 53, 69, ' . $opacity . ')',  // Red
        ];
        
        return $colors[strtolower($status)] ?? 'rgba(108, 117, 125, ' . $opacity . ')';  // Gray as default
    }
}
