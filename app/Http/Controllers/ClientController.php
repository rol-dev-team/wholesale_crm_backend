<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    // public function index(Request $request)
    // {
    //     $customers = DB::connection('mysql_second')->select("
    //         SELECT DISTINCT pa.full_name, pa.code as party_code,pa.mobile,pa.email, pa.inactive
    //         ,'Dhaka' as division, 'Banani' as zone, 'Sam' as assigned_kam
    //         FROM parties pa 
    //         WHERE pa.type = 'customer';
    //     ");

    //     // return response()->json($customers);
    //     return response()->json([
    //         'data' => $customers
    //     ]);
    // }


    // public function index(Request $request)
    // {
    //     // $customers = DB::connection('mysql_second')->select("
    //     //     SELECT DISTINCT pa.full_name, pa.code as party_code,pa.mobile,pa.email, pa.inactive,'Dhaka' as division, 
    //     //         'Banani' as zone, 'Sam' as assigned_kam
    //     //     FROM parties pa 
    //     //     WHERE pa.type = 'customer'
    //     //     AND pa.inactive = 0;
    //     // ");



    //     $customers = DB::connection('mysql_second')->select("
    //         SELECT DISTINCT pa.full_name, pa.code as party_code,pa.mobile,pa.email, pa.inactive,b.full_name AS division
    //     ,pk.full_name as assigned_kam,'ROL' as zone
    //     FROM party_supervisors ps
    //     JOIN employments e ON e.employee_id = ps.other_party_id
    //     JOIN parties pa ON ps.party_id = pa.id AND pa.type = 'customer' 
    //     JOIN parties pk ON ps.other_party_id = pk.id 
    //     JOIN branches b ON b.id = e.employment_branch_id
    //     JOIN departments d ON e.department_id = d.id
    //     WHERE pa.inactive = 0
    //     ORDER BY pk.full_name;
    //     ");


        

        
    //     $totalClients = DB::connection('mysql_second')
    //             ->table('parties as pa')
    //             ->where('pa.type', 'customer')
    //             ->where('pa.inactive', 0)
    //             ->distinct('pa.id')
    //             ->count('pa.id');

    //     $totalKams = DB::connection('mysql_second')
    //         ->table('party_supervisors as ps')
    //         ->join('parties as pk', 'ps.other_party_id', '=', 'pk.id')
    //         ->where('pk.inactive', 0)
    //         ->distinct('pk.id')
    //         ->count('pk.id');


    //     $totalDivisions = DB::connection('mysql_second')
    //         ->table('party_supervisors as ps')
    //         ->join('employments as e', 'e.employee_id', '=', 'ps.other_party_id')
    //         ->join('branches as b', 'b.id', '=', 'e.employment_branch_id')
    //         ->distinct('b.id')
    //         ->count('b.id');


    //     $totalZones = DB::connection('mysql_second')->table('parties')
    //         ->where('type', 'customer')
    //         ->distinct()
    //         ->count(DB::raw("'ROL'")); 


    //     return response()->json([
    //         'data' => $customers,
    //         'counts' => [
    //             'clients'   => $totalClients,
    //             'kams'      => $totalKams,
    //             'divisions' => $totalDivisions,
    //             'zones'     => $totalZones
    //         ]
    //     ]);

    // }


    public function index(Request $request)
    {
        try {
            // Get current user
            $user = Auth::user();
            
            // ✅ Handle null user
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized: Please login first',
                    'data' => [],
                    'counts' => [
                        'clients' => 0,
                        'kams' => 0,
                        'divisions' => 0,
                        'zones' => 0
                    ]
                ], 401);
            }

            $userRole = $user->role ?? 'super_admin';
            $userPartyId = $user->default_kam_id;

            // Get filter parameters
            $search = $request->search;
            $divisionFilter = $request->division;
            $kamFilter = $request->kam;

            // ===== ROLE-BASED FILTERING =====
            $roleCondition = '';
            $roleBindings = [];

            if ($userRole === 'supervisor') {
                if (!$kamFilter || $kamFilter === 'all') {
                    // ✅ DEFAULT: supervisor + all their KAMs
                    $roleCondition = '
                        AND ps.other_party_id IN (
                            SELECT e.employee_id
                            FROM employments e
                            WHERE e.manager_1 = ?
                            UNION
                            SELECT ?
                        )
                    ';
                    $roleBindings = [$userPartyId, $userPartyId];
                } else {
                    // ✅ Specific KAM selected
                    $roleCondition = ' AND ps.other_party_id = ?';
                    $roleBindings = [$kamFilter];
                }
            } elseif ($userRole === 'kam') {
                // ✅ KAM always sees only their own clients
                $roleCondition = ' AND ps.other_party_id = ?';
                $roleBindings = [$userPartyId];
            }
            // super_admin and management see all data (no restriction)

            // ===== SEARCH CONDITION =====
            $searchCondition = '';
            $searchBindings = [];
            if ($search && $search !== 'all') {
                $searchCondition = "
                    AND (
                        pa.full_name LIKE ?
                        OR pa.code LIKE ?
                        OR pk.full_name LIKE ?
                    )
                ";
                $searchBindings = ["%{$search}%", "%{$search}%", "%{$search}%"];
            }

            // ===== DIVISION FILTER =====
            $divisionCondition = '';
            $divisionBindings = [];
            if ($divisionFilter && $divisionFilter !== 'all') {
                $divisionCondition = ' AND b.full_name = ?';
                $divisionBindings = [$divisionFilter];
            }

            // ===== KAM FILTER (for super_admin/management) =====
            $kamCondition = '';
            $kamBindings = [];
            if ($kamFilter && $kamFilter !== 'all' && $userRole !== 'kam' && $userRole !== 'supervisor') {
                $kamCondition = ' AND ps.other_party_id = ?';
                $kamBindings = [$kamFilter];
            }

            // ===== MAIN QUERY =====
            $query = "
                SELECT DISTINCT 
                    pa.full_name, 
                    pa.code as party_code,
                    pa.mobile,
                    pa.email, 
                    pa.inactive,
                    b.full_name AS division,
                    pk.full_name as assigned_kam,
                    'ROL' as zone
                FROM party_supervisors ps
                JOIN employments e ON e.employee_id = ps.other_party_id
                JOIN parties pa ON ps.party_id = pa.id AND pa.type = 'customer' 
                JOIN parties pk ON ps.other_party_id = pk.id 
                JOIN branches b ON b.id = e.employment_branch_id
                JOIN departments d ON e.department_id = d.id
                WHERE pa.inactive = 0
                {$roleCondition}
                {$searchCondition}
                {$divisionCondition}
                {$kamCondition}
                ORDER BY pk.full_name
            ";

            // Merge all bindings
            $bindings = array_merge($roleBindings, $searchBindings, $divisionBindings, $kamBindings);

            // Execute query
            $customers = DB::connection('mysql_second')->select($query, $bindings);

            // ===== COUNTS WITH ROLE-BASED FILTERING =====
            
            // Total Clients
            $clientCountQuery = "
                SELECT COUNT(DISTINCT pa.id) as total
                FROM party_supervisors ps
                JOIN parties pa ON ps.party_id = pa.id AND pa.type = 'customer' 
                WHERE pa.inactive = 0
                {$roleCondition}
            ";
            $totalClients = DB::connection('mysql_second')->selectOne($clientCountQuery, $roleBindings)->total ?? 0;

            // Total KAMs
            $kamCountQuery = "
                SELECT COUNT(DISTINCT pk.id) as total
                FROM party_supervisors ps
                JOIN parties pk ON ps.other_party_id = pk.id
                WHERE pk.inactive = 0
                {$roleCondition}
            ";
            $totalKams = DB::connection('mysql_second')->selectOne($kamCountQuery, $roleBindings)->total ?? 0;

            // Total Divisions
            $divisionCountQuery = "
                SELECT COUNT(DISTINCT b.id) as total
                FROM party_supervisors ps
                JOIN employments e ON e.employee_id = ps.other_party_id
                JOIN branches b ON b.id = e.employment_branch_id
                WHERE 1=1
                {$roleCondition}
            ";
            $totalDivisions = DB::connection('mysql_second')->selectOne($divisionCountQuery, $roleBindings)->total ?? 0;

            // Total Zones (static for now)
            $totalZones = 1;

            return response()->json([
                'status' => true,
                'data' => $customers,
                'counts' => [
                    'clients' => $totalClients,
                    'kams' => $totalKams,
                    'divisions' => $totalDivisions,
                    'zones' => $totalZones
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ],
                'data' => [],
                'counts' => [
                    'clients' => 0,
                    'kams' => 0,
                    'divisions' => 0,
                    'zones' => 0
                ]
            ], 500);
        }
    }

}
