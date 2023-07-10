<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessOrganization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1015_Setup_Conversation_Batches extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info(Carbon::now()." - Setup Conversation Batches Start");
        // Batch 1
        // - PSA - 2
        // - SDPR - 165
        // - Royal BC Museum - 164
        $groups = AccessOrganization::whereIn('orgid', [2, 165, 164])
            ->get();
        foreach($groups AS $org){
            $this->command->info(Carbon::now()." - Batch 1 - {$org->orgid} - {$org->organization}");
            $org->conversation_batch = 1;
            $org->save();
        }

        // Batch 2
        // - Children & Family Development - 131
        // - Education & Child Care - 137
        // - Emergency Management & Climate Readiness - 22064
        // - Finance - 140
        // - Government Communications & Public Engagement - 146
        // - Jobs, Economic Development & Innovation - 1000002
        // - Labour - 19969
        // - Mental Health & Addictions - 20078
        // - Municipal Affairs - 134
        // - Justice (Public Safety & Solicitor General, and AG) - 153
        // - Tourism, Arts, Culture & Sport - 20079

        $groups = AccessOrganization::whereIn('orgid', [131, 137, 22064, 140, 146, 1000002, 19969, 20078, 134, 153, 20079])
            ->get();
        foreach($groups AS $org){
            $this->command->info(Carbon::now()." - Batch 2 - {$org->orgid} - {$org->organization}");
            $org->conversation_batch = 2;
            $org->save();
        }

        // Batch 0 will follow last batch default conversation dates
        $this->command->info(Carbon::now()." - Setup Conversation Batches End");
    }
}
