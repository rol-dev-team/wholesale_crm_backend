<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskLog;

class ActivityController extends Controller
{
    /* ================= LIST ================= */
    public function index(Request $request)
{
    $perPage = (int) $request->get('per_page', 10);
    $search  = $request->get('search');
    $status  = $request->get('status');

    $query = Task::query();

    // 🔍 Search
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('meeting_location', 'like', "%{$search}%");
        });
    }

    // 📌 Status filter
    if ($status) {
        $query->where('status', $status);
    }

    $tasks = $query
        ->orderBy('id', 'desc')
        ->paginate($perPage);

    return response()->json([
        'status'  => true,
        'message' => 'Task list',
        'data'    => $tasks->items(), // ONLY DATA
        'meta'    => [
            'current_page' => $tasks->currentPage(),
            'per_page'     => $tasks->perPage(),
            'total'        => $tasks->total(),
            'last_page'    => $tasks->lastPage(),
            'from'         => $tasks->firstItem(),
            'to'           => $tasks->lastItem(),
        ]
    ]);
}


    /* ================= CREATE ================= */
   public function store(Request $request)
{
    $validated = $request->validate([
        'kam_id'            => 'required|integer',
        'client_id'         => 'required|integer',
        'activity_type_id'  => 'required|integer',
        'posted_by'         => 'required|integer', // 👈 frontend থেকে আসবে

        'title'             => 'required|string|max:255',
        'description'       => 'nullable|string',
        'meeting_location'  => 'nullable|string|max:255',

        'activity_schedule' => 'nullable|date',
        'status'            => 'nullable|in:upcoming,overdue,completed,cancelled',
    ]);

    // default status
    if (!isset($validated['status'])) {
        $validated['status'] = 'upcoming';
    }

    // 🔹 task insert
    $task = Task::create($validated);

    // 🔹 log insert (action_by = Auth user)
    $this->log($task, TaskLog::ACTION_INSERTED);

    return response()->json([
        'status'  => true,
        'message' => 'Task created successfully',
        'data'    => $task
    ], 201);
}


    /* ================= SHOW ================= */
    public function show(Task $task)
    {
        return response()->json([
            'status' => true,
            'data'   => $task
        ]);
    }

    /* ================= UPDATE ================= */
    public function update(Request $request, Task $task)
{
    $validated = $request->validate([
        'kam_id'            => 'sometimes|integer',
        'client_id'         => 'sometimes|integer',
        'activity_type_id'  => 'sometimes|integer',
        'posted_by'         => 'sometimes|integer', // 👈 frontend চাইলে update করতে পারবে

        'title'             => 'sometimes|string|max:255',
        'description'       => 'nullable|string',
        'meeting_location'  => 'nullable|string|max:255',

        'activity_schedule' => 'nullable|date',
        'status'            => 'sometimes|in:upcoming,overdue,completed,cancelled',
    ]);

    $oldStatus   = $task->status;
    $oldSchedule = $task->activity_schedule;

    // 🔹 task update
    $task->update($validated);

    /* ===== action_type decision ===== */
    if (isset($validated['status']) && $oldStatus !== $task->status) {

        $actionType = match ($task->status) {
            'completed' => TaskLog::ACTION_COMPLETED,
            'cancelled' => TaskLog::ACTION_CANCELLED,
            default     => TaskLog::ACTION_STATUS_CHANGED,
        };

    } elseif (
        array_key_exists('activity_schedule', $validated) &&
        $oldSchedule != $task->activity_schedule
    ) {
        $actionType = TaskLog::ACTION_RESCHEDULED;

    } else {
        $actionType = TaskLog::ACTION_EDITED;
    }

    // 🔹 log insert
    $this->log($task, $actionType);

    return response()->json([
        'status'  => true,
        'message' => 'Task updated successfully',
        'data'    => $task
    ]);
}


    /* ================= DELETE ================= */
    public function destroy(Task $task)
    {
        $task->delete();

        $this->log($task, TaskLog::ACTION_CANCELLED);

        return response()->json([
            'status'  => true,
            'message' => 'Task deleted successfully'
        ]);
    }

    /* ================= LOG HELPER ================= */
    private function log(Task $task, string $actionType): void
{
    TaskLog::create([
        'kam_id'            => $task->kam_id,
        'client_id'         => $task->client_id,
        'activity_type_id'  => $task->activity_type_id,
        'action_by'         => Auth::id(), // frontend না, auth user
        'title'             => $task->title,
        'description'       => $task->description,
        'meeting_location'  => $task->meeting_location,
        'activity_schedule' => $task->activity_schedule,
        'status'            => $task->status,
        'action_type'       => $actionType,
    ]);
}

}
