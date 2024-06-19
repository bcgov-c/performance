<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use App\Models\StoredDate;


class StoredDatesSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seed_dates = [
            [
                'id' => 2,
                'name' => 'CalcNextConversationDate',
                'value' => Carbon::now()->subDays(3650)
            ]
        ];

        foreach ($seed_dates as $stored_date) {
            StoredDate::updateOrCreate([
              'id' => $stored_date['id'],
            ], $stored_date);
        }
    }
}
