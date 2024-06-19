<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalAuthUserInAuditsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('audits', function (Blueprint $table) {
            //
            $table->bigInteger('original_auth_id')->nullable()->after('user_id');

            $table->index(['user_id', 'original_auth_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('audits', function (Blueprint $table) {
            //
            $table->dropIndex(['user_id', 'original_auth_id']);

            $table->dropColumn('original_auth_id');

        });
    }
}
