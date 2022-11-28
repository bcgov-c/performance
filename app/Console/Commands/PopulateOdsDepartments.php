<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;

class PopulateOdsDepartments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PopulateOdsDepartments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate Departments table with deptid from ODS Employee Demo table';

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
        $retention_date = Carbon::now()->subMonth(1)->toDateString();
        $this->info( 'Populate ODS Departments, Started:   '. $start_time);
  
        $job_name = 'command:PopulateOdsDepartments';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );
          \DB::statement("
            DELETE 
            FROM ods_departments 
            WHERE date_created < '".$retention_date."'
        ");

        \DB::statement("
            INSERT INTO ods_departments (
                SELECT DISTINCT ".$audit_id.",
                    ed.deptid,
                    ed.organization,
                    ed.level1_program,
                    ed.level2_division,
                    ed.level3_branch,
                    ed.level4,
                    now()
                FROM 
                    employee_demo AS ed
                WHERE 
                    TRIM(ed.organization) <> ''
                    AND NOT ed.organization IS NULL
                    AND ed.date_updated = (SELECT MAX(ed1.date_updated) FROM employee_demo AS ed1 WHERE ed1.guid = ed.guid AND ed1.deptid = ed.deptid)
                ORDER BY 
                    ed.organization, 
                    ed.level1_program, 
                    ed.level2_division, 
                    ed.level3_branch, 
                    ed.level4
            )
        ");

        $end_time = Carbon::now()->format('c');
        DB::table('job_sched_audit')->updateOrInsert(
          [
            'id' => $audit_id
          ],
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
            'status' => 'Completed',
          ]
        );
  
        $this->info( 'Populate ODS Departments, Completed: ' . $end_time);
  
    }
}
