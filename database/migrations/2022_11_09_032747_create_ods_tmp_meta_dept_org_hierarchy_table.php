<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdsTmpMetaDeptOrgHierarchyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ods_tmp_meta_dept_org_hierarchy', function (Blueprint $table) {

            $table->id();

            $table->unsignedBigInteger('jobsched_id');
            $table->string('DepartmentID')->nullable();
            $table->string('BusinessName')->nullable();
            $table->unsignedBigInteger('HierarchyLevel')->nullable();
            $table->unsignedBigInteger('OrgHierarchyKey')->nullable();
            $table->unsignedBigInteger('ParentOrgHierarchyKey')->nullable();
            $table->datetime('date_deleted')->nullable();
            $table->datetime('date_updated')->nullable();

            $table->foreign('jobsched_id')->references('id')->on('job_sched_audit')->onDelete('cascade');

            $table->timestamps();

            $table->index(['jobsched_id', 'date_updated'], 'idx_byDateUpdated');
            $table->index(['jobsched_id', 'DepartmentID', 'date_updated'], 'idx_byDeptDateUpdated');
        });

        Schema::create('ods_dept_org_hierarchy', function (Blueprint $table) {

            $table->dateTime('effdt');
            $table->string('deptid');

            $table->string('name')->nullable();
            $table->unsignedBigInteger('hlevel')->nullable();
            $table->unsignedBigInteger('olevel')->nullable();
            $table->unsignedBigInteger('plevel')->nullable();
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
        Schema::dropIfExists('ods_tmp_meta_dept_org_hierarchy');
        Schema::dropIfExists('ods_dept_org_hierarchy');
    }
}
