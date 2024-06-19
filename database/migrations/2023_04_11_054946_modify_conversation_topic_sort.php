<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyConversationTopicSort extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversation_topics', function (Blueprint $table) {
          $table->string('sort')->nullable();
        });
        
        \DB::statement("UPDATE conversation_topics SET sort = 1 WHERE name = 'Performance Check-In'");
        \DB::statement("UPDATE conversation_topics SET sort = 2 WHERE name = 'Onboarding'");
        \DB::statement("UPDATE conversation_topics SET sort = 3 WHERE name = 'Goal Setting'");
        \DB::statement("UPDATE conversation_topics SET sort = 4 WHERE name = 'Career Development'");
        \DB::statement("UPDATE conversation_topics SET sort = 5 WHERE name = 'Performance Improvement'");
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
