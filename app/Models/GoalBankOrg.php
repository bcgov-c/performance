<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoalBankOrg extends Model
{
    use HasFactory;

    protected $fillable = [
        'goal_id','version','deptid','organization','level1_program','level2_division','level3_branch','level4'
    ];

    public function goal() {

        return $this->belongsTo('App\Models\Goal');        

    }


}
