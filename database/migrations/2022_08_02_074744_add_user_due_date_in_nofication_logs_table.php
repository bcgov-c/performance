<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUserDueDateInNoficationLogsTable extends Migration
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
            $table->bigInteger('notify_user_id')->nullable()->after('sender_id');
            $table->date('notify_due_date')->nullable()->after('notify_user_id');
            $table->integer('notify_for_days')->nullable()->after('notify_due_date');
            $table->dateTime('date_sent')->change();
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
            $table->dropColumn('notify_user_id');
            $table->dropColumn('notify_due_date');
            $table->dropColumn('notify_for_days');
            $table->date('date_sent')->change();
        });
    }

}
