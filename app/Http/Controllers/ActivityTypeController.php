<?php

namespace App\Http\Controllers;

use App\Models\ActivityType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActivityTypeController extends Controller
{
    // GET /api/activity-types
    public function index()
    {
        return response()->json([
            'success' => true,
            'data' => ActivityType::orderBy('id')->get(),
        ]);
    }

    // POST /api/activity-types
    public function store(Request $request)
    {
        $validated = $request->validate([
            'activity_type_name' => [
                'required',
                'string',
                'max:100',
                'unique:activity_types,activity_type_name',
            ],
        ]);

        $activityType = ActivityType::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Activity type created successfully',
            'data' => $activityType,
        ], 201);
    }

    // GET /api/activity-types/{id}
    public function show($id)
    {
        $activityType = ActivityType::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $activityType,
        ]);
    }

    // PUT /api/activity-types/{id}
    public function update(Request $request, $id)
    {
        $activityType = ActivityType::findOrFail($id);

        $validated = $request->validate([
            'activity_type_name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('activity_types', 'activity_type_name')->ignore($activityType->id),
            ],
        ]);

        $activityType->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Activity type updated successfully',
            'data' => $activityType,
        ]);
    }

    // DELETE /api/activity-types/{id}
    public function destroy($id)
    {
        $activityType = ActivityType::findOrFail($id);
        $activityType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity type deleted successfully',
        ]);
    }
}
