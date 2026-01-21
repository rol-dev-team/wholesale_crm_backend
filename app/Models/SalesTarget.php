<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesTarget extends Model
{
    protected $table = 'sales_targets';

    protected $fillable = [
        'target_month',
        'division',
        'supervisor_id',
        'kam_id',
        'amount',
        'posted_by',
    ];
}
