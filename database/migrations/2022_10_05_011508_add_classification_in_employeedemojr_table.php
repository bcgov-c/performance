<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClassificationInEmployeedemojrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_demo_jr', function (Blueprint $table) {
          $table->string('last_classification')->nullable();
          $table->string('current_classification')->nullable();
          $table->string('last_manual_excuse')->nullable();
          $table->string('current_manual_excuse')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('employee_demo_jr', 'last_classification');
        $this->dropExistingColumn('employee_demo_jr', 'current_classification');
        $this->dropExistingColumn('employee_demo_jr', 'last_manual_excuse');
        $this->dropExistingColumn('employee_demo_jr', 'current_manual_excuse');
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

}
