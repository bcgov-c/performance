<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentsConversation extends Migration
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
            $table->string('emp_self_summary')->nullable()->after('info_comment11');
            $table->string('emp_additional_comments')->nullable()->after('emp_self_summary');
            $table->string('emp_action_items')->nullable()->after('emp_additional_comments');
            $table->string('sup_appreciation')->nullable()->after('emp_action_items');
            $table->string('sup_coaching')->nullable()->after('sup_appreciation');
            $table->string('sup_evaluation')->nullable()->after('sup_coaching');
            $table->string('sup_additional_comments')->nullable()->after('sup_evaluation');
            $table->string('sup_acition_items')->nullable()->after('sup_additional_comments');
            $table->string('sup_employee_accomplish')->nullable()->after('sup_acition_items');
            $table->string('sup_supervisor_provide')->nullable()->after('sup_employee_accomplish');
            $table->string('sup_meeting_occur')->nullable()->after('sup_supervisor_provide');
            $table->string('sup_comments')->nullable()->after('sup_meeting_occur');
            $table->string('emp_comments')->nullable()->after('sup_comments');
            $table->string('emp_career_goal_statement')->nullable()->after('emp_comments');
            $table->string('emp_strenghs')->nullable()->after('emp_career_goal_statement');
            $table->string('emp_areas_growth')->nullable()->after('emp_strenghs');
            $table->string('sup_employee_strengths')->nullable()->after('emp_areas_growth');
            $table->string('sup_employee_growth')->nullable()->after('sup_employee_strengths');

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
            $table->dropColumn('emp_self_summary');
            $table->dropColumn('emp_additional_comments');
            $table->dropColumn('emp_action_items');
            $table->dropColumn('sup_appreciation');
            $table->dropColumn('sup_coaching');
            $table->dropColumn('sup_evaluation');
            $table->dropColumn('sup_additional_comments');
            $table->dropColumn('sup_acition_items');
            $table->dropColumn('sup_employee_accomplish');
            $table->dropColumn('sup_supervisor_provide');
            $table->dropColumn('sup_meeting_occur');
            $table->dropColumn('sup_comments');
            $table->dropColumn('emp_comments');
            $table->dropColumn('emp_career_goal_statement');
            $table->dropColumn('emp_strenghs');
            $table->dropColumn('emp_areas_growth');
            $table->dropColumn('sup_employee_strengths');
            $table->dropColumn('sup_employee_growth');
            
        });
    }
}
