<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEmployeeidInEmployeedemojrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('employee_demo_jr', function (Blueprint $table) {
          $table->string('employee_id')->after('guid')->nullable();
          $table->index(['employee_id','id']);
        });

        \DB::statement("UPDATE employee_demo_jr SET employee_id = (SELECT ed.employee_id FROM employee_demo AS ed WHERE ed.guid = employee_demo_jr.guid AND ed.date_updated = (SELECT max(ed1.date_updated) FROM employee_demo AS ed1 WHERE ed1.guid = ed.guid))");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('employee_demo_jr', 'employee_id');
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
