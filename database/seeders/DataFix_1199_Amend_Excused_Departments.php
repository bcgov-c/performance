<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExcusedDepartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1199_Amend_Excused_Departments extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info(Carbon::now()." - Setup Excused Departments Start");
        ExcusedDepartment::create(['deptid' => '105-2111', 'updated_by' => 'Setup']);
        $this->command->info(Carbon::now()." - Setup Excused Departments End");
    }
}
