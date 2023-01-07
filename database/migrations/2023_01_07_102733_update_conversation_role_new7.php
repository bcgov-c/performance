<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateConversationRoleNew7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conversations = array();
        $mgr_participants = array();
        $emp_participants = array();
        
        
        $conversation_list = '53,54,196,344,427,456,458,2506,2834,2835,2893';
        $mgr_list = '17006,13466,36725,17829';
        $emp_list = '16627,26011,45298,38477';
        
        $conversations = explode(',', $conversation_list);
        $mgr_participants = explode(',', $mgr_list);
        $emp_participants = explode(',', $emp_list);
        
        //update mgr role
        DB::table('conversation_participants')
                                ->WhereIn('conversation_id', $conversations) 
                                ->WhereIn('participant_id', $mgr_participants) 
                                ->update(array('role' => 'mgr')); 
        
        //update emp role
        DB::table('conversation_participants')
                                ->WhereIn('conversation_id', $conversations) 
                                ->WhereIn('participant_id', $emp_participants) 
                                ->update(array('role' => 'emp'));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
