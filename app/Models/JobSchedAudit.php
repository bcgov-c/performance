<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\Builder;

class JobSchedAudit extends Model
{

    public $table = 'job_sched_audit';
    public $timestamps = false;

    use HasFactory;

    //protected $primaryKey = ['id'];

    protected $fillable = [
        'id',
        'job_name',
        'start_time',
        'end_time',
        'status',
    ];

}
