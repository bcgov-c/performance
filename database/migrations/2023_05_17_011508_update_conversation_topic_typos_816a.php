<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateConversationTopicTypos816a extends Migration
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
            SET preparing_for_conversation = REPLACE(preparing_for_conversation, '(how we accomplish things).', '(how we accomplish things)')
            WHERE id = 2 
                AND preparing_for_conversation LIKE '%(how we accomplish things).%'
        ");
        \DB::statement("
            UPDATE conversation_topics 
            SET question_html = REPLACE(question_html, 'control and/or influence).', 'control and/or influence)')
            WHERE id = 2 
                AND question_html LIKE '%control and/or influence).%'
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
