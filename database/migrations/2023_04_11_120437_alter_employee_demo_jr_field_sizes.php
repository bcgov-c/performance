<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEmployeeDemoJrFieldSizes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('employee_demo_jr', function (Blueprint $table) {
            $table->string('guid', 32)->change();
            $table->string('employee_id', 10)->change();
            $table->string('last_employee_status', 1)->change();
            $table->string('current_employee_status', 1)->change();
            $table->string('due_date_paused', 1)->change();
            $table->string('created_by_id', 1)->change();
            $table->string('updated_by_id', 1)->change();
            $table->string('updated_by_name', 100)->change();
            $table->string('last_classification', 10)->change();
            $table->string('current_classification', 10)->change();
            $table->string('last_manual_excuse', 1)->change();
            $table->string('current_manual_excuse', 1)->change();
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
