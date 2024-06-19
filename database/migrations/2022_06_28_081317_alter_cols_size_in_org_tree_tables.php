<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterColsSizeInOrgTreeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('organization_trees', function (Blueprint $table) {
            //
            $table->string('organization', 100)->change();
            $table->string('level1_program', 100)->change();
            $table->string('level2_division', 100)->change();
            $table->string('level3_branch', 100)->change();
            $table->string('level4', 100)->change();

            $table->dropIndex('org_structure');
            $table->index(['level', 'organization', 'level1_program','level2_division', 'level3_branch', 'level4'], 'organization_structure');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('organization_trees', function (Blueprint $table) {
            //
            $table->dropIndex('organization_structure');
            $table->index(['level1_program','level2_division', 'level3_branch', 'level4'], 'org_structure');
        });
    }
}
