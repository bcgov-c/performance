<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeSupervisor extends Model
{
    use SoftDeletes;

    public $table = 'employee_supervisor';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'id',
        'user_id',
        'supervisor_id',
        'reason',
        'created_at',
        'deleted_at',
        'updated_at',
        'updated_by',
    ];
}
