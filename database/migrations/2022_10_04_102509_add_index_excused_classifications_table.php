<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexExcusedClassificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('excused_classifications', function (Blueprint $table) {
            //
            $table->index(['jobcode_desc']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('excused_classifications', function (Blueprint $table) {
            //
            $table->dropIndex(['jobcode_desc']);
        });
    }
}
