<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnEmployeeDemoJr extends Migration
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
            

            $table->index(['guid','id']);
            
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
        Schema::table('employee_demo_jr', function (Blueprint $table) {
            $table->dropIndex(['guid','id']);
            

        });
    }
}
