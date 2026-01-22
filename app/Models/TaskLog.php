<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    protected $table = 'task_logs';

    protected $fillable = [
        'kam_id',
        'client_id',
        'activity_type_id',
        'action_by',
        'title',
        'description',
        'meeting_location',
        'activity_schedule',
        'status',
        'action_type',
    ];

    protected $casts = [
        'activity_schedule' => 'datetime',
    ];

    public const ACTION_INSERTED       = 'inserted';
    public const ACTION_EDITED         = 'edited';
    public const ACTION_NOTE_ADDED     = 'note_added';
    public const ACTION_COMPLETED      = 'completed';
    public const ACTION_CANCELLED      = 'cancelled';
    public const ACTION_STATUS_CHANGED = 'status_changed';
    public const ACTION_RESCHEDULED    = 'rescheduled';
    /* ================= Relationships ================= */

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
}
