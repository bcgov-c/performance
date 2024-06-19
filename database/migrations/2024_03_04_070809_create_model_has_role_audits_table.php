<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelHasRoleAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('model_has_role_audits');
        Schema::create('model_has_role_audits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->unsignedBigInteger('role_id');
            $table->string('deptid', 10)->nullable();
            $table->string('position_number')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->string('updated_by')->nullable();
            $table->string('deleted_by')->nullable();
        });

        $this->dropExistingTableIndex('model_has_role_audits', 'model_has_role_audits_model_id_role_id_index');
        $this->createTableIndexIfNotExist('model_has_role_audits', 'model_has_role_audits_model_id_role_id_index', ['model_id', 'role_id']);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingTableIndex('model_has_role_audits', 'model_has_role_audits_model_id_role_id_index');
        Schema::dropIfExists('model_has_role_audits');
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
