<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DataFix_1130_Disable_R11 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Update R11 entry in employee demographics
        \DB::statement("UPDATE employee_demo SET date_deleted = '2023-10-01' WHERE employee_id = '178226' AND empl_record = 1;");
    }
}
