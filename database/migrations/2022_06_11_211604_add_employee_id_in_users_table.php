<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeIdInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('employee_id')->nullable()->first();
            $table->unsignedBigInteger('empl_record')->nullable()->after('employee_id');

            $table->index(['employee_id', 'empl_record']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropIndex(['employee_id', 'empl_record']);
            
            $table->dropColumn('employee_id');
            $table->dropColumn('empl_record');



        });
    }
}
