<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Organization;
use App\Models\JobSchedAudit;
use Carbon\Carbon;

class GetODSDepartments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getODSDepartments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieve data from CData:Datamart_ePerform_meta_dept_org_levels';

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
    public function handle() {
        $start_time = Carbon::now();
        $job_name = 'command:getODSDepartments';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => 'Initiated'
          ]
        );

        $this->info('Department Org Levels pull from ODS, Started: '. $start_time);

        $demodata = Http::withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))->get(env('ODS_DEMO_URI_3'));

        $cutoff_time = Carbon::now();

        $data = $demodata['value'];

        $counter = 0;

        foreach($data as $item){
            DB::table('ods_tmp_meta_dept_org_levels')->updateOrInsert(
                [
                    'jobsched_id' => $audit_id,
                    'DepartmentID' => $item['DepartmentID'],
                ],
                [
                    'Organization' => $item['Organization'],
                    'Level1' => $item['Level1'],
                    'Level2' => $item['Level2'],
                    'Level3' => $item['Level3'],
                    'Level4' => $item['Level4'],
                    'date_updated' => date('Y-m-d H:i:s', strtotime($item['date_updated'])),
                    'date_deleted' => date('Y-m-d H:i:s', strtotime($item['date_deleted'])),
                ]
            );
            $counter += 1;
          };

        $end_time = Carbon::now();
        DB::table('job_sched_audit')->updateOrInsert(
          [
            'id' => $audit_id
          ],
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
            'cutoff_time' => date('Y-m-d H:i:s', strtotime($cutoff_time)),
            'status' => 'Completed',
            'details'=> 'Processed '.$counter.' entries.'
          ]
        );

        $this->info('Department Org Levels pull from ODS, Completed: ' . $end_time);
    }
}
