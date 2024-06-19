<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeSupervisorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('employee_supervisor');
        Schema::create('employee_supervisor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->string('reason')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->string('updated_by')->nullable();
        });

        $this->dropExistingTableIndex('employee_supervisor', 'employee_supervisor_supervisor_id_user_id_index');
        $this->createTableIndexIfNotExist('employee_supervisor', 'employee_supervisor_supervisor_id_user_id_index', ['supervisor_id', 'user_id']);
        $this->dropExistingTableIndex('employee_supervisor', 'employee_supervisor_user_id_supervisor_id_index');
        $this->createTableIndexIfNotExist('employee_supervisor', 'employee_supervisor_user_id_supervisor_id_index', ['user_id', 'supervisor_id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('employee_supervisor', 'employee_supervisor_supervisor_id_user_id_index');
        $this->dropExistingTableIndex('employee_supervisor', 'employee_supervisor_user_id_supervisor_id_index');
        Schema::dropIfExists('employee_supervisor');
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
