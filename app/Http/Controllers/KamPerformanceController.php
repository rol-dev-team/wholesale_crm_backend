<?php

namespace App\Http\Controllers;
// namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class KamPerformanceController extends Controller
{

   


    // public function index(Request $request)
    // {
    //     $search = $request->search;
    //     $clientType = $request->client_type ?? 'All Client';
    //     $viewMode = $request->view_mode ?? 'monthly';

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

    //     // SEARCH CONDITION (extended for supervisor_id)
    //     $searchCondition = '';
    //     if ($search) {
    //         $searchCondition = "
    //             AND (
    //                 pk.full_name LIKE ?
    //                 OR pa.full_name LIKE ?
    //                 OR ps.other_party_id = ?
    //             )
    //         ";
    //     }

    //     // Conditional SELECT and GROUP BY based on view mode
    //     if ($viewMode === 'yearly') {
    //         $dateGrouping = "YEAR(v.voucher_date) AS voucher_year";
    //         $groupBy = "pk.full_name, ps.other_party_id, YEAR(v.voucher_date)";
    //         $orderBy = "pk.full_name, YEAR(v.voucher_date)";
    //     } else {
    //         // monthly mode (default)
    //         $dateGrouping = "YEAR(v.voucher_date) AS voucher_year, MONTH(v.voucher_date) AS voucher_month_number";
    //         $groupBy = "pk.full_name, ps.other_party_id, YEAR(v.voucher_date), MONTH(v.voucher_date)";
    //         $orderBy = "pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)";
    //     }

    //     $query = "
    //         SELECT 
    //             pk.full_name AS current_supervisor,
    //             ps.other_party_id AS supervisor_id,
    //             {$dateGrouping},
    //             COUNT(DISTINCT ps.party_id) AS total_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
    //             COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
    //             COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
    //             COALESCE(pv.previous_month_transferred_amount, 0) AS transferred_previous_month_voucher_amount,
    //             COALESCE(pv.previous_month_self_amount, 0) AS self_previous_month_voucher_amount,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0)
    //                 - COALESCE(pv.previous_month_transferred_amount, 0) AS transfer_up_down_voucher,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0)
    //                 - COALESCE(pv.previous_month_self_amount, 0) AS self_up_down_voucher
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
    //                 COALESCE(SUM(CASE WHEN sup_prev.total_supervisors_ever > 1 THEN v_prev.amount ELSE 0 END), 0) AS previous_month_transferred_amount,
    //                 COALESCE(SUM(CASE WHEN sup_prev.total_supervisors_ever = 1 THEN v_prev.amount ELSE 0 END), 0) AS previous_month_self_amount
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
    //         {$searchCondition}
    //         GROUP BY {$groupBy}, pv.previous_month_transferred_amount, pv.previous_month_self_amount
    //         ORDER BY {$orderBy}
    //     ";

    //     // Bind parameters
    //     $bindings = [
    //         $start_date,
    //         $end_date,
    //         $prevMonthStart,
    //         $prevMonthEnd,
    //     ];

    //     if ($search) {
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = "%{$search}%";
    //         $bindings[] = $search;
    //     }

    //     // Execute raw query
    //     $results = DB::connection('mysql_second')->select($query, $bindings);

    //     // ✅ SEPARATE QUERY FOR ACCURATE TOTALS (no supervisor duplication)
    //     $totalQuery = "
    //         SELECT 
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_total,
    //             COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_total,
    //             COALESCE(SUM(v.amount), 0) AS grand_total
    //         FROM vouchers v
    //         LEFT JOIN (
    //             SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
    //             FROM party_supervisors
    //             GROUP BY party_id
    //         ) sup ON sup.party_id = v.party_id
    //         WHERE v.type = 75 
    //             AND v.voucher_date BETWEEN ? AND ?
    //     ";

    //     $totalBindings = [$start_date, $end_date];

    //     if ($search) {
    //         // Add search condition to totals query if needed
    //         $totalQuery .= " AND v.party_id IN (
    //             SELECT ps.party_id FROM party_supervisors ps
    //             JOIN parties pa ON pa.id = ps.party_id
    //             JOIN parties pk ON pk.id = ps.other_party_id
    //             WHERE pk.full_name LIKE ? OR pa.full_name LIKE ? OR ps.other_party_id = ?
    //         )";
    //         $totalBindings[] = "%{$search}%";
    //         $totalBindings[] = "%{$search}%";
    //         $totalBindings[] = $search;
    //     }

    //     if ($clientType !== 'All Client') {
    //         if ($clientType === 'Self Client') {
    //             $totalQuery .= " AND sup.total_supervisors_ever = 1";
    //         } elseif ($clientType === 'Transferred Client') {
    //             $totalQuery .= " AND sup.total_supervisors_ever > 1";
    //         }
    //     }

    //     $totals = DB::connection('mysql_second')->selectOne($totalQuery, $totalBindings);

    //     // Fetch sales targets from primary DB
    //     $salesTargets = DB::connection('mysql')->select("
    //         SELECT 
    //             kam_id,
    //             YEAR(target_month) as target_year,
    //             MONTH(target_month) as target_month_number,
    //             amount
    //         FROM sales_targets
    //     ");

    //     // Create lookup map
    //     $targetMap = [];
    //     foreach ($salesTargets as $target) {
    //         if ($viewMode === 'yearly') {
    //             // For yearly, sum all months' targets
    //             $key = $target->kam_id . '_' . $target->target_year;
    //             $targetMap[$key] = ($targetMap[$key] ?? 0) + $target->amount;
    //         } else {
    //             // For monthly, keep specific month
    //             $key = $target->kam_id . '_' . $target->target_year . '_' . $target->target_month_number;
    //             $targetMap[$key] = $target->amount;
    //         }
    //     }

    //     // Merge sales targets
    //     foreach ($results as $result) {
    //         if ($viewMode === 'yearly') {
    //             $targetKey = $result->supervisor_id . '_' . $result->voucher_year;
    //         } else {
    //             $targetKey = $result->supervisor_id . '_' . $result->voucher_year . '_' . $result->voucher_month_number;
    //         }
    //         $result->target_amount = $targetMap[$targetKey] ?? '0.00';
    //     }

    //     // Manual pagination
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
    //         // ✅ ADD ACCURATE TOTALS TO RESPONSE
    //         'totals' => [
    //             'transferred_total' => $totals->transferred_total ?? 0,
    //             'self_total' => $totals->self_total ?? 0,
    //             'grand_total' => $totals->grand_total ?? 0,
    //         ]
    //     ]);
    // }



    public function getKamList(Request $request)
    {
        try {
            $user = Auth::user();
            $userRole = $user->role; // Assuming role is stored in user table
            $userId = $user->id; // or user_party_id depending on your structure

            // Get user's party_id if needed
            $userPartyId = DB::connection('mysql_second')
                ->table('parties')
                ->where('id', $userId)
                ->value('id');

            $kams = [];

            if (in_array($userRole, ['super_admin', 'management'])) {
                // Get all KAMs
                $kams = DB::connection('mysql_second')->select("
                    SELECT DISTINCT 
                        e.employee_id AS kam_id,
                        pa.full_name AS kam_name
                    FROM employments e
                    JOIN parties pa ON e.employee_id = pa.id
                    JOIN departments d ON e.department_id = d.id
                    JOIN designations ds ON e.designation_id = ds.id
                    WHERE pa.type IS NULL 
                    AND pa.subtype = 2 
                    AND pa.role = 8
                    AND d.id = 6 
                    AND pa.inactive = 0
                    ORDER BY pa.full_name ASC
                ");
            } elseif ($userRole === 'supervisor') {
                // Get KAMs under this supervisor
                $kams = DB::connection('mysql_second')->select("
                    SELECT DISTINCT 
                        e.employee_id AS kam_id,
                        pa.full_name AS kam_name
                    FROM employments e
                    JOIN parties pa ON e.employee_id = pa.id
                    JOIN departments d ON e.department_id = d.id
                    JOIN designations ds ON e.designation_id = ds.id
                    LEFT JOIN parties p ON p.id = e.manager_1
                    WHERE pa.type IS NULL 
                    AND pa.subtype = 2 
                    AND pa.role = 8
                    AND d.id = 6 
                    AND pa.inactive = 0
                    AND e.manager_1 = ?
                    ORDER BY pa.full_name ASC
                ", [$userPartyId]);
            }
            // For KAM role, return empty array

            return response()->json([
                'status' => true,
                'message' => 'KAM list fetched successfully',
                'data' => $kams
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    
    public function supervisorWiseKamList($supervisor_id)
    {
        try {
            $supervisorDetails = DB::connection('mysql_second')
                ->table('parties')
                ->where('id', $supervisor_id)
                ->first(['id', 'full_name']);

            $kams = DB::connection('mysql_second')->select("
                SELECT DISTINCT 
                    e.employee_id AS kam_id,
                    pa.full_name AS kam_name,
                    e.employee_code,
                    p.full_name AS supervisor_name,
                    p.id AS supervisor_id
                FROM employments e
                JOIN parties pa ON e.employee_id = pa.id
                JOIN departments d ON e.department_id = d.id
                JOIN designations ds ON e.designation_id = ds.id
                LEFT JOIN parties p ON p.id = e.manager_1
                WHERE pa.type IS NULL 
                AND pa.subtype = 2 
                AND pa.role = 8
                AND d.id = 6 
                AND pa.inactive = 0
                AND e.manager_1 = ?
                ORDER BY pa.full_name ASC
            ", [$supervisor_id]);

            return response()->json([
                'status' => true,
                'message' => 'KAM list fetched successfully',
                'supervisor' => $supervisorDetails,
                'data' => $kams
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

   
    public function index(Request $request)
    {
        try {
            // Get current user
            $user = Auth::user();
            
            // ✅ FIX: Handle null user
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized: Please login first',
                    'data' => []
                ], 401);
            }

            $userRole = $user->role ?? 'super_admin';
            $userPartyId = $user->id;

            $search = $request->search;
            $clientType = $request->client_type ?? 'All Client';
            $viewMode = $request->view_mode ?? 'monthly';

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

            // SEARCH CONDITION
            $searchCondition = '';
            $searchBindings = [];
            if ($search && $search !== 'all') {
                $searchCondition = "
                    AND (
                        pk.full_name LIKE ?
                        OR pa.full_name LIKE ?
                        OR ps.other_party_id = ?
                    )
                ";
                $searchBindings = ["%{$search}%", "%{$search}%", $search];
            }

            // ROLE-BASED FILTERING
            $roleCondition = '';
            $roleBindings = [];

            if ($userRole === 'supervisor') {
                // Supervisor sees only KAMs under them
                $roleCondition = ' AND ps.other_party_id IN (
                    SELECT e.employee_id
                    FROM employments e
                    WHERE e.manager_1 = ?
                )';
                $roleBindings = [$userPartyId];
            } elseif ($userRole === 'kam') {
                // KAM sees only their own data
                $roleCondition = ' AND ps.other_party_id = ?';
                $roleBindings = [$userPartyId];
            }
            // super_admin and management see all data (no restriction)

            // Conditional SELECT and GROUP BY based on view mode
            if ($viewMode === 'yearly') {
                $dateGrouping = "YEAR(v.voucher_date) AS voucher_year";
                $groupBy = "pk.full_name, ps.other_party_id, YEAR(v.voucher_date)";
                $orderBy = "pk.full_name, YEAR(v.voucher_date)";
            } else {
                // monthly mode (default)
                $dateGrouping = "YEAR(v.voucher_date) AS voucher_year, MONTH(v.voucher_date) AS voucher_month_number";
                $groupBy = "pk.full_name, ps.other_party_id, YEAR(v.voucher_date), MONTH(v.voucher_date)";
                $orderBy = "pk.full_name, YEAR(v.voucher_date), MONTH(v.voucher_date)";
            }

            $query = "
                SELECT 
                    pk.full_name AS current_supervisor,
                    ps.other_party_id AS supervisor_id,
                    {$dateGrouping},
                    COUNT(DISTINCT ps.party_id) AS total_client_count,
                    COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever > 1 THEN ps.party_id ELSE NULL END) AS transferred_client_count,
                    COUNT(DISTINCT CASE WHEN sup.total_supervisors_ever = 1 THEN ps.party_id ELSE NULL END) AS self_client_count,
                    COALESCE(SUM(v.amount), 0) AS total_voucher_amount,
                    COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_client_voucher_amount,
                    COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_client_voucher_amount,
                    COALESCE(pv.previous_month_transferred_amount, 0) AS transferred_previous_month_voucher_amount,
                    COALESCE(pv.previous_month_self_amount, 0) AS self_previous_month_voucher_amount,
                    COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0)
                        - COALESCE(pv.previous_month_transferred_amount, 0) AS transfer_up_down_voucher,
                    COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0)
                        - COALESCE(pv.previous_month_self_amount, 0) AS self_up_down_voucher
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
                        COALESCE(SUM(CASE WHEN sup_prev.total_supervisors_ever > 1 THEN v_prev.amount ELSE 0 END), 0) AS previous_month_transferred_amount,
                        COALESCE(SUM(CASE WHEN sup_prev.total_supervisors_ever = 1 THEN v_prev.amount ELSE 0 END), 0) AS previous_month_self_amount
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
                {$searchCondition}
                {$roleCondition}
                GROUP BY {$groupBy}, pv.previous_month_transferred_amount, pv.previous_month_self_amount
                ORDER BY {$orderBy}
            ";

            // Bind parameters
            $bindings = array_merge(
                [$start_date, $end_date, $prevMonthStart, $prevMonthEnd],
                $searchBindings,
                $roleBindings
            );

            // Execute raw query
            $results = DB::connection('mysql_second')->select($query, $bindings);

            // ✅ SEPARATE QUERY FOR ACCURATE TOTALS
            $totalQuery = "
                SELECT 
                    COALESCE(SUM(CASE WHEN sup.total_supervisors_ever > 1 THEN v.amount ELSE 0 END), 0) AS transferred_total,
                    COALESCE(SUM(CASE WHEN sup.total_supervisors_ever = 1 THEN v.amount ELSE 0 END), 0) AS self_total,
                    COALESCE(SUM(v.amount), 0) AS grand_total
                FROM vouchers v
                LEFT JOIN (
                    SELECT party_id, COUNT(DISTINCT other_party_id) AS total_supervisors_ever
                    FROM party_supervisors
                    GROUP BY party_id
                ) sup ON sup.party_id = v.party_id
                WHERE v.type = 75 
                    AND v.voucher_date BETWEEN ? AND ?
            ";

            $totalBindings = [$start_date, $end_date];

            if ($search && $search !== 'all') {
                // Add search condition to totals query if needed
                $totalQuery .= " AND v.party_id IN (
                    SELECT ps.party_id FROM party_supervisors ps
                    JOIN parties pa ON pa.id = ps.party_id
                    JOIN parties pk ON pk.id = ps.other_party_id
                    WHERE pk.full_name LIKE ? OR pa.full_name LIKE ? OR ps.other_party_id = ?
                )";
                $totalBindings = array_merge($totalBindings, ["%{$search}%", "%{$search}%", $search]);
            }

            if ($clientType !== 'All Client') {
                if ($clientType === 'Self Client') {
                    $totalQuery .= " AND sup.total_supervisors_ever = 1";
                } elseif ($clientType === 'Transferred Client') {
                    $totalQuery .= " AND sup.total_supervisors_ever > 1";
                }
            }

            // Add role condition to totals query
            if ($userRole === 'supervisor') {
                $totalQuery .= " AND v.party_id IN (
                    SELECT ps.party_id FROM party_supervisors ps
                    WHERE ps.other_party_id IN (
                        SELECT e.employee_id FROM employments e WHERE e.manager_1 = ?
                    )
                )";
                $totalBindings[] = $userPartyId;
            } elseif ($userRole === 'kam') {
                $totalQuery .= " AND v.party_id IN (
                    SELECT ps.party_id FROM party_supervisors ps WHERE ps.other_party_id = ?
                )";
                $totalBindings[] = $userPartyId;
            }

            $totals = DB::connection('mysql_second')->selectOne($totalQuery, $totalBindings);

            // Fetch sales targets from primary DB
            $salesTargets = DB::connection('mysql')->select("
                SELECT 
                    kam_id,
                    YEAR(target_month) as target_year,
                    MONTH(target_month) as target_month_number,
                    amount
                FROM sales_targets
            ");

            // Create lookup map
            $targetMap = [];
            foreach ($salesTargets as $target) {
                if ($viewMode === 'yearly') {
                    // For yearly, sum all months' targets
                    $key = $target->kam_id . '_' . $target->target_year;
                    $targetMap[$key] = ($targetMap[$key] ?? 0) + $target->amount;
                } else {
                    // For monthly, keep specific month
                    $key = $target->kam_id . '_' . $target->target_year . '_' . $target->target_month_number;
                    $targetMap[$key] = $target->amount;
                }
            }

            // Merge sales targets
            foreach ($results as $result) {
                if ($viewMode === 'yearly') {
                    $targetKey = $result->supervisor_id . '_' . $result->voucher_year;
                } else {
                    $targetKey = $result->supervisor_id . '_' . $result->voucher_year . '_' . $result->voucher_month_number;
                }
                $result->target_amount = $targetMap[$targetKey] ?? '0.00';
            }

            // Manual pagination
            $perPage = $request->per_page ?? 10;
            $page = $request->page ?? 1;
            $offset = ($page - 1) * $perPage;
            $paginated = array_slice($results, $offset, $perPage);

            return response()->json([
                'status' => true,
                'data' => $paginated,
                'total' => count($results),
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil(count($results) / $perPage),
                'totals' => [
                    'transferred_total' => $totals->transferred_total ?? 0,
                    'self_total' => $totals->self_total ?? 0,
                    'grand_total' => $totals->grand_total ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ]
            ], 500);
        }
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
