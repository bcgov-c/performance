<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Organization;
use App\Models\JobSchedAudit;
use Carbon\Carbon;

class GetODSOrganizations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:getODSOrganizations';

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
    public function handle() {
        $start_time = Carbon::now();
        $job_name = 'command:getODSOrganizations';
        // $audit_id = DB::table('job_sched_audit')->insertGetId(
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => 'Initiated'
          ]
        );

        $demodata = Http::withHeaders(['Content-Type' => 'application/x-www-form-urlencoded'])->withBasicAuth(env('ODS_DEMO_CLIENT_ID'),env('ODS_DEMO_CLIENT_SECRET'))->get(env('ODS_DEMO_URI_3'));

        $cutoff_time = Carbon::now();

        $data = $demodata['value'];

        foreach($data as $item){
            DB::table('organizations')->updateOrInsert(
              [
                'deptid' => $item['DepartmentID'],
              ],
              [
                'organization' => $item['Organization'],
                'level1' => $item['Level1'],
                'level2' => $item['Level2'],
                'level3' => $item['Level3'],
                'level4' => $item['Level4'],
                'date_updated' => date('Y-m-d H:i:s', strtotime($item['date_updated'])),
                'date_deleted' => date('Y-m-d H:i:s', strtotime($item['date_deleted'])),
              ]
            );
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
            'status' => 'Completed'
          ]
        );


        //return 0;
    }
}
