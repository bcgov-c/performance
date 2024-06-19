<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddFieldsToAccessOrganizations1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('access_organizations', function (Blueprint $table) {
            $table->integer('conversation_batch')->default(0)->after('allow_email_msg');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('access_organizations', 'conversation_batch');
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
