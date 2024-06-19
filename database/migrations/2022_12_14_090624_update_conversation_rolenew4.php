<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateConversationRolenew4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       $cons = DB::table('conversation_participants')
                                    ->WhereNull('role')
                                    ->distinct()
                                    ->get();

        foreach ($cons as $con) {
            $conroles = DB::table('conversation_participants')
                                    ->Where('conversation_id', $con->conversation_id) 
                                    ->WhereNotNull('role')
                                    ->get();
            
            foreach($conroles as $conrole){
                if($conrole->role == 'emp') {
                    DB::table('conversation_participants')
                                ->where('conversation_id', $conrole->conversation_id) 
                                ->where('participant_id', '<>', $conrole->participant_id) 
                                ->update(array('role' => 'mgr')); 
                } else {
                    DB::table('conversation_participants')
                                ->where('conversation_id', $conrole->conversation_id) 
                                ->where('participant_id', '<>', $conrole->participant_id) 
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
