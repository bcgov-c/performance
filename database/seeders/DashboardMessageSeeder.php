<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\DashboardMessage;

class DashboardMessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $list = [
            [
                'message' => 'Welcome to the early implementation of the Performance Development Platform (PDP)! Please check out the resources section and click on the info icons throughout the platform to learn more about all the new functions.',
                'status' => 0,
            ],
        ];

        foreach ($list as $l) {
            \App\Models\DashboardMessage::updateOrCreate([
                
            ], $l);
        }
    }
}
