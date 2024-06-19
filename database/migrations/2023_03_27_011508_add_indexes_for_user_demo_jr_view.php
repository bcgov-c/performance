<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddIndexesForUserDemoJrView extends Migration
{
    public function createTableIndexIfNotExist($tableName, $indexName, $fieldNames = []) {
        $exists = DB::table('information_schema.statistics')
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->get()->count();
        if (!$exists) {
            Schema::table($tableName, function (Blueprint $table) use($indexName, $fieldNames) {
                $table->index($fieldNames, $indexName);
            });
        }
    }

    public function dropExistingTableIndex($tableName, $indexName) {
        $exists = DB::table('information_schema.statistics')
            ->where('table_name', $tableName)
            ->where('index_name', $indexName)
            ->get()->count();
        if ($exists) {
            Schema::table($tableName, function($table) use($indexName) {
                $table->dropIndex($indexName);
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->down();
        $this->createTableIndexIfNotExist('employee_demo_jr', 'idx_for_userdemojrview_j', ['employee_id', 'id']);
        $this->createTableIndexIfNotExist('employee_demo_tree', 'idx_edt_deptid', ['deptid']);
        $this->createTableIndexIfNotExist('employee_demo', 'idx_employee_demo_deptid', ['deptid']);
        $this->createTableIndexIfNotExist('employee_demo', 'employee_demo_employee_id_empl_record_index', ['employee_id', 'empl_record']);
        $this->createTableIndexIfNotExist('users', 'idx_users_id', ['id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('employee_demo_jr', 'idx_for_userdemojrview_j');
        $this->dropExistingTableIndex('employee_demo_tree', 'idx_edt_deptid');
        $this->dropExistingTableIndex('employee_demo', 'idx_employee_demo_deptid');
        $this->dropExistingTableIndex('employee_demo', 'employee_demo_employee_id_empl_record_index');
        $this->dropExistingTableIndex('users', 'idx_users_id');
    }
}
