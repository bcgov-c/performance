<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTitleInDashboardMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dashboard_message', function (Blueprint $table) {
            $table->text('title')->nullable()->first();
            $table->id()->first();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dashboard_message', function (Blueprint $table) {
            if(Schema::hasColumn('dashboard_message', 'title')) {
                $table->dropColumn('title');
            }
            if(Schema::hasColumn('dashboard_message', 'id')) {
                $table->dropColumn('id');
            }
        });
    }
}
