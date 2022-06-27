<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnEmployeeDemo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_demo', function (Blueprint $table) {
            //
            $table->index(['level1_program','level2_division', 'level3_branch', 'level4'], 'org_structure');
            $table->index(['organization']);
            $table->index(['jobcode_desc']);
            $table->index(['employee_name']);
            $table->index(['deptid']);
            $table->index(['classification']);
                        
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('employee_demo', function (Blueprint $table) {
            //
            $table->dropIndex('org_structure');
            $table->dropIndex(['organization']);
            $table->dropIndex(['jobcode_desc']);
            $table->dropIndex(['employee_name']);
            $table->dropIndex(['deptid']);
            $table->dropIndex(['classification']);

        });
    }
}
