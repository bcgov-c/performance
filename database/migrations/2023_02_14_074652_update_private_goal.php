<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\GoalType;

class UpdatePrivateGoal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
     {
        $goaltypes = [
                [
                    'id' => 4,
                    'name' => 'Private',
                    'order' => 4,
                    'description' => 'Private Goals are only visible to you as the goal owner. You can change this status later if you want to make them visible to others.'
                ]
            ];

            foreach($goaltypes as $goaltype) {
                GoalType::updateOrCreate([
                    'id' => $goaltype['id'],
                ], $goaltype);
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
