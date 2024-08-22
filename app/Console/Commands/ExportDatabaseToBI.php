<?php

namespace App\Console\Commands;

use stdClass;
use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\JobSchedAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ExportDatabaseToBI extends Command
{

    protected $db_tables = [
        // ['name' => 'admin_orgs',                    'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'conversations',                 'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'conversation_participants',     'delta' => null,            'hidden' => null ],
        ['name' => 'conversation_topics',           'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'dashboard_notifications',       'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'excused_reasons',               'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'generic_templates',             'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'generic_template_binds',        'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'goals',                         'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'goals_shared_with',             'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'goal_comments',                 'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'goal_tags',                     'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'goal_types',                    'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'job_sched_audit',               'delta' => null,            'hidden' => null ],
        // ['name' => 'linked_goals',                  'delta' => null,            'hidden' => null ],
        // ['name' => 'migrations',                    'delta' => null,            'hidden' => null ],
        // ['name' => 'model_has_permissions',         'delta' => null,            'hidden' => null ],
        // ['name' => 'model_has_roles',               'delta' => null,            'hidden' => null ],
        // ['name' => 'notification_logs',             'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'notification_log_recipients',   'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'permissions',                   'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'roles',                         'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'role_has_permissions',          'delta' => null,            'hidden' => null ],
        // ['name' => 'shared_profiles',               'delta' => 'updated_at',    'hidden' => null ],
        // ['name' => 'stored_dates',                  'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'tags',                          'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'users',                         'delta' => 'updated_at',    'hidden' => ['password', 'remember_token'] ],
        ['name' => 'user_reporting_tos',            'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'preferred_supervisor',          'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'employee_managers',             'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'users_annex',                   'delta' => 'updated_at',    'hidden' => null ],
        ['name' => 'ods_dept_org_hierarchy',        'delta' => 'updated_at',    'hidden' => null ],
    ];
 
    protected $success;
    protected $failure;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:ExportDatabaseToBI {--manual}';  

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sending Performance database to Datawarehouse vis ODS';

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
        $this->info( 'Bulk Database Upload to ODS, Started: '. $start_time);
  
        $job_name = 'command:ExportDatabaseToBI';
        $switch = strtolower(env('PRCS_SEND_ODS_DATA'));
        $manualoverride = (strtolower($this->option('manual')) ? true : false);
        $status = (($switch == 'on' || $manualoverride) ? 'Initiated' : 'Disabled');
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );
  
        if ($switch == 'on' || $manualoverride) {

            $stored = DB::table('stored_dates')
            ->where('name', 'ODS Bulk Database Upload')
            ->first();
    
            if(is_null($stored)){
                $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
                $this->info( 'Last Bulk Upload Date not found.  Using ' . $last_cutoff_time);
                $stored = DB::table('stored_dates')->updateOrInsert(
                    [
                    'name' => 'ODS Bulk Database Upload',
                    ],
                    [
                    'value' => Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c'),
                    ]
            );
            } else {  
                if($stored->value){
                    $last_cutoff_time = $stored->value;
                    $this->info( 'Last Bulk Upload Date:  ' . $last_cutoff_time);
                }else{
                    $last_cutoff_time = Carbon::create(1900, 1, 1, 0, 0, 0, 'PDT')->format('c');
                    $this->info( 'Last Bulk Upload Date not found.  Using ' . $last_cutoff_time);
                }
            }
    
            $this->info( now() );

            // Main Loop
            foreach ($this->db_tables as $table) {
                $table_name =  $table['name'];
                $delta_field = $table['delta'];
                $hidden_fields = $table['hidden'];
                $this->sendTableDataToDataWarehouse($table_name, $delta_field, $hidden_fields, $last_cutoff_time);
            }
        
            DB::table('stored_dates')->updateOrInsert(
                [
                'name' => 'ODS Bulk Database Upload',
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
                ]
            );
        
            $this->info( 'Bulk Database Upload to ODS, Completed: ' . $end_time);

        } else {
            $this->info( 'Process is currently disabled; or "PRCS_SEND_ODS_DATA=on" is currently missing in the .env file.');
        }

        return 0;
    }


    /**
     * Main Function for sending pledges transactions to Datawarehouse.
     *
     * @return int
     */
    private function sendTableDataToDataWarehouse($table_name, $delta_field, $hidden_fields, $last_cutoff_time) {
        $this->info("Table '{$table_name}' Detail to BI (Datawarehouse) start");

        $this->success = 0;
        $this->failure = 0;
        $n = 0;

        // Main Process for each table 
        $sql = DB::table($table_name)
            ->when( $delta_field, function($q) use($last_cutoff_time, $delta_field, $hidden_fields) {
                return $q->where ($delta_field, '>=', $last_cutoff_time);
            })
            ->orderByRaw('1');
        
        // Chucking
        $sql->chunk(2000, function($chuck) use($table_name, $hidden_fields, &$n) {
            $this->info( "Sending table '{$table_name}' batch (2000) - " . ++$n );

            //$chuck->makeHidden(['password', 'remember_token']);
            if ($hidden_fields) {
                foreach($chuck as $item) {
                    foreach($hidden_fields as $hidden_field) {
                        // unset($item->password);
                        unset($item->$hidden_field);
                    }
                }
            }

            $pushdata = new stdClass();
            $pushdata->table_name = $table_name;
            $pushdata->table_data = json_encode($chuck);

            $this->sendData( $pushdata );
            
            unset($pushdata);
        });


        $this->info("Table '{$table_name}' data sent completed");
        $this->info( now() );
        $this->info("Success - " . $this->success);
        $this->info("failure - " . $this->failure);

        // // Update the Task Audit log
        // $task->end_time = Carbon::now();
        // $task->status = 'Completed';
        // $task->save();

        return 0;

    }

    
    protected function sendData($pushdata) {

        $response = Http::withBasicAuth(
            env('ODS_DEMO_CLIENT_ID'),
            env('ODS_DEMO_CLIENT_SECRET')
        )->withBody( json_encode($pushdata), 'application/json')
        ->post( env('ODS_BULK_UPLOAD') );

        if ($response->successful()) {
            $this->success += 1;
        } else {
                                    
            $this->info( $response->status() );
            $this->info( $response->body() );
            // dd( json_encode($data) );
            //$this->info( "Failed : " . print_r($response) );
            $this->failure += 1;
        }

    }

}
