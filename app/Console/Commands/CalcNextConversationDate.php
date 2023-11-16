<?php

namespace App\Console\Commands;

use Carbon\Carbon; 
use App\Models\Conversation;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\ExcusedReason;
use App\Models\EmployeeDemoJunior;
use App\Models\ExcusedClassification;
use App\Models\JobSchedAudit;
use App\Models\JobDataAudit;
use App\Models\ExcusedDepartment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

use function PHPUnit\Framework\assertFalse;

class CalcNextConversationDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:CalcNextConversationDate';

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
        $processname = 'CalcNextConversationDate';
        $DefaultCreatorName = 'System';

        $start_time = Carbon::now()->format('c');
        // $current_cutoff_datetime = $start_time;
        $this->info( $processname.', Started: '. $start_time);
        // Log::info($start_time.' - '.$processname.' - Started.');

        $job_name = 'command:CalcNextConversationDate';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );

        $stored = DB::table('stored_dates')
        ->where('name', 'CalcNextConversationDate')
        ->first();

        if ($stored) {
            if ($stored->value){
                $last_cutoff_time = $stored->value;
                $this->info(Carbon::now()->format('c').' - Last Run Date:  ' . $last_cutoff_time);
            } else { 
                $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
                $this->info(Carbon::now()->format('c').' - Last Run Date not found.  Using ' . $last_cutoff_time);
            }
        } else {  
            $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
            $this->info(Carbon::now()->format('c').' - Last Run Date not found.  Using ' . $last_cutoff_time);
            $stored = DB::table('stored_dates')->updateOrInsert(
                [
                    'name' => 'CalcNextConversationDate',
                ],
                [
                    'value' => Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c'),
                ]
            );
        }

        //Process all employees;
        $counter = 0;
        $updatecounter = 0;
        $updateemp = array();
        $ClassificationArray = ExcusedClassification::select('jobcode')->pluck('jobcode')->toArray();
        $ExcusedDepartmentArray = ExcusedDepartment::select('deptid')->pluck('deptid')->toArray();
        EmployeeDemo::whereNull('employee_demo.date_deleted')
        ->leftjoin('users', 'users.employee_id', 'employee_demo.employee_id')
        ->leftjoin('employee_demo_tree', 'employee_demo_tree.id', 'employee_demo.orgid')
        ->leftjoin('access_organizations', 'access_organizations.orgid', 'employee_demo_tree.organization_key')
        ->whereRaw("trim(employee_demo.guid) <> ''")
        ->whereNotNull('employee_demo.guid')
        ->whereRaw("employee_demo.employee_status = (select min(a.employee_status) from employee_demo a where a.employee_id = employee_demo.employee_id)")
        ->whereRaw("employee_demo.empl_record = (select min(a.empl_record) from employee_demo a where a.employee_id = employee_demo.employee_id and a.employee_status = employee_demo.employee_status)")
        ->whereRaw("
            (
                EXISTS (SELECT 1 FROM employee_demo AS edchk1 USE INDEX (idx_employee_demo_employeeid_record) WHERE edchk1.employee_id = employee_demo.employee_id AND edchk1.empl_record = employee_demo.empl_record AND edchk1.date_deleted IS NULL)
                OR (
                    NOT EXISTS (SELECT 1 FROM employee_demo AS edchk2 USE INDEX (idx_employee_demo_employeeid_record) WHERE edchk2.employee_id = employee_demo.employee_id AND edchk2.empl_record = employee_demo.empl_record AND edchk2.date_deleted IS NULL)
                        AND EXISTS (SELECT 1 FROM employee_demo AS edchk3 USE INDEX (idx_employee_demo_employeeid_record) WHERE edchk3.employee_id = employee_demo.employee_id)
                    )
            )
        ")
        // Parameter below is for testing purposes only
        // ->where('employee_demo.employee_id', \DB::raw('XXXXXX'))
        ->distinct()
        ->orderBy('employee_demo.employee_id')
        ->orderBy('employee_demo.empl_record')
        ->chunk(10000, function($employeeDemo) use (&$counter, &$updatecounter, $updateemp, $ClassificationArray, $ExcusedDepartmentArray, $DefaultCreatorName, $audit_id) {
            foreach ($employeeDemo as $demo) {
                $changeType = 'noChange';
                $new_last_employee_status = null;
                $new_last_classification = null;
                $new_last_classification_descr = null;
                $new_last_deptid = null;
                $new_last_manual_excuse = 'N';
                $excuseType = null;
                $lastConversationDate = null;
                $initLastConversationDate = null;
                $initNextConversationDate = null;
                $DDt = null;
                $jr_inarray = false;
                $demo_inarray = false;
                $diffInDays = 0;
                $prevPause = null;
                $prevDate = null;
                $lastDateCalculated = false;
                $excused_updated_by = '';
                $excused_updated_at = null;
                $usedate1 = '';
                $usedate2 = '';
                $newEndDate = '';
                $currDate = Carbon::now()->toDateString();
                $excused_reason_id = null;
                $excused_reason_desc = null;

                if ($demo->guid) {
                    // YES GUID
                    // get last conversation details
                    $lastConv = Conversation::join('conversation_participants', function($join) {
                        return $join->on(function($on) {
                            return $on->on('conversations.id', 'conversation_participants.conversation_id')
                                ->whereRaw("role = 'emp'");
                        });
                    })
                    ->join('users', 'users.id', 'conversation_participants.participant_id')
                    ->whereRaw("trim(users.guid) <> ''")
                    ->whereNotNull('users.guid')
                    ->whereNotNull('signoff_user_id')
                    ->whereNotNull('supervisor_signoff_id')
                    ->where('participant_id', $demo->users->id)
                    ->with('user')
                    ->where('signoff_user_id', $demo->users->id)
                    ->orderBy('conversations.sign_off_time', 'desc')
                    ->first();
                    
                    if ($lastConv) {
                        // use last conversation + 4 months as initial next conversation date
                        // $lastConversationDate = $lastConv->getLastSignOffDateAttribute()->format('M d, Y');
                        // $initNextConversationDate = $lastConv->getLastSignOffDateAttribute()->addMonth(4)->format('M d, Y');
                        
                        $lastConversationDate = $lastConv->getLastSignOffDateAttribute()->toDateString();
                        //$initNextConversationDate = $lastConv->getLastSignOffDateAttribute()->addMonth(4)->toDateString();
                        //clone instance to avoid duplicate calling from same instance
                        $initNextConversationDate = $lastConv->getLastSignOffDateAttribute()->clone()->addMonth(4)->toDateString();
                        // echo 'Last Conversation Date:'.$lastConversationDate; echo "\r\n";
                    } else {
                        // no last conversation, use randomizer to assign initial next conversation date
                        $lastConversationDate = null;
                        $initNextConversationDate = $demo->users->joining_date->addMonth(4)->toDateString();
                    }
                    // post go-live hard-coded initial next conversation due dates
                    // $virtualHardDate = Carbon::createFromDate(2022, 10, 14);
                    // Moved 1 month forward
                    // $virtualHardDate = Carbon::createFromDate(2022, 11, 14);
                    // Moved 2 week later
                    // $virtualHardDate = Carbon::createFromDate(2022, 11, 30);
                    // if ($virtualHardDate->gt($initNextConversationDate)) {
                    //     // distribute next conversation date, based on last digit of employee ID
                    //     $DDt = abs (($demo->employee_id % 10) - 1) * 5 + (($demo->employee_id % 5));
                    //     $initNextConversationDate = $virtualHardDate->addDays($DDt)->toDateString();
                    // }
                    // Updated Conversation Start Dates
                    // Based on access_organizations.conversation_batch value for ministry groupings
                    switch ($demo->conversation_batch) {
                        case 0:
                        case 3:
                            $virtualHardDate = Carbon::createFromDate(2023, 12, 7);
                            if ($virtualHardDate->gt($initNextConversationDate)) {
                                // distribute next conversation date, based on last digit of employee ID
                                $DDt = (int) (($demo->employee_id % 100) * 71 / 100);
                                //$initNextConversationDate = $virtualHardDate->addDays($DDt)->toDateString();
                                $initNextConversationDate = $virtualHardDate->clone()->addDays($DDt)->toDateString(); // Clone the instance
                            }
                            break;
                        case 2:
                            $virtualHardDate = Carbon::createFromDate(2023, 11, 01);
                            if ($virtualHardDate->gt($initNextConversationDate)) {
                                // distribute next conversation date, based on last digit of employee ID
                                $DDt = (int) (($demo->employee_id % 100) * 52 / 100);
                                //$initNextConversationDate = $virtualHardDate->addDays($DDt)->toDateString();
                                $initNextConversationDate = $virtualHardDate->clone()->addDays($DDt)->toDateString(); // Clone the instance
                            }
                            break;
                        case 1:
                            $virtualHardDate = Carbon::createFromDate(2022, 11, 30);
                            if ($virtualHardDate->gt($initNextConversationDate)) {
                                // distribute next conversation date, based on last digit of employee ID
                                $DDt = abs (($demo->employee_id % 10) - 1) * 5 + (($demo->employee_id % 5));
                                //$initNextConversationDate = $virtualHardDate->addDays($DDt)->toDateString();
                                $initNextConversationDate = $virtualHardDate->clone()->addDays($DDt)->toDateString(); // Clone the instance
                            }
                            break;
                        default:
                            $virtualHardDate = Carbon::createFromDate(2023, 12, 7);
                            if ($virtualHardDate->gt($initNextConversationDate)) {
                                // distribute next conversation date, based on last digit of employee ID
                                $DDt = (int) (($demo->employee_id % 100) * 71 / 100);
                                //$initNextConversationDate = $virtualHardDate->addDays($DDt)->toDateString();
                                $initNextConversationDate = $virtualHardDate->clone()->addDays($DDt)->toDateString(); // Clone the instance
                            }
                            break;
                    }
                    // calcualte initial last conversation date; init next conversation minus 4 months
                    //$initLastConversationDate = Carbon::parse($initNextConversationDate)->subMonth(4)->toDateString();
                    $initLastConversationDate = Carbon::parse($initNextConversationDate)->clone()->subMonth(4)->toDateString(); // Clone the instance
                    if ($lastConversationDate && Carbon::parse($lastConversationDate)->gt($initLastConversationDate)) {
                        $initLastConversationDate = $lastConversationDate;
                    }
                    $demo_inarray = in_array($demo->jobcode, $ClassificationArray);
                    $demo_in_deptarray = in_array($demo->deptid, $ExcusedDepartmentArray);
                    // get last stored detail in junior table
                    $jr = EmployeeDemoJunior::where('employee_id', '=', $demo->employee_id)->orderBy('id', 'desc')->first();
                    if ($jr) {
                        // Previous JR record exist
                        $new_last_employee_status = $jr->current_employee_status;
                        $new_last_classification = $jr->current_classification;
                        $new_last_classification_descr = $jr->current_classification_descr;
                        $new_last_deptid = $jr->current_deptid;
                        $new_last_manual_excuse = $jr->current_manual_excuse ?? 'N';
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status != 'A') {
                            // STATUS CHANGE
                            $changeType = 'statusStartExcuse';
                            $excuseType = 'A';
                            $excused_reason_id = 1;
                            $excused_reason_desc = 'PeopleSoft Status';
                        }
                        if ($jr->current_employee_status != 'A' 
                            && $demo->employee_status == 'A') {
                            // STATUS CHANGE
                            $changeType = 'statusEndExcuse';
                        }
                        $jr_inarray = in_array($jr->current_classification, $ClassificationArray);
                        $excused = ($demo->employee_status != 'A' 
                            || $demo_inarray 
                            || $demo_in_deptarray 
                            || $demo->excused_flag);
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr_inarray == false
                            && $demo_inarray) {
                            // CLASSIFICATION CHANGE
                            $changeType = 'classStartExcuse';
                            $excuseType = 'A';
                            $excused_reason_id = 2;
                            $excused_reason_desc = 'Classification';
                        }
                        $jr_in_deptarray = in_array($jr->current_deptid, $ExcusedDepartmentArray);
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr_in_deptarray == false
                            && $demo_in_deptarray) {
                            // DEPTID CHANGE
                            $changeType = 'deptStartExcuse';
                            $excuseType = 'A';
                            $excused_reason_id = 0;
                            $excused_reason_desc = 'Department';
                        }
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr->excused_type == 'A'
                            && $jr->excused_reason_id == 2
                            && $demo_inarray == false) {
                            // CLASSIFICATION CHANGE
                            $changeType = 'classEndExcuse';
                        }
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr->excused_type == 'A'
                            && $jr->excused_reason_id == 0
                            && $demo_in_deptarray == false) {
                            // DEPTID CHANGE
                            $changeType = 'deptEndExcuse';
                        }
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A' 
                            && $jr_inarray == false
                            && $demo_inarray == false
                            && $jr_in_deptarray == false
                            && $demo_in_deptarray == false
                            && (!$jr->current_manual_excuse 
                                || $jr->current_manual_excuse == 'N') 
                            && $demo->excused_flag == 1) {
                            // MANUAL CHANGE
                            $changeType = 'manualStartExcuse';
                            $excuseType = 'M';
                            $excused_updated_by = $demo->excused_updated_by;
                            $excused_updated_at = $demo->excused_updated_at;
                            $excused_reason_id = $demo->users->excused_reason_id;
                            $excused_reason_desc = ExcusedReason::whereRaw('id ='.$demo->users->excused_reason_id)->first()->name;
                        }
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr_inarray == false
                            && $demo_inarray == false
                            && $jr_in_deptarray == false
                            && $demo_in_deptarray == false
                            && $jr->current_manual_excuse == 'Y' 
                            // && (!$demo->excused_flag || $demo->excused_flag == 0)) {
                            && !$demo->excused_flag) {
                            // MANUAL CHANGE
                            $changeType = 'manualEndExcuse';
                            $excused_updated_by = $demo->excused_updated_by;
                            $excused_updated_at = $demo->excused_updated_at;
                        }
                        if (in_array($changeType, ['statusEndExcuse', 'classEndExcuse', 'deptEndExcuse', 'manualEndExcuse', 'noChange'])) {
                            // re-calc next conversation date
                            // get historical dates
                            $allDates = EmployeeDemoJunior::from('employee_demo_jr as j')
                            ->where('j.employee_id', $demo->employee_id)
                            ->whereRaw("trim(j.guid) <> ''")
                            ->whereNotNull('j.guid')
                            ->where('j.created_at', '>', $initLastConversationDate)
                            ->where(function ($where) use ($initLastConversationDate) {
                                $where->where('j.created_at', '>', $initLastConversationDate)
                                ->orWhereRaw("j.id = (SELECT MAX(cd.id) from employee_demo_jr cd where cd.employee_id = j.employee_id AND cd.created_at <= '".$initLastConversationDate."')");
                            })
                            ->orderBy('j.id')
                            ->get();
                            $lastDateCalculated = false;
                            // calc excused days
                            foreach($allDates as $oneDay) {
                                if ($prevDate == null) {
                                    $prevDate = $oneDay->created_at->toDateString();
                                    $prevPause = $oneDay->due_date_paused;
                                    // if ($prevPause == 'Y') {
                                    //     echo $demo->employee_id.': First row found excused date '.$prevDate.'. Status:'.$prevPause.'.'; echo "\r\n";
                                    // }
                                } else {
                                    if ($prevPause == 'Y' 
                                        && $oneDay->due_date_paused == 'N') {
                                        $calcDays = 0;
                                        $calcDate = Carbon::parse($oneDay->created_at->toDateString()); 
                                        $currDate = Carbon::now()->toDateString();
                                        if ($prevDate > $initLastConversationDate) {
                                                $usedate1 = $prevDate;
                                        } else {
                                            $usedate1 = $initLastConversationDate;
                                        }
                                        if ($calcDate > $currDate) {
                                            $calcDate = $currDate;
                                        }
                                        if ($calcDate > $initNextConversationDate) {
                                            $usedate2 = $calcDate;
                                        } else {
                                            $usedate2 = $initNextConversationDate;
                                        }
                                        if ($usedate1 != $usedate2 
                                            && $usedate2 > $initLastConversationDate) {
                                            $calcDays = abs(Carbon::parse($usedate2)->diffInDays($usedate1));
                                        } else {
                                            $calcDays = 0;
                                        }
                                        $diffInDays += $calcDays;
                                        $lastDateCalculated = true;
                                        $prevPause = 'N';
                                        echo $demo->employee_id.': End excused period for '.$usedate1.' to '.$usedate2.'. '.$calcDays.' days.'; echo "\r\n";
                                    } else {
                                        if ($prevPause == 'N' 
                                            && $oneDay->due_date_paused == 'Y') {
                                            $prevDate = $oneDay->created_at->toDateString();
                                            $prevPause = $oneDay->due_date_paused;
                                            $lastDateCalculated = false;
                                            echo $demo->employee_id.': Start new excused period for '.$prevDate.'.'; echo "\r\n";
                                        }
                                    }
                                }
                            }
                            if ($lastDateCalculated == false 
                                && $excused == false 
                                && $prevPause == 'Y') {
                                $calcDays = 0;
                                if ($prevDate > $initLastConversationDate) {
                                    $usedate1 = $prevDate;
                                } else {
                                    $usedate1 = $initLastConversationDate;
                                }
                                $currDate = Carbon::now()->toDateString();
                                $usedate2 = $currDate;
                                if ($usedate1 != $usedate2 
                                    && $usedate2 > $initLastConversationDate) {
                                    $calcDays = abs(Carbon::parse($usedate2)->diffInDays($usedate1));
                                } else {
                                    $calcDays = 0;
                                }
                                $diffInDays += $calcDays;
                                $lastDateCalculated = true;
                                $prevPause = 'N';
                                echo $demo->employee_id.': End excused period for '.$usedate1.' to '.$usedate2.'. '.$calcDays.' days.'; echo "\r\n";
                            }
                            if ($diffInDays < 0) {
                                $diffInDays = 0;
                            }
                            $newEndDate = Carbon::parse($initNextConversationDate)->clone()->addDays($diffInDays)->toDateString();
                            if ($newEndDate > $initNextConversationDate) {
                                $initNextConversationDate = $newEndDate;
                            }
                        }
                    } else {
                        // NO Previous JR record exist, store details to junior table
                        if ($demo->employee_status != 'A') {
                            $changeType = 'statusNewExcuse';
                            $excuseType = 'A';
                            $excused_reason_id = 1;
                            $excused_reason_desc = 'PeopleSoft Status';
                        } else {
                            if ($demo->employee_status == 'A' 
                                && $demo_inarray) {
                                $changeType = 'classNewExcuse';
                                $excuseType = 'A';
                                $excused_reason_id = 2;
                                $excused_reason_desc = 'Classification';
                            } else {
                                if ($demo->employee_status == 'A' 
                                    && $demo_in_deptarray) {
                                    $changeType = 'deptNewExcuse';
                                    $excuseType = 'A';
                                    $excused_reason_id = 0;
                                    $excused_reason_desc = 'Department';
                                } else {
                                    if ($demo->employee_status == 'A' 
                                        && $demo_inarray == false 
                                        && $demo_in_deptarray == false 
                                        && $demo->excused_flag) {
                                        $changeType = 'manualNewExcuse';
                                        $excuseType = 'M';
                                        $excused_updated_by = $demo->excused_updated_by;
                                        $excused_updated_at = $demo->excused_updated_at;
                                        $excused_reason_id = $demo->users->excused_reason_id;
                                        $excused_reason_desc = ExcusedReason::whereRaw('id ='.$demo->users->excused_reason_id)->first()->name;
                                    } else {
                                        $changeType = 'noExcuse';
                                        $excuseType = null;
                                    }
                                }
                            }
                        }
                    }
                    $updated_by_rec = User::where('id', $excused_updated_by)->first();
                    $updated_by_name = $updated_by_rec ? $updated_by_rec->name : null;
                    $excusedArrayTypes = ['statusStartExcuse', 'classStartExcuse', 'deptStartExcuse', 'manualStartExcuse', 'statusNewExcuse', 'classNewExcuse', 'deptNewExcuse', 'manualNewExcuse'];
                    if ($changeType != 'noChange') {
                        $newJr = new EmployeeDemoJunior;
                        $newJr->guid = $demo->guid;
                        $newJr->employee_id = $demo->employee_id;
                        $newJr->current_employee_status = $demo->employee_status;
                        $newJr->current_classification = $demo->jobcode;
                        $newJr->current_classification_descr = $demo->jobcode_desc;
                        $newJr->current_deptid = $demo->deptid;
                        $newJr->current_manual_excuse = $demo->excused_flag ? 'Y' : 'N';
                        $newJr->due_date_paused = in_array($changeType, $excusedArrayTypes) ? 'Y' : 'N';
                        $newJr->last_employee_status = $new_last_employee_status;
                        $newJr->last_classification = $new_last_classification;
                        $newJr->last_classification_descr = $new_last_classification_descr;
                        $newJr->last_deptid = $new_last_deptid;
                        $newJr->last_manual_excuse = $new_last_manual_excuse;
                        $newJr->excused_type = $excuseType;
                        $newJr->last_conversation_date = $lastConversationDate ? Carbon::parse($lastConversationDate) : null;
                        $newJr->next_conversation_date = $initNextConversationDate ? Carbon::parse($initNextConversationDate) : null;
                        $newJr->created_by_id = $DefaultCreatorName;
                        $newJr->updated_by_id = $excused_updated_by ?? $DefaultCreatorName;
                        $newJr->updated_by_name = $updated_by_name;
                        $newJr->excused_reason_id = $excused_reason_id;
                        $newJr->excused_reason_desc = $excused_reason_desc;
                        if($excused_updated_at) {
                            $newJr->updated_at = $excused_updated_at;
                        }
                        $newJr->save();
                        $updatecounter += 1;
                        $updateemp[] = $demo->employee_id;
                        echo '$changeType '.$changeType.'.  EMPLID '.$demo->employee_id.'.  newDueDate '.$newJr->next_conversation_date.'.  '; echo "\r\n";
                    } else {
                        if ($jr 
                            //&& $jr->excused_type 
                            && $jr->next_conversation_date 
                            && $initNextConversationDate 
                            && $jr->next_conversation_date <> $initNextConversationDate) {
                            // save new next conversation due date;
                            $newJr = new EmployeeDemoJunior;
                            $newJr->guid = $jr->guid;
                            $newJr->employee_id = $jr->employee_id;
                            $newJr->current_employee_status = $jr->current_employee_status;
                            $newJr->current_classification = $jr->current_classification;
                            $newJr->current_classification_descr = $jr->current_classification_descr;
                            $newJr->current_deptid = $jr->current_deptid;
                            $newJr->current_manual_excuse = $jr->current_manual_excuse;
                            $newJr->due_date_paused = $jr->due_date_paused;
                            $newJr->last_employee_status = $jr->last_employee_status;
                            $newJr->last_classification = $jr->last_classification;
                            $newJr->last_classification_descr = $jr->last_classification_descr;
                            $newJr->last_deptid = $jr->last_deptid;
                            $newJr->last_manual_excuse = $jr->last_manual_excuse;
                            $newJr->excused_type = $jr->excused_type;
                            $newJr->last_conversation_date = $jr->last_conversation_date;
                            $newJr->next_conversation_date = $initNextConversationDate ? Carbon::parse($initNextConversationDate) : null;
                            $newJr->created_by_id = $jr->created_by_id;
                            $newJr->updated_by_id = $jr->updated_by_id;
                            $newJr->updated_by_name = $jr->updated_by_name;
                            $newJr->excused_reason_id = $jr->excused_reason_id;
                            $newJr->excused_reason_desc = $jr->excused_reason_desc;
                            // $newJr->created_at = $jr->created_at;
                            $newJr->updated_at = $jr->updated_at;
                            $newJr->save();
                            $updatecounter += 1;
                            $updateemp[] = $demo->employee_id;

                            $js_next_conversation_date = $initNextConversationDate ? Carbon::parse($initNextConversationDate)->toDateString() : null;
                            \DB::beginTransaction();
                            \DB::statement("
                                UPDATE 
                                    users_annex 
                                SET 
                                    jr_next_conversation_date = '$js_next_conversation_date'
                                WHERE 
                                    employee_id = '$jr->employee_id'
                            ");
                            \DB::commit();

                            echo '$changeType updateDueDate.  EMPLID '.$demo->employee_id.'.  oldDueDate '.$jr->next_conversation_date.'.  newDueDate '.$initNextConversationDate.'.  '; echo "\r\n";
                        } else {
                            // SKIP if no change
                        }
                    }
                } else {
                    // NO GUID
                    $details = '';
                    if ($demo->employee_id) {
                        $details = 'EmplID='.$demo->employee_id;
                    }
                    if ($demo->employee_email) {
                        if ($details) {
                            $details = $details.'|';
                        }
                        $details = $details.'eMail='.$demo->employee_email;
                    }
                    if ($demo->employee_name) {
                        if ($details) {
                            $details = $details.'|';
                        }
                        $details = $details.'Name='.$demo->employee_name;
                    }
                    if ($details == '') {
                        $details = 'Unidentified';
                    }
                    Log::info(Carbon::now()->format('c').' - ['.$details.'] does not have GUID in Employee Demo table.');
                }
                $counter += 1;
                // echo 'Processed '.$counter.'.  Updated '.$updatecounter.'.'; echo "\r";
            }
            $this->info(Carbon::now()->format('c').' - Processed '.$counter.'.  Updated '.$updatecounter.'.');
        });

        // Note: for speeding up performance, update 'Next conversation Due' and 'due_date_paused' in users table
        $this->updateUsersTable();
        
        echo 'Processed '.$counter.'.  Updated '.$updatecounter.'.'; echo "\r\n";
        DB::table('stored_dates')->updateOrInsert(
            [
            'name' => 'CalcNextConversationDate',
            ],
            [
            'value' => $start_time,
            ]
        );
        $this->info(Carbon::now()->format('c').' - Last Run Date Updated to: '.$start_time);
        $end_time = Carbon::now();
        $updated_employees = json_encode($updateemp);
        DB::table('job_sched_audit')->updateOrInsert(
            [
                'id' => $audit_id
            ],
            [
                'job_name' => $job_name,
                'start_time' => date('Y-m-d H:i:s',strtotime($start_time)),
                'end_time' => date('Y-m-d H:i:s',strtotime($end_time)),
                'cutoff_time' => date('Y-m-d H:i:s',strtotime($last_cutoff_time)),
                'status' => 'Completed',
                'details' => 'Processed '.$counter.' and Updated '.$updatecounter.' rows.',
            ]
        );
        $this->info(Carbon::now()->format('c').' - CalcNextConversationDate, Completed: '.$end_time);
        // Log::info($end_time->format('c').' - '.$processname.' - Finished');
    } 
     
    protected function updateUsersTable() {

        $users = User::from('users')
        ->whereRaw("trim(users.guid) <> ''")
        ->whereNotNull('users.guid')
        ->whereExists(function ($query) {
            $query->select(\DB::raw(1))
                ->from('employee_demo_jr')
                ->whereRaw("employee_demo_jr.id = (select max(id) from employee_demo_jr j2 where employee_demo_jr.employee_id = j2.employee_id)")
                ->whereColumn('employee_demo_jr.employee_id', 'users.employee_id')
                ->where(function($query) {
                    $query->whereRaw( 'employee_demo_jr.next_conversation_date <> users.next_conversation_date')
                            ->orWhereRaw( 'employee_demo_jr.due_date_paused <> users.due_date_paused')
                            ->orWhereNull('users.next_conversation_date')
                            ->orWhereNull('users.due_date_paused');
                });
        })
        ->update([
            'users.next_conversation_date' => \DB::raw(" (select next_conversation_date from employee_demo_jr j1 
                                        where id = (select max(id) from employee_demo_jr j2 where j1.employee_id = j2.employee_id)
                                                and users.employee_id = employee_id)" ),

            'users.due_date_paused' =>  \DB::raw(" (select due_date_paused from employee_demo_jr j1 
                                    where id = (select max(id) from employee_demo_jr j2 where j1.employee_id = j2.employee_id)
                                        and users.employee_id = employee_id)" )
        ]); 

    }

}
