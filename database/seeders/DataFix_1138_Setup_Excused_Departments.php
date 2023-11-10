<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExcusedDepartment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1138_Setup_Excused_Departments extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info(Carbon::now()." - Setup Excused Departments Start");
        // ExcusedDepartment::create(['deptid' => '105-0751', 'updated_by' => 'Setup']);
        ExcusedDepartment::create(['deptid' => '105-1115', 'updated_by' => 'Setup']);
        // ExcusedDepartment::create(['deptid' => '105-1120', 'updated_by' => 'Setup']);
        // ExcusedDepartment::create(['deptid' => '105-1125', 'updated_by' => 'Setup']);
        ExcusedDepartment::create(['deptid' => '105-1200', 'updated_by' => 'Setup']);
        $this->command->info(Carbon::now()." - Setup Excused Departments End");
    }
}
