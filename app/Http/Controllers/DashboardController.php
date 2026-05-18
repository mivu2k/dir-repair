<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\RepairJob;
use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $thisWeek = now()->startOfWeek();

        $user = auth()->user();
        $isTechOnly = $user->hasPermissionTo('change own jobs only');

        // 1. Key Metrics
        $metrics = [
            'today_jobs' => RepairJob::when($isTechOnly, fn($q) => $q->where('assigned_technician_id', $user->id))
                ->where('created_at', '>=', $today)->count(),
            'pending_approvals' => $isTechOnly ? 0 : Quotation::where('status', 'sent')->count(),
            'active_repairs' => RepairJob::when($isTechOnly, fn($q) => $q->where('assigned_technician_id', $user->id))
                ->whereIn('status', ['diagnosing', 'in_progress'])->count(),
            'today_revenue' => $isTechOnly ? 0 : Payment::where('created_at', '>=', $today)->sum('amount'),
        ];

        // 2. Status Breakdown
        $statusBreakdown = RepairJob::when($isTechOnly, fn($q) => $q->where('assigned_technician_id', $user->id))
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        // 3. Recent Jobs
        $recentJobs = RepairJob::when($isTechOnly, fn($q) => $q->where('assigned_technician_id', $user->id))
            ->with('customer:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // 4. Weekly Revenue Trend (Hidden for restricted techs)
        $weeklyRevenue = $isTechOnly ? collect() : Payment::where('created_at', '>=', now()->subDays(7))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(amount) as total'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'statusBreakdown' => $statusBreakdown,
            'recentJobs' => $recentJobs,
            'weeklyRevenue' => $weeklyRevenue,
        ]);
    }
}
