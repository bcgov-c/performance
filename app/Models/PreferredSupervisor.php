<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PreferredSupervisor extends Model
{
    public $table = 'preferred_supervisor';
    public $timestamps = true;
    public $incrementing = false;

    use HasFactory;

    // protected $primaryKey = ['employee_id', 'position_nbr'];

    protected $fillable = [
        'id',
        'employee_id',
        'position_nbr',
        'supv_empl_id'
    ];
}
