<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AdminOrg;
use App\Models\GoalBankOrg;
use App\Models\EmployeeDemoTree;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1019_Prod2_Rollout extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $version1 = 1;
        $version2 = 2;

        // Create version 2 entries in admin_orgs table
        $this->command->info(Carbon::now()." - Update admin_orgs from version 1 to 2");
        $admorgs = AdminOrg::where('version', '=', $version1)->orderBy('user_id')->get();
        foreach($admorgs AS $org){
            $tree = EmployeeDemoTree::whereNotNull('organization')
                ->when($org->organization, function($q)use($org){return $q->where('organization', '=', $org->organization);})
                ->when($org->level1_program, function($q)use($org){return $q->where('level1_program', '=', $org->level1_program);})
                ->when($org->level2_division, function($q)use($org){return $q->where('level2_division', '=', $org->level2_division);})
                ->when($org->level3_branch, function($q)use($org){return $q->where('level3_branch', '=', $org->level3_branch);})
                ->when($org->level4, function($q)use($org){return $q->where('level4', '=', $org->level4);})
                ->when(!$org->organization, function($q){return $q->whereNull('organization');})
                ->when(!$org->level1_program, function($q){return $q->whereNull('level1_program');})
                ->when(!$org->level2_division, function($q){return $q->whereNull('level2_division');})
                ->when(!$org->level3_branch, function($q){return $q->whereNull('level3_branch');})
                ->when(!$org->level4, function($q){return $q->whereNull('level4');})
                ->first();
            if($tree){
                $ver2 = AdminOrg::where('user_id', '=', $org->user_id)
                    ->where('version', '=', $version2)
                    ->where('orgid', '=', $tree->id)
                    ->first();
                if($ver2) {
                    $this->command->info(Carbon::now()." - Version 2 entry found for {$org->user_id} - {$tree->id} - {$org->organization}>{$org->level1_program}>{$org->level2_division}>{$org->level3_branch}>{$org->level4}");
                } else {
                    $this->command->info(Carbon::now()." - Creating version 2 entry for {$org->user_id} - {$tree->id} - {$org->organization}>{$org->level1_program}>{$org->level2_division}>{$org->level3_branch}>{$org->level4}");
                    AdminOrg::create([
                        'user_id' => $org->user_id,
                        'version' => 2,
                        'orgid' => $tree->id,
                        'inherited' => 0
                    ]);
                }
            } else {
                $this->command->info(Carbon::now()." - Exception: Org NOT FOUND for {$org->user_id} - {$org->organization}>{$org->level1_program}>{$org->level2_division}>{$org->level3_branch}>{$org->level4}");
            }
        }

        // Create version 2 entries in goal_bank_orgs table
        $this->command->info(Carbon::now()." - Update goal_bank_orgs from version 1 to 2");
        $goalorgs = GoalBankOrg::where('version', '=', $version1)->orderBy('goal_id')->get();
        foreach($goalorgs AS $org){
            $tree = EmployeeDemoTree::whereNotNull('organization')
                ->when($org->organization, function($q)use($org){return $q->where('organization', '=', $org->organization);})
                ->when($org->level1_program, function($q)use($org){return $q->where('level1_program', '=', $org->level1_program);})
                ->when($org->level2_division, function($q)use($org){return $q->where('level2_division', '=', $org->level2_division);})
                ->when($org->level3_branch, function($q)use($org){return $q->where('level3_branch', '=', $org->level3_branch);})
                ->when($org->level4, function($q)use($org){return $q->where('level4', '=', $org->level4);})
                ->when(!$org->organization, function($q){return $q->whereNull('organization');})
                ->when(!$org->level1_program, function($q){return $q->whereNull('level1_program');})
                ->when(!$org->level2_division, function($q){return $q->whereNull('level2_division');})
                ->when(!$org->level3_branch, function($q){return $q->whereNull('level3_branch');})
                ->when(!$org->level4, function($q){return $q->whereNull('level4');})
                ->first();
            if($tree){
                $ver2 = GoalBankOrg::where('goal_id', '=', $org->goal_id)
                    ->where('version', '=', $version2)
                    ->where('orgid', '=', $tree->id)
                    ->first();
                if($ver2) {
                    $this->command->info(Carbon::now()." - Version 2 entry found for {$org->goal_id} - {$tree->id} - {$org->organization}>{$org->level1_program}>{$org->level2_division}>{$org->level3_branch}>{$org->level4}");
                } else {
                    $this->command->info(Carbon::now()." - Creating version 2 entry for {$org->goal_id} - {$tree->id} - {$org->organization}>{$org->level1_program}>{$org->level2_division}>{$org->level3_branch}>{$org->level4}");
                    GoalBankOrg::create([
                        'goal_id' => $org->goal_id,
                        'version' => 2,
                        'orgid' => $tree->id,
                        'inherited' => 0
                    ]);
                }
            } else {
                $this->command->info(Carbon::now()." - Exception: Org NOT FOUND for {$org->goal_id} - {$org->organization}>{$org->level1_program}>{$org->level2_division}>{$org->level3_branch}>{$org->level4}");
            }
        }

    }
}
