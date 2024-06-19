<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExcusedReason;


class ExcusedReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $excused_reasons = [
            [
                'id' => 1,
                'name' => 'PeopleSoft Status',
                'description' => 'PeopleSoft Status'
            ],
            [
                'id' => 2,
                'name' => 'Classification',
                'description' => 'Classification'
            ],
            [
                'id' => 3,
                'name' => 'Casual Employee',
                'description' => 'Casual Employee'
            ],
            [
                'id' => 4,
                'name' => 'Leave Not Captured in PeopleSoft',
                'description' => 'Leave Not Captured in PeopleSoft'
            ],
            [
                'id' => 5,
                'name' => 'Student',
                'description' => 'Student'
            ]
        ];

        foreach ($excused_reasons as $excused_reason) {
            ExcusedReason::updateOrCreate(['id' => $excused_reason['id']
            ], $excused_reason);
        }
    }
}
