<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateConversationRoleNew5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        ////reset all roles
        DB::table('conversation_participants')
                                ->update(array('role' => null)); 
        
        //get all conversations with supervisor sign off id
        $sup_cons = DB::table('conversations')
                                    ->WhereNotNull('supervisor_signoff_id')
                                    ->get();
        foreach($sup_cons as $supcon) {
            $conversation_id = $supcon->id;
            $supvisor_id = $supcon->supervisor_signoff_id;
            //update all matched role
            DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id', $supvisor_id) 
                                ->update(array('role' => 'mgr')); 
            DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id','<>', $supvisor_id) 
                                ->update(array('role' => 'emp')); 
        }
        
        //get all conversations with employee sign off id
        $emp_cons = DB::table('conversations')
                                    ->WhereNotNull('signoff_user_id')
                                    ->get();
        foreach($emp_cons as $emp_con) {
            $conversation_id = $emp_con->id;
            $employee_id = $emp_con->signoff_user_id;
            //update all matched role
            DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id', $employee_id) 
                                ->update(array('role' => 'emp')); 
            DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id','<>', $employee_id) 
                                ->update(array('role' => 'mgr')); 
        }
        
        //get all conversations without employee and supervisor sign off
        $non_cons =  DB::table('conversations')
                                    ->WhereNull('supervisor_signoff_id')
                                    ->WhereNull('signoff_user_id')
                                    ->get();
        $owner_teammember = array();
        foreach($non_cons as $non_con) {
            $conversation_id = $non_con->id;
            $owner = $non_con->user_id;    
            
            //owners is team member
            $direct = DB::table('users')
                                    ->Where('id', $owner)
                                    ->get();
            $reporting_to = $direct[0]->reporting_to;
            
            $shares = DB::table('shared_profiles')
                                    ->Where('shared_id', $owner)
                                    ->get();
            $supervisors = array();
            $supervisors[] = $reporting_to;
            foreach($shares as $share){
                $supervisors[] = $share->shared_with;
            }      
            
            //owner is supervisor
            $owner_teams = array();
            $direct_teams = DB::table('users')
                                    ->Where('reporting_to', $owner)
                                    ->get();
            $shared_teams = DB::table('shared_profiles')
                                    ->Where('shared_with', $owner)
                                    ->get();
            foreach($direct_teams as $direct_team) {
                $owner_teams[] = $direct_team->id;
            }
            foreach($shared_teams as $shared_team) {
                $owner_teams[] = $shared_team->shared_id;
            }
            
            //get another participant of the conversation
            $another_participant =  DB::table('conversation_participants')
                                    ->where('conversation_id', $conversation_id) 
                                    ->where('participant_id', '<>', $owner)
                                    ->get();
            if(count($another_participant) == 1) {
                $another_participant_id = $another_participant[0]->participant_id;
                
                if(in_array($another_participant_id, $supervisors)){
                    DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id',$another_participant_id)    
                                ->update(array('role' => 'mgr')); 
                    DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id',$owner) 
                                ->update(array('role' => 'emp'));  
                }

                if(in_array($another_participant_id, $owner_teams)){
                    DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id',$another_participant_id)    
                                ->update(array('role' => 'emp')); 
                    DB::table('conversation_participants')
                                ->where('conversation_id', $conversation_id) 
                                ->where('participant_id',$owner) 
                                ->update(array('role' => 'mgr'));  
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
