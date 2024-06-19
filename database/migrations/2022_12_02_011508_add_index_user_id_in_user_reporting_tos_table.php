<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexUserIdInUserReportingTosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_reporting_tos', function (Blueprint $table) {
            //
            $table->index(['user_id', 'reporting_to_id'],'idx_user_id');	
            $table->index(['reporting_to_id', 'user_id'], 'idx_reporting_to_id');	
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_reporting_tos', function (Blueprint $table) {
            //
            $table->dropIndex('idx_user_id');	
            $table->dropIndex('idx_reporting_to_id');	
        });
    }
}
