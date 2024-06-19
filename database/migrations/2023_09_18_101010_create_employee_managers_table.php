<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeManagersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('employee_managers');
        Schema::create('employee_managers', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 10);
            $table->string('position_number', 10)->nullable();
            $table->string('orgid', 10)->nullable();
            $table->string('supervisor_emplid', 10)->nullable();
            $table->string('supervisor_name', 100)->nullable();
            $table->string('supervisor_position_number', 10)->nullable();
            $table->string('supervisor_email')->nullable();
            $table->integer('priority')->default(99);
            $table->string('source', 10)->nullable();
        });

        $this->dropExistingTableIndex('employee_managers', 'employee_managers_employee_id_position_number_index');
        $this->createTableIndexIfNotExist('employee_managers', 'employee_managers_employee_id_position_number_index', ['employee_id', 'position_number']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('employee_managers', 'employee_managers_employee_id_position_number_index');
        Schema::dropIfExists('employee_managers');
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
