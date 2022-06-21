<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeShare extends Model
{
    protected $table = 'employee_shares';

    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'shared_with_id',
        'shared_element_id',
        'reason'
    ];

    public function sharedWith()
    {
        return $this->belongsToMany('App\Models\User', 'employee_shares', 'shared_with_id', 'user_id')->withTimestamps();
    }


}
