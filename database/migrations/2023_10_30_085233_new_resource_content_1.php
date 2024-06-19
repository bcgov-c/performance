<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class NewResourceContent1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            INSERT INTO `resource_content`
            (`category`,
            `question`,
            `answer`,
            `answer_file`)
            VALUES
            ('hr-admin',
            'File Down Load Link',
            '<a href=\"/storage/test_pdf.pdf\" target=\"_blank\">Click here to download file.</a>',
            '1');
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // You can define the reverse migration if needed.
    }
}