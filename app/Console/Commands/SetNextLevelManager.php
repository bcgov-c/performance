<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\ExcusedReason;
use App\Models\JobSchedAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class SetNextLevelManager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SetNextLevelManager';

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
        $processname = 'SetNextLevelManager';
        $DefaultCreatorName = 'System';

        $start_time = Carbon::now()->format('c');
        $this->info( $processname.', Started: '. $start_time);
        Log::info($start_time.' - '.$processname.' - Started.');

        $job_name = 'command:SetNextLevelManager';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );

        //Process all users;
        $counter = 0;
        $updatecounter = 0;

        EmployeeDemo::whereNull('employee_demo.date_deleted')
        ->join('users', 'users.employee_id', 'employee_demo.employee_id')
        ->whereRaw("users.reporting_to IS NULL OR TRIM(users.reporting_to) = ''")
        ->whereRaw("trim(employee_demo.guid) <> ''")
        ->whereNotNull('employee_demo.guid')
        ->orderBy('employee_demo.employee_id')
        ->orderBy('employee_demo.empl_record')
        ->chunk(1000, function($employeeDemo) use (&$counter, &$updatecounter, $start_time) {
            foreach ($employeeDemo as $demo) {
                $reporting_to = $this->getReportingUserId($demo);  
                $user = User::where('guid', $demo->guid)->first();
                if ($user) {
                    if ($user->reporting_to != $reporting_to) {
                        $user->reporting_to = $reporting_to;
                        $user->last_sync_at = $start_time;
                        $user->save();             
                        // Update Reporting Tos
                        if ($reporting_to) {
                            $user->reportingTos()->updateOrCreate([ 'reporting_to_id' => $reporting_to, ]);
                        }
                        $this->info('EID '.$demo->employee_id.' - '.$demo->employee_name.' updated Manager to UID '.$reporting_to.'.');
                        $updatecounter += 1;
                    }
                } else {
                    $this->info('EID '.$demo->employee_id.' - '.$demo->employee_name.' not found by guid.');
                }
                $counter += 1;
                echo 'Processed '.$counter.'.  Updated '.$updatecounter.'.'; echo "\r";
            }
        });

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
                'details' => 'Processed '.$counter.' and Updated '.$updatecounter.' rows.',
            ]
        );
        $this->info('CalcNextConversationDate, Completed: '.$end_time);
        Log::info($end_time->format('c').' - '.$processname.' - Finished');
    } 

    public function getReportingUserId($employee){
        if ($employee->supervisor_emplid) {
            $supervisor = EmployeeDemo::where('employee_id', $employee->supervisor_emplid)
                ->orderBy('job_indicator', 'desc')
                ->orderBy('empl_record')
                ->first();
            if ($supervisor) {
                $user = User::where('guid', str_replace('-', '', $supervisor->guid))->first();
                if ($user) {
                    return $user->id;
                } else {
                    $text = 'Supervisor Not found - ' . $employee->supervisor_emplid . ' | employee -' .
                        $employee->employee_id;
                    $this->info( 'exception ' . $text );
                }
            }
        } else {
            $employee2 = EmployeeDemo::where('position_number', $employee->supervisor_position_number)
                ->orderBy('job_indicator', 'desc')
                ->orderBy('empl_record')
                ->first();
            if ($employee2) {
                $supervisor = EmployeeDemo::where('employee_id', $employee2->supervisor_emplid)
                    ->orderBy('job_indicator', 'desc')
                    ->orderBy('empl_record')
                    ->first();
                if ($supervisor) {
                    $user = User::where('guid', str_replace('-', '', $supervisor->guid))->first();
                    if ($user) {
                        return $user->id;
                    } else {
                        $text = 'Supervisor Not found - ' . $employee2->supervisor_emplid . ' | employee2 -' .
                            $employee2->employee_id;
                        $this->info( 'exception ' . $text );
                    }
                }
            }
        }
        return null;
    }

}
