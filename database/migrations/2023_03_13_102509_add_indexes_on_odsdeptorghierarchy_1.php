<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnOdsdeptorghierarchy1 extends Migration
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
            $table->index(['organization_key', 'deptid']);
            $table->index(['level1_key', 'deptid']);
            $table->index(['level2_key', 'deptid']);
            $table->index(['level3_key', 'deptid']);
            $table->index(['level4_key', 'deptid']);
            $table->index(['level5_key', 'deptid']);
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
            $table->dropIndex(['organization_key', 'deptid']);
            $table->dropIndex(['level1_key', 'deptid']);
            $table->dropIndex(['level2_key', 'deptid']);
            $table->dropIndex(['level3_key', 'deptid']);
            $table->dropIndex(['level4_key', 'deptid']);
            $table->dropIndex(['level5_key', 'deptid']);
        });
    }
}
