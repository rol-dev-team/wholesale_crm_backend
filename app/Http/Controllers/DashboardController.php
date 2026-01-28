<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskNote;
use App\Models\SalesTarget;


class DashboardController extends Controller
{
    public function adminSummary()
{
  $users = auth()->user();
  return response()->json([
      'status' => true, 
      'data' => $users
  ]);
    try {
        /* ---------------- TOTAL KAM ---------------- */
        $totalKams = DB::connection('mysql_second')
            ->table('employments as e')
            ->join('parties as pa', 'e.employee_id', '=', 'pa.id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->join('designations as ds', 'e.designation_id', '=', 'ds.id')
            ->leftJoin('parties as p', 'p.id', '=', 'e.manager_1')
            ->whereNull('pa.type')
            ->where('pa.subtype', 2)
            ->where('pa.role', 8)
            ->where('d.id', 6)
            ->where('pa.inactive', 0)
            ->distinct('e.employee_id')
            ->count('e.employee_id');

        /* ---------------- TOTAL CLIENT ---------------- */

        $totalClients = DB::connection('mysql_second')
        ->table('parties as pa')
        ->where('pa.type', 'customer')
        ->where('pa.inactive', 0)
        ->distinct('pa.id')
        ->count('pa.id');


        /* ---------------- TOTAL BRANCH ---------------- */
        $totalBranches = DB::connection('mysql_second')
            ->table('branches')
            ->distinct('id')
            ->count('id');

        return response()->json([
            'status' => true,
            'data' => [
                'total_branches' => $totalBranches,
                'total_kams'     => $totalKams,
                'total_clients'  => $totalClients,
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to load dashboard summary',
        ], 500);
    }
}

public function kpiSummary()
{
    $startOfMonth = now()->startOfMonth();
    $endOfMonth   = now()->endOfMonth();

    /* ---------- LAST MONTH ---------- */
    $startLastMonth = now()->subMonth()->startOfMonth();
    $endLastMonth   = now()->subMonth()->endOfMonth();

    /* -------- TOTAL ACTIVE KAM -------- */
    $totalKams = DB::connection('mysql_second')
        ->table('employments as e')
        ->join('parties as pa', 'e.employee_id', '=', 'pa.id')
        ->join('departments as d', 'e.department_id', '=', 'd.id')
        ->join('designations as ds', 'e.designation_id', '=', 'ds.id')
        ->leftJoin('parties as p', 'p.id', '=', 'e.manager_1')
        ->whereNull('pa.type')
        ->where('pa.subtype', 2)
        ->where('pa.role', 8)
        ->where('d.id', 6)
        ->where('pa.inactive', 0)
        ->distinct('e.employee_id')
        ->count('e.employee_id');

    /* -------- TOTAL ACTIVITIES (THIS MONTH) -------- */
    $totalActivities = Task::whereBetween('created_at', [$startOfMonth, $endOfMonth])
        ->whereNull('deleted_at')
        ->count();

    /* -------- AVG ACTIVITIES -------- */
    $avgActivities = $totalKams > 0
        ? round($totalActivities / $totalKams, 2)
        : 0;

        /* ---------- LAST MONTH ACTIVITIES ---------- */
    $totalActivitiesLastMonth = Task::whereBetween(
            'created_at',
            [$startLastMonth, $endLastMonth]
        )
        ->whereNull('deleted_at')
        ->count();

    $avgActivitiesLastMonth = $totalKams > 0
        ? round($totalActivitiesLastMonth / $totalKams, 2)
        : 0;

        /* -------- Target This Month -------- */
    $targetThisMonth = DB::table('sales_targets')
        ->whereMonth('target_month', now()->month)
        ->whereYear('target_month', now()->year)
        ->sum('amount');
    
        /* ---------- LAST MONTH TARGET ---------- */
    $lastMonthTarget = DB::table('sales_targets')
        ->whereMonth('target_month', now()->subMonth()->month)
        ->whereYear('target_month', now()->subMonth()->year)
        ->sum('amount');


    /* -------- ACHIEVEMENT -------- */
    $achivedData = $this->getSupervisorMonthlyTotals();

    $thisMonthAchived = $achivedData['current_month_total'];
    $lastMonthAchived = $achivedData['last_month_total'];

    $thisMonthAchivedPercentage = $targetThisMonth > 0
        ? round(($thisMonthAchived / $targetThisMonth) * 100, 2)
        : 0;

    $lastMonthAchivedPercentage = $lastMonthTarget > 0
        ? round(($lastMonthAchived / $lastMonthTarget) * 100, 2)
        : 0;

    return response()->json([
        'status' => true,
        'data' => [
            'total_kams'                    => $totalKams,
            'total_activities_this_month'   => $totalActivities,
            'avg_activities_this_month'     => $avgActivities,
            'avg_activities_last_month'      => $avgActivitiesLastMonth,
            'target_this_month'             => round($targetThisMonth),
            'target_last_month'             => round($lastMonthTarget),
            'this_month_achieved'           => round($thisMonthAchived),
            'last_month_achieved'           => round($lastMonthAchived),
            'this_month_achieved_percentage'=> $thisMonthAchivedPercentage,
            'last_month_achieved_percentage'=> $lastMonthAchivedPercentage,
            'this_month_label'              => $startOfMonth->format('M Y'),
            'last_month_label'              => $startLastMonth->format('M Y'),
        ],
    ]);
}




private function getSupervisorMonthlyTotals(
    $supervisorId = null,
    $start_date = null,
    $end_date = null
) {
    if (!$start_date || !$end_date) {
        $now = Carbon::now();
        $start_date = $now->copy()->startOfMonth()->format('Y-m-d');
        $end_date = $now->copy()->endOfMonth()->format('Y-m-d');
    }

    $prevMonthStart = Carbon::parse($start_date)->subMonth()->startOfMonth()->format('Y-m-d');
    $prevMonthEnd = Carbon::parse($start_date)->subMonth()->endOfMonth()->format('Y-m-d');

    $supervisorCondition = '';
    $bindingsCurrent = [$start_date, $end_date];
    $bindingsLast = [$prevMonthStart, $prevMonthEnd];

    if ($supervisorId) {
        $supervisorCondition = " AND ps.other_party_id = ?";
        $bindingsCurrent[] = $supervisorId;
        $bindingsLast[] = $supervisorId;
    }

    $query = "
        SELECT COALESCE(SUM(v.amount), 0) AS total_voucher_amount
        FROM party_supervisors ps
        JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
        JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
        LEFT JOIN vouchers v ON v.party_id = ps.party_id
            AND v.voucher_date BETWEEN ? AND ?
            AND v.type = 75
        WHERE ps.end_date IS NULL
        {$supervisorCondition}
    ";

    $current = DB::connection('mysql_second')->selectOne($query, $bindingsCurrent);
    $last = DB::connection('mysql_second')->selectOne($query, $bindingsLast);

    $currentTotal = (float) ($current->total_voucher_amount ?? 0);
    $lastTotal = (float) ($last->total_voucher_amount ?? 0);

    $diff = round($currentTotal - $lastTotal, 2);
    $percentage = $lastTotal > 0
        ? round(($diff / $lastTotal) * 100, 2)
        : 0;

    return [
        'current_month_total' => $currentTotal,
        'last_month_total' => $lastTotal,
        'month_over_month_diff' => $diff,
        'month_over_month_percentage' => $percentage,
    ];
}


}

