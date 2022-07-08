<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\EmployeeDemo;
use App\Models\JobSchedAudit;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class GetODSEmployeeDemographics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GetODSEmployeeDemographics {--manual}';

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
      $this->info( 'Employee Demographic Data pull from ODS, Started: '. $start_time);

      $job_name = 'command:GetODSEmployeeDemographics';
      $switch = strtolower(env('PRCS_PULL_ODS_DATA'));
      $manualoverride = (strtolower($this->option('manual')) ? true : false);
      $status = (($switch == 'on' || $manualoverride) ? 'Initiated' : 'Disabled in .env');
      $audit_id = JobSchedAudit::insertGetId(
        [
          'job_name' => $job_name,
          'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
          'status' => $status
        ]
      );

      if ($switch == 'on' || $manualoverride) {

        $stored = DB::table('stored_dates')
        ->where('name', 'ODS Employee Demo Last Pull')
        ->first();

        if(is_null($stored)){
          $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
          $this->info( 'Last Pull Date not found.  Using ' . $last_cutoff_time);
          $stored = DB::table('stored_dates')->updateOrInsert(
            [
              'name' => 'ODS Employee Demo Last Pull',
            ],
            [
              'value' => Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c'),
            ]
          );
        } else {  
          if($stored->value){
            $last_cutoff_time = $stored->value;
            $this->info( 'Last Pull Date:  ' . $last_cutoff_time);
          }else{
            $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
            $this->info( 'Last Pull Date not found.  Using ' . $last_cutoff_time);
          }
        }

        $top = 1000;
        $skip = 0;
        $demodata = Http::acceptJson()
        ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
        ->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))
        ->withOptions(['query' => [
          '$top' => $top, 
          '$skip' => $skip, 
          '$filter' => "date_updated gt '" . $last_cutoff_time . "'",
          '$orderby' => 'EMPLID,EMPL_RCD,EFFDT,EFFSEQ',
        ], 
        ])
        ->get( env('ODS_EMPLOYEE_DEMO_URI') . '?$top=' . $top . '&$skip=' . $skip );
        $data = $demodata['value'];
        $total = 0;

        do {

          $total += count($data);
          $this->info( '$top = ' . $top . ' : $skip = ' . $skip . ' : $data = ' . count($data) . ' : Count = ' . $total);

          foreach($data as $item){
            DB::table('employee_demo')->updateOrInsert(
              [
                //'guid' => $item['GUID'],
                'employee_id' => $item['EMPLID'],
                'empl_record' => $item['EMPL_RCD'],
              ],
              [
                // 'guid' => $item['GUID'],
                // 'employee_id' => $item['EMPLID'],
                // 'empl_record' => $item['EMPL_RCD'],
                'employee_first_name' => $item['first_name'],
                'employee_last_name' => $item['last_name'],
                'employee_status' => $item['EMPL_STATUS'],
                'employee_email' => $item['email'],
                'classification_group' => $item['ClassificationGroup'],
                'deptid' => $item['DEPTID'],
                'jobcode' => $item['JOBCODE'],
                // 'job_title' => $item['job_title'],
                'job_title' => $item['jobcode_desc'],
                'position_number' => $item['position_number'],
                'position_start_date' => $item['position_start_date'] ? date('Y-m-d', strtotime($item['position_start_date'])) : null,
                // 'manager_id' => $item['manager_id'],
                // 'manager_first_name' => $item['manager_first_name'],
                // 'manager_last_name' => $item['manager_last_name'],
                'guid' => $item['GUID'],
                // 'date_posted' => date('Y-m-d H:i:s', strtotime($item['date_posted'])),
                'date_deleted' => $item['date_deleted'] ? date('Y-m-d H:i:s', strtotime($item['date_deleted'])) : null,
                'date_updated' => $item['date_updated'] ? date('Y-m-d H:i:s', strtotime($item['date_updated'])) : null,
                // 'date_created' => date('Y-m-d H:i:s', strtotime($item['date_created'])),
                'business_unit' => $item['BUSINESS_UNIT'],
                'effdt' => $item['EFFDT'],
                'effseq' => $item['EFFSEQ'],
                'empl_class' => $item['EMPL_CLASS'],
                'empl_ctg' => $item['EMPL_CTG'],
                'hire_dt' => $item['HIRE_DT'] ? date('Y-m-d H:i:s', strtotime($item['HIRE_DT'])) : null,
                'idir' => $item['IDIR'],
                'job_function' => $item['JOB_FUNCTION'],
                'jobcodedescgroup' => $item['JobCodeDescGroup'],
                'occupationalgroup' => $item['OccupationalGroup'],
                'paygroup' => $item['PAYGROUP'],
                'organization' => $item['Organization'],
                'job_indicator' => $item['job_indicator'],
                'address1' => $item['address1'],
                'address2' => $item['address2'],
                'appointment_status' => $item['appointment_status'],
                'can_noc_code' => $item['can_noc_code'],
                'city' => $item['city'],
                'country' => $item['country'],
                'employee_status_long' => $item['employee_status_long_description'],
                // 'estimated_years_service' => $item['estimated_years_service'],
                'job_function_employee_group' => $item['job_function_employee_group'],
                'jobcode_desc' => $item['jobcode_desc'],
                'level1_program' => $item['level1_program'],
                'level2_division' => $item['level2_division'],
                'level3_branch' => $item['level3_branch'],
                'level4' => $item['level4'],
                'employee_middle_name' => $item['middle_name'],
                'employee_name' => $item['name'],
                'office_address' => $item['office_address1'],
                'office_address2' => $item['office_address2'],
                'office_city' => $item['office_city'],
                'office_country' => $item['office_country'],
                'office_location_code' => $item['office_location_code'],
                'office_phone' => $item['office_phone'],
                'office_postal' => $item['office_postal'],
                'office_stateprovince' => $item['office_stateprovince'],
                'phone' => $item['phone'],
                'postal' => $item['postal'],
                'public_service_act' => $item['public_service_act'],
                'sal_admin_plan' => $item['sal_admin_plan'],
                'stateprovince' => $item['stateprovince'],
                'supervisor_emplid' => $item['supervisor_emplid'],
                //'supervisor_position_start_date' => $item['supervisor_position_start_date'] ? date('Y-m-d', strtotime($item['supervisor_position_start_date'])) : null,
                'supervisor_email' => $item['supervisor_email'],
                'supervisor_name' => $item['supervisor_name'],
                'supervisor_position_number' => $item['supervisor_position_number'],
                //'supervisor_position_title' => $item['supervisor_position_title'],
                'tgb_reg_district' => $item['tgb_reg_district'],
              ]
            );
          };

          $skip += $top;

          $demodata = Http::acceptJson()
          ->withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])
          ->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))
          ->withOptions(['query' => [
            '$top' => $top, 
            '$skip' => $skip, 
            '$filter' => "date_updated gt '" . $last_cutoff_time . "'",
            '$orderby' => 'EMPLID,EMPL_RCD,EFFDT,EFFSEQ',
          ], 
          ])
    ->get( env('ODS_EMPLOYEE_DEMO_URI') . '?$top=' . $top . '&$skip=' . $skip );
          $data = $demodata['value'];
    
      } while(count($data)!=0);

      DB::table('stored_dates')->updateOrInsert(
        [
          'name' => 'ODS Employee Demo Last Pull',
        ],
        [
          'value' => $start_time,
        ]
      );
      $this->info( 'Last Pull Date Updated to: ' . $start_time);

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
          'details' => 'Processed ' . $total . ' rows from ' . $last_cutoff_time . '.',
        ]
      );

      $this->info( 'Employee Demographic Data pull from ODS, Completed: ' . $end_time);
    } else {
        $this->info( 'Process is currently disabled; or "PRCS_PULL_ODS_DATA=on" is currently missing in the .env file.');
    }

  }
    
}
