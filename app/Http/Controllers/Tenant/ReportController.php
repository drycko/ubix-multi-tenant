<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Room;
use App\Models\Property;
use Carbon\Carbon;

class ReportController extends Controller
{
    /**
     * Display advanced reports dashboard
     */
    public function advanced()
    {
        $property = current_property();
        
        // Get report data
        $reportData = [
            'occupancy' => $this->getOccupancyReport(),
            'revenue' => $this->getRevenueReport(),
            'booking_source' => $this->getBookingSourceReport(),
            'room_performance' => $this->getRoomPerformanceReport()
        ];

        return view('tenant.reports.advanced', compact('reportData', 'property'));
    }

    /**
     * Generate occupancy report
     */
    protected function getOccupancyReport()
    {
        $property = current_property();
        
        $data = [
            'monthly_occupancy' => Booking::where('property_id', $property->id)
                ->selectRaw('MONTH(arrival_date) as month, 
                            YEAR(arrival_date) as year,
                            COUNT(*) as total_bookings,
                            AVG(DATEDIFF(departure_date, arrival_date)) as avg_stay_length')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),

            'current_occupancy' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->where('arrival_date', '<=', now())
                ->where('departure_date', '>=', now())
                ->count(),

            'total_rooms' => Room::where('property_id', $property->id)->count()
        ];

        $data['occupancy_rate'] = $data['total_rooms'] > 0 
            ? round(($data['current_occupancy'] / $data['total_rooms']) * 100, 2) 
            : 0;

        return $data;
    }

    /**
     * Generate revenue report
     */
    protected function getRevenueReport()
    {
        $property = current_property();
        
        return [
            'monthly_revenue' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->selectRaw('MONTH(arrival_date) as month, 
                            YEAR(arrival_date) as year,
                            SUM(total_amount) as revenue,
                            COUNT(*) as bookings')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->limit(12)
                ->get(),

            'ytd_revenue' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->whereYear('arrival_date', date('Y'))
                ->sum('total_amount'),

            'average_daily_rate' => Booking::where('property_id', $property->id)
                ->where('status', 'confirmed')
                ->avg('daily_rate')
        ];
    }

    /**
     * Generate booking source report
     */
    protected function getBookingSourceReport()
    {
        $property = current_property();
        
        return Booking::where('property_id', $property->id)
            ->selectRaw('source, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('source')
            ->orderBy('count', 'desc')
            ->get();
    }

    /**
     * Generate room performance report
     */
    protected function getRoomPerformanceReport()
    {
        $property = current_property();
        
        return Room::with(['type', 'bookings' => function($query) {
                $query->where('status', 'confirmed');
            }])
            ->where('property_id', $property->id)
            ->get()
            ->map(function($room) {
                return [
                    'room_number' => $room->number,
                    'room_type' => $room->type->name,
                    'total_bookings' => $room->bookings->count(),
                    'total_revenue' => $room->bookings->sum('total_amount'),
                    'occupancy_rate' => $room->bookings->count() > 0 
                        ? round(($room->bookings->count() / 30) * 100, 2) // Simple 30-day calculation
                        : 0
                ];
            })
            ->sortByDesc('total_revenue');
    }

    /**
     * Export reports to CSV/Excel
     */
    public function export(Request $request, $type)
    {
        $request->validate([
            'report_type' => 'required|in:occupancy,revenue,bookings,rooms'
        ]);

        $property = current_property();
        $data = [];

        switch ($request->report_type) {
            case 'occupancy':
                $data = $this->getOccupancyReport();
                $filename = 'occupancy-report-' . date('Y-m-d') . '.csv';
                break;

            case 'revenue':
                $data = $this->getRevenueReport();
                $filename = 'revenue-report-' . date('Y-m-d') . '.csv';
                break;

            case 'bookings':
                $data = $this->getBookingSourceReport();
                $filename = 'bookings-report-' . date('Y-m-d') . '.csv';
                break;

            case 'rooms':
                $data = $this->getRoomPerformanceReport();
                $filename = 'rooms-report-' . date('Y-m-d') . '.csv';
                break;
        }

        if ($type === 'csv') {
            return $this->exportToCsv($data, $filename);
        }

        return response()->json(['error' => 'Export type not supported'], 400);
    }

    /**
     * Simple CSV export
     */
    protected function exportToCsv($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Add header row based on data type
            if (isset($data['monthly_occupancy'])) {
                fputcsv($file, ['Month', 'Year', 'Bookings', 'Average Stay Length']);
                foreach ($data['monthly_occupancy'] as $row) {
                    fputcsv($file, [
                        $row->month,
                        $row->year,
                        $row->total_bookings,
                        $row->avg_stay_length
                    ]);
                }
            }
            // Add other export formats as needed

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
