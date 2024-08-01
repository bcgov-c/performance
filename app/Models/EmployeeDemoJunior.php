<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDemoJunior extends Model
{

    public $table = 'employee_demo_jr';
    public $timestamps = true;
    public $incrementing = true;

    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'guid',
        'employee_id',
        'last_employee_status',
        'current_employee_status',
        'last_conversation_date',
        'next_conversation_date',
        'due_date_paused',
        'excused_type',
        'created_by_id',
        'updated_by_id',
        'updated_by_name',
        'last_classification',
        'current_classification',
        'last_manual_excuse',
        'current_manual_excuse',
        'last_classification_descr',
        'current_classification_descr',
        'excused_reason_id',
        'excused_reason_descr',
        'last_deptid',
        'current_Deptid',
    ];

    public function users() {
        return $this->hasOne(User::class, 'employee_id', 'employee_id');
    }

}
