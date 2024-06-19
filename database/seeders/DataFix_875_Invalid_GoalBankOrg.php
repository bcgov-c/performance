<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoalBankOrg;


class DataFix_875_Invalid_GoalBankOrg extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        GoalBankOrg::whereNull('version')->delete();
    }
}
