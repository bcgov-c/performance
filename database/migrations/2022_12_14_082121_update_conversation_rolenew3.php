<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateConversationRolenew3 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conversations_signed = DB::table('conversations')
                                    ->select('id', 'user_id', 'signoff_user_id', 'supervisor_signoff_id')
                                    ->whereNotNull('signoff_user_id')
                                    ->orWhereNotNull('supervisor_signoff_id')
                                    ->get();

        foreach($conversations_signed as $conversation){
            if ($conversation->signoff_user_id != ''){
                //update emp role
                DB::table('conversation_participants')
                        ->where('conversation_id', $conversation->id) 
                        ->where('participant_id', $conversation->signoff_user_id) 
                        ->update(array('role' => 'emp'));    
                
                DB::table('conversation_participants')
                        ->where('conversation_id', $conversation->id) 
                        ->where('participant_id', '<>', $conversation->signoff_user_id) 
                        ->update(array('role' => 'mgr'));    
                
            }
            if ($conversation->supervisor_signoff_id != ''){
                //update sup role
                DB::table('conversation_participants')
                        ->where('conversation_id', $conversation->id) 
                        ->where('participant_id', $conversation->supervisor_signoff_id) 
                        ->update(array('role' => 'mgr'));   
                
                DB::table('conversation_participants')
                        ->where('conversation_id', $conversation->id) 
                        ->where('participant_id', '<>', $conversation->supervisor_signoff_id) 
                        ->update(array('role' => 'emp'));   
            }
        }
        
        $conversations_nosigned = DB::table('conversations')
                                    ->select('id', 'user_id')
                                    ->whereNull('signoff_user_id')
                                    ->WhereNull('supervisor_signoff_id')
                                    ->get();
        foreach($conversations_nosigned as $conversation){
            if ($conversation->user_id != ''){
                //direct report
                $reports_to = DB::table('users')
                                    ->select('id')
                                    ->Where('reporting_to', $conversation->user_id)
                                    ->get();
                if(count($reports_to)>0){
                    foreach($reports_to as $report_to) {
                        //update emp role
                        DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $report_to->id) 
                            ->update(array('role' => 'emp'));  
                    }                    
                    //update sup role
                    DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $conversation->user_id) 
                            ->update(array('role' => 'mgr'));  
                }   
                
                $report_with = DB::table('users')
                                    ->select('reporting_to')
                                    ->Where('id', $conversation->user_id)
                                    ->get();
                DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $conversation->user_id) 
                            ->update(array('role' => 'emp')); 
                DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $report_with[0]->reporting_to) 
                            ->update(array('role' => 'mgr')); 
                
                //share relation
                //share relationship as manager
                $share_managers = DB::table('shared_profiles')
                                    ->select('shared_id')
                                    ->Where('shared_with', $conversation->user_id)
                                    ->get();
                if(count($share_managers)>0){
                    foreach($share_managers as $share_manager){
                        DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $share_manager->shared_id) 
                            ->update(array('role' => 'emp'));  
                    }
                    DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $conversation->user_id) 
                            ->update(array('role' => 'mgr')); 
                }
                //share relationship as emp
                $share_emps = DB::table('shared_profiles')
                                    ->select('shared_with')
                                    ->Where('shared_id', $conversation->user_id)
                                    ->get();
                if(count($share_emps)>0){
                    foreach($share_emps as $share_emp){
                        DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $share_emp->shared_with) 
                            ->update(array('role' => 'mgr'));  
                    }
                    DB::table('conversation_participants')
                            ->where('conversation_id', $conversation->id) 
                            ->where('participant_id', $conversation->user_id) 
                            ->update(array('role' => 'emp')); 
                }
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
        //
    }
}
