<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDemoTreeTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_demo_tree_temp', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->string('name', 100)->nullable();
            $table->string('deptid', 10)->nullable();
            $table->integer('status')->default(1);
            $table->integer('level')->default(0);
            $table->integer('headcount')->default(0);
            $table->integer('groupcount')->default(0);
            $table->string('organization', 100)->nullable();
            $table->string('level1_program', 100)->nullable();
            $table->string('level2_division', 100)->nullable();
            $table->string('level3_branch', 100)->nullable();
            $table->string('level4', 100)->nullable();
            $table->string('level5', 100)->nullable();
            $table->unsignedBigInteger('organization_key')->nullable();
            $table->unsignedBigInteger('level1_key')->nullable();
            $table->unsignedBigInteger('level2_key')->nullable();
            $table->unsignedBigInteger('level3_key')->nullable();
            $table->unsignedBigInteger('level4_key')->nullable();
            $table->unsignedBigInteger('level5_key')->nullable();
            $table->string('organization_deptid', 10)->nullable();
            $table->string('level1_deptid', 10)->nullable();
            $table->string('level2_deptid', 10)->nullable();
            $table->string('level3_deptid', 10)->nullable();
            $table->string('level4_deptid', 10)->nullable();
            $table->string('level5_deptid', 10)->nullable();
            $table->string('organization_orgid')->nullable();
            $table->string('level1_orgid', 20)->nullable();
            $table->string('level2_orgid', 20)->nullable();
            $table->string('level3_orgid', 20)->nullable();
            $table->string('level4_orgid', 20)->nullable();
            $table->string('level5_orgid', 20)->nullable();
            $table->nestedSet();
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
        Schema::dropIfExists('employee_demo_tree_temp');
    }
}
