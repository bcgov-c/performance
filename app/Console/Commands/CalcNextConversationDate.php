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
        
        EmployeeDemo::whereNotNull('guid')
        // ->where('organization', 'like', '%BC Public Service%')
        ->whereDate('date_updated', '>', $last_cutoff_time)
        ->orderBy('employee_id')
        ->chunk(100, function($employeeDemo) {
            foreach ($employeeDemo as $demo) {
                $jr = EmployeeDemoJunior::where('guid', '=', $demo->guid)->orderBy('id', 'desc')->first();
                if($jr) {
                    if(($demo->employee_status == 'A' && $jr->current_employee_status != 'A') || ($demo->employee_status != 'A' && $jr->current_employee_status == 'A')) {
                        $user = User::where('guid', '=', $demo->guid)->first();
                        if ($user){
                            $lastConv = Conversation::whereNotNull('signoff_user_id')
                            ->whereNotNull('supervisor_signoff_id')
                            ->whereNotNull('sign_off_time')
                            ->select('sign_off_time')
                            ->orderBy('sign_off_time', 'desc')
                            ->first();
                            $newJr = new EmployeeDemoJunior;
                            $newJr->last_employee_status = $jr->current_employee_status;
                            $newJr->current_employee_status = $demo->employee_status;
                            $newJr->due_Date_paused = ($demo->employee_status != 'A' ? 'Y' : 'N');
                            $newJr->last_conversation_date = (($lastConv && $lastConv->sign_off_time) ?? $lastConv->sign_off_time);
                            // Standard + 4 months of last signed off conversation
                            $newJr->next_conversation_date = (($demo->employee_status == 'A' && $lastConv && $lastConv->sign_off_time) ?? $lastConv->sign_off_time->addMonth(4));
                            // Manual excused from users table
                            
                            $newJr->save();
                        } else {
                            // skip
                        }
                    } else {
                        // skip
                    }
                } else {
                    $user = User::where('guid', '=', $demo->guid)->first();
                    if ($user){
                        $lastConv = Conversation::whereNotNull('signoff_user_id')
                        ->whereNotNull('supervisor_signoff_id')
                        ->whereNotNull('sign_off_time')
                        ->select('sign_off_time')
                        ->orderBy('sign_off_time', 'desc')
                        ->first();
                        $newJr = new EmployeeDemoJunior;
                        $newJr->last_employee_status = null;
                        $newJr->current_employee_status = $demo->employee_status;
                        $newJr->due_Date_paused = ($demo->employee_status != 'A' ? 'Y' : 'N');
                        $newJr->last_conversation_date = (($lastConv && $lastConv->sign_off_time) ?? $lastConv->sign_off_time);
                        // Standard + 4 months of last signed off conversation
                        $newJr->next_conversation_date = (($demo->employee_status == 'A' && $lastConv && $lastConv->sign_off_time) ?? $lastConv->sign_off_time->addMonth(4));
                        // Manual excused from users table
                        
                        $newJr->save();
                    } else {
                        // skip
                    }
                }
            }
        });

      DB::table('stored_dates')->updateOrInsert(
        [
          'name' => 'CalcNextConversationDate',
        ],
        [
          'value' => $start_time,
        ]
      );
      $this->info( 'Last Run Date Updated to: ' . $start_time);

      $end_time = Carbon::now();
        DB::table('job_sched_audit')->updateOrInsert(
          [
            'id' => $audit_id
          ],
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
            'cutoff_time' => date('Y-m-d H:i:s', strtotime($last_cutoff_time)),
            'status' => 'Completed',
          ]
        );

        $this->info('CalcNextConversationDate, Completed: ' . $end_time);

    } 
    
}
