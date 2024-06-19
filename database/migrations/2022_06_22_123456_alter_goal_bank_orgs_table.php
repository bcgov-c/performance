<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterGoalBankOrgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goal_bank_orgs', function (Blueprint $table) {
            $table->string('organization')->nullable()->change();
            $table->string('level1_program')->nullable()->change();
            $table->string('level2_division')->nullable()->change();
            $table->string('level3_branch')->nullable()->change();
            $table->string('level4')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('goal_bank_orgs', function (Blueprint $table) {
            $table->string('organization')->nullable(false)->change();
            $table->string('level1_program')->nullable(false)->change();
            $table->string('level2_division')->nullable(false)->change();
            $table->string('level3_branch')->nullable(false)->change();
            $table->string('level4')->nullable(false)->change();
        });
    }
}
