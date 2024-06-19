<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\ExcusedClassification;

class UpdateExcusedClassifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $classifications = [
                [ 'jobcode' => '111101', 'jobcode_desc' => 'Executive Lead', ],
                [ 'jobcode' => '023900', 'jobcode_desc' => 'Executive 1', ],
                [ 'jobcode' => '024000', 'jobcode_desc' => 'Executive 2 MS', ],
                [ 'jobcode' => '111107', 'jobcode_desc' => 'Assistant Deputy Minister 1', ],
                [ 'jobcode' => '111108', 'jobcode_desc' => 'Assistant Deputy Minister 2', ],
                [ 'jobcode' => '111106', 'jobcode_desc' => 'Assist Deputy Minister Non-OIC', ],
                [ 'jobcode' => '103011', 'jobcode_desc' => 'Associate Deputy Minister', ],
                [ 'jobcode' => '101012', 'jobcode_desc' => 'Deputy Minister', ],
                [ 'jobcode' => '101015', 'jobcode_desc' => 'Deputy Minister 1', ],
                [ 'jobcode' => '101016', 'jobcode_desc' => 'Deputy Minister 2', ],
                [ 'jobcode' => '101017', 'jobcode_desc' => 'Deputy Minister 3', ],
            ];

            foreach($classifications as $classification) {
                ExcusedClassification::updateOrCreate(['jobcode' => $classification['jobcode']], ['jobcode' => $classification['jobcode'], 'jobcode_desc' => $classification['jobcode_desc']]);
            }

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
