<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertTableConversation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            //
            $table->text('info_comment7')->change();
            $table->text('info_comment8')->change();
            $table->text('info_comment9')->change();
            $table->text('info_comment10')->change();
            $table->text('emp_self_summary')->change();
            $table->text('emp_additional_comments')->change();
            $table->text('emp_action_items')->change();
            $table->text('sup_appreciation')->change();
            $table->text('sup_coaching')->change();
            $table->text('sup_evaluation')->change();
            $table->text('sup_additional_comments')->change();
            $table->text('sup_acition_items')->change();
            $table->text('sup_employee_accomplish')->change();
            $table->text('sup_supervisor_provide')->change();
            $table->text('sup_meeting_occur')->change();
            $table->text('sup_comments')->change();
            $table->text('emp_comments')->change();
            $table->text('emp_career_goal_statement')->change();
            $table->text('emp_strenghs')->change();
            $table->text('emp_areas_growth')->change();
            $table->text('emp_self_summary')->change();
            $table->text('sup_employee_growth')->change();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            //

        });
    }
}
