<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Conversation;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\ExcusedClassification;
use App\Models\JobSchedAudit;
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

        $start_time = Carbon::now()->format('c');
        // $current_cutoff_datetime = $start_time;
        $this->info( 'CalcNextConversationDate, Started: '. $start_time);
        Log::info($start_time.' - '.$processname.' - Started.');

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
                $this->info( 'Last Run Date:  ' . $last_cutoff_time);
            } else { 
                $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
                $this->info( 'Last Run Date not found.  Using ' . $last_cutoff_time);
            }
        } else {  
            $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
            $this->info( 'Last Run Date not found.  Using ' . $last_cutoff_time);
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
        $ClassificationArray = ExcusedClassification::select('jobcode')->get()->toArray();
        EmployeeDemo::leftjoin('users', 'users.guid', 'employee_demo.guid')
        ->whereNotNull('employee_demo.guid')
        ->whereNull('employee_demo.date_deleted')
        ->orderBy('employee_demo.employee_id')
        ->chunk(1000, function($employeeDemo) use (&$counter, $ClassificationArray, $processname) {
            foreach ($employeeDemo as $demo) {
                $changeType = 'noChange';
                $new_last_employee_status = null;
                $new_last_classification = null;
                $new_last_classification_descr = null;
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
                if ($demo->guid) {
                    // YES GUID
                    // get last conversation details
                    $lastConv = Conversation::join('conversation_participants', 'conversations.id', 'conversation_participants.conversation_id')
                    ->join('users', 'users.id', 'conversation_participants.participant_id')
                    ->whereNotNull('signoff_user_id')
                    ->whereNotNull('supervisor_signoff_id')
                    ->whereNotNull('sign_off_time')
                    ->where('participant_id', '=', $demo->users->id)
                    ->whereRaw('cast(users.employee_id as unsigned) = signoff_user_id')
                    ->select('users.employee_id', 'conversations.sign_off_time')
                    ->orderBy('sign_off_time', 'desc')
                    ->first();
                    if ($lastConv) {
                        // use last conversation + 4 months as initial next conversation date
                        $lastConversationDate = $lastConv->sign_off_time->format('M d, Y');
                        $initNextConversationDate = $lastConv->sign_off_time->addMonth(4)->format('M d, Y');
                        // echo 'Last Conversation Date:'.$lastConversationDate; echo "\r\n";
                    } else {
                        // no last conversation, use randomizer to assign initial next conversation date
                        $lastConversationDate = null;
                        $initNextConversationDate = $demo->users->joining_date->addMonth(4)->format('M d, Y');
                    }
                    // post go-live hard-coded initial next conversation due dates
                    $virtualHardDate = Carbon::createFromDate(2022, 10, 14);
                    if ($virtualHardDate->gt($initNextConversationDate)) {
                        // distribute next conversation date, based on last digit of employee ID
                        $DDt = abs (($demo->employee_id % 10) - 1) * 5 + (($demo->employee_id % 5));
                        $initNextConversationDate = $virtualHardDate->addDays($DDt)->format('M d, Y');
                    }
                    // calcualte initial last conversation date; init next conversation minus 4 months
                    $initLastConversationDate = Carbon::parse($initNextConversationDate)->subMonth(4);
                    if ($lastConversationDate && Carbon::parse($lastConversationDate)->gt($initLastConversationDate)) {
                        $initLastConversationDate = $lastConversationDate;
                    }
                    // get last stored detail in junior table
                    $jr = EmployeeDemoJunior::where('guid', '=', $demo->guid)->orderBy('id', 'desc')->first();
                    if ($jr) {
                        // Previous JR record exist
                        $new_last_employee_status = $jr->current_employee_status;
                        $new_last_classification = $jr->current_classification;
                        $new_last_classification_descr = $jr->current_classification_descr;
                        $new_last_manual_excuse = $jr->current_manual_excuse ?? 'N';
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status != 'A') {
                            // STATUS CHANGE
                            $changeType = 'statusStartExcuse';
                            $excuseType = 'A';
                        }
                        if ($jr->current_employee_status != 'A' 
                            && $demo->employee_status == 'A') {
                            // STATUS CHANGE
                            $changeType = 'statusEndExcuse';
                        }
                        $jr_inarray = in_array($jr->current_classification, $ClassificationArray);
                        $demo_inarray = in_array($demo->jobcode, $ClassificationArray);
                        $excused = ($demo->employee_status != 'A' || $demo_inarray || $demo->excused_flag);
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr_inarray == false
                            && $demo_inarray) {
                            // CLASSIFICATION CHANGE
                            $changeType = 'classStartExcuse';
                            $excuseType = 'A';
                        }
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr_inarray 
                            && $demo_inarray == false) {
                            // CLASSIFICATION CHANGE
                            $changeType = 'classEndExcuse';
                        }
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A' 
                            && $jr_inarray == false
                            && $demo_inarray == false
                            && ($jr->current_manual_excuse || $jr->current_manual_excuse != 'Y') 
                            && $demo->excused_flag) {
                            // MANUAL CHANGE
                            $changeType = 'manualStartExcuse';
                            $excuseType = 'M';
                        }
                        if ($jr->current_employee_status == 'A' 
                            && $demo->employee_status == 'A'
                            && $jr_inarray == false
                            && $demo_inarray == false
                            && $jr->current_manual_excuse == 'Y' 
                            && $demo->excused_flag != null) {
                            // MANUAL CHANGE
                            $changeType = 'manualEndExcuse';
                        }
                        if (in_array($changeType, ['statusEndExcuse', 'classEndExcuse', 'manualEndExcuse'])) {
                            // re-calc next conversation date
                            // get historical dates
                            $allDates = EmployeeDemoJunior::where('guid', $demo->guid)
                            ->where('created_at', '>', $initLastConversationDate)
                            ->orderBy('created_at')
                            ->get();
                            // calc excused days
                            foreach($allDates as $oneDay) {
                                $lastDateCalculated = false;
                                if ($prevDate == null) {
                                    $prevDate = $oneDay->created_at->format('M d, Y');
                                    $prevPause = $oneDay->due_date_paused;
                                } else {
                                    if ($prevPause != $oneDay->due_date_paused) {
                                        $currDate = Carbon::parse($oneDay->created_at->format('M d, Y'));
                                        if ($currDate->gt($initLastConversationDate)) {
                                            $usedate1 = $currDate;
                                        } else {
                                            $usedate1 = $initLastConversationDate;
                                        }
                                        if ($currDate->gt($initNextConversationDate)) {
                                            $useDate2 = $initNextConversationDate;
                                        } else {
                                            $useDate2 = $currDate;
                                        }
                                        $diffInDays += abs(Carbon::parse($useDate2)->diffInDays($usedate1));
                                        $lastDateCalculated = true;
                                    }
                                }
                            }
                            if ($lastDateCalculated == false && $excused == false && $prevPause == 'Y') {
                                $currDate = Carbon::parse(Carbon::now());
                                if ($currDate ->gt($initLastConversationDate)) {
                                    $usedate1 = $currDate ;
                                } else {
                                    $usedate1 = $initLastConversationDate;
                                }
                                if ($currDate->gt($initNextConversationDate)) {
                                    $useDate2 = $initNextConversationDate;
                                } else {
                                    $useDate2 = $currDate;
                                }
                                $diffInDays += abs(Carbon::parse($useDate2)->diffInDays($usedate1));
                                $lastDateCalculated = true;
                            }
                            if ($diffInDays < 0) {
                                $diffInDays = 0;
                            }
                            $newEndDate = Carbon::parse($initNextConversationDate)->addDays($diffInDays);
                            if ($newEndDate->gt($initNextConversationDate)) {
                                $initNextConversationDate = $newEndDate->format('M d, Y');
                            }
                        }
                    } else {
                        // NO Previous JR record exist, store details to junior table
                        if ($demo->employee_status != 'A') {
                            $changeType = 'statusNewExcuse';
                            $excuseType = 'A';
                        } else {
                            if ($demo_inarray) {
                                $changeType = 'classNewExcuse';
                                $excuseType = 'A';
                            } else {
                                if ($demo->excused_flag) {
                                    $changeType = 'manualNewExcuse';
                                    $excuseType = 'M';
                                } else {
                                    $changeType = 'noExcuse';
                                    $excuseType = null;
                                }
                            }
                        }
                    }
                    $excusedArrayTypes = ['statusStartExcuse', 'classStartExcuse', 'manualStartExcuse', 'statusNewExcuse', 'classNewExcuse', 'manualNewExcuse'];
                    if ($changeType != 'noChange') {
                        $newJr = new EmployeeDemoJunior;
                        $newJr->guid = $demo->guid;
                        $newJr->current_employee_status = $demo->employee_status;
                        $newJr->current_classification = $demo->jobcode;
                        $newJr->current_classification_descr = $demo->jobcode_desc;
                        $newJr->current_manual_excuse = $demo->excused_flag ? 'Y' : 'N';
                        $newJr->due_Date_paused = in_array($changeType, $excusedArrayTypes) ? 'Y' : 'N';
                        $newJr->last_employee_status = $new_last_employee_status;
                        $newJr->last_classification = $new_last_classification;
                        $newJr->last_classification_descr = $new_last_classification_descr;
                        $newJr->last_manual_excuse = $new_last_manual_excuse;
                        $newJr->excused_type = $excuseType;
                        $newJr->last_conversation_date = $lastConversationDate ? Carbon::parse($lastConversationDate) : null;
                        $newJr->next_conversation_date = $initNextConversationDate ? Carbon::parse($initNextConversationDate) : null;
                        $newJr->created_by_id = $processname;
                        $newJr->updated_by_id = $processname;
                        $newJr->save();
                    } else {
                        // SKIP if no change
                    }
                } else {
                    // NO GUID
                    Log::info(Carbon::now()->format('c').' - '.$processname.' - Employee ID['.$demo->employee_id.' does not have GUID in Employee Demo table.');
                }
                $counter += 1;
                echo 'Processed '.$counter; echo "\r";
            }
        });
        echo 'Processed '.$counter; echo "\r\n";
        DB::table('stored_dates')->updateOrInsert(
            [
            'name' => 'CalcNextConversationDate',
            ],
            [
            'value' => $start_time,
            ]
        );
        $this->info( 'Last Run Date Updated to: '.$start_time);
        $end_time = Carbon::now();
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
                'details' => 'Processed '.$counter.' rows from '.date('Y-m-d H:i:s',strtotime($last_cutoff_time)).'.',
            ]
        );
        $this->info('CalcNextConversationDate, Completed: '.$end_time);
        Log::info($end_time->format('c').' - '.$processname.' - Finished');
    } 
    
}
