<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCommentConversations extends Migration
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
            $table->string('info_comment7')->nullable()->after('info_comment6');
            $table->string('info_comment8')->nullable()->after('info_comment7');
            $table->string('info_comment9')->nullable()->after('info_comment8');
            $table->string('info_comment10')->nullable()->after('info_comment9');
            $table->string('info_comment11')->nullable()->after('info_comment10');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) { $table->dropColumn('info_comment7'); });
        Schema::table('conversations', function (Blueprint $table) { $table->dropColumn('info_comment8'); });
        Schema::table('conversations', function (Blueprint $table) { $table->dropColumn('info_comment9'); });
        Schema::table('conversations', function (Blueprint $table) { $table->dropColumn('info_comment10'); });
        Schema::table('conversations', function (Blueprint $table) { $table->dropColumn('info_comment11'); });
    }
}
