<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class PrismApiController extends Controller
{
    public function branchList()
    {
        try{

        $branch = DB::connection('mysql_second')->select("SELECT DISTINCT
		id,
    full_name AS branch_name
FROM branches ");
        return response()->json([
        'status'  => true,
        'message' => 'Fetched branch successfully',
        'data'    => $branch
    ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function branchWiseSupervisorList($branch_id)
    {
        try{

        $branch = DB::connection('mysql_second')->select("SELECT DISTINCT ps.other_party_id AS supervisor_id, pa.full_name AS supervisor,e.employment_branch_id
,b.full_name AS branch_name
FROM party_supervisors ps
 JOIN parties pa ON pa.id = ps.other_party_id
 JOIN employments e ON e.employee_id = ps.other_party_id
 JOIN branches b ON b.id = e.employment_branch_id
 JOIN departments d ON e.department_id = d.id
WHERE pa.type is NULL AND pa.subtype=2 AND pa.role=8 AND d.id = 6;
AND e.employment_branch_id = '$branch_id'");
        return response()->json([
        'status'  => true,
        'message' => 'Fetched supervisor successfully',
        'data'    => $branch
    ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function supervisorWiseKamList($supervisor_id)
    {
        try{

        $branch = DB::connection('mysql_second')->select("SELECT DISTINCT e.employee_code,e.employee_id, pa.full_name, e.manager_1, p.full_name AS supervisor, d.name department, ds.name designation
, e.created, e.updated, e.start, e.end, e.job_location_id, pa.branch_id, e.manager_2
FROM employments e
JOIN parties pa ON e.employee_id = pa.id
JOIN departments d ON e.department_id = d.id
JOIN designations ds ON e.designation_id = ds.id
LEFT JOIN parties p ON p.id = e.manager_1
WHERE pa.type is NULL and pa.subtype = 2 and pa.role = 8
AND d.id = 6 AND pa.inactive = 0
AND e.manager_1 = '$supervisor_id'");
        return response()->json([
        'status'  => true,
        'message' => 'Fetched kam successfully',
        'data'    => $branch
    ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    public function kamWiseClientList($kam_id)
    {
        try{

        $branch = DB::connection('mysql_second')->select("SELECT DISTINCT ps.party_id,pa.full_name AS client,pk.full_name AS current_supervisor,
    (SELECT COUNT(DISTINCT other_party_id) 
     FROM party_supervisors 
     WHERE party_id = ps.party_id) AS total_supervisors_ever,
    CASE 
        WHEN (
            SELECT COUNT(DISTINCT other_party_id) 
            FROM party_supervisors 
            WHERE party_id = ps.party_id
        ) > 1 THEN 'Transferred Client'
        ELSE 'Own Client'
    END AS client_transfer_status,
    ps.start_date AS supervisor_start_date,ps.end_date AS supervisor_end_date,ps.other_party_id,ps.inactive
FROM party_supervisors ps
JOIN parties pa ON ps.party_id = pa.id AND pa.type = 'customer'
JOIN parties pk ON ps.other_party_id = pk.id

WHERE ps.end_date IS NULL
AND ps.other_party_id = '$kam_id'
GROUP BY ps.party_id, ps.inactive, pa.full_name, pk.full_name, ps.other_party_id, ps.start_date, ps.end_date");
        return response()->json([
        'status'  => true,
        'message' => 'Fetched client successfully',
        'data'    => $branch
    ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }


    public function kamList()
    {
        try{

        $branch = DB::connection('mysql_second')->select("SELECT DISTINCT e.employee_code,e.employee_id AS kam_id, pa.full_name AS kam_name, e.manager_1, p.full_name AS supervisor, d.name department, ds.name designation
, e.created, e.updated, e.start, e.end, e.job_location_id, pa.branch_id, e.manager_2
FROM employments e
JOIN parties pa ON e.employee_id = pa.id
JOIN departments d ON e.department_id = d.id
JOIN designations ds ON e.designation_id = ds.id
LEFT JOIN parties p ON p.id = e.manager_1
WHERE pa.type is NULL and pa.subtype = 2 and pa.role = 8
AND d.id = 6 AND pa.inactive = 0");
        return response()->json([
        'status'  => true,
        'message' => 'Fetched kam successfully',
        'data'    => $branch
    ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

     public function supervisorList()
    {
        try{

        $branch = DB::connection('mysql_second')->select("SELECT DISTINCT ps.other_party_id AS supervisor_id, pa.full_name AS supervisor,e.employment_branch_id
,b.full_name AS branch_name
FROM party_supervisors ps
 JOIN parties pa ON pa.id = ps.other_party_id
 JOIN employments e ON e.employee_id = ps.other_party_id
 JOIN branches b ON b.id = e.employment_branch_id
 JOIN departments d ON e.department_id = d.id
WHERE pa.type is NULL AND pa.subtype=2 AND pa.role=8 AND d.id = 6");
        return response()->json([
        'status'  => true,
        'message' => 'Fetched supervisor successfully',
        'data'    => $branch
    ], 200);

        }catch(\Exception $e){
            return response()->json([
                'status' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
    
}
