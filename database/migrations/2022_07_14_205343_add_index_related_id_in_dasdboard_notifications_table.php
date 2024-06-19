<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexRelatedIdInDasdboardNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashboard_notifications', function (Blueprint $table) {
            //
            $table->string('notification_type', 10)->change();

            $table->index(['notification_type', 'related_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboard_notifications', function (Blueprint $table) {
            //
            $table->dropIndex(['notification_type', 'related_id']);
        });
    }
}
