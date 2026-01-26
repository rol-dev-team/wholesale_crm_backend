<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Task extends Model
{
     use SoftDeletes;

    protected $table = 'tasks';

    protected $fillable = [
        'kam_id',
        'client_id',
        'activity_type_id',
        'posted_by',
        'title',
        'description',
        'meeting_location',
        'activity_schedule',
        'status',
    ];

    protected $casts = [
        'activity_schedule' => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    /* ================= Relationships ================= */

    public function logs()
    {
        return $this->hasMany(TaskLog::class);
    }

    public function notes()
    {
        return $this->hasMany(TaskNote::class, 'task_id');
    }


}
