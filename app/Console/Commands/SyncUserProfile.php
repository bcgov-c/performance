<?php

namespace App\Console\Commands;

use DateTime;
use Carbon\Carbon;
use App\Models\User;
use App\Models\EmployeeDemo;
use App\Models\JobSchedAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;


class SyncUserProfile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:SyncUserProfiles';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Or Create User Profile based on Employee demography data';

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

        $job = JobSchedAudit::where('job_name', $this->signature)
            ->where('status','completed')
            ->orderBy('id','desc')
            ->first();     

        $last_cutoff_time = ($job) ? $job->cutoff_time : new DateTime( '1990-01-01');

        $start_time = Carbon::now();

        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $this->signature,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => 'Initiated'
          ]
        );

        $cutoff_time = Carbon::now();

        $this->SyncUserProfile($last_cutoff_time, $cutoff_time);

        $end_time = Carbon::now();
        JobSchedAudit::updateOrInsert(
          [
            'id' => $audit_id
          ],
          [
            'job_name' => $this->signature,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
            'cutoff_time' => date('Y-m-d H:i:s', strtotime($cutoff_time)),
            'status' => 'Completed'
          ]
        );

        return 0;
    }

    protected function SyncUserProfile($last_sync_at, $new_sync_at)
    {

        // $new_sync_at = Carbon::now();
        // $last_sync_at = User::max('last_sync_at'); 
        $last_sync_at = '1990-01-01';       // always do the full set

        $employees = EmployeeDemo::whereNotIn('guid', ['', ' '])
            ->whereNotIn('employee_email', ['', ' '])
            ->where(function ($query) use ($last_sync_at) {
                $query->whereNull('date_updated');
                $query->orWhere('date_updated', '>=', $last_sync_at );
            })
            //->whereNotNull('date_updated')
            //->where('date_updated', '>=', $last_sync_at )
            //->whereIn('employee_id',['105823', '060061', '107653',
            //'115637','131116','139238','145894','146113','152843','152921','163102'] )
            ->orderBy('employee_id')
            ->orderBy('job_indicator', 'desc')
            ->orderBy('empl_record')
            ->get(['employee_id', 'empl_record', 'employee_email', 'guid', 'idir',
                'employee_first_name', 'employee_last_name', 'job_indicator',
                'position_start_date', 'supervisor_emplid', 'date_updated', 'date_deleted']);


        // Step 1 : Create and Update User Profile (no update on reporting to)
        $this->info( now() );
        $this->info('Step 1 - Create and Update User Profile (but no update on reporting to)' );


        $password = Hash::make(env('SYNC_USER_PROFILE_SECRET'));
        foreach ($employees as $employee) {

          //$reporting_to = $this->getReportingUserId($employee);
          $reporting_to = null;

          // Check the user by GUID 
          $user = User::where('guid', $employee->guid)->first();

          if ($user) {

            if ($employee->employee_email) {
                if ( strtolower(trim($user->email)) == strtolower(trim($employee->employee_email)) )  {
                    
                    //$user->email = $employee->employee_email;
                    $user->employee_id = $employee->employee_id;
                    $user->empl_record = $employee->empl_record;
                    //$user->reporting_to = $reporting_to;
                    $user->joining_date = $employee->position_start_date;
                    $user->acctlock = $employee->date_deleted ? true : false;
                    $user->last_sync_at = $new_sync_at;
        
                    $user->save();
                } else {
                    $this->info('Step 1: User ' . $employee->employee_email . ' - ' . 
                            $employee->guid . ' has difference email address with same GUID.');
                }

            }
          } else {

              $user = User::whereRaw("lower(email) = '". strtolower(addslashes($employee->employee_email))."'") 
                            ->first();
                                                      
              if ($user) {
                if ( strtolower(trim($user->email)) == strtolower(trim($employee->employee_email)) &&
                        (!($user->guid)) )  {
                    $user->guid = $employee->guid;
                    $user->employee_id = $employee->employee_id;
                    $user->empl_record = $employee->empl_record;
                    //$user->reporting_to = $reporting_to;
                    $user->joining_date = $employee->position_start_date;
                    $user->last_sync_at = $new_sync_at;
                    $user->save();
                }

              } else {
                $user = User::create([
                  'guid' => $employee->guid,
                  'name' => $employee->employee_first_name . ' ' . $employee->employee_last_name,
                  'email' => $employee->employee_email,
                  //'reporting_to' => $reporting_to,
                  'employee_id' => $employee->employee_id,
                  'empl_record' => $employee->empl_record,
                  'joining_date' => $employee->position_start_date,
                  'password' => $password,
                  'acctlock' => $employee->date_deleted ? true : false,
                  'last_sync_at' => $new_sync_at,
                ]);
              }

          }
        
        }

        // Step 2 : Update Reporting to
        $this->info( now() );
        $this->info('Step 2 - Update Reporting to');

        foreach ($employees as $employee) {

            $reporting_to = $this->getReportingUserId($employee);  
            
            $user = User::where('guid', $employee->guid)->first();

            if ($user) {

                if ($user->reporting_to != $reporting_to) {
                    $user->reporting_to = $reporting_to;
                    $user->last_sync_at = $new_sync_at;
                    $user->save();             

                    // Update Reporting Tos
                    if ($reporting_to) {
                        $user->reportingTos()->updateOrCreate([
                            'reporting_to_id' => $reporting_to,
                        ]);
                    }
                }
            } else {
                $this->info('Step 2: User ' . $employee->employee_email . ' - ' . 
                            $employee->guid . ' not found by guid.');
            }
          
        }

        // Step 3 : Lock Inactivate User account
        $this->info( now() );        
        $this->info('Step 3 - Lock Out Inactivate User account');

        $users = User::whereIn('guid',function($query) { 
                    $query->select('guid')->from('employee_demo')->whereNotNull('date_deleted');
            })->update(['acctlock'=>true, 'last_sync_at' => $new_sync_at]);

            
        // Step 4 : Lock all users except pivot run users
        $this->info( now() );        
        $this->info('Step 4 - Lock Out Users except Pivot run based on organization');

        $users = User::whereNotNull('guid')
            ->whereNotIn('guid',function($query) { 
                $query->select('guid')->from('employee_demo')
                    ->whereIn('organization', ['BC Public Service Agency']);
        })->update(['acctlock'=>true, 'last_sync_at' => $new_sync_at]);

        echo now();
    }

    public function getReportingUserId($employee)
    {

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
                
/*
                $reportingToUser = User::create([
                    'name' => $supervisor->employee_first_name . ' ' . $supervisor->employee_last_name,
                    'email' => (trim($supervisor->employee_email)) ? $supervisor->employee_email : $supervisor->employee_id,
                    'guid' => $supervisor->guid,
                    'joining_date' => $supervisor->position_start_date,
                    'password' => Hash::make('mywatchdog'),
                ]);

                return $reportingToUser->id;
*/
            }
        }

        return null;

    }

}
