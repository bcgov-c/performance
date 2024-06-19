<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateConversationRole extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conversations = DB::table('conversations')   
                            ->select('id')
                            ->get();         
        
        foreach($conversations as $con){
            $participants = DB::table('conversation_participants')   
                            ->select('participant_id')
                            ->where('conversation_id', '=', $con->id)
                            ->get();    
            
            if(count($participants) == 2) {
                $mgrinfo_0 = DB::table('users')                        
                                ->select('reporting_to')
                                ->where('id', '=', $participants[0]->participant_id)
                                ->get();
                $mgrinfo_1 = DB::table('users')                        
                                ->select('reporting_to')
                                ->where('id', '=', $participants[1]->participant_id)
                                ->get();
                $isset = 0;
                if ($participants[0]->participant_id == $mgrinfo_1[0]->reporting_to || $participants[1]->participant_id == $mgrinfo_0[0]->reporting_to) {
                    if ($participants[0]->participant_id == $mgrinfo_1[0]->reporting_to) {
                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[0]->participant_id) 
                        ->where('conversation_id', $con->id) 
                        ->limit(1) 
                        ->update(array('role' => 'mgr'));    

                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[1]->participant_id)
                        ->where('conversation_id', $con->id)         
                        ->limit(1) 
                        ->update(array('role' => 'emp'));  
                    }
                    if ($participants[1]->participant_id == $mgrinfo_0[0]->reporting_to) {
                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[1]->participant_id) 
                        ->where('conversation_id', $con->id)         
                        ->limit(1) 
                        ->update(array('role' => 'mgr'));    

                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[0]->participant_id) 
                        ->where('conversation_id', $con->id)         
                        ->limit(1) 
                        ->update(array('role' => 'emp')); 
                    }
                    $isset = 1;
                }
                if ($isset == 0){
                    $shareinfo_0 = DB::table('shared_profiles')                        
                                ->where('shared_id', $participants[1]->participant_id)
                                ->where('shared_with', $participants[0]->participant_id)
                                ->count(); 
                    $shareinfo_1 = DB::table('shared_profiles')                        
                                ->where('shared_id', $participants[0]->participant_id)
                                ->where('shared_with', $participants[1]->participant_id)
                                ->count(); 
                    if($shareinfo_0 > 0) {
                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[0]->participant_id) 
                        ->limit(1) 
                        ->update(array('role' => 'mgr'));    

                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[1]->participant_id) 
                        ->limit(1) 
                        ->update(array('role' => 'emp')); 
                    }

                    if($shareinfo_1 > 0) {
                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[1]->participant_id) 
                        ->where('conversation_id', $con->id)         
                        ->limit(1) 
                        ->update(array('role' => 'mgr'));    

                        DB::table('conversation_participants')
                        ->where('participant_id', $participants[0]->participant_id) 
                        ->where('conversation_id', $con->id)         
                        ->limit(1) 
                        ->update(array('role' => 'emp')); 
                    }

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
