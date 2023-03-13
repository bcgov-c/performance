<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeDemoTreeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_demo_tree', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->unique();
            $table->string('name')->nullable();
            $table->string('deptid')->nullable();
            $table->integer('status')->default(1);
            $table->integer('level')->default(0);
            $table->integer('headcount')->default(0);
            $table->integer('groupcount')->default(0);
            $table->string('organization')->nullable();
            $table->string('level1_program')->nullable();
            $table->string('level2_division')->nullable();
            $table->string('level3_branch')->nullable();
            $table->string('level4')->nullable();
            $table->string('level5')->nullable();
            $table->unsignedBigInteger('organization_key')->nullable();
            $table->unsignedBigInteger('level1_key')->nullable();
            $table->unsignedBigInteger('level2_key')->nullable();
            $table->unsignedBigInteger('level3_key')->nullable();
            $table->unsignedBigInteger('level4_key')->nullable();
            $table->unsignedBigInteger('level5_key')->nullable();
            $table->string('organization_deptid')->nullable();
            $table->string('level1_deptid')->nullable();
            $table->string('level2_deptid')->nullable();
            $table->string('level3_deptid')->nullable();
            $table->string('level4_deptid')->nullable();
            $table->string('level5_deptid')->nullable();
            $table->string('organization_orgid')->nullable();
            $table->string('level1_orgid')->nullable();
            $table->string('level2_orgid')->nullable();
            $table->string('level3_orgid')->nullable();
            $table->string('level4_orgid')->nullable();
            $table->string('level5_orgid')->nullable();
            $table->nestedSet();
            $table->timestamps();
            $table->index(['name', 'id'], 'idx_edt_name_id');
            $table->index(['deptid', 'id'], 'idx_edt_deptid_id');
            $table->index(['level', 'id'], 'idx_edt_level_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_demo_tree');
    }
}
