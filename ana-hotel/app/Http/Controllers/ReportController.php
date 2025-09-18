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

class ReportController extends Controller
{
    /**
     * Display the reports dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Default date range: current month
        $startDate = request('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate = request('end_date', now()->endOfMonth()->format('Y-m-d'));
        
        // Get quick stats for the dashboard
        $stats = $this->getQuickStats($startDate, $endDate);
        
        return view('admin.reports.index', array_merge(compact('startDate', 'endDate'), $stats));
    }
    
    /**
     * Get quick stats for the dashboard
     *
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    private function getQuickStats($startDate, $endDate)
    {
        // Total bookings
        $totalBookings = Booking::whereBetween('created_at', [$startDate, $endDate])
            ->count();
            
        // Total revenue (based on when payment was accepted/confirmed on bookings)
        $totalRevenue = Booking::where('payment_status', 'paid')
            ->whereBetween('payment_confirmed_at', [$startDate, $endDate])
            ->sum('total_price');
            
        // Total rooms
        $totalRooms = Room::count();
        
        // Total guests
        $totalGuests = User::where('role', 'guest')->count();
        
        // Occupancy rate
        $totalNights = 0;
        $availableNights = $totalRooms * Carbon::parse($startDate)->diffInDays($endDate);
        
        $bookings = Booking::where(function($q) use ($startDate, $endDate) {
            $q->where('check_in', '<=', $endDate)
              ->where('check_out', '>=', $startDate);
        })->get();
        
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
    
    /**
     * Get quick stats via API
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function quickStats()
    {
        $startDate = now()->startOfMonth()->format('Y-m-d');
        $endDate = now()->endOfMonth()->format('Y-m-d');
        
        $stats = $this->getQuickStats($startDate, $endDate);
        
        return response()->json($stats);
    }
    
    /**
     * Generate occupancy report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function occupancy(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'room_type' => 'nullable|exists:room_types,id',
            'floor' => 'nullable|integer|min:1',
        ]);
        
        // Set default date range if not provided (last 30 days)
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date)
            : now()->subDays(29)->startOfDay();
            
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfDay();
        
        // Base query for bookings
        $query = Booking::query()
            ->with(['room.roomType'])
            ->where('check_in', '<=', $endDate)
            ->where('check_out', '>=', $startDate);
            
        // Apply room type filter if provided
        if ($request->filled('room_type')) {
            $query->whereHas('room', function($q) use ($request) {
                $q->where('room_type_id', $request->room_type);
            });
        }
        
        // Apply floor filter if provided
        if ($request->filled('floor')) {
            $query->whereHas('room', function($q) use ($request) {
                $q->where('floor', $request->floor);
            });
        }
        
        // Get all rooms for the occupancy calculation
        $roomsQuery = Room::query();
        
        if ($request->filled('room_type')) {
            $roomsQuery->where('room_type_id', $request->room_type);
        }
        
        if ($request->filled('floor')) {
            $roomsQuery->where('floor', $request->floor);
        }
        
        $totalRooms = $roomsQuery->count();
        $totalNights = $startDate->diffInDays($endDate) + 1;
        $totalAvailableNights = $totalRooms * $totalNights;
        
        // Get all bookings for the period
        $bookings = $query->get();
        
        // Calculate occupied nights
        $occupiedNights = 0;
        $dailyOccupancy = [];
        
        // Initialize daily occupancy array
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dailyOccupancy[$currentDate->format('Y-m-d')] = 0;
            $currentDate->addDay();
        }
        
        // Calculate occupancy for each booking
        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in)->max($startDate);
            $checkOut = Carbon::parse($booking->check_out)->min($endDate);
            $nights = $checkIn->diffInDays($checkOut);
            $occupiedNights += $nights;
            
            // Update daily occupancy
            $currentDate = $checkIn->copy();
            while ($currentDate < $checkOut && $currentDate <= $endDate) {
                $dateKey = $currentDate->format('Y-m-d');
                $dailyOccupancy[$dateKey]++;
                $currentDate->addDay();
            }
        }
        
        // Calculate occupancy rate
        $occupancyRate = $totalAvailableNights > 0 ? ($occupiedNights / $totalAvailableNights) * 100 : 0;
        
        // Prepare data for the chart
        $chartLabels = [];
        $chartData = [];
        
        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $chartLabels[] = $currentDate->format('M d');
            $chartData[] = $totalRooms > 0 ? round(($dailyOccupancy[$dateKey] / $totalRooms) * 100, 2) : 0;
            $currentDate->addDay();
        }
        
        // Get room types for the filter
        $roomTypes = RoomType::orderBy('name')->get();
        
        // Calculate occupancy by room type
        $roomTypeOccupancy = [];
        foreach ($roomTypes as $roomType) {
            $roomTypeBookings = $bookings->filter(function($booking) use ($roomType) {
                return $booking->room->room_type_id === $roomType->id;
            });
            
            $roomTypeRooms = $roomType->rooms()->count();
            $roomTypeAvailableNights = $roomTypeRooms * $totalNights;
            $roomTypeOccupiedNights = 0;
            
            foreach ($roomTypeBookings as $booking) {
                $checkIn = Carbon::parse($booking->check_in)->max($startDate);
                $checkOut = Carbon::parse($booking->check_out)->min($endDate);
                $roomTypeOccupiedNights += $checkIn->diffInDays($checkOut);
            }
            
            $roomTypeOccupancy[$roomType->name] = [
                'total_rooms' => $roomTypeRooms,
                'occupied_nights' => $roomTypeOccupiedNights,
                'available_nights' => $roomTypeAvailableNights,
                'occupancy_rate' => $roomTypeAvailableNights > 0 ? 
                    round(($roomTypeOccupiedNights / $roomTypeAvailableNights) * 100, 2) : 0
            ];
        }
        
        // Prepare data for the view
        $data = [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'selectedRoomType' => $request->room_type,
            'selectedFloor' => $request->floor,
            'totalRooms' => $totalRooms,
            'totalNights' => $totalNights,
            'totalAvailableNights' => $totalAvailableNights,
            'occupiedNights' => $occupiedNights,
            'occupancyRate' => round($occupancyRate, 2),
            'roomTypes' => $roomTypes,
            'roomTypeOccupancy' => $roomTypeOccupancy,
            'floors' => range(1, 10), // Assuming max 10 floors
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'dates' => $chartLabels, // Add dates array for the chart
            'occupancyRates' => $chartData, // Add occupancy rates for the chart
        ];
        
        // Return JSON if it's an AJAX request
        if ($request->ajax()) {
            return response()->json($data);
        }
        
        return view('admin.reports.occupancy', $data);
    }
    
    /**
     * Generate revenue report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function revenue(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'group_by' => 'nullable|in:day,week,month,year',
            'room_type' => 'nullable|exists:room_types,id',
        ]);
        
        // Set default date range if not provided (last 30 days)
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date)
            : now()->subDays(29)->startOfDay();
            
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfDay();
            
        $groupBy = $request->input('group_by', 'day');
        
        // Use payments table for revenue aggregation so extensions (and any partials) count on the day paid
        $useBookings = false;
        $payments = collect();
        
        if ($useBookings) {
            $query = Booking::query()
                ->where('payment_status', 'paid')
                ->whereNotNull('payment_confirmed_at')
                ->whereBetween('payment_confirmed_at', [$startDate, $endDate])
                ->where('status', '!=', 'cancelled')
                ->with(['room.roomType']);

            if ($request->filled('room_type')) {
                $query->whereHas('room', function($q) use ($request) {
                    $q->where('room_type_id', $request->room_type);
                });
            }

            $payments = $query->get()
                ->groupBy(function($booking) use ($groupBy) {
                    return $this->getPeriod($booking->payment_confirmed_at, $groupBy);
                });
        } else {
            // Aggregate by actual payments completed in the period
            $payments = Payment::with(['booking.room.roomType'])
                ->where('status', 'completed')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->when($request->filled('room_type'), function($q) use ($request) {
                    $q->whereHas('booking.room', function($qq) use ($request) {
                        $qq->where('room_type_id', $request->room_type);
                    });
                })
                ->get()
                ->groupBy(function($payment) use ($groupBy) {
                    return $this->getPeriod($payment->paid_at, $groupBy);
                });
        }
        
        // Prepare data for the chart
        $chartLabels = [];
        $chartData = [];
        $chartDataRooms = [];
        
        // Generate all periods in the range
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
                'revpar' => 0,
            ];
            
            // Move to next period
            switch ($groupBy) {
                case 'day':
                    $currentDate->addDay();
                    break;
                case 'week':
                    $currentDate->addWeek();
                    break;
                case 'month':
                    $currentDate->addMonth();
                    break;
                case 'year':
                    $currentDate->addYear();
                    break;
            }
        }
        
        // Calculate metrics for each period
        foreach ($payments as $period => $periodPayments) {
            if (!isset($periods[$period])) {
                continue;
            }
            
            $periodData = &$periods[$period];
            
            if ($useBookings) {
                // Process bookings data
                $periodData['bookings'] = $periodPayments->count();
                $periodData['room_revenue'] = $periodPayments->sum('total_price');
                $periodData['revenue'] = $periodData['room_revenue'];
                $periodData['other_revenue'] = 0; // No other revenue from bookings
            } else {
                // Process payments data
                foreach ($periodPayments as $payment) {
                    $periodData['revenue'] += $payment->amount;
                    
                    if ($payment->type === 'room') {
                        $periodData['room_revenue'] += $payment->amount;
                    } else {
                        $periodData['other_revenue'] += $payment->amount;
                    }
                    
                    // Count unique bookings
                    if ($payment->booking) {
                        $periodData['bookings'] = $periodPayments->pluck('booking_id')->unique()->count();
                    }
                }
            }
            
            // Calculate ADR (Average Daily Rate)
            $periodData['adr'] = $periodData['bookings'] > 0 
                ? $periodData['room_revenue'] / $periodData['bookings'] 
                : 0;
                
            // Calculate RevPAR (Revenue Per Available Room)
            $totalRooms = Room::when($request->filled('room_type'), function($q) use ($request) {
                $q->where('room_type_id', $request->room_type);
            })->count();
            
            $periodData['revpar'] = $totalRooms > 0 
                ? $periodData['room_revenue'] / $totalRooms 
                : 0;
        }
        
        // Prepare data for the view
        $chartLabels = array_column($periods, 'label');
        $chartData = [
            'Room Revenue' => array_column($periods, 'room_revenue'),
            'Other Revenue' => array_column($periods, 'other_revenue'),
        ];
        
        $tableData = array_values($periods);
        
        // Calculate totals
        $totalRevenue = array_sum(array_column($periods, 'revenue'));
        $totalRoomRevenue = array_sum(array_column($periods, 'room_revenue'));
        $totalOtherRevenue = array_sum(array_column($periods, 'other_revenue'));
        $totalBookings = array_sum(array_column($periods, 'bookings'));
        
        $averageAdr = $totalBookings > 0 ? $totalRoomRevenue / $totalBookings : 0;
        $averageRate = $totalBookings > 0 ? $totalRevenue / $totalBookings : 0;
        
        // Get payment methods data
        $paymentMethods = [];
        if ($useBookings) {
            // If using booking data, we don't have payment method information
            $paymentMethods = [
                'Booking' => [
                    'amount' => $totalRevenue,
                    'percentage' => 100,
                    'count' => $totalBookings
                ]
            ];
        } else {
            $paymentMethods = Payment::select('payment_method', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as count'))
                ->where('status', 'completed')
                ->whereBetween('paid_at', [$startDate, $endDate])
                ->groupBy('payment_method')
                ->get()
                ->mapWithKeys(function($item) use ($totalRevenue) {
                    return [
                        $item->payment_method => [
                            'amount' => $item->total_amount,
                            'percentage' => $totalRevenue > 0 ? ($item->total_amount / $totalRevenue) * 100 : 0,
                            'count' => $item->count
                        ]
                    ];
                });
        }
            
        // Get room type revenue data
        $roomTypeRevenue = [];
        if ($useBookings) {
            $roomTypeRevenue = Booking::select(
                    'room_types.name',
                    DB::raw('SUM(bookings.total_price) as amount'),
                    DB::raw('COUNT(bookings.id) as bookings')
                )
                ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                ->whereIn('bookings.status', ['checked_in', 'checked_out', 'completed'])
                ->whereBetween('bookings.check_in', [$startDate, $endDate])
                ->when($request->filled('room_type'), function($q) use ($request) {
                    $q->where('rooms.room_type_id', $request->room_type);
                })
                ->groupBy('room_types.name')
                ->get()
                ->mapWithKeys(function($item) use ($totalRevenue) {
                    return [
                        $item->name => [
                            'amount' => $item->amount,
                            'bookings' => $item->bookings,
                            'percentage' => $totalRevenue > 0 ? ($item->amount / $totalRevenue) * 100 : 0
                        ]
                    ];
                });
        } else {
            $roomTypeRevenue = Booking::select(
                    'room_types.name',
                    DB::raw('SUM(bookings.total_price) as amount'),
                    DB::raw('COUNT(bookings.id) as bookings')
                )
                ->join('payments', 'bookings.id', '=', 'payments.booking_id')
                ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
                ->join('room_types', 'rooms.room_type_id', '=', 'room_types.id')
                ->where('payments.status', 'completed')
                ->whereBetween('payments.paid_at', [$startDate, $endDate])
                ->when($request->filled('room_type'), function($q) use ($request) {
                    $q->where('rooms.room_type_id', $request->room_type);
                })
                ->groupBy('room_types.name')
                ->get()
                ->mapWithKeys(function($item) use ($totalRevenue) {
                    return [
                        $item->name => [
                            'amount' => $item->amount,
                            'bookings' => $item->bookings,
                            'percentage' => $totalRevenue > 0 ? ($item->amount / $totalRevenue) * 100 : 0
                        ]
                    ];
                });
        }
            
        // Get room types for the filter
        $roomTypes = RoomType::orderBy('name')->get();
        
        // Prepare revenue data for the table
        $revenueData = [];
        foreach ($tableData as $period) {
            $revenueData[] = [
                'period' => $period['label'],
                'revenue' => $period['revenue'],
                'bookings' => $period['bookings']
            ];
        }
        
        // Prepare data for the view
        $data = [
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'groupBy' => $groupBy,
            'selectedRoomType' => $request->room_type,
            'totalRevenue' => $totalRevenue,
            'totalRoomRevenue' => $totalRoomRevenue,
            'totalOtherRevenue' => $totalOtherRevenue,
            'totalBookings' => $totalBookings,
            'averageAdr' => $averageAdr,
            'averageRate' => $averageRate,
            'roomTypes' => $roomTypes,
            'chartLabels' => $chartLabels,
            'chartData' => $chartData,
            'tableData' => $tableData,
            'revenueData' => $revenueData,
            'paymentMethods' => $paymentMethods,
            'roomTypeRevenue' => $roomTypeRevenue,
        ];
        
        // Return JSON if it's an AJAX request
        if ($request->ajax()) {
            return response()->json($data);
        }
        
        return view('admin.reports.revenue', $data);
    }
    
    /**
     * Generate booking statistics report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function bookings(Request $request)
    {
        // Log the incoming request data
        \Log::info('Bookings Report Request:', [
            'all_params' => $request->all(),
            'url' => $request->fullUrl(),
            'method' => $request->method()
        ]);

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:pending,confirmed,checked_in,checked_out,cancelled',
            'room_type' => 'nullable|exists:room_types,id',
        ]);
        
        // Set default date range if not provided (current month)
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date)
            : now()->startOfMonth();
            
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfMonth();
        
        // Log the date range being used
        \Log::info('Date range for report:', [
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'is_default_range' => !$request->filled('start_date') && !$request->filled('end_date')
        ]);
        
        // Base query for bookings
        $query = Booking::query()
            ->with(['room.roomType', 'user', 'payments'])
            // Find bookings that overlap with the date range
            ->where(function($q) use ($startDate, $endDate) {
                // Simplified overlap condition
                $q->where('check_in', '<=', $endDate)
                  ->where('check_out', '>=', $startDate);
            })
            // Exclude cancelled bookings by default
            ->when(!$request->filled('status'), function($q) {
                $q->where('status', '!=', 'cancelled');
            });
        
        // Apply status filter if provided
        if ($request->filled('status')) {
            $query->where('status', $request->status);
            \Log::info('Applied status filter:', ['status' => $request->status]);
        }
        
        // Apply room type filter if provided
        if ($request->filled('room_type')) {
            $query->whereHas('room', function($q) use ($request) {
                $q->where('room_type_id', $request->room_type);
            });
            \Log::info('Applied room type filter:', ['room_type_id' => $request->room_type]);
        }
        
        // Get the bookings with pagination
        $bookings = $query->orderBy('check_in', 'desc')->paginate(15);
        
        // Debug: Log the raw SQL query and bindings
        \Log::info('Final Bookings Query:', [
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
            'count' => $bookings->total()
        ]);
        
        // Log the number of bookings found
        $bookingsCount = $bookings->total();
        \Log::info('Bookings found:', [
            'count' => $bookingsCount,
            'current_page' => $bookings->currentPage(),
            'per_page' => $bookings->perPage(),
            'total_pages' => $bookings->lastPage()
        ]);
        
        // If no bookings found, log the current date for debugging
        if ($bookingsCount === 0) {
            \Log::warning('No bookings found in the specified date range', [
                'current_date' => now()->format('Y-m-d'),
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'filters' => [
                    'status' => $request->status,
                    'room_type' => $request->room_type
                ]
            ]);
        }
        
        // Calculate totals and metrics
        $totalBookings = $bookings->total();
        $totalRevenue = $bookings->sum('total_price');
        
        // Calculate average stay length
        $totalNights = $bookings->sum(function($booking) {
            return Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
        });
        
        // Calculate average stay
        $averageStay = $totalBookings > 0 ? $totalNights / $totalBookings : 0;
        
        // Calculate average daily rate (ADR)
        $averageDailyRate = $totalNights > 0 ? $totalRevenue / $totalNights : 0;
        
        // Calculate occupancy rate
        $totalRooms = Room::count();
        $totalAvailableNights = $totalRooms * $startDate->diffInDays($endDate);
        $occupiedNights = $bookings->sum(function($booking) use ($startDate, $endDate) {
            $checkIn = Carbon::parse($booking->check_in)->max($startDate);
            $checkOut = Carbon::parse($booking->check_out)->min($endDate);
            return $checkIn->diffInDays($checkOut);
        });
        $occupancyRate = $totalAvailableNights > 0 ? ($occupiedNights / $totalAvailableNights) * 100 : 0;
        
        // Group bookings by status
        $statusCounts = $bookings->groupBy('status')
            ->map(function($bookings, $status) use ($totalBookings) {
                $count = $bookings->count();
                return [
                    'count' => $count,
                    'revenue' => $bookings->sum('total_price'),
                    'color' => $this->getStatusColor($status),
                    'percentage' => $totalBookings > 0 ? ($count / $totalBookings) * 100 : 0
                ];
            });
        
        // Group bookings by room type
        $roomTypeBookings = $bookings->groupBy('room.roomType.name')
            ->map(function($bookings, $roomType) {
                $count = $bookings->count();
                $revenue = $bookings->sum('total_price');
                $totalNights = $bookings->sum(function($booking) {
                    return Carbon::parse($booking->check_in)->diffInDays($booking->check_out);
                });
                
                return [
                    'count' => $count,
                    'revenue' => $revenue,
                    'average_rate' => $count > 0 ? $revenue / $count : 0,
                    'average_stay' => $count > 0 ? $totalNights / $count : 0
                ];
            });
        
        // Prepare monthly trends data
        $monthlyBookings = collect();
        $monthlyStatusTrends = [
            'labels' => [],
            'datasets' => []
        ];
        
        // Add status trends data if needed
        $statuses = ['confirmed', 'checked_in', 'checked_out', 'cancelled'];
        foreach ($statuses as $status) {
            $monthlyStatusTrends['datasets'][$status] = [
                'label' => ucfirst($status),
                'data' => [],
                'borderColor' => $this->getStatusColor($status),
                'backgroundColor' => $this->getStatusColor($status, 0.2),
                'tension' => 0.3,
                'fill' => false
            ];
        }
        
        // Add sample data for now - replace with actual data from your database
        $currentMonth = now()->startOfMonth();
        for ($i = 0; $i < 6; $i++) {
            $month = $currentMonth->copy()->subMonths(5 - $i);
            $monthlyBookings->push((object)[
                'month' => $month->format('M Y'),
                'bookings' => $bookings->where('check_in', '>=', $month->startOfMonth())
                                     ->where('check_in', '<=', $month->endOfMonth())
                                     ->count(),
                'revenue' => $bookings->where('check_in', '>=', $month->startOfMonth())
                                    ->where('check_in', '<=', $month->endOfMonth())
                                    ->sum('total_price')
            ]);
            
            $monthlyStatusTrends['labels'][] = $month->format('M Y');
            
            foreach ($statuses as $status) {
                $monthlyStatusTrends['datasets'][$status]['data'][] = 
                    $bookings->where('status', $status)
                            ->where('check_in', '>=', $month->startOfMonth())
                            ->where('check_in', '<=', $month->endOfMonth())
                            ->count();
            }
        }
        
        // Convert monthly status trends to array for the view
        $monthlyStatusTrends['datasets'] = array_values($monthlyStatusTrends['datasets']);
        
        // Get room types for the filter
        $roomTypes = RoomType::orderBy('name')->get();
        
        // Prepare data for the view
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
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'selectedStatus' => $request->status,
            'selectedRoomType' => $request->room_type,
            'roomTypes' => $roomTypes,
        ];
        
        // Log the data being passed to the view
        \Log::info('Data being passed to view:', [
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'statusCounts' => $statusCounts->toArray(),
            'roomTypeBookings' => $roomTypeBookings->toArray(),
            'monthlyBookings' => $monthlyBookings->toArray(),
            'monthlyStatusTrends' => $monthlyStatusTrends
        ]);
        
        // Return the view with data
        return view('admin.reports.bookings', $data);
    }
    
    /**
     * Get color for booking status
     *
     * @param string $status
     * @return string
     */
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
    
    /**
     * Generate guest statistics report
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function guests(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'country' => 'nullable|string',
            'source' => 'nullable|string',
        ]);
        
        // Set default date range if not provided (last 30 days)
        $startDate = $request->filled('start_date') 
            ? Carbon::parse($request->start_date)
            : now()->subDays(29)->startOfDay();
            
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)
            : now()->endOfDay();
        
        // Base query for guests
        $query = User::query()
            ->where('role', 'guest')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->withCount(['bookings', 'payments'])
            ->withSum('payments', 'amount');
        
        // Apply country filter if provided
        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }
        
        // Apply source filter if provided
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        
        // Get guests with pagination
        $guests = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Calculate metrics
        $totalGuests = $guests->total();
        $totalBookings = $guests->sum('bookings_count');
        $totalRevenue = $guests->sum('payments_sum_amount');
        
        // Group guests by country
        $countryStats = User::where('role', 'guest')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('country', DB::raw('count(*) as total'))
            ->groupBy('country')
            ->orderBy('total', 'desc')
            ->get();
        
        // Group guests by source
        $sourceStats = User::where('role', 'guest')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->orderBy('total', 'desc')
            ->get();
        
        // Get unique countries and sources for filters
        $countries = User::where('role', 'guest')
            ->distinct('country')
            ->pluck('country')
            ->filter()
            ->sort();
            
        $sources = User::where('role', 'guest')
            ->distinct('source')
            ->pluck('source')
            ->filter()
            ->sort();
        
        // Prepare data for the view
        $data = [
            'guests' => $guests,
            'totalGuests' => $totalGuests,
            'totalBookings' => $totalBookings,
            'totalRevenue' => $totalRevenue,
            'countryStats' => $countryStats,
            'sourceStats' => $sourceStats,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
            'selectedCountry' => $request->country,
            'selectedSource' => $request->source,
            'countries' => $countries,
            'sources' => $sources,
        ];
        
        // Return JSON if it's an AJAX request
        if ($request->ajax()) {
            return response()->json($data);
        }
        
        return view('admin.reports.guests', $data);
    }
    
    /**
     * Get period string for grouping
     * 
     * @param  string $date
     * @param  string $groupBy
     * @return string
     */
    protected function getPeriod($date, $groupBy)
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        switch ($groupBy) {
            case 'day':
                return $date->format('Y-m-d');
            case 'week':
                return $date->startOfWeek()->format('Y-m-d');
            case 'month':
                return $date->startOfMonth()->format('Y-m');
            case 'year':
                return $date->startOfYear()->format('Y');
            default:
                return $date->format('Y-m-d');
        }
    }
    
    /**
     * Get period label for display
     * 
     * @param  string $date
     * @param  string $groupBy
     * @return string
     */
    protected function getPeriodLabel($date, $groupBy)
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);
        
        switch ($groupBy) {
            case 'day':
                return $date->format('M d, Y');
            case 'week':
                return 'Week of ' . $date->startOfWeek()->format('M d, Y');
            case 'month':
                return $date->format('M Y');
            case 'year':
                return $date->format('Y');
            default:
                return $date->format('M d, Y');
        }
    }
    
    /**
     * Calculate occupancy rate for a given period
     *
     * @param  \Carbon\Carbon  $startDate
     * @param  \Carbon\Carbon  $endDate
     * @param  \Illuminate\Database\Eloquent\Collection  $bookings
     * @return float
     */
    protected function calculateOccupancyRate($startDate, $endDate, $bookings)
    {
        $totalRooms = Room::count();
        $totalNights = $startDate->diffInDays($endDate) + 1;
        $totalAvailableNights = $totalRooms * $totalNights;
        
        $occupiedNights = 0;
        
        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in)->max($startDate);
            $checkOut = Carbon::parse($booking->check_out)->min($endDate);
            $occupiedNights += $checkIn->diffInDays($checkOut);
        }
        
        return $totalAvailableNights > 0 ? ($occupiedNights / $totalAvailableNights) * 100 : 0;
    }
    
    /**
     * Calculate monthly occupancy
     *
     * @param  \Carbon\Carbon  $date
     * @param  \Illuminate\Database\Eloquent\Collection  $bookings
     * @return float
     */
    protected function calculateMonthlyOccupancy($date, $bookings)
    {
        $totalRooms = Room::count();
        $daysInMonth = $date->daysInMonth;
        $totalAvailableNights = $totalRooms * $daysInMonth;
        
        $occupiedNights = 0;
        
        foreach ($bookings as $booking) {
            $checkIn = Carbon::parse($booking->check_in)->max($date->copy()->startOfMonth());
            $checkOut = Carbon::parse($booking->check_out)->min($date->copy()->endOfMonth());
            $occupiedNights += $checkIn->diffInDays($checkOut);
        }
        
        return $totalAvailableNights > 0 ? ($occupiedNights / $totalAvailableNights) * 100 : 0;
    }
    
    /**
     * Generate a random color for charts
     *
     * @param  int  $opacity
     * @return string
     */
    protected function getRandomColor($opacity = 0.7)
    {
        $red = mt_rand(0, 255);
        $green = mt_rand(0, 255);
        $blue = mt_rand(0, 255);
        
        return "rgba($red, $green, $blue, $opacity)";
    }
}
