<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Position extends Model
{
    public $table = 'positions';
    // public $timestamps = true;
    public $incrementing = false;

    use HasFactory;

    protected $primaryKey = ['position_nbr'];

    protected $fillable = [
        'position_nbr',
        'descr',
        'descrshort',
        'reports_to',
        'date_deleted',
    ];

}
