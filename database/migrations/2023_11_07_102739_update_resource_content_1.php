<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateResourceContent1 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("
            UPDATE `resource_content` SET
            `question` = 'HR Admin Access in the PDP',
            `answer` = '<h3>Overview</h3>
                        <li>Training:
                            <ul>
                                <li><a href=\"/storage/HR Administrator Guide for PDP.pdf\" target=\"_blank\">HR Administrator Guide for PDP.pdf</a></li>
                                <li><a href=\"/storage/Guide to Creating Org Goals in PDP.pdf\" target=\"_blank\">Guide to Creating Org Goals in PDP.pdf</a></li>
                            </ul>
                        </li>'
            where category = 'File Down Load Link';
        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
