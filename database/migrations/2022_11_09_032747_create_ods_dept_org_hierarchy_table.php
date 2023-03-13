<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
 
class CreateOdsDeptOrgHierarchyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ods_dept_org_hierarchy_stg', function (Blueprint $table) {
            // $table->string('id')->unique();
            $table->string('OrgID')->unique();
            $table->unsignedBigInteger('HierarchyLevel')->nullable();
            $table->unsignedBigInteger('ParentOrgHierarchyKey')->nullable();
            $table->unsignedBigInteger('OrgHierarchyKey')->nullable();
            $table->string('DepartmentID')->nullable();
            $table->string('BusinessName')->nullable();
            $table->datetime('date_deleted')->nullable();
            $table->datetime('date_updated')->nullable();
            $table->timestamps(); 
            $table->primary('OrgID');
            $table->index(['date_updated'], 'idx_byDateUpdated');
            $table->index(['DepartmentID', 'date_updated'], 'idx_byDeptDateUpdated');
            $table->index(['HierarchyLevel', 'ParentOrgHierarchyKey', 'OrgHierarchyKey'], 'idx_byHierarchy');
        });

        Schema::create('ods_dept_org_hierarchy', function (Blueprint $table) {
            $table->string('orgid')->unique();
            $table->unsignedBigInteger('hlevel')->nullable();
            $table->unsignedBigInteger('pkey')->nullable();
            $table->unsignedBigInteger('okey')->nullable();
            $table->string('deptid')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('ulevel')->nullable();
            $table->string('organization')->nullable();
            $table->string('organization_label')->nullable();
            $table->string('organization_deptid')->nullable();
            $table->string('organization_key')->nullable();
            $table->string('level1')->nullable();
            $table->string('level1_label')->nullable();
            $table->string('level1_deptid')->nullable();
            $table->string('level1_key')->nullable();
            $table->string('level2')->nullable();
            $table->string('level2_label')->nullable();
            $table->string('level2_deptid')->nullable();
            $table->string('level2_key')->nullable();
            $table->string('level3')->nullable();
            $table->string('level3_label')->nullable();
            $table->string('level3_deptid')->nullable();
            $table->string('level3_key')->nullable();
            $table->string('level4')->nullable();
            $table->string('level4_label')->nullable();
            $table->string('level4_deptid')->nullable();
            $table->string('level4_key')->nullable();
            $table->string('level5')->nullable();
            $table->string('level5_label')->nullable();
            $table->string('level5_deptid')->nullable();
            $table->string('level5_key')->nullable();
            $table->string('search_key')->nullable();
            $table->longtext('org_path')->nullable();
            $table->datetime('date_deleted')->nullable();
            $table->dateTime('date_updated')->nullable();
            $table->tinyInteger('exception')->default(0);
            $table->string('exception_reason')->nullable();
            $table->string('depts')->nullable();
            $table->tinyInteger('unallocated')->default(0);
            $table->tinyInteger('duplicate')->default(0);
            $table->timestamps();
            $table->primary('orgid');
            $table->index(['deptid', 'okey'], 'idx_byHierarchyDeptOkey');
            $table->index(['name', 'okey'], 'idx_byHierarchyNameOkey');
            $table->index(['ulevel', 'okey'], 'idx_byHierarchyUlevelOkey');
            $table->index(['okey'], 'idx_byHierarchyokey');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ods_dept_org_hierarchy_stg');
        Schema::dropIfExists('ods_dept_org_hierarchy');
    }
}
