<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterEmployeeDemoTreeLevel6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // ODS Dept Org Hierarchy
        $this->addNewString('ods_dept_org_hierarchy', 'level6', 'level5_key', 191);
        $this->addNewString('ods_dept_org_hierarchy', 'level6_label', 'level6', 191);
        $this->addNewString('ods_dept_org_hierarchy', 'level6_deptid', 'level6_label', 191);
        $this->addNewString('ods_dept_org_hierarchy', 'level6_key', 'level6_deptid', 191);
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'ods_dept_org_hierarchy_deptid_level6_key_index', ['deptid', 'level6_key']);
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level6_key', ['level6_key']);

        // Employee Demo Tree Temp
        $this->addNewString('employee_demo_tree_temp', 'level6', 'level5', 100);
        $this->addNewString('employee_demo_tree_temp', 'level6_key', 'level5_key', 10);
        $this->addNewString('employee_demo_tree_temp', 'level6_deptid', 'level5_deptid', 10);
        $this->addNewString('employee_demo_tree_temp', 'level6_orgid', 'level5_orgid', 20);

        // Employee Demo Tree
        $this->addNewString('employee_demo_tree', 'level6', 'level5', 100);
        $this->addNewString('employee_demo_tree', 'level6_key', 'level5_key', 10);
        $this->addNewString('employee_demo_tree', 'level6_deptid', 'level5_deptid', 10);
        $this->addNewString('employee_demo_tree', 'level6_orgid', 'level5_orgid', 20);

        // Users Annex
        $this->addNewString('users_annex', 'level6', 'level5', 100);
        $this->addNewBigIntUnsigned('users_annex', 'level6_key', 'level5_key');
        $this->addNewString('users_annex', 'level6_deptid', 'level5_deptid', 10);
        $this->addNewString('users_annex', 'level6_orgid', 'level5_orgid', 20);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level6_key');
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'ods_dept_org_hierarchy_deptid_level6_key_index');
        $this->dropExistingColumn('ods_dept_org_hierarchy', 'level6');
        $this->dropExistingColumn('ods_dept_org_hierarchy', 'level6_label');
        $this->dropExistingColumn('ods_dept_org_hierarchy', 'level6_deptid');
        $this->dropExistingColumn('ods_dept_org_hierarchy', 'level6_key');
        $this->dropExistingColumn('employee_demo_tree_temp', 'level6');
        $this->dropExistingColumn('employee_demo_tree_temp', 'level6_key');
        $this->dropExistingColumn('employee_demo_tree_temp', 'level6_deptid');
        $this->dropExistingColumn('employee_demo_tree_temp', 'level6_orgid');
        $this->dropExistingColumn('employee_demo_tree', 'level6');
        $this->dropExistingColumn('employee_demo_tree', 'level6_key');
        $this->dropExistingColumn('employee_demo_tree', 'level6_deptid');
        $this->dropExistingColumn('employee_demo_tree', 'level6_orgid');
        $this->dropExistingColumn('users_annex', 'level6');
        $this->dropExistingColumn('users_annex', 'level6_key');
        $this->dropExistingColumn('users_annex', 'level6_deptid');
        $this->dropExistingColumn('users_annex', 'level6_orgid');
    }


        /**
     * Drop existing column.
     *
     * @return True/False
     */
    public function dropExistingColumn($tableName, $columnName) {
        if (Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columnName) {
                $table->dropColumn($columnName);
                echo " - Dropped {$tableName}.{$columnName}"; echo "\r\n";
            });
        } else {
            echo " - {$tableName}.{$columnName} NOT found.  Nothing to drop."; echo "\r\n";
        }
    }

    /**
     * Add new column.
     *
     * @return True/False
     */
    public function addNewString($tableName, $columnName, $afterColumn, $columnSize) {
        if (!Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columnName, $afterColumn, $columnSize) {
                $table->string($columnName, $columnSize)->nullable()->after($afterColumn);
                echo " - Added {$tableName}.{$columnName}"; echo "\r\n";
            });
        } else {
            echo " - {$tableName}.{$columnName} already exist.  New field NOT added."; echo "\r\n";
        }
    }

    public function addNewBigIntUnsigned($tableName, $columnName, $afterColumn) {
        if (!Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columnName, $afterColumn) {
                $table->unsignedBigInteger($columnName)->nullable()->after($afterColumn);
                echo " - Added {$tableName}.{$columnName}"; echo "\r\n";
            });
        } else {
            echo " - {$tableName}.{$columnName} already exist.  New field NOT added."; echo "\r\n";
        }
    }

    public function createTableIndexIfNotExist($tableName, $indexName, $fieldNames = []) {
        $exists = DB::table('information_schema.statistics')
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->get()->count();
        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use($indexName, $fieldNames) {
                $table->index($fieldNames, $indexName);
            });
            echo " - Added index {$indexName} in {$tableName}"; echo "\r\n";
        } else {
            echo " - Index {$indexName} in {$tableName} already exist.  New index NOT added."; echo "\r\n";
        }
    }

    public function dropExistingTableIndex($tableName, $indexName) {
        $exists = DB::table('information_schema.statistics')
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->get()->count();
        if ($exists) {
            Schema::table($tableName, function($table) use($tableName, $indexName) {
                $table->dropIndex($indexName);
            });
            echo " - Dropped index {$indexName} in {$tableName}"; echo "\r\n";
        } else {
            echo " - Index {$indexName} in {$tableName} NOT found.  Nothing to drop."; echo "\r\n";
        }
    }

}
