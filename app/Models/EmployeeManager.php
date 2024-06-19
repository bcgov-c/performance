<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeManager extends Model
{
    public $table = 'employee_managers';
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'id',
        'employee_id',
        'position_number',
        'orgid',
        'supervisor_emplid',
        'supervisor_name',
        'supervisor_position_number',
        'supervisor_email',
        'priority',
        'source',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'employee_id', 'employee_id')            
            ->select(
                'id',
                'name',
                'email',
                'employee_id',
                'empl_record',
                'reporting_to',
                'joining_date',
                'acctlock',
                'last_signon_at',
                'last_sync_at',
                'created_at',
                'updated_at',
                'excused_reason_id',
                'next_conversation_date',
                'due_date_paused',
                'excused_flag',
                'excused_updated_by',
                'excused_updated_at',
            )->first();
    }

}
