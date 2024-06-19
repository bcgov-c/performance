<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAnnexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_annex', function (Blueprint $table) {
            $table->bigInteger('user_id')->nullable();
            $table->string('orgid', 10)->nullable();
            $table->string('employee_id', 10)->nullable();

            $table->integer('level')->nullable();
            $table->integer('headcount')->nullable();
            $table->integer('groupcount')->nullable();
            $table->string('organization', 100)->nullable();
            $table->string('level1_program', 100)->nullable();
            $table->string('level2_division', 100)->nullable();
            $table->string('level3_branch', 100)->nullable();
            $table->string('level4', 100)->nullable();
            $table->string('level5', 100)->nullable();
            $table->unsignedBigInteger('organization_key')->nullable();
            $table->unsignedBigInteger('level1_key')->nullable();
            $table->unsignedBigInteger('level2_key')->nullable();
            $table->unsignedBigInteger('level3_key')->nullable();
            $table->unsignedBigInteger('level4_key')->nullable();
            $table->unsignedBigInteger('level5_key')->nullable();
            $table->string('organization_deptid', 10)->nullable();
            $table->string('level1_deptid', 10)->nullable();
            $table->string('level2_deptid', 10)->nullable();
            $table->string('level3_deptid', 10)->nullable();
            $table->string('level4_deptid', 10)->nullable();
            $table->string('level5_deptid', 10)->nullable();
            $table->string('organization_orgid', 20)->nullable();
            $table->string('level1_orgid', 20)->nullable();
            $table->string('level2_orgid', 20)->nullable();
            $table->string('level3_orgid', 20)->nullable();
            $table->string('level4_orgid', 20)->nullable();
            $table->string('level5_orgid', 20)->nullable();

            $table->string('reporting_to_employee_id', 10)->nullable();
            $table->string('reporting_to_name', 100)->nullable();
            $table->string('reporting_to_email')->nullable();

            $table->unsignedBigInteger('jr_id')->nullable();
            $table->string('jr_due_date_paused', 1)->nullable();
            $table->date('jr_next_conversation_date')->nullable();
            $table->string('jr_excused_type', 1)->nullable();
            $table->string('jr_current_manual_excuse', 1)->nullable();
            $table->string('jr_created_by_id', 10)->nullable();
            $table->datetime('jr_created_at')->nullable();
            $table->string('jr_updated_by_id', 10)->nullable();
            $table->datetime('jr_updated_at')->nullable();
            $table->string('jr_excused_reason_id', 1)->nullable();
            $table->string('jr_excused_reason_desc')->nullable();
            $table->string('jr_updated_by_name', 100)->nullable();

            $table->string('excused_updated_by_name', 100)->nullable();
            $table->string('r_name', 100)->nullable();

            $table->string('reason_id', 1)->nullable();
            $table->string('reason_name', 100)->nullable();
            $table->string('excusedtype', 100)->nullable();
            $table->string('excusedlink')->nullable();
            $table->string('excused_by_name', 100)->nullable();
            $table->string('created_at_string')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'orgid'], 'idx_users_annex_userid_orgid');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_annex');
    }
}
