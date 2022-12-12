<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateConversationRolenew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $conversations = DB::table('conversation_participants')   
                            ->select('conversation_id')
                            ->whereNull('role')
                            ->distinct()
                            ->get();  
        foreach($conversations as $conversation) {
            $conversation_id = $conversation->conversation_id;
            DB::table('conversation_participants')
                        ->where('conversation_id', $conversation_id) 
                        ->update(array('role' => null));  
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
