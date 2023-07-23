<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalSharedWith extends Model
{
    use HasFactory;

    protected $table = 'goals_shared_with';

    protected $fillable = [
        'goal_id', 
        'user_id',    
        'reason',
    ];

    public function goal() {

        return $this->belongsTo('App\Models\Goal');        

    }

    public function user() {

        return $this->belongsTo('App\Models\User');        

    }

}
