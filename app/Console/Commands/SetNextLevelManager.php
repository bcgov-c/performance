<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Position;
use App\Models\EmployeeDemo;
use App\Models\ExcusedReason;
use App\Models\JobSchedAudit;
use App\Models\JobDataAudit;
use App\Models\UserReportingTo;
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
        ->join(\DB::raw('users USE INDEX (idx_users_employeeid_emplrecord)'), 'users.employee_id', 'employee_demo.employee_id')
        ->whereRaw("(users.reporting_to IS NULL OR TRIM(users.reporting_to) = '')")
        ->whereRaw("trim(employee_demo.guid) <> ''")
        ->whereNotNull('employee_demo.guid')
        ->orderBy('employee_demo.employee_id')
        ->orderBy('employee_demo.empl_record')
        ->chunk(10000, function($employeeDemo) use (&$counter, &$updatecounter, $start_time, $audit_id) {
            foreach ($employeeDemo as $demo) {
                $reporting_to = $this->getReportingUserId($demo);  
                $user = User::from(\DB::raw('users USE INDEX (idx_users_employeeid_emplrecord)'))->whereRaw("employee_id = '".$demo->employee_id."'")->first();
                if ($user) {
                    if ($user->reporting_to != $reporting_to) {
                        DB::beginTransaction();
                        try {
                            $user->reporting_to = $reporting_to;
                            $user->last_sync_at = $start_time;
                            $user->save();             
                            // Update Reporting Tos
                            if ($reporting_to) {
                                UserReportingTo::updateOrCreate(
                                    [
                                        'user_id' => $user->id
                                    ],
                                    [
                                        'reporting_to_id' => $reporting_to
                                    ]
                                );
                            }
                            DB::commit();
                            $this->info('EID '.$demo->employee_id.' - '.$demo->employee_name.' updated Manager to UID '.$reporting_to.'.');
                            $updatecounter += 1;
                        } catch (Exception $e) {
                            echo 'Unable to reporting_to for EID # '.$demo->employee_id.'.'; echo "\r\n";
                            DB::rollback();
                        }
                    }
                } else {
                    $this->info('EID '.$demo->employee_id.' - '.$demo->employee_name.' not found.');
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
                $user = User::whereRaw("employee_id = '".$supervisor->employee_id."'")->first();
                if ($user) {
                    return $user->id;
                } else {
                    $text = 'Supervisor Not found Supv EID = ' . $employee->supervisor_emplid . ' | EID = ' .
                        $employee->employee_id;
                    $this->info( 'exception ' . $text );
                }
            } else {
                $text = 'Supervisor not found Supv EID = ' . $employee->supervisor_emplid;
                $this->info( 'exception ' . $text );
            }
        } else {
            $employee2 = Position::where('position_nbr', $employee->supervisor_position_number)
                ->whereNull('date_deleted')
                ->first();
            if ($employee2) {
                $supervisor = EmployeeDemo::where('position_number', $employee2->reports_to)
                ->whereNull('date_deleted')
                ->orderBy('job_indicator', 'desc')
                ->orderBy('empl_record')
                ->first();
                if ($supervisor) {
                    $user = User::whereRaw("employee_id = '".$supervisor->employee_id."'")->first();
                    if ($user) {
                        return $user->id;
                    } else {
                        $text = 'Supervisor Not found Posn # ' . $employee2->reports_to;
                        $this->info( 'exception ' . $text );
                    }
                } else {
                    $text = 'Supervisor Not found for Posn # ' . $employee2->reports_to;
                    $this->info( 'exception ' . $text );
                }   
            } else {
                $employee3 = Position::where('position_nbr', $employee->position_number)
                ->whereNull('date_deleted')
                ->first();
                if ($employee3) {
                    $supervisor = EmployeeDemo::where('position_number', $employee3->reports_to)
                    ->orderBy('job_indicator', 'desc')
                    ->orderBy('empl_record')
                    ->first();
                    if ($supervisor) {
                        $user = User::whereRaw("employee_id = '".$supervisor->employee_id."'")->first();
                        if ($user) {
                            return $user->id;
                        } else {
                            $text = 'Supervisor Not found Posn # ' . $employee3->reports_to;
                            $this->info( 'exception ' . $text );
                        }
                    } else {
                        $employee4 = Position::where('position_nbr', $employee3->reports_to)
                        ->whereNull('date_deleted')
                        ->first();
                        if($employee4) {
                            $supervisor2 = EmployeeDemo::where('position_number', $employee4->reports_to)
                            ->orderBy('job_indicator', 'desc')
                            ->orderBy('empl_record')
                            ->first();
                            if($supervisor2) {
                                $user = User::whereRaw("employee_id = '".$supervisor2->employee_id."'")->first();
                                if ($user) {
                                    return $user->id;
                                } else {
                                    $text = 'Supervisor Not found Posn # '.$employee3->reports_to.'/'.$employee4->reports_to;
                                    $this->info( 'exception ' . $text );
                                }
                            } else {
                                $text = 'Supervisor Posn Not found for Posn # ' . $employee4->reports_to;
                                $this->info( 'exception ' . $text );
                            }
                        } else {
                            $text = 'Supervisor Posn Not found for Posn # '.$employee3->reports_to;
                            $this->info( 'exception ' . $text );
                        }
                    }
                } else {
                    $text = 'Supervisor Not found for EID = ' . $employee->employee_id;
                    $this->info( 'exception ' . $text );
                }
            }
        }
        return null;
    }

}
