<?php

namespace App\Http\Controllers;

use App\Models\DemoIssuance;
use App\Models\GatePass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LogisticsReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfDay()->toDateString());

        // Asset Flow (Inward/Outward)
        $gatePassStats = GatePass::select('type', DB::raw('count(*) as count'))
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->groupBy('type')
            ->get()
            ->pluck('count', 'type');

        // Demo Issuances Tracking
        $demoStats = DemoIssuance::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $overdueUnits = DemoIssuance::with('customer')
            ->where('status', 'issued')
            ->where('expected_return_date', '<', now()->toDateString())
            ->get();

        $activeIssuances = DemoIssuance::with(['customer', 'issuedBy'])
            ->where('status', 'issued')
            ->latest()
            ->get()
            ->map(function ($item) {
                $item->aging_days = now()->diffInDays($item->issued_at);
                return $item;
            });

        return Inertia::render('Logistics/Logistics', [
            'stats' => [
                'inward' => $gatePassStats['inward'] ?? 0,
                'outward' => $gatePassStats['outward'] ?? 0,
                'demo_issued' => $demoStats['issued'] ?? 0,
                'demo_returned' => $demoStats['returned'] ?? 0,
                'overdue_count' => $overdueUnits->count(),
            ],
            'activeIssuances' => $activeIssuances,
            'overdueUnits' => $overdueUnits,
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]
        ]);
    }
}
