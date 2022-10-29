<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;

class PopulateAuthUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PopulateAyuthUsers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build assigned users based on admin_orgs table';

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
        $this->info( 'Populate Authorized HR Users, Started:   '. $start_time);
  
        $job_name = 'command:PopulateAuthUsers';
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
            FROM auth_users 
            WHERE type = 'HR'
        ");

        \DB::statement("
            INSERT INTO auth_users (
                SELECT DISTINCT
                    'HR',
                    o.user_id as auth_id,
                    u.id as user_id,
                    now()
                FROM 
                    users as u,
                    employee_demo d,
                    admin_orgs o
                WHERE 
                    u.guid = d.guid
                    AND (o.organization = d.organization OR ((TRIM(o.organization) = '' OR o.organization IS NULL) AND (d.organization = '' OR d.organization IS NULL))) 
                    AND (o.level1_program = d.level1_program OR ((TRIM(o.level1_program) = '' OR o.level1_program IS NULL) AND (d.level1_program = '' OR d.level1_program IS NULL))) 
                    AND (o.level2_division = d.level2_division OR ((TRIM(o.level2_division) = '' OR o.level2_division IS NULL) AND (d.level2_division = '' OR d.level2_division IS NULL))) 
                    AND (o.level3_branch = d.level3_branch OR ((TRIM(o.level3_branch) = '' OR o.level3_branch IS NULL) AND (d.level3_branch = '' OR d.level3_branch IS NULL)))
                    AND (o.level4 = d.level4 OR ((TRIM(o.level4) = '' OR o.level4 IS NULL) AND (d.level4 = '' OR d.level4 IS NULL)))
                ORDER BY 
                    o.id, 
                    u.id
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
  
        $this->info( 'Populate Authorized HR Users, Completed: ' . $end_time);
  
    }
}
