<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelHasRoleAudit extends Model
{
    use SoftDeletes;

    public $table = 'model_has_role_audits';
    public $timestamps = true;
    public $incrementing = true;

    protected $fillable = [
        'id',
        'model_id',
        'role_id',
        'deptid',
        'position_number',
        'created_at',
        'deleted_at',
        'updated_at',
        'updated_by',
        'deleted_by',
    ];


}
