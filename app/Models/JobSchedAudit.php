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
        'details'
    ];

    // Static function for gettig the list of status
    public static function job_status_options() {

        return self::whereNotNull('status')
                ->select('status')
                ->distinct()
                ->orderBy('status')
                ->pluck('status');
    }

}
