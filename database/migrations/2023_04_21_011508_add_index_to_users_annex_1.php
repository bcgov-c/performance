<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIndexToUsersAnnex1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        $this->createTableIndexIfNotExist('users_annex', 'idx_users_annex_userid_orgid_organizationkey', ['user_id', 'orgid', 'organization_key']);
        $this->createTableIndexIfNotExist('users_annex', 'idx_users_annex_userid_orgid_level1key', ['user_id', 'orgid', 'level1_key']);
        $this->createTableIndexIfNotExist('users_annex', 'idx_users_annex_userid_orgid_level2key', ['user_id', 'orgid', 'level2_key']);
        $this->createTableIndexIfNotExist('users_annex', 'idx_users_annex_userid_orgid_level3key', ['user_id', 'orgid', 'level3_key']);
        $this->createTableIndexIfNotExist('users_annex', 'idx_users_annex_userid_orgid_level4key', ['user_id', 'orgid', 'level4_key']);
        $this->createTableIndexIfNotExist('users_annex', 'idx_users_annex_userid_orgid_level5key', ['user_id', 'orgid', 'level5_key']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('users_annex', 'idx_users_annex_userid_orgid_organizationkey');
        $this->dropExistingTableIndex('users_annex', 'idx_users_annex_userid_orgid_level1key');
        $this->dropExistingTableIndex('users_annex', 'idx_users_annex_userid_orgid_level2key');
        $this->dropExistingTableIndex('users_annex', 'idx_users_annex_userid_orgid_level3key');
        $this->dropExistingTableIndex('users_annex', 'idx_users_annex_userid_orgid_level4key');
        $this->dropExistingTableIndex('users_annex', 'idx_users_annex_userid_orgid_level5key');
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
