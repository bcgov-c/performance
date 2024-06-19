<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateConversationTopicTypos816 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            UPDATE conversation_topics 
            SET preparing_for_conversation = REPLACE(preparing_for_conversation, '(how we accomplish things).s', '(how we accomplish things)')
            WHERE id = 2 
                AND preparing_for_conversation LIKE '%(how we accomplish things).s%'
        ");
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
