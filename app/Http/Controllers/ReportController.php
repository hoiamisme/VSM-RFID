<?php

namespace App\Http\Controllers;

use App\Models\TrackingLog;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\TrackingLogsExport;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $locations = Location::where('code', '!=', 'MAIN')->get();
        
        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $locationCode = $request->input('location_code');
        
        // Summary statistics
        $stats = [
            'total_entries' => 0,
            'total_exits' => 0,
            'currently_inside' => 0,
            'unique_visitors' => 0
        ];
        
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $query = TrackingLog::whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);
            
            if ($locationCode) {
                $query->where('location_code', $locationCode);
            }
            
            $stats['total_entries'] = (clone $query)->where('action_type', 'entry')->count();
            $stats['total_exits'] = (clone $query)->where('action_type', 'exit')->count();
            $stats['unique_visitors'] = (clone $query)
                ->distinct('user_id')
                ->count('user_id');
        }
        
        // Currently inside (regardless of date filter)
        $stats['currently_inside'] = User::whereHas('trackingLogs', function($query) {
            $query->where('location_code', 'MAIN')
                  ->where('action_type', 'entry')
                  ->whereNotExists(function($subQuery) {
                      $subQuery->select(DB::raw(1))
                               ->from('tracking_logs as tl2')
                               ->whereColumn('tl2.user_id', 'tracking_logs.user_id')
                               ->where('tl2.location_code', 'MAIN')
                               ->where('tl2.action_type', 'exit')
                               ->whereColumn('tl2.created_at', '>', 'tracking_logs.created_at');
                  });
        })->count();
        
        // Detailed logs
        $logs = collect();
        if ($request->filled('date_from') || $request->filled('date_to')) {
            $logsQuery = TrackingLog::with(['user', 'location'])
                ->whereHas('user')
                ->whereHas('location')
                ->whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59'
                ])
                ->orderBy('created_at', 'desc');
            
            if ($locationCode) {
                $logsQuery->where('location_code', $locationCode);
            }
            
            $logs = $logsQuery->paginate(20)->withQueryString();
        }
        
        // Location occupancy
        $locationOccupancy = [];
        foreach ($locations as $location) {
            $count = TrackingLog::where('location_code', $location->code)
                ->where('action_type', 'entry')
                ->whereNotExists(function($query) use ($location) {
                    $query->select(DB::raw(1))
                          ->from('tracking_logs as tl2')
                          ->whereColumn('tl2.user_id', 'tracking_logs.user_id')
                          ->where('tl2.location_code', $location->code)
                          ->where('tl2.action_type', 'exit')
                          ->whereColumn('tl2.created_at', '>', 'tracking_logs.created_at');
                })
                ->distinct('user_id')
                ->count('user_id');
            
            $locationOccupancy[] = [
                'location' => $location,
                'current_count' => $count,
                'capacity' => $location->capacity,
                'percentage' => $location->capacity > 0 ? round(($count / $location->capacity) * 100) : 0
            ];
        }
        
        return view('reports.index', compact('stats', 'logs', 'locations', 'locationOccupancy', 'dateFrom', 'dateTo', 'locationCode'));
    }
    
    public function export(Request $request)
    {
        $dateFrom = $request->input('date_from', date('Y-m-d'));
        $dateTo = $request->input('date_to', date('Y-m-d'));
        $locationCode = $request->input('location_code');
        $format = $request->input('format', 'excel');
        
        $filename = 'laporan_tracking_' . date('Y-m-d_His');
        
        switch ($format) {
            case 'excel':
                return Excel::download(
                    new TrackingLogsExport($dateFrom, $dateTo, $locationCode),
                    $filename . '.xlsx'
                );
                
            case 'csv':
                return Excel::download(
                    new TrackingLogsExport($dateFrom, $dateTo, $locationCode),
                    $filename . '.csv'
                );
                
            case 'pdf':
                return $this->exportPdf($dateFrom, $dateTo, $locationCode);
                
            default:
                return redirect()->back()->with('error', 'Format export tidak didukung');
        }
    }
    
    protected function exportPdf($dateFrom, $dateTo, $locationCode)
    {
        $query = TrackingLog::with(['user', 'location'])
            ->whereHas('user')
            ->whereHas('location')
            ->whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ])
            ->orderBy('created_at', 'desc');
        
        if ($locationCode) {
            $query->where('location_code', $locationCode);
        }
        
        $logs = $query->get();
        
        // Statistics
        $stats = [
            'total_entries' => TrackingLog::whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59'
                ])
                ->when($locationCode, fn($q) => $q->where('location_code', $locationCode))
                ->where('action_type', 'entry')
                ->count(),
            'total_exits' => TrackingLog::whereBetween('created_at', [
                    $dateFrom . ' 00:00:00',
                    $dateTo . ' 23:59:59'
                ])
                ->when($locationCode, fn($q) => $q->where('location_code', $locationCode))
                ->where('action_type', 'exit')
                ->count(),
        ];
        
        $location = $locationCode ? Location::where('code', $locationCode)->first() : null;
        
        $pdf = Pdf::loadView('reports.pdf', [
            'logs' => $logs,
            'stats' => $stats,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'location' => $location
        ]);
        
        return $pdf->download('laporan_tracking_' . date('Y-m-d_His') . '.pdf');
    }
}
