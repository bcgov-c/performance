<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Goal;

class GoalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
     

        // \App\Models\Goal::factory(1)->create();
        $goal_types = \App\Models\GoalType::pluck('id')->toArray();
        $tags       = \App\Models\Tag::pluck('id')->toArray();
        $users      = \App\Models\User::whereIn('id', 
                            // [1,2,3,4,7,8]
                            [9,21,22,23,24,25,31,32,33,34,35]
                            )->pluck('id')->toArray();

        $faker = \Faker\Factory::create();

        for ($x = 0; $x <= 50; $x++) {
        
            $goal = Goal::create([
                'title' => $faker->text(),
                'start_date' => $faker->dateTimeThisMonth(),
                'target_date' => $faker->dateTimeBetween($startDate = '-1 years', $endDate = '+1 months'),
                'what' => $faker->sentence(),
                'why' => $faker->paragraph,
                'how' => $faker->paragraph,
                'measure_of_success' => $faker->paragraph,
                // 'status' => $faker->randomElement(['active', 'not met', 'cancelled or deferred']),
                'status' => $faker->randomElement(['active', 'achieved', 'archived']),
                'goal_type_id' => $faker->randomElement( $goal_types ),
                'user_id' => $faker->randomElement( $users ),
            ]);

            $goal_tags = [ $faker->randomElement( $tags ), $faker->randomElement( $tags ) ];
            $goal->tags()->sync($goal_tags);
        
        }    


    }
}
