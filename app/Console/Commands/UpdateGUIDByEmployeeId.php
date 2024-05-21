<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\JobSchedAudit;
use App\Models\JobDataAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class UpdateGUIDByEmployeeId extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:UpdateGUIDByEmployeeId';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $processname = 'UpdateGUIDByEmployeeId';
        $DefaultCreatorName = 'System';
        $start_time = Carbon::now()->format('c');
        $this->info( $processname.', Started: '. $start_time);
        Log::info($start_time.' - '.$processname.' - Started.');
        $job_name = 'command:UpdateGUIDByEmployeeId';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );
        //Process users with new GUID in employee_demo table;
        $counter = 0;
        $updatecounter = 0;
        $userList = User::select('id', 'employee_id', 'name')
        ->whereRaw("EXISTS (SELECT 1 FROM employee_demo WHERE employee_demo.employee_id = users.employee_id and employee_demo.guid <> users.guid AND NOT employee_demo.guid IS NULL AND TRIM(employee_demo.guid) <> '' AND employee_demo.pdp_excluded = 0 AND employee_demo.date_updated = (SELECT MAX(ed.date_updated) FROM employee_demo ed WHERE ed.employee_id = employee_demo.employee_id))")
        ->distinct()
        ->orderBy('users.employee_id')
        ->orderBy('users.empl_record')
        ->get();

        $log_content = '';
        foreach ($userList as $item) {
            DB::beginTransaction();
            try {
                $update = User::select('id', 'employee_id', 'guid')
                ->find($item->id);
                $demo = EmployeeDemo::select('guid')
                ->whereRaw("employee_id = '".$update->employee_id."' AND NOT guid IS NULL AND TRIM(guid) <> ''")
                ->orderBy('date_updated', 'desc')
                ->first();
                $old_guid = $update->guid;
                $new_guid = $demo ? $demo->guid : null;
                $jr = EmployeeDemoJunior::whereRaw("guid = '".$old_guid."' AND NOT guid IS NULL AND TRIM(guid) <> ''")
                ->update(['guid' => $new_guid]);
                $update->guid = $new_guid;
                $update->save(); 
                // $old_values = [ 
                //     'table' => 'users', 
                //     'guid' => $old_guid 
                // ];
                // $new_values = [ 
                //     'table' => 'users', 
                //     'guid' => $new_guid 
                // ];
                // $audit = new JobDataAudit;
                // $audit->job_sched_id = $audit_id;
                // $audit->old_values = json_encode($old_values);
                // $audit->new_values = json_encode($new_values);
                // $audit->save();
                DB::commit();
                $updatecounter += 1;
                echo 'Processed UID '.$item->id.'. Updated GUID '.$old_guid.' to '.$new_guid.'.'; echo "\r\n";
                
                $log_content .= '<br/> Processed UID: '.$item->id.', EMPLOYEE ID: '. $item->employee_id .', Name: '. $item->name .'. Updated GUID '.$old_guid.' to '.$new_guid.'.';
            } catch (Exception $e) {
                echo 'Unable to update UID '.$item->id.' from '.$old_guid.' to '.$new_guid.'.'; echo "\r\n";
                DB::rollback();
            }
            $counter += 1;
        }
        echo 'Processed '.$counter.'.  Updated '.$updatecounter.'.'; echo "\r\n";
        $end_time = Carbon::now();
        DB::table('job_sched_audit')->updateOrInsert(
            [
                'id' => $audit_id
            ],
            [
                'job_name' => $job_name,
                'start_time' => date('Y-m-d H:i:s',strtotime($start_time)),
                'end_time' => date('Y-m-d H:i:s',strtotime($end_time)),
                'status' => 'Completed',
                'details' => 'Processed '.$counter.' and Updated '.$updatecounter.' rows.' . '<br/> ' . $log_content,
            ]
        );
        $this->info('CalcNextConversationDate, Completed: '.$end_time);
        Log::info($end_time->format('c').' - '.$processname.' - Finished');
    } 
}
