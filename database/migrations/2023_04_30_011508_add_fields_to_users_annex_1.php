<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFieldsToUsersAnnex1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_annex', function (Blueprint $table) {
            $table->string('isSupervisor', 1)->default(0);
            $table->string('isDelegate', 1)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('users_annex', 'isSupervisor');
        $this->dropExistingColumn('users_annex', 'isDelegate');
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
