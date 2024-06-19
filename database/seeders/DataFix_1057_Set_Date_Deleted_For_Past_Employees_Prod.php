<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeDemo;
use App\Models\User;
use App\Models\UserReportingTo;
use App\Models\SharedProfile;
use App\Models\PreferredSupervisor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1057_Set_Date_Deleted_For_Past_Employees_Prod extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Log::info(Carbon::now()." - ZenHub #1057 - Start");

        $past_array = [
            ['employee_id' => '000985', 'empl_record' => 0],
            ['employee_id' => '007941', 'empl_record' => 0],
            ['employee_id' => '012741', 'empl_record' => 0],
            ['employee_id' => '027950', 'empl_record' => 3],
            ['employee_id' => '029509', 'empl_record' => 0],
            ['employee_id' => '029879', 'empl_record' => 1],
            ['employee_id' => '044719', 'empl_record' => 0],
            ['employee_id' => '046156', 'empl_record' => 1],
            ['employee_id' => '056353', 'empl_record' => 0],
            ['employee_id' => '060290', 'empl_record' => 1],
            ['employee_id' => '068687', 'empl_record' => 0],
            ['employee_id' => '071373', 'empl_record' => 0],
            ['employee_id' => '086997', 'empl_record' => 0],
            ['employee_id' => '096265', 'empl_record' => 1],
            ['employee_id' => '100170', 'empl_record' => 1],
            ['employee_id' => '106282', 'empl_record' => 1],
            ['employee_id' => '107060', 'empl_record' => 1],
            ['employee_id' => '114120', 'empl_record' => 1],
            ['employee_id' => '115975', 'empl_record' => 1],
            ['employee_id' => '117609', 'empl_record' => 1],
            ['employee_id' => '118595', 'empl_record' => 0],
            ['employee_id' => '122267', 'empl_record' => 0],
            ['employee_id' => '123574', 'empl_record' => 1],
            ['employee_id' => '126435', 'empl_record' => 0],
            ['employee_id' => '126867', 'empl_record' => 1],
            ['employee_id' => '130855', 'empl_record' => 1],
            ['employee_id' => '131153', 'empl_record' => 0],
            ['employee_id' => '133882', 'empl_record' => 0],
            ['employee_id' => '134404', 'empl_record' => 0],
            ['employee_id' => '135263', 'empl_record' => 0],
            ['employee_id' => '137641', 'empl_record' => 0],
            ['employee_id' => '138164', 'empl_record' => 1],
            ['employee_id' => '139835', 'empl_record' => 0],
            ['employee_id' => '140450', 'empl_record' => 1],
            ['employee_id' => '140666', 'empl_record' => 0],
            ['employee_id' => '144147', 'empl_record' => 1],
            ['employee_id' => '147070', 'empl_record' => 0],
            ['employee_id' => '148264', 'empl_record' => 0],
            ['employee_id' => '148645', 'empl_record' => 0],
            ['employee_id' => '151687', 'empl_record' => 0],
            ['employee_id' => '151714', 'empl_record' => 0],
            ['employee_id' => '152158', 'empl_record' => 0],
            ['employee_id' => '152205', 'empl_record' => 1],
            ['employee_id' => '152756', 'empl_record' => 0],
            ['employee_id' => '153592', 'empl_record' => 1],
            ['employee_id' => '156354', 'empl_record' => 0],
            ['employee_id' => '156558', 'empl_record' => 1],
            ['employee_id' => '157108', 'empl_record' => 0],
            ['employee_id' => '157441', 'empl_record' => 1],
            ['employee_id' => '158671', 'empl_record' => 0],
            ['employee_id' => '158926', 'empl_record' => 0],
            ['employee_id' => '159165', 'empl_record' => 1],
            ['employee_id' => '159627', 'empl_record' => 0],
            ['employee_id' => '159650', 'empl_record' => 1],
            ['employee_id' => '160897', 'empl_record' => 0],
            ['employee_id' => '161223', 'empl_record' => 1],
            ['employee_id' => '161296', 'empl_record' => 1],
            ['employee_id' => '161904', 'empl_record' => 1],
            ['employee_id' => '162740', 'empl_record' => 1],
            ['employee_id' => '163935', 'empl_record' => 1],
            ['employee_id' => '164312', 'empl_record' => 1],
            ['employee_id' => '165800', 'empl_record' => 0],
            ['employee_id' => '167042', 'empl_record' => 1],
            ['employee_id' => '167286', 'empl_record' => 0],
            ['employee_id' => '167436', 'empl_record' => 0],
            ['employee_id' => '167493', 'empl_record' => 0],
            ['employee_id' => '167505', 'empl_record' => 0],
            ['employee_id' => '167817', 'empl_record' => 1],
            ['employee_id' => '167916', 'empl_record' => 0],
            ['employee_id' => '168206', 'empl_record' => 1],
            ['employee_id' => '168534', 'empl_record' => 0],
            ['employee_id' => '169117', 'empl_record' => 0],
            ['employee_id' => '169395', 'empl_record' => 1],
            ['employee_id' => '169792', 'empl_record' => 1],
            ['employee_id' => '170271', 'empl_record' => 0],
            ['employee_id' => '170890', 'empl_record' => 1],
            ['employee_id' => '171570', 'empl_record' => 1],
            ['employee_id' => '171857', 'empl_record' => 0],
            ['employee_id' => '171908', 'empl_record' => 1],
            ['employee_id' => '172294', 'empl_record' => 0],
            ['employee_id' => '173129', 'empl_record' => 0],
            ['employee_id' => '173147', 'empl_record' => 1],
            ['employee_id' => '173226', 'empl_record' => 1],
            ['employee_id' => '173869', 'empl_record' => 1],
            ['employee_id' => '174311', 'empl_record' => 1],
            ['employee_id' => '175291', 'empl_record' => 1],
            ['employee_id' => '175897', 'empl_record' => 0],
            ['employee_id' => '175908', 'empl_record' => 0],
            ['employee_id' => '176175', 'empl_record' => 1],
            ['employee_id' => '176438', 'empl_record' => 0],
            ['employee_id' => '177102', 'empl_record' => 1],
            ['employee_id' => '177321', 'empl_record' => 1],
            ['employee_id' => '177536', 'empl_record' => 0],
            ['employee_id' => '177572', 'empl_record' => 0],
            ['employee_id' => '177660', 'empl_record' => 0],
            ['employee_id' => '177663', 'empl_record' => 0],
            ['employee_id' => '177814', 'empl_record' => 1],
            ['employee_id' => '178038', 'empl_record' => 0],
            ['employee_id' => '178363', 'empl_record' => 1],
            ['employee_id' => '178584', 'empl_record' => 1],
            ['employee_id' => '178732', 'empl_record' => 0],
            ['employee_id' => '179070', 'empl_record' => 1],
            ['employee_id' => '179304', 'empl_record' => 1],
            ['employee_id' => '180479', 'empl_record' => 1],
            ['employee_id' => '180647', 'empl_record' => 0],
            ['employee_id' => '181534', 'empl_record' => 0],
            ['employee_id' => '181737', 'empl_record' => 0],
            ['employee_id' => '181882', 'empl_record' => 0],
            ['employee_id' => '181905', 'empl_record' => 0],
            ['employee_id' => '181923', 'empl_record' => 0],
            ['employee_id' => '181923', 'empl_record' => 1],
            ['employee_id' => '182394', 'empl_record' => 0],
            ['employee_id' => '182425', 'empl_record' => 0],
            ['employee_id' => '182703', 'empl_record' => 0],
            ['employee_id' => '182983', 'empl_record' => 1],
            ['employee_id' => '183477', 'empl_record' => 0],
            ['employee_id' => '184839', 'empl_record' => 0],
            ['employee_id' => '184855', 'empl_record' => 0],
            ['employee_id' => '185113', 'empl_record' => 0],
            ['employee_id' => '185160', 'empl_record' => 0],
            ['employee_id' => '185433', 'empl_record' => 0],
            ['employee_id' => '186253', 'empl_record' => 0],
            ['employee_id' => '186281', 'empl_record' => 0],
            ['employee_id' => '186680', 'empl_record' => 0],
            ['employee_id' => '186907', 'empl_record' => 0],
            ['employee_id' => '187005', 'empl_record' => 0],
            ['employee_id' => '187097', 'empl_record' => 0],
            ['employee_id' => '187491', 'empl_record' => 0],
            ['employee_id' => '187953', 'empl_record' => 0],
            ['employee_id' => '187956', 'empl_record' => 0],
            ['employee_id' => '188329', 'empl_record' => 0],
            ['employee_id' => '188330', 'empl_record' => 0],
            ['employee_id' => '188331', 'empl_record' => 0],
            ['employee_id' => '188332', 'empl_record' => 0],
            ['employee_id' => '188333', 'empl_record' => 0],
            ['employee_id' => '188334', 'empl_record' => 0],
            ['employee_id' => '188381', 'empl_record' => 0],
            ['employee_id' => '188384', 'empl_record' => 0],
            ['employee_id' => '188393', 'empl_record' => 0],
            ['employee_id' => '188396', 'empl_record' => 0],
            ['employee_id' => '188399', 'empl_record' => 0],
            ['employee_id' => '188451', 'empl_record' => 0],
            ['employee_id' => '188452', 'empl_record' => 0],
            ['employee_id' => '188456', 'empl_record' => 0],
            ['employee_id' => '188461', 'empl_record' => 0],
            ['employee_id' => '188462', 'empl_record' => 0],
            ['employee_id' => '188464', 'empl_record' => 0],
            ['employee_id' => '188483', 'empl_record' => 0],
            ['employee_id' => '188488', 'empl_record' => 0],
            ['employee_id' => '188492', 'empl_record' => 0],
            ['employee_id' => '188493', 'empl_record' => 0],
            ['employee_id' => '188496', 'empl_record' => 0],
            ['employee_id' => '188500', 'empl_record' => 0],
            ['employee_id' => '188501', 'empl_record' => 0],
            ['employee_id' => '188505', 'empl_record' => 0],
            ['employee_id' => '188508', 'empl_record' => 0],
            ['employee_id' => '188512', 'empl_record' => 0],
            ['employee_id' => '188529', 'empl_record' => 0],
            ['employee_id' => '188530', 'empl_record' => 0],
            ['employee_id' => '188531', 'empl_record' => 0],
            ['employee_id' => '188532', 'empl_record' => 0],
            ['employee_id' => '188595', 'empl_record' => 0],
            ['employee_id' => '188693', 'empl_record' => 0],
            ['employee_id' => '188695', 'empl_record' => 0],
            ['employee_id' => '188698', 'empl_record' => 0],
            ['employee_id' => '188756', 'empl_record' => 0],
            ['employee_id' => '188758', 'empl_record' => 0],
        ];

        foreach($past_array AS $item) {

            $employee_id = $item['employee_id'];
            $empl_record = $item['empl_record'];

            $profs = User::where('employee_id', $employee_id)
                ->where('empl_record', $empl_record)
                ->whereRaw("NOT EXISTS (SELECT 1 FROM employee_demo WHERE employee_demo.employee_id = users.employee_id AND employee_demo.empl_record <> users.empl_record AND employee_demo.date_deleted IS NULL LIMIT 1)")
                ->distinct()
                ->orderBy('id')
                ->get();
            if($profs->isEmpty()) {
                Log::info(Carbon::now()." - ACTIVE employee_demo found - {$employee_id}");
            } else {
                foreach($profs AS $prof) {
                    $all_reportto = UserReportingTo::where('reporting_to_id', $prof->id)
                        ->get();
                    foreach($all_reportto AS $rpt) {
                        Log::info(Carbon::now()." - Deleting from user_reporting_tos - {$prof->employee_id} - {$rpt->reporting_to_id} / {$rpt->user_id}");
                        $rpt->delete();
                    }
                    $all_shared = SharedProfile::where('shared_with', $prof->id)
                        ->get();
                    foreach($all_shared AS $shr) {
                        Log::info(Carbon::now()." - Deleting from shared_profiles - {$prof->employee_id} - {$shr->shared_with} / {$shr->shared_id} / {$shr->shared_by}");
                        $shr->delete();
                    }
                    $changed = false;
                    if(!$prof->acctlock) {
                        Log::info(Carbon::now()." - Updating acctlock in users - {$prof->employee_id} - acctlock={$prof->acctlock}");
                        $prof->acctlock = 1;
                        $changed = true;
                    } 
                    if($prof->reporting_to) {
                        Log::info(Carbon::now()." - Updating blank to reporting_to in users - {$prof->employee_id} - reporting_to={$prof->reporting_to}");
                        $prof->reporting_to = null;
                        $changed = true;
                    }
                    if($changed) {
                        $prof->save();
                    }
                    $prefers = PreferredSupervisor::where('supv_empl_id', $prof->employee_id)
                        ->get();
                    foreach($prefers AS $prefer) {
                        Log::info(Carbon::now()." - Deleting from preferred_supervisor - {$prof->employee_id} - {$prefer->supv_empl_id} / {$prefer->employee_id} / {$prefer->position_nbr}");
                        $prefer->delete();
                    }
                }
            }
            $demos = EmployeeDemo::where('employee_id', $employee_id)
                ->where('empl_record', $empl_record)
                ->orderBy('employee_id')
                ->orderBy('empl_record')
                ->get();
            if($demos->isEmpty()) {
                Log::info(Carbon::now()." - NOT found in employee_demo - {$employee_id} / {$empl_record}");
            } else {
                foreach($demos AS $demo) {
                    if($demo->date_deleted) {
                        Log::info(Carbon::now()." - Skipping employee_demo update, date_deleted is NOT blank - {$demo->employee_id} / {$demo->empl_record} - date_deleted={$demo->date_deleted}");
                    } else {
                        Log::info(Carbon::now()." - Updating blank date_deleted in employee_demo - {$demo->employee_id} / {$demo->empl_record} - date_deleted=2023-07-15");
                        EmployeeDemo::where('employee_id', $demo->employee_id)
                            ->where('empl_record', $demo->empl_record)
                            ->update(['date_deleted' => '2023-07-15']);
                    }
                }
            }
        }
    
        Log::info(Carbon::now()." - ZenHub #1057 - End");

    }
}
