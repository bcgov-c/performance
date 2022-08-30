<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();

            $table->bigInteger('user_id');

            $table->string('goal_comment_flag',1)->default('N');
            $table->string('goal_bank_flag',1)->default('N');
            $table->string('share_profile_flag',1)->default('N');

            $table->string('conversation_setup_flag',1)->default('Y');
            $table->string('conversation_signoff_flag',1)->default('Y');
            $table->string('conversation_disagree_flag',1)->default('Y');

            $table->string('conversation_due_month',1)->default('N');
            $table->string('conversation_due_week',1)->default('N');
            $table->string('conversation_due_past',1)->default('Y');

            $table->string('team_conversation_due_month',1)->default('N');
            $table->string('team_conversation_due_week',1)->default('N');
            $table->string('team_conversation_due_past',1)->default('Y');

            $table->bigInteger('created_by_id')->nullable();
            $table->bigInteger('updated_by_id')->nullable();

            $table->timestamps();

            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_preferences');
    }
}
