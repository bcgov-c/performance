<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdsTmpMetaDeptOrgLevelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ods_tmp_meta_dept_org_levels', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('jobsched_id');
            $table->string('DepartmentID');
            $table->string('Organization')->nullable();
            $table->string('Level1')->nullable();
            $table->string('Level2')->nullable();
            $table->string('Level3')->nullable();
            $table->string('Level4')->nullable();
            $table->datetime('date_deleted')->nullable();
            $table->datetime('date_updated')->nullable();

            $table->foreign('jobsched_id')->references('id')->on('job_sched_audit')->onDelete('cascade');

            $table->timestamps();

            $table->index(['jobsched_id', 'date_updated'], 'idx_byDateUpdated');
            $table->index(['jobsched_id', 'DepartmentID', 'date_updated'], 'idx_byDeptDateUpdated');
        });

        Schema::create('ods_dept_org_levels', function (Blueprint $table) {

            $table->dateTime('effdt');
            $table->string('deptid');

            $table->string('organization')->nullable();
            $table->string('level1_program')->nullable();
            $table->string('level2_division')->nullable();
            $table->string('level3_branch')->nullable();
            $table->string('level4')->nullable();
            $table->dateTime('date_updated')->nullable();

            $table->primary(['effdt', 'deptid']);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ods_tmp_meta_dept_org_levels');
        Schema::dropIfExists('ods_dept_org_levels');
    }
}
