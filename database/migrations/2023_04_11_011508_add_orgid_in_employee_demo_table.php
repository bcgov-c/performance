<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrgidInEmployeeDemoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->addNewString('employee_demo', 'orgid');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('employee_demo', 'orgid');
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
    public function addNewString($tableName, $columnName) {
        if (!Schema::hasColumn($tableName, $columnName)) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $columnName) {
                $table->string($columnName)->nullable();
                echo " - Added {$tableName}.{$columnName}"; echo "\r\n";
            });
        } else {
            echo " - {$tableName}.{$columnName} already exist.  New field NOT added."; echo "\r\n";
        }
    }

}
