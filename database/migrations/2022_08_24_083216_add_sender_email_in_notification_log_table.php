<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSenderEmailInNotificationLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            //
            $table->string('sender_email')->nullable()->after('sender_id');
            $table->string('overdue_user_id')->nullable()->after('notify_user_id');
            $table->boolean('use_queue')->after('status');

            $table->index(['date_sent']);
            $table->index(['alert_type', 'alert_format']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            //
            $table->dropColumn('sender_email');
            $table->dropColumn('overdue_user_id');
            $table->dropColumn('use_queue');

            $table->dropIndex(['date_sent']);
            $table->dropIndex(['alert_type', 'alert_format']);
        });
    }
}
