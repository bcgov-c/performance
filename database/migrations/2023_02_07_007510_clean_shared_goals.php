<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CleanSharedGoals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $shared_goals = DB::table('goals_shared_with')->select('goal_id')->distinct()->get();
        foreach($shared_goals as $shared_goal) {
            $goal_owner = DB::table('goals')->select('user_id')->where('id', $shared_goal->goal_id)->get();
            $goal_owner_id = $goal_owner[0]->user_id;
            
            DB::table('goals_shared_with')
                    ->whereNotIn('user_id', function ($query) use ($goal_owner_id)  {
                        $query->select('id')->from('users')->where('reporting_to', $goal_owner_id);
                    })
                    ->whereNotIn('user_id', function ($query) use ($goal_owner_id) {
                        $query->select('shared_id')->from('shared_profiles')->where('shared_with', $goal_owner_id);
                    })
                    ->delete();
        }    
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
    }
}
