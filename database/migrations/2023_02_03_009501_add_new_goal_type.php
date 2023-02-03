<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\GoalType;

class AddNewGoalType extends Migration
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
                    'description' => 'Private goal only can check by owner.'
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
        
    }
}
