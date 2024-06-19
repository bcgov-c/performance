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

    public function user() {
        return $this->belongsTo(User::class)            
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
