<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDemoJunior extends Model
{

    public $table = 'employee_demo_jr';

    use HasFactory;

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'id',
        'guid',
        'last_employee_status',
        'current_employee_status',
        'last_conversation_date',
        'next_conversation_date',
        'due_date_paused',
    ];

}
