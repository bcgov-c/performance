<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertConversation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            //
            $table->text('info_comment1')->change();
            $table->text('info_comment2')->change();
            $table->text('info_comment3')->change();
            $table->text('info_comment4')->change();
            $table->text('info_comment5')->change();
            $table->text('info_comment6')->change();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            //

        });
    }
}
