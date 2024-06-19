<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOdsDepartmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ods_departments', function (Blueprint $table) {
            $table->unsignedBigInteger('jobsched_id');
            $table->string('deptid');
            $table->string('organization');
            $table->string('level1_program');
            $table->string('level2_division');
            $table->string('level3_branch');
            $table->string('level4');

            $table->datetime('date_created');

            $table->primary(['jobsched_id', 'deptid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ods_departments');
    }
}
