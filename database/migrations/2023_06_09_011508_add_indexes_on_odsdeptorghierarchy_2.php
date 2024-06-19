<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesOnOdsdeptorghierarchy2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_organization_key', ['organization_key']);
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level1_key', ['level1_key']);
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level2_key', ['level2_key']);
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level3_key', ['level3_key']);
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level4_key', ['level4_key']);
        $this->createTableIndexIfNotExist('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level5_key', ['level5_key']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_organization_key');
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level1_key');
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level2_key');
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level3_key');
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level4_key');
        $this->dropExistingTableIndex('ods_dept_org_hierarchy', 'idx_ods_dept_org_hierarchy_level5_key');
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
