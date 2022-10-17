<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexOverdueInfoInNotificationLogsTable extends Migration
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
            $table->index(['alert_type','alert_format', 'notify_user_id', 'overdue_user_id', 'notify_due_date', 'notify_for_days'],'overdue_notify_log');	
            $table->index(['subject']);	
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
            $table->dropIndex('overdue_notify_log');	
            $table->dropIndex(['subject']);	
        });
    }
}
