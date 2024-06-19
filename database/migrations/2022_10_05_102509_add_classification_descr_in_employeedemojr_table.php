<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClassificationDescrInEmployeedemojrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_demo_jr', function (Blueprint $table) {
          $table->string('last_classification_descr')->nullable();
          $table->string('current_classification_descr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('employee_demo_jr', 'last_classification_descr');
        $this->dropExistingColumn('employee_demo_jr', 'current_classification_descr');
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
