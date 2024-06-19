<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccessOrganization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1121_Setup_Conversation_Batch_3 extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info(Carbon::now()." - Setup Conversation Batch 3 Start");
        // Batch 3
        // - Agriculture & Food - 126 
        // - Citizens' Services - 166
        // - Energy Mines & Low Carbon Innovation - 138
        // - Environment & Climate Change Strategy - 139
        // - Forests - 144
        // - Indigenous Relations & Reconciliation - 124
        // - Post-Secondary Education and Future Skills - 125
        // - Transportation & Infrastructure - 168
        // - Transportation Investment Corporation - 20602
        // - Water, Land & Resource Stewardship - 21352
        $groups = AccessOrganization::whereIn('orgid', [126, 166, 138, 139, 144, 124, 125, 168, 20602, 21352])
            ->get();
        foreach($groups AS $org){
            $this->command->info(Carbon::now()." - Batch 3 - {$org->orgid} - {$org->organization}");
            $org->conversation_batch = 3;
            $org->save();
        }

        // Batch 0 will follow last batch default conversation dates
        $this->command->info(Carbon::now()." - Setup Conversation Batch 3 End");
    }
}
