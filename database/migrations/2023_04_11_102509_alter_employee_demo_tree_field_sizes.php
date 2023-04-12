<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEmployeeDemoTreeFieldSizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('employee_demo_tree', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('deptid', 10)->change();
            $table->string('organization', 100)->change();
            $table->string('level1_program', 100)->change();
            $table->string('level2_division', 100)->change();
            $table->string('level3_branch', 100)->change();
            $table->string('level4', 100)->change();
            $table->string('level5', 100)->change();
            $table->string('organization_deptid', 10)->change();
            $table->string('level1_deptid', 10)->change();
            $table->string('level2_deptid', 10)->change();
            $table->string('level3_deptid', 10)->change();
            $table->string('level4_deptid', 10)->change();
            $table->string('level5_deptid', 10)->change();
            $table->string('organization_orgid', 20)->change();
            $table->string('level1_orgid', 20)->change();
            $table->string('level2_orgid', 20)->change();
            $table->string('level3_orgid', 20)->change();
            $table->string('level4_orgid', 20)->change();
            $table->string('level5_orgid', 20)->change();
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
