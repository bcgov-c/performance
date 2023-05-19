<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEmployeeDemoFieldSizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('employee_demo', function (Blueprint $table) {
            $table->string('guid', 32)->change();
            $table->string('employee_id', 10)->change();
            $table->string('employee_first_name', 100)->change();
            $table->string('employee_last_name', 100)->change();
            $table->string('employee_status', 1)->change();
            $table->string('deptid', 10)->change();
            $table->string('jobcode', 10)->change();
            $table->string('position_number', 10)->change();
            $table->string('job_indicator', 1)->change();
            $table->string('supervisor_emplid', 10)->change();
            $table->string('supervisor_position_number', 10)->change();
            $table->string('supervisor_name', 100)->change();
            $table->string('employee_name', 100)->change();
            $table->string('employee_middle_name', 100)->change();
            $table->string('employee_status_long', 100)->change();
            $table->string('empl_ctg', 1)->change();
            $table->string('empl_class', 1)->change();
            $table->string('business_unit', 10)->change();
            $table->string('orgid', 10)->change();
        });

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
