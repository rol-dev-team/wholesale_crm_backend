<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskNote;
use App\Models\ActivityType;
use App\Models\User;
use DB;


class ActivityController extends Controller
{
    /* ================= LIST ================= */


public function statusSummary($kamId)
{
$statusSummary = Task::query()
    ->select('status', DB::raw('COUNT(*) as total'))
    ->when($kamId, function ($q) use ($kamId) {
        $q->where('kam_id', $kamId);
    })
    ->groupBy('status')
    ->pluck('total', 'status');



    return response()->json([
        'status'  => true,
        'message' => 'Summary fetched successfully',
        'data'    => $statusSummary,
        
    ]);
}
public function index(Request $request)
{
    $perPage = (int) $request->get('per_page', 10);

    $search         = $request->get('search');
    $status         = $request->get('status');
    $kamId          = $request->get('kam_id');
    $clientId       = $request->get('client_id');
    $activityTypeId = $request->get('activity_type_id');
    $fromDate       = $request->get('from_date');
    $toDate         = $request->get('to_date');

    /* =========================
       MAIN DB : TASKS QUERY
    ========================== */

    $query = Task::query()
    ->with([
        'notes:id,task_id,note,created_at,updated_at'
    ])
    
    ->select(
        'tasks.*',
        'activity_types.activity_type_name',
        'users.username as posted_by_user'
    )
    ->selectSub(
        DB::table('task_notes')
            ->selectRaw('COUNT(*)')
            ->whereColumn('task_notes.task_id', 'tasks.id'),
        'notes_count' 
    )
    ->join('activity_types', 'activity_types.id', '=', 'tasks.activity_type_id')
    ->join('users', 'users.id', '=', 'tasks.posted_by');


    /* 🔯োগ SEARCH */
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('tasks.title', 'like', "%{$search}%")
              ->orWhere('tasks.description', 'like', "%{$search}%")
              ->orWhere('tasks.meeting_location', 'like', "%{$search}%");
        });
    }

    /* 📌 STATUS */
    if ($status && $status !== 'all') {
        $query->where('tasks.status', $status);
    }

    /* 👤 KAM */
    if ($kamId) {
        $query->where('tasks.kam_id', $kamId);
    }

    /* 🏢 CLIENT */
    if ($clientId) {
        $query->where('tasks.client_id', $clientId);
    }

    /* 🧾 ACTIVITY TYPE */
    if ($activityTypeId) {
        $query->where('tasks.activity_type_id', $activityTypeId);
    }

    /* 📅 DATE RANGE */
    if ($fromDate && $toDate) {
        $query->whereBetween('tasks.activity_schedule', [
            $fromDate . ' 00:00:00',
            $toDate . ' 23:59:59'
        ]);
    } elseif ($fromDate) {
        $query->whereDate('tasks.activity_schedule', '>=', $fromDate);
    } elseif ($toDate) {
        $query->whereDate('tasks.activity_schedule', '<=', $toDate);
    }

    $tasks = $query
        ->orderBy('tasks.id', 'desc')
        ->paginate($perPage);

    /* =========================
       SECOND DB : KAM MAP
    ========================== */
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
    ");

    $kamMap = collect($kams)->pluck('kam_name', 'kam_id');

    /* =========================
       SECOND DB : CLIENT MAP
    ========================== */
    $clients = DB::connection('mysql_second')->select("
        SELECT DISTINCT 
            ps.party_id AS client_id,
            pa.full_name AS client_name
        FROM party_supervisors ps
        JOIN parties pa ON ps.party_id = pa.id
        WHERE pa.type = 'customer'
          AND ps.end_date IS NULL
    ");

    $clientMap = collect($clients)->pluck('client_name', 'client_id');

    /* =========================
       MERGE NAMES INTO TASKS
    ========================== */
    $data = collect($tasks->items())->map(function ($task) use ($kamMap, $clientMap) {
    return array_merge($task->toArray(), [
        'notes_count' => $task->notes_count, 
        'kam_name'    => $kamMap[$task->kam_id] ?? null,
        'client_name' => $clientMap[$task->client_id] ?? null,
    ]);
});


    return response()->json([
        'status'  => true,
        'message' => 'Task list',
        'data'    => $data,
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

    public function addNotes(Request $request)
    {
        $request->validate([
            'note' => 'required|string',
        ]);

        // Ensure task exists
        $task = Task::findOrFail($request->task_id);

        $note = TaskNote::create([
            'task_id' => $task->id,
            'note'    => $request->note,
        ]);

        return response()->json([
            'message' => 'Note added successfully',
            'data'    => $note,
        ], 201);
    }

//     public function updateStatus(Request $request)
// {
//     $data = $request->validate([
//         'task_id' => 'required|exists:tasks,id',
//         'status'  => 'required|in:completed,cancelled',
//         'message' => 'required|string',
//     ]);

//      $task = Task::findOrFail($data['task_id']);

//      if (in_array($task->status, ['completed', 'cancelled'])) {
//         return response()->json([
//             'status'  => true,
//             'message' => 'Task already finalized',
//         ], 400);
//     }
//     DB::transaction(function () use ($data) {

//         // 1️⃣ Update task
//         $task->update([
//             'status'      => $data['status'],
//             'description' => $data['message'], // ⭐ important
//         ]);

//         // 2️⃣ Log using EXISTING function
//         $this->log(
//             $task,
//             $data['status'] === 'completed'
//                 ? TaskLog::ACTION_COMPLETED
//                 : TaskLog::ACTION_CANCELLED
//         );
//     });

//     return response()->json([
//         'status'  => true,
//         'message' => 'Task status updated successfully',
//     ]);
// }


public function updateStatus(Request $request)
{
    $data = $request->validate([
        'task_id' => 'required|exists:tasks,id',
        'status'  => 'required|in:completed,cancelled',
        'message' => 'required|string',
    ]);

    $task = Task::findOrFail($data['task_id']);

    if (in_array($task->status, ['completed', 'cancelled'])) {
        return response()->json([
            'status'  => false,
            'message' => 'Task already finalized',
        ], 400);
    }

    DB::transaction(function () use ($data, $task) {

        // 1️⃣ Update task
        $task->update([
            'status'      => $data['status'],
            'description' => $data['message'],
        ]);

        // 2️⃣ Log using EXISTING function
        $this->log(
            $task,
            $data['status'] === 'completed'
                ? TaskLog::ACTION_COMPLETED
                : TaskLog::ACTION_CANCELLED
        );
    });

    return response()->json([
        'status'  => true,
        'message' => 'Task status updated successfully',
    ]);
}

    /* ================= LOG HELPER ================= */
    private function log(Task $task, string $actionType): void
{
    TaskLog::create([
        'kam_id'            => $task->kam_id,
        'client_id'         => $task->client_id,
        'activity_type_id'  => $task->activity_type_id,
        'action_by'         => $task->posted_by,
        'title'             => $task->title,
        'description'       => $task->description,
        'meeting_location'  => $task->meeting_location,
        'activity_schedule' => $task->activity_schedule,
        'status'            => $task->status,
        'action_type'       => $actionType,
    ]);
}

}
