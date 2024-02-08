<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExcludedDepartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1195_Setup_Excluded_Departments extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info(Carbon::now()." - Setup Excluded Departments Start");
        ExcludedDepartment::create(['deptid' => '105-1115', 'updated_by' => 'Setup']);
        ExcludedDepartment::create(['deptid' => '105-1200', 'updated_by' => 'Setup']);
        $this->command->info(Carbon::now()." - Setup Excluded Departments End");
    }
}
