<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DashboardMessage extends Model
{
    // use HasFactory;
    protected $table = "dashboard_message";

    protected $fillable = [
        'id',
        'title',
        'message',
        'status',
        'created_at',
        'updated_at',
    ];

}
