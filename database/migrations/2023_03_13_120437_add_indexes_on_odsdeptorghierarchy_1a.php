<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnOdsdeptorghierarchy1a extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('ods_dept_org_hierarchy', function (Blueprint $table) {
            $table->index(['deptid', 'organization_key']);
            $table->index(['deptid', 'level1_key']);
            $table->index(['deptid', 'level2_key']);
            $table->index(['deptid', 'level3_key']);
            $table->index(['deptid', 'level4_key']);
            $table->index(['deptid', 'level5_key']);
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
        Schema::table('ods_dept_org_hierarchy', function (Blueprint $table) {
            $table->dropIndex(['deptid', 'organization_key']);
            $table->dropIndex(['deptid', 'level1_key']);
            $table->dropIndex(['deptid', 'level2_key']);
            $table->dropIndex(['deptid', 'level3_key']);
            $table->dropIndex(['deptid', 'level4_key']);
            $table->dropIndex(['deptid', 'level5_key']);
        });
    }
}
