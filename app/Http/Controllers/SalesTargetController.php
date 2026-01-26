<?php

namespace App\Http\Controllers;

use App\Models\SalesTarget;
use Illuminate\Http\Request;

class SalesTargetController extends Controller
{
    /**
     * GET /api/sales-targets
     * Pagination + Filters
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $query = SalesTarget::query();

        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }

        if ($request->filled('supervisor_id')) {
            $query->where('supervisor_id', $request->supervisor_id);
        }

        if ($request->filled('kam_id')) {
            $query->where('kam_id', $request->kam_id);
        }

        if ($request->filled('target_month')) {
            $query->whereMonth('target_month', date('m', strtotime($request->target_month)))
                  ->whereYear('target_month', date('Y', strtotime($request->target_month)));
        }

        $targets = $query->orderBy('target_month', 'desc')->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $targets->items(),
            'meta' => [
                'current_page' => $targets->currentPage(),
                'per_page' => $targets->perPage(),
                'total' => $targets->total(),
                'last_page' => $targets->lastPage(),
            ]
        ]);
    }

    /**
     * POST /api/sales-targets
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'target_month' => 'required|date',
            'division' => 'required|string',
            'supervisor_id' => 'nullable|integer',
            'kam_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'posted_by' => 'required|integer',
        ]);

        $target = SalesTarget::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Sales target created',
            'data' => $target
        ], 201);
    }

    /**
     * GET /api/sales-targets/{id}
     */
    public function show($id)
    {
        $target = SalesTarget::find($id);

        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        return response()->json(['status' => true, 'data' => $target]);
    }

    /**
     * PUT /api/sales-targets/{id}
     */
    public function update(Request $request, $id)
    {
        $target = SalesTarget::find($id);

        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $validated = $request->validate([
            'target_month' => 'sometimes|date',
            'division' => 'sometimes|string',
            'supervisor_id' => 'sometimes|integer',
            'kam_id' => 'sometimes|integer',
            'amount' => 'sometimes|numeric|min:0',
        ]);

        $target->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Sales target updated',
            'data' => $target
        ]);
    }

    /**
     * DELETE /api/sales-targets/{id}
     */
    public function destroy($id)
    {
        $target = SalesTarget::find($id);

        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $target->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sales target deleted'
        ]);
    }

    public function totalSaleTarget()
    {
        $target = SalesTarget::find($id);

        if (!$target) {
            return response()->json(['status' => false, 'message' => 'Not found'], 404);
        }

        $target->delete();

        return response()->json([
            'status' => true,
            'message' => 'Sales target deleted'
        ]);
    }
}
