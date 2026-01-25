<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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


    public function index(Request $request)
    {
        $customers = DB::connection('mysql_second')->select("
            SELECT DISTINCT pa.full_name, pa.code as party_code,pa.mobile,pa.email, pa.inactive,'Dhaka' as division, 
                'Banani' as zone, 'Sam' as assigned_kam
            FROM parties pa 
            WHERE pa.type = 'customer';
        ");

        
        $totalClients = DB::connection('mysql_second')->table('parties')
            ->where('type', 'customer')
            ->distinct('full_name')
            ->count('full_name');

        $totalDivisions = DB::connection('mysql_second')->table('parties')
            ->where('type', 'customer')
            ->distinct()
            ->count(DB::raw("'Dhaka'"));

        $totalZones = DB::connection('mysql_second')->table('parties')
            ->where('type', 'customer')
            ->distinct()
            ->count(DB::raw("'Banani'")); 

        $totalKams = DB::connection('mysql_second')->table('parties')
            ->where('type', 'customer')
            ->distinct()
            ->count(DB::raw("'Sam'"));

        return response()->json([
            'data' => $customers,
            'counts' => [
                'clients' => $totalClients,
                'divisions' => $totalDivisions,
                'zones' => $totalZones,
                'kams' => $totalKams
            ]
        ]);
    }

}
