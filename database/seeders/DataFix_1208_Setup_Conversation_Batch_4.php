<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessOrganization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1208_Setup_Conversation_Batch_4 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $batch_no = 4;
        $this->command->info(Carbon::now()." - Setup Conversation Batch {$batch_no} Start");
        // Batch 4
        // - Transportation Investment Corporation - 20602
        $groups = AccessOrganization::whereIn('orgid', [20602])->get();
        foreach($groups AS $org){
            $this->command->info(Carbon::now()." - Batch {$batch_no} - {$org->orgid} - {$org->organization}");
            $org->conversation_batch = 4;
            $org->save();
        }
        $this->command->info(Carbon::now()." - Setup Conversation Batch {$batch_no} End");
    }
}
