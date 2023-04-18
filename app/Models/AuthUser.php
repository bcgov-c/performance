<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class JobDataAudit extends Model
{

    public $table = 'job_data_audit';
    public $timestamps = true;
    public $incrementing = false;

    use HasFactory;

    protected $primaryKey = ['id'];

    protected $fillable = [
        'id',
        'job_sched_id',
        'old_values',
        'new_values',
    ];

}
