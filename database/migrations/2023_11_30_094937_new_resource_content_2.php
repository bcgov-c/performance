<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class NewResourceContent2 extends Migration
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
            ('access-performance',
            'Accessing MyPerformance',
            '<p>The PDP replaced the previous performance management tool - MyPerformance - in summer 2023.</p>

            <p>You can still access your past MyPerformance profiles and while you cannot copy records directly into the PDP, you can copy and paste any relevant text into the new format.</p>
            
            <p>Note: only MyPerformance profiles that have been signed by both employee and supervisor will be kept in the system. Please make sure all required records are signed off by both parties.</p>
            
            <p><a href=\"https://employee.gov.bc.ca/epm/\" target=\"_blank\">Access your MyPerformance Profile</a> (IDIR restricted)</p>',
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
        //
    }
}
