<?php

namespace App\Http\Controllers;
// namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KamPerformanceController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->select('id','username','fullname',
                DB::raw("'Cl' as client"),DB::raw("100 as target"),DB::raw("80 as achieved")
            )
            ->when($request->search, function ($q) use ($request) {
                $q->where('username', 'like', "%{$request->search}%")
                ->orWhere('fullname', 'like', "%{$request->search}%");
            })
            ->paginate($request->per_page ?? 10);

        return response()->json($users);
    }
}
