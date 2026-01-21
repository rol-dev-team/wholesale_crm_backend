<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSupervisorMapping extends Model
{
    protected $table = 'user_supervisor_mappings';

    protected $fillable = [
        'user_id',
        'supervisor_id',
    ];
}
