<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ExcusedReason;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1138_Add_Excused_Reason_Department extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info(Carbon::now()." - Add Excused Reason Department Start");
        \DB::statement("INSERT INTO excused_reasons (id, name, description, created_at, updated_at) VALUES (0, 'Department', 'Department', NOW(), NOW())");
        \DB::statement("UPDATE excused_reasons SET id = 0 WHERE name = 'Department'");
        $this->command->info(Carbon::now()." - Add Excused Reason Department End");
    }
}
