<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmplRecordInUsersAnnexTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_annex', function (Blueprint $table) {
            $table->unsignedBigInteger('empl_record')->after('employee_id');
            $table->unsignedBigInteger('reporting_to_userid')->nullable()->after('reporting_to_position_number');
            $table->string('reporting_to_position_number')->nullable()->change();
            $table->string('reporting_to_name2')->nullable()->after('reporting_to_name');
        });
        $this->dropExistingTableIndex('users_annex', 'users_annex_employee_id_record_index');
        $this->createTableIndexIfNotExist('users_annex', 'users_annex_employee_id_record_index', ['employee_id', 'empl_record']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('users_annex', 'users_annex_employee_id_record_index');
        $this->dropExistingColumn('users_annex', 'empl_record');
        $this->dropExistingColumn('users_annex', 'reporting_to_userid');
        $this->dropExistingColumn('users_annex', 'reporting_to_name2');
    }

    /**
     * Drop existing column.
     *
     * @return True/False
     */
    public function dropExistingColumn($tableName, $columnName) {
        if (Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $tableName) use ($columnName) {
                $tableName->dropColumn($columnName);
            });
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
