<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrgidInGoalbankorgsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('goal_bank_orgs', function (Blueprint $table) {
            $table->string('orgid')->nullable()->after('version');
            $table->boolean('inherited')->after('level4')->default(0);
            $table->index(['orgid', 'goal_id']);
            $table->index(['goal_id', 'orgid']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->dropExistingColumn('goal_bank_orgs', 'orgid');
        $this->dropExistingColumn('goal_bank_orgs', 'inherited');
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
