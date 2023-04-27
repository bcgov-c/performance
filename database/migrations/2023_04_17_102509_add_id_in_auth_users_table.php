<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdInAuthUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('auth_users');
        Schema::create('auth_users', function (Blueprint $table) {
            $table->id();
            $table->string('type', 2);
            $table->bigInteger('auth_id');
            $table->bigInteger('user_id');
            $table->datetime('date_updated');
            $table->timestamps();
        });

        $this->dropExistingTableIndex('auth_users', 'auth_users_type_auth_id_user_id_index');
        $this->createTableIndexIfNotExist('auth_users', 'idx_auth_users_type_auth_id_user_id', ['type', 'auth_id', 'user_id']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('auth_users', 'idx_auth_users_type_auth_id_user_id');
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
