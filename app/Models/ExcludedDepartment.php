<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExcludedDepartment extends Model
{
    use SoftDeletes;

    public $table = 'excluded_departments';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'id',
        'deptid',
        'created_at',
        'deleted_at',
        'updated_at',
        'updated_by',
    ];


}
