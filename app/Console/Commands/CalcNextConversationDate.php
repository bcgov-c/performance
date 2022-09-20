<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\Conversation;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\EmployeeDemoJunior;
use App\Models\JobSchedAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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

        $start_time = Carbon::now()->format('c');
        $this->info( 'CalcNextConversationDate, Started: '. $start_time);

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
        $counter = 0;
        EmployeeDemo::whereNotNull('guid')
        // ->where('organization', 'like', '%BC Public Service%')
        // ->whereIn('employee_id', ['163102', '111908', '062149', '108057'])
        // ->where('employee_name', 'like', '%Zehra%')
        ->whereDate('date_updated', '>', $last_cutoff_time)
        ->orderBy('employee_id')
        ->chunk(1000, function($employeeDemo) use (&$counter) {
            foreach ($employeeDemo as $demo) {
                $assumeChange = true;
                $due_Date_paused = $demo->employee_status != 'A' ? 'Y' : 'N';
                // get user info
                $user = User::where('guid', '=', $demo->guid)->first();
                if ($user) {
                    // echo 'User:'.$user->id.$user->name; echo "\r\n";
                    // get last conversation details
                    $lastConv = Conversation::join('conversation_participants', 'conversations.id', '=', 'conversation_participants.conversation_id')
                    ->join('users', 'users.id', '=', 'conversation_participants.participant_id')
                    ->whereNotNull('signoff_user_id')
                    ->whereNotNull('supervisor_signoff_id')
                    ->whereNotNull('sign_off_time')
                    ->where('participant_id', '=', $user->id)
                    ->whereRaw('cast(users.employee_id as unsigned) = signoff_user_id')
                    ->select('users.employee_id', 'conversations.sign_off_time')
                    ->orderBy('sign_off_time', 'desc')
                    ->first();
                    $lastConversationDate = null;
                    $initLastConversationDate = null;
                    $initNextConversationDate = null;
                    $DDt = null;
                    if ($lastConv) {
                        // use last conversation + 4 months as initial next conversation date
                        $lastConversationDate = $lastConv->sign_off_time->format('M d, Y');
                        $initNextConversationDate = $lastConv->sign_off_time->addMonth(4)->format('M d, Y');
                        // echo 'Last Conversation Date:'.$lastConversationDate; echo "\r\n";
                    } else {
                        // echo 'Not found conversation'; echo "\r\n";
                        // no last conversation, use randomizer to assign initial next conversation date
                        $lastConversationDate = null;
                        $initNextConversationDate = $user->joining_date->addMonth(4)->format('M d, Y');
                    }
                    // post go-live hard-coded initial next conversation due dates
                    $virtualHardDate = Carbon::createFromDate(2022, 10, 14);
                    if ($virtualHardDate->gt($initNextConversationDate)) {
                        // distribute next conversation date, based on last digit of employee ID
                        $DDt = abs (($user->id % 10) - 1) * 5 + (($user->id % 5));
                        $initNextConversationDate = $virtualHardDate->addDays($DDt)->format('M d, Y');
                    }
                    // calcualte initial last conversation date; init next conversation minus 4 months
                    $initLastConversationDate = (new Carbon($initNextConversationDate))->subMonth(4);
                    if ($lastConversationDate && (new Carbon($lastConversationDate))->gt($initLastConversationDate)) {
                        $initLastConversationDate = $lastConversationDate;
                    }
                    // get last stored detail in junior table
                    $jr = EmployeeDemoJunior::where('guid', '=', $demo->guid)->orderBy('id', 'desc')->first();
                    if ($jr) {
                        $lastEmployeeStatus = $jr->current_employee_status;
                        if ($jr->current_employee_status == 'A' && $demo->employee_status != 'A') {
                            // no next conversation date
                            $initLastConversationDate = null;
                        }
                        if ($jr->current_employee_status != 'A' && $demo->employee_status == 'A'){
                            // re-calc next conversation date
                            $prevActive = EmployeeDemoJunior::where('guid', '=', $demo->guid)->where('current_employee_status', '=', 'A')->where('id', '<', $jr->id)->orderBy('id', 'desc')->first();
                            if ($prevActive) {
                                $nextRow = EmployeeDemoJunior::where('guid', '=', $demo->guid)->where('current_employee_status', '!=', 'A')->where('id', '<=', $jr->id)->where('id', '>', $prevActive->id)->orderBy('id')->first();
                            } else {
                                $nextRow = EmployeeDemoJunior::where('guid', '=', $demo->guid)->where('current_employee_status', '!=', 'A')->where('id', '<=', $jr->id)->orderBy('id')->first();
                            }
                            $newStartDate = $initLastConversationDate->format('M d, Y');
                            $newEndDate = (new Carbon($nextRow->date_updated));
                            $diffInDays = (new Carbon($initNextConversationDate))->diffInDays($newStartDate);
                            if ($diffInDays < 0) {
                                $diffInDays = 0;
                            }
                            $newEndDate->addDays($diffInDays);
                            if ($newEndDate->gt($initNextConversationDate)) {
                                $initNextConversationDate = $newEndDate->format('M d, Y');
                            }
                        }
                        if (($jr->current_employee_status == 'A' && $demo->employee_status == 'A') || ($jr->current_employee_status != 'A' && $demo->employee_status != 'A')) {
                            // no employee status change, no update needed
                            $assumeChange = false;
                        }
                    } else {
                        // no previous junior detail
                        $lastEmployeeStatus = null;
                    }
                    if ($assumeChange) {
                        //store details to junior table
                        $newJr = new EmployeeDemoJunior;
                        $newJr->guid = $demo->guid;
                        $newJr->last_employee_status = $lastEmployeeStatus;
                        $newJr->current_employee_status = $demo->employee_status;
                        $newJr->due_Date_paused = $due_Date_paused;
                        $newJr->last_conversation_date = $lastConversationDate ? (new Carbon($lastConversationDate)) : null;
                        // echo 'Storing Last Conversation Date:'.$newJr->last_conversation_date; echo "\r\n";
                        $newJr->next_conversation_date = $initNextConversationDate ? (new Carbon($initNextConversationDate)) : null;
                        $newJr->save();
                    } else {
                        // no change, do not update
                        // echo 'Skipping:'.$demo->guid.' - '.$demo->employee_name.' - No change'; echo "\r\n";
                    }
                } else {
                    // skip, no user info, do not process
                    // echo 'Skipping:'.$demo->guid.' - '.$demo->employee_name.' - No user details'; echo "\r\n";
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

    } 
    
}
