<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFieldsToUsersAnnex2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_annex', function (Blueprint $table) {
            $table->integer('reportees')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('users_annex', 'reportees');
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


}
