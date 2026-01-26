<?php

namespace App\Http\Controllers;
// namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KamPerformanceController extends Controller
{





    // public function index(Request $request)
    // {
    //     $search = $request->search;

    //     // ---------------- DEFAULT DATE RANGE: LAST MONTH ----------------
    //     if (!$request->start_date || !$request->end_date) {
    //         $lastMonth = Carbon::now()->subMonth(); // previous month
    //         $start_date = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
    //         $end_date = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
    //     } else {
    //         $start_date = $request->start_date;
    //         $end_date = $request->end_date;
    //     }

    //     $query = "
    //         SELECT 
    //             pk.full_name AS current_supervisor,
    //             YEAR(v.voucher_date) AS voucher_year,
    //             MONTH(v.voucher_date) AS voucher_month_number,
                
    //             COUNT(DISTINCT ps.party_id) AS total_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
    //             COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount
    //         FROM party_supervisors ps
    //         JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
    //         JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
    //         LEFT JOIN vouchers v ON v.party_id = ps.party_id
    //             AND v.voucher_date BETWEEN ? AND ?
    //         LEFT JOIN (
    //             SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //             FROM party_supervisors
    //             GROUP BY party_id
    //         ) sup ON sup.party_id = ps.party_id
    //         WHERE ps.end_date IS NULL
    //         AND v.voucher_date IS NOT NULL
    //         " . ($search ? " AND (pk.full_name LIKE ? OR pa.full_name LIKE ?)" : "") . "
    //         GROUP BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //         ORDER BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //     ";

    //     // Bind parameters
    //     $bindings = [$start_date, $end_date];

    //     if ($search) {
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = "%{$search}%";
    //     }

    //     // Execute raw query
    //     $results = DB::connection('mysql_second')->select($query, $bindings);

    //     // Optional: manual pagination
    //     $perPage = $request->per_page ?? 10;
    //     $page = $request->page ?? 1;
    //     $offset = ($page - 1) * $perPage;

    //     $paginated = array_slice($results, $offset, $perPage);

    //     return response()->json([
    //         'data' => $paginated,
    //         'total' => count($results),
    //         'per_page' => $perPage,
    //         'current_page' => $page,
    //         'last_page' => ceil(count($results) / $perPage),
    //     ]);
    // }



    // public function index(Request $request)
    // {
    //     $search = $request->search;
        
    //     // DEFAULT DATE RANGE: LAST MONTH
    //     if (!$request->start_date || !$request->end_date) {
    //         $lastMonth = Carbon::now()->subMonth();
    //         $start_date = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
    //         $end_date = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
    //     } else {
    //         $start_date = $request->start_date;
    //         $end_date = $request->end_date;
    //     }

    //     // Get previous month's first day for voucher calculation
    //     $previousMonthStart = Carbon::createFromFormat('Y-m-d', $start_date)->subMonth()->startOfMonth()->format('Y-m-d');
    //     $previousMonthEnd = Carbon::createFromFormat('Y-m-d', $start_date)->subMonth()->endOfMonth()->format('Y-m-d');

    //     $query = "
    //         SELECT 
    //             pk.full_name AS current_supervisor,
    //             YEAR(v.voucher_date) AS voucher_year,
    //             MONTH(v.voucher_date) AS voucher_month_number,
    //             COUNT(DISTINCT ps.party_id) AS total_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
    //             COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
    //             COALESCE(SUM(
    //                 CASE 
    //                     WHEN sup.total_supervisors_ever > 1 THEN pv.previous_month_amount
    //                     ELSE 0 
    //                 END
    //             ), 0) AS transferred_previous_month_voucher_amount
    //         FROM party_supervisors ps
    //         JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
    //         JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
    //         LEFT JOIN vouchers v ON v.party_id = ps.party_id
    //             AND v.voucher_date BETWEEN ? AND ?
    //         LEFT JOIN (
    //             SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //             FROM party_supervisors
    //             GROUP BY party_id
    //         ) sup ON sup.party_id = ps.party_id
    //         LEFT JOIN (
    //             SELECT 
    //                 v_prev.party_id,
    //                 COALESCE(SUM(v_prev.amount), 0) AS previous_month_amount
    //             FROM vouchers v_prev
    //             WHERE v_prev.voucher_date BETWEEN ? AND ?
    //             GROUP BY v_prev.party_id
    //         ) pv ON pv.party_id = ps.party_id
    //         WHERE ps.end_date IS NULL
    //         AND v.voucher_date IS NOT NULL
    //         " . ($search ? " AND (pk.full_name LIKE ? OR pa.full_name LIKE ?)" : "") . "
    //         GROUP BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //         ORDER BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //     ";

    //     // Bind parameters
    //     $bindings = [
    //         $start_date, 
    //         $end_date,
    //         $previousMonthStart,
    //         $previousMonthEnd
    //     ];
        
    //     if ($search) {
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = "%{$search}%";
    //     }

    //     // Execute raw query
    //     $results = DB::connection('mysql_second')->select($query, $bindings);

    //     // Optional: manual pagination
    //     $perPage = $request->per_page ?? 10;
    //     $page = $request->page ?? 1;
    //     $offset = ($page - 1) * $perPage;
    //     $paginated = array_slice($results, $offset, $perPage);

    //     return response()->json([
    //         'data' => $paginated,
    //         'total' => count($results),
    //         'per_page' => $perPage,
    //         'current_page' => $page,
    //         'last_page' => ceil(count($results) / $perPage),
    //     ]);
    // }


    // public function index(Request $request)
    // {
    //     $search = $request->search;
        
    //     // DEFAULT DATE RANGE: LAST MONTH
    //     if (!$request->start_date || !$request->end_date) {
    //         $lastMonth = Carbon::now()->subMonth();
    //         $start_date = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
    //         $end_date = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
    //     } else {
    //         $start_date = $request->start_date;
    //         $end_date = $request->end_date;
    //     }

    //     $query = "
    //         SELECT 
    //             pk.full_name AS current_supervisor,
    //             ps.other_party_id AS supervisor_id,
    //             YEAR(v.voucher_date) AS voucher_year,
    //             MONTH(v.voucher_date) AS voucher_month_number,
    //             COUNT(DISTINCT ps.party_id) AS total_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
    //             COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
    //             COALESCE(SUM(
    //                 CASE 
    //                     WHEN sup.total_supervisors_ever > 1 THEN pv.previous_month_amount
    //                     ELSE 0 
    //                 END
    //             ), 0) AS transferred_previous_month_voucher_amount
    //         FROM party_supervisors ps
    //         JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
    //         JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
    //         LEFT JOIN vouchers v ON v.party_id = ps.party_id
    //             AND v.voucher_date BETWEEN ? AND ?
    //         LEFT JOIN (
    //             SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //             FROM party_supervisors
    //             GROUP BY party_id
    //         ) sup ON sup.party_id = ps.party_id
    //         LEFT JOIN (
    //             SELECT 
    //                 ps_prev.party_id,
    //                 COALESCE(SUM(v_prev.amount), 0) AS previous_month_amount
    //             FROM party_supervisors ps_prev
    //             LEFT JOIN vouchers v_prev ON v_prev.party_id = ps_prev.party_id
    //                 AND YEAR(v_prev.voucher_date) = YEAR(DATE_SUB(ps_prev.start_date, INTERVAL 1 MONTH))
    //                 AND MONTH(v_prev.voucher_date) = MONTH(DATE_SUB(ps_prev.start_date, INTERVAL 1 MONTH))
    //             WHERE ps_prev.end_date IS NULL
    //             GROUP BY ps_prev.party_id
    //         ) pv ON pv.party_id = ps.party_id
    //         WHERE ps.end_date IS NULL
    //         AND v.voucher_date IS NOT NULL
    //         " . ($search ? " AND (pk.full_name LIKE ? OR pa.full_name LIKE ?)" : "") . "
    //         GROUP BY pk.full_name, ps.other_party_id, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //         ORDER BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //     ";

    //     // Bind parameters
    //     $bindings = [
    //         $start_date, 
    //         $end_date
    //     ];
        
    //     if ($search) {
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = "%{$search}%";
    //     }

    //     // Execute raw query
    //     $results = DB::connection('mysql_second')->select($query, $bindings);

    //     // Optional: manual pagination
    //     $perPage = $request->per_page ?? 10;
    //     $page = $request->page ?? 1;
    //     $offset = ($page - 1) * $perPage;
    //     $paginated = array_slice($results, $offset, $perPage);

    //     return response()->json([
    //         'data' => $paginated,
    //         'total' => count($results),
    //         'per_page' => $perPage,
    //         'current_page' => $page,
    //         'last_page' => ceil(count($results) / $perPage),
    //     ]);
    // }


    // public function index(Request $request)
    // {
    //     $search = $request->search;
        
    //     // DEFAULT DATE RANGE: LAST MONTH
    //     if (!$request->start_date || !$request->end_date) {
    //         $lastMonth = Carbon::now()->subMonth();
    //         $start_date = $lastMonth->copy()->startOfMonth()->format('Y-m-d');
    //         $end_date = $lastMonth->copy()->endOfMonth()->format('Y-m-d');
    //     } else {
    //         $start_date = $request->start_date;
    //         $end_date = $request->end_date;
    //     }

    //                 $query = "
    //         SELECT 
    //             pk.full_name AS current_supervisor,
    //             ps.other_party_id AS supervisor_id,
    //             YEAR(v.voucher_date) AS voucher_year,
    //             MONTH(v.voucher_date) AS voucher_month_number,
    //             COUNT(DISTINCT ps.party_id) AS total_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
    //             COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
    //             COALESCE(SUM(
    //                 CASE 
    //                     WHEN sup.total_supervisors_ever > 1 THEN pv.previous_month_amount
    //                     ELSE 0 
    //                 END
    //             ), 0) AS transferred_previous_month_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) - COALESCE(SUM(
    //                 CASE 
    //                     WHEN sup.total_supervisors_ever > 1 THEN pv.previous_month_amount
    //                     ELSE 0 
    //                 END
    //             ), 0) AS transfer_up_down_voucher
    //         FROM party_supervisors ps
    //         JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
    //         JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
    //         LEFT JOIN vouchers v ON v.party_id = ps.party_id
    //             AND v.voucher_date BETWEEN ? AND ?
    //         LEFT JOIN (
    //             SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //             FROM party_supervisors
    //             GROUP BY party_id
    //         ) sup ON sup.party_id = ps.party_id
    //         LEFT JOIN (
    //             SELECT 
    //                 ps_prev.party_id,
    //                 COALESCE(SUM(v_prev.amount), 0) AS previous_month_amount
    //             FROM party_supervisors ps_prev
    //             LEFT JOIN vouchers v_prev ON v_prev.party_id = ps_prev.party_id
    //                 AND YEAR(v_prev.voucher_date) = YEAR(DATE_SUB(ps_prev.start_date, INTERVAL 1 MONTH))
    //                 AND MONTH(v_prev.voucher_date) = MONTH(DATE_SUB(ps_prev.start_date, INTERVAL 1 MONTH))
    //             WHERE ps_prev.end_date IS NULL
    //             GROUP BY ps_prev.party_id
    //         ) pv ON pv.party_id = ps.party_id
    //         WHERE ps.end_date IS NULL
    //         AND v.voucher_date IS NOT NULL
    //         " . ($search ? " AND (pk.full_name LIKE ? OR pa.full_name LIKE ?)" : "") . "
    //         GROUP BY pk.full_name, ps.other_party_id, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //         ORDER BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //     ";

    //     // Bind parameters
    //     $bindings = [
    //         $start_date, 
    //         $end_date
    //     ];
        
    //     if ($search) {
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = "%{$search}%";
    //     }

    //     // Execute raw query
    //     $results = DB::connection('mysql_second')->select($query, $bindings);

    //     // Optional: manual pagination
    //     $perPage = $request->per_page ?? 10;
    //     $page = $request->page ?? 1;
    //     $offset = ($page - 1) * $perPage;
    //     $paginated = array_slice($results, $offset, $perPage);

    //     return response()->json([
    //         'data' => $paginated,
    //         'total' => count($results),
    //         'per_page' => $perPage,
    //         'current_page' => $page,
    //         'last_page' => ceil(count($results) / $perPage),
    //     ]);
    // }



    // public function index(Request $request)
    // {
    //     $search = $request->search;
    //     $clientType = $request->client_type ?? 'All Client'; // all, self, transferred

    //     // DEFAULT DATE RANGE: CURRENT MONTH
    //     if (!$request->start_date || !$request->end_date) {
    //         $now = Carbon::now();
    //         $start_date = $now->copy()->startOfMonth()->format('Y-m-d');
    //         $end_date = $now->copy()->endOfMonth()->format('Y-m-d');
    //     } else {
    //         $start_date = $request->start_date;
    //         $end_date = $request->end_date;
    //     }

    //     // Build client type condition
    //     $clientTypeCondition = '';
    //     if ($clientType === 'Self Client') {
    //         $clientTypeCondition = ' AND sup.total_supervisors_ever = 1';
    //     } elseif ($clientType === 'Transferred Client') {
    //         $clientTypeCondition = ' AND sup.total_supervisors_ever > 1';
    //     }
    //     // else 'All Client' - no additional condition

    //     $query = "
    //         SELECT 
    //             pk.full_name AS current_supervisor,
    //             ps.other_party_id AS supervisor_id,
    //             YEAR(v.voucher_date) AS voucher_year,
    //             MONTH(v.voucher_date) AS voucher_month_number,
    //             COUNT(DISTINCT ps.party_id) AS total_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
    //             COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
    //             COALESCE(SUM(
    //                 CASE 
    //                     WHEN sup.total_supervisors_ever > 1 THEN pv.previous_month_amount
    //                     ELSE 0 
    //                 END
    //             ), 0) AS transferred_previous_month_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) - COALESCE(SUM(
    //                 CASE 
    //                     WHEN sup.total_supervisors_ever > 1 THEN pv.previous_month_amount
    //                     ELSE 0 
    //                 END
    //             ), 0) AS transfer_up_down_voucher
    //         FROM party_supervisors ps
    //         JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
    //         JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
    //         LEFT JOIN vouchers v ON v.party_id = ps.party_id
    //             AND v.voucher_date BETWEEN ? AND ?
    //         LEFT JOIN (
    //             SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //             FROM party_supervisors
    //             GROUP BY party_id
    //         ) sup ON sup.party_id = ps.party_id
    //         LEFT JOIN (
    //             SELECT 
    //                 ps_prev.party_id,
    //                 COALESCE(SUM(v_prev.amount), 0) AS previous_month_amount
    //             FROM party_supervisors ps_prev
    //             LEFT JOIN vouchers v_prev ON v_prev.party_id = ps_prev.party_id
    //                 AND YEAR(v_prev.voucher_date) = YEAR(DATE_SUB(ps_prev.start_date, INTERVAL 1 MONTH))
    //                 AND MONTH(v_prev.voucher_date) = MONTH(DATE_SUB(ps_prev.start_date, INTERVAL 1 MONTH))
    //             WHERE ps_prev.end_date IS NULL
    //             GROUP BY ps_prev.party_id
    //         ) pv ON pv.party_id = ps.party_id
    //         WHERE ps.end_date IS NULL
    //         AND v.voucher_date IS NOT NULL
    //         AND v.type = 75
    //         {$clientTypeCondition}
    //         " . ($search ? " AND (pk.full_name LIKE ? OR pa.full_name LIKE ?)" : "") . "
    //         GROUP BY pk.full_name, ps.other_party_id, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //         ORDER BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //     ";

    //     // Bind parameters
    //     $bindings = [
    //         $start_date, 
    //         $end_date
    //     ];
        
    //     if ($search) {
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = "%{$search}%";
    //     }

    //     // Execute raw query
    //     $results = DB::connection('mysql_second')->select($query, $bindings);

    //     // Optional: manual pagination
    //     $perPage = $request->per_page ?? 10;
    //     $page = $request->page ?? 1;
    //     $offset = ($page - 1) * $perPage;
    //     $paginated = array_slice($results, $offset, $perPage);

    //     return response()->json([
    //         'data' => $paginated,
    //         'total' => count($results),
    //         'per_page' => $perPage,
    //         'current_page' => $page,
    //         'last_page' => ceil(count($results) / $perPage),
    //     ]);
    // }


    // public function index(Request $request)
    // {
    //     $search = $request->search;
    //     $clientType = $request->client_type ?? 'All Client'; // all, self, transferred

    //     // DEFAULT DATE RANGE: CURRENT MONTH
    //     if (!$request->start_date || !$request->end_date) {
    //         $now = Carbon::now();
    //         $start_date = $now->copy()->startOfMonth()->format('Y-m-d');
    //         $end_date = $now->copy()->endOfMonth()->format('Y-m-d');
    //     } else {
    //         $start_date = $request->start_date;
    //         $end_date = $request->end_date;
    //     }

    //     // Calculate previous month date range
    //     $prevMonthStart = Carbon::parse($start_date)->subMonth()->startOfMonth()->format('Y-m-d');
    //     $prevMonthEnd = Carbon::parse($start_date)->subMonth()->endOfMonth()->format('Y-m-d');

    //     // Build client type condition
    //     $clientTypeCondition = '';
    //     if ($clientType === 'Self Client') {
    //         $clientTypeCondition = ' AND sup.total_supervisors_ever = 1';
    //     } elseif ($clientType === 'Transferred Client') {
    //         $clientTypeCondition = ' AND sup.total_supervisors_ever > 1';
    //     }
    //     // else 'All Client' - no additional condition

    //     $query = "
    //         SELECT 
    //             pk.full_name AS current_supervisor,
    //             ps.other_party_id AS supervisor_id,
    //             YEAR(v.voucher_date) AS voucher_year,
    //             MONTH(v.voucher_date) AS voucher_month_number,
    //             COUNT(DISTINCT ps.party_id) AS total_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
    //             COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
    //             COALESCE(pv.previous_month_amount, 0) AS transferred_previous_month_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) - COALESCE(pv.previous_month_amount, 0) AS transfer_up_down_voucher
    //         FROM party_supervisors ps
    //         JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
    //         JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
    //         LEFT JOIN vouchers v ON v.party_id = ps.party_id
    //             AND v.voucher_date BETWEEN ? AND ?
    //             AND v.type = 75
    //         LEFT JOIN (
    //             SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //             FROM party_supervisors
    //             GROUP BY party_id
    //         ) sup ON sup.party_id = ps.party_id
    //         LEFT JOIN (
    //             SELECT 
    //                 ps_prev.other_party_id AS supervisor_id,
    //                 COALESCE(SUM(CASE WHEN sup_prev.total_supervisors_ever > 1 THEN v_prev.amount ELSE 0 END), 0) AS previous_month_amount
    //             FROM party_supervisors ps_prev
    //             JOIN vouchers v_prev ON v_prev.party_id = ps_prev.party_id
    //                 AND v_prev.voucher_date BETWEEN ? AND ?
    //                 AND v_prev.type = 75
    //             LEFT JOIN (
    //                 SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //                 FROM party_supervisors
    //                 GROUP BY party_id
    //             ) sup_prev ON sup_prev.party_id = ps_prev.party_id
    //             WHERE ps_prev.end_date IS NULL
    //             GROUP BY ps_prev.other_party_id
    //         ) pv ON pv.supervisor_id = ps.other_party_id
    //         WHERE ps.end_date IS NULL
    //         AND v.voucher_date IS NOT NULL
    //         {$clientTypeCondition}
    //         " . ($search ? " AND (pk.full_name LIKE ? OR pa.full_name LIKE ?)" : "") . "
    //         GROUP BY pk.full_name, ps.other_party_id, YEAR(v.voucher_date), MONTH(v.voucher_date), pv.previous_month_amount
    //         ORDER BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
    //     ";

    //     // Bind parameters
    //     $bindings = [
    //         $start_date, 
    //         $end_date,
    //         $prevMonthStart,
    //         $prevMonthEnd
    //     ];
        
    //     if ($search) {
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = "%{$search}%";
    //     }

    //     // Execute raw query
    //     $results = DB::connection('mysql_second')->select($query, $bindings);

    //     // Optional: manual pagination
    //     $perPage = $request->per_page ?? 20;
    //     $page = $request->page ?? 1;
    //     $offset = ($page - 1) * $perPage;
    //     $paginated = array_slice($results, $offset, $perPage);

    //     return response()->json([
    //         'data' => $paginated,
    //         'total' => count($results),
    //         'per_page' => $perPage,
    //         'current_page' => $page,
    //         'last_page' => ceil(count($results) / $perPage),
    //     ]);
    // }

    public function index(Request $request)
    {
        $search = $request->search;
        $clientType = $request->client_type ?? 'All Client'; // all, self, transferred

        // DEFAULT DATE RANGE: CURRENT MONTH
        if (!$request->start_date || !$request->end_date) {
            $now = Carbon::now();
            $start_date = $now->copy()->startOfMonth()->format('Y-m-d');
            $end_date = $now->copy()->endOfMonth()->format('Y-m-d');
        } else {
            $start_date = $request->start_date;
            $end_date = $request->end_date;
        }

        // Calculate previous month date range
        $prevMonthStart = Carbon::parse($start_date)->subMonth()->startOfMonth()->format('Y-m-d');
        $prevMonthEnd = Carbon::parse($start_date)->subMonth()->endOfMonth()->format('Y-m-d');

        // Build client type condition
        $clientTypeCondition = '';
        if ($clientType === 'Self Client') {
            $clientTypeCondition = ' AND sup.total_supervisors_ever = 1';
        } elseif ($clientType === 'Transferred Client') {
            $clientTypeCondition = ' AND sup.total_supervisors_ever > 1';
        }
        // else 'All Client' - no additional condition

        $query = "
            SELECT 
                pk.full_name AS current_supervisor,
                ps.other_party_id AS supervisor_id,
                YEAR(v.voucher_date) AS voucher_year,
                MONTH(v.voucher_date) AS voucher_month_number,
                COUNT(DISTINCT ps.party_id) AS total_client_count,
                COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
                COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
                COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
                COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
                COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
                COALESCE(pv.previous_month_amount, 0) AS transferred_previous_month_voucher_amount,
                COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) - COALESCE(pv.previous_month_amount, 0) AS transfer_up_down_voucher
            FROM party_supervisors ps
            JOIN parties pa ON pa.id = ps.party_id AND pa.type = 'customer' AND pa.inactive = 0
            JOIN parties pk ON pk.id = ps.other_party_id AND pk.inactive = 0
            LEFT JOIN vouchers v ON v.party_id = ps.party_id
                AND v.voucher_date BETWEEN ? AND ?
                AND v.type = 75
            LEFT JOIN (
                SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
                FROM party_supervisors
                GROUP BY party_id
            ) sup ON sup.party_id = ps.party_id
            LEFT JOIN (
                SELECT 
                    ps_prev.other_party_id AS supervisor_id,
                    COALESCE(SUM(CASE WHEN sup_prev.total_supervisors_ever > 1 THEN v_prev.amount ELSE 0 END), 0) AS previous_month_amount
                FROM party_supervisors ps_prev
                JOIN vouchers v_prev ON v_prev.party_id = ps_prev.party_id
                    AND v_prev.voucher_date BETWEEN ? AND ?
                    AND v_prev.type = 75
                LEFT JOIN (
                    SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
                    FROM party_supervisors
                    GROUP BY party_id
                ) sup_prev ON sup_prev.party_id = ps_prev.party_id
                WHERE ps_prev.end_date IS NULL
                GROUP BY ps_prev.other_party_id
            ) pv ON pv.supervisor_id = ps.other_party_id
            WHERE ps.end_date IS NULL
            AND v.voucher_date IS NOT NULL
            {$clientTypeCondition}
            " . ($search ? " AND (pk.full_name LIKE ? OR pa.full_name LIKE ?)" : "") . "
            GROUP BY pk.full_name, ps.other_party_id, YEAR(v.voucher_date), MONTH(v.voucher_date), pv.previous_month_amount
            ORDER BY pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)
        ";

        // Bind parameters
        $bindings = [
            $start_date, 
            $end_date,
            $prevMonthStart,
            $prevMonthEnd
        ];
        
        if ($search) {
            $bindings[] = "%{$search}%";
            $bindings[] = "%{$search}%";
        }

        // Execute raw query
        $results = DB::connection('mysql_second')->select($query, $bindings);

        // Fetch sales targets from the primary database (mysql connection)
        $salesTargets = DB::connection('mysql')
            ->select("
                SELECT 
                    kam_id,
                    YEAR(target_month) as target_year,
                    MONTH(target_month) as target_month_number,
                    amount
                FROM sales_targets
            ");

        // Create a lookup map for sales targets: kam_id_year_month => amount
        $targetMap = [];
        foreach ($salesTargets as $target) {
            $key = $target->kam_id . '_' . $target->target_year . '_' . $target->target_month_number;
            $targetMap[$key] = $target->amount;
        }

        // Merge sales targets with results
        foreach ($results as $result) {
            $targetKey = $result->supervisor_id . '_' . $result->voucher_year . '_' . $result->voucher_month_number;
            
            $result->target_amount = $targetMap[$targetKey] ?? '0.00';
        }

        // Optional: manual pagination
        $perPage = $request->per_page ?? 200;
        $page = $request->page ?? 1;
        $offset = ($page - 1) * $perPage;
        $paginated = array_slice($results, $offset, $perPage);

        return response()->json([
            'data' => $paginated,
            'total' => count($results),
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil(count($results) / $perPage),
        ]);
    }


    public function getTransferredPreviousMonthBreakdown(Request $request)
    {
        $supervisor_id = $request->supervisor_id; // e.g., 3777 for Atikul Islam
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 200;
        $offset = ($page - 1) * $perPage;

        $query = "
            SELECT 
                ps.party_id,
                pa.full_name AS client_name,
                ps.start_date AS supervisor_start_date,
                v_prev.voucher_date,
                v_prev.amount AS voucher_amount,
                COUNT(*) OVER (PARTITION BY pa.full_name) AS client_voucher_count,
                SUM(v_prev.amount) OVER (PARTITION BY pa.full_name) AS client_total_amount,
                SUM(v_prev.amount) OVER () AS grand_total
            FROM party_supervisors ps
            JOIN parties pa ON ps.party_id = pa.id AND pa.type = 'customer' AND pa.inactive = 0
            JOIN parties pk ON ps.other_party_id = pk.id
            LEFT JOIN (
                SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
                FROM party_supervisors
                GROUP BY party_id
            ) sup ON sup.party_id = ps.party_id
            LEFT JOIN vouchers v_prev ON v_prev.party_id = ps.party_id
                AND YEAR(v_prev.voucher_date) = YEAR(DATE_SUB(ps.start_date, INTERVAL 1 MONTH))
                AND MONTH(v_prev.voucher_date) = MONTH(DATE_SUB(ps.start_date, INTERVAL 1 MONTH))
            WHERE ps.end_date IS NULL
            AND v_prev.type = 75
            AND ps.other_party_id = ?
            AND sup.total_supervisors_ever > 1
            AND v_prev.amount > 0
            ORDER BY pa.full_name, v_prev.voucher_date DESC
            LIMIT ? OFFSET ?
        ";

        // Get breakdown data
        $breakdownData = DB::connection('mysql_second')->select($query, [
            $supervisor_id,
            $perPage,
            $offset
        ]);

        // Get total count
        $countQuery = "
            SELECT COUNT(DISTINCT v_prev.id) as total_vouchers
            FROM party_supervisors ps
            JOIN parties pa ON ps.party_id = pa.id AND pa.type = 'customer' AND pa.inactive = 0
            LEFT JOIN (
                SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
                FROM party_supervisors
                GROUP BY party_id
            ) sup ON sup.party_id = ps.party_id
            LEFT JOIN vouchers v_prev ON v_prev.party_id = ps.party_id
                AND YEAR(v_prev.voucher_date) = YEAR(DATE_SUB(ps.start_date, INTERVAL 1 MONTH))
                AND MONTH(v_prev.voucher_date) = MONTH(DATE_SUB(ps.start_date, INTERVAL 1 MONTH))
            WHERE ps.end_date IS NULL
            AND ps.other_party_id = ?
            AND sup.total_supervisors_ever > 1
            AND v_prev.amount > 0
        ";

        $countResult = DB::connection('mysql_second')->select($countQuery, [$supervisor_id]);
        $totalVouchers = $countResult[0]->total_vouchers ?? 0;

        // Get summary by client
        $summaryQuery = "
            SELECT 
                ps.party_id,
                pa.full_name AS client_name,
                ps.start_date AS supervisor_start_date,
                COUNT(DISTINCT v_prev.id) AS voucher_count,
                COALESCE(SUM(v_prev.amount), 0) AS client_total_amount,
                GROUP_CONCAT(DISTINCT DATE_FORMAT(v_prev.voucher_date, '%Y-%m-%d') ORDER BY v_prev.voucher_date DESC SEPARATOR ', ') AS voucher_dates
            FROM party_supervisors ps
            JOIN parties pa ON ps.party_id = pa.id AND pa.type = 'customer' AND pa.inactive = 0
            LEFT JOIN (
                SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
                FROM party_supervisors
                GROUP BY party_id
            ) sup ON sup.party_id = ps.party_id
            LEFT JOIN vouchers v_prev ON v_prev.party_id = ps.party_id
                AND YEAR(v_prev.voucher_date) = YEAR(DATE_SUB(ps.start_date, INTERVAL 1 MONTH))
                AND MONTH(v_prev.voucher_date) = MONTH(DATE_SUB(ps.start_date, INTERVAL 1 MONTH))
            WHERE ps.end_date IS NULL
            AND ps.other_party_id = ?
            AND sup.total_supervisors_ever > 1
            AND v_prev.amount > 0
            GROUP BY ps.party_id, pa.full_name, ps.start_date
            ORDER BY client_total_amount DESC
        ";

        $summaryData = DB::connection('mysql_second')->select($summaryQuery, [$supervisor_id]);

        $grandTotal = 0;
        if (!empty($breakdownData)) {
            $grandTotal = $breakdownData[0]->grand_total ?? 0;
        }

        return response()->json([
            'summary' => [
                'supervisor_id' => $supervisor_id,
                'total_transferred_clients' => count($summaryData),
                'total_vouchers' => $totalVouchers,
                'grand_total_amount' => $grandTotal,
                'client_summary' => $summaryData
            ],
            'detailed_breakdown' => [
                'data' => $breakdownData,
                'per_page' => $perPage,
                'current_page' => $page,
                'total' => $totalVouchers,
                'last_page' => ceil($totalVouchers / $perPage)
            ]
        ]);
    }


}
