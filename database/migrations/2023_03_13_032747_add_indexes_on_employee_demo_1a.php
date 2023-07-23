<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnEmployeeDemo1a extends Migration
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
            $table->index(['employee_id', 'deptid', 'date_deleted']);
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
        Schema::table('employee_demo', function (Blueprint $table) {
            $table->dropIndex(['employee_id', 'deptid', 'date_deleted']);
        });
    }
}
