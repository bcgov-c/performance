<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CleanSharedRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $shared_relations = DB::table('shared_profiles')->select('shared_id', 'shared_with')->get();
        foreach($shared_relations as $shared_relation) {
            $share_empolyee = $shared_relation->shared_id;
            $share_supervisor = $shared_relation->shared_with;
            
            $user_relation = DB::table('users')->select('reporting_to')->where('id', $share_empolyee)->get();
            $user_supervisor = $user_relation[0]->reporting_to;
            
            if($share_supervisor == $user_supervisor){
                DB::table('shared_profiles')
                    ->where('shared_id', $share_empolyee)    
                    ->where('shared_with', $share_supervisor)    
                    ->delete();
            }            
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
