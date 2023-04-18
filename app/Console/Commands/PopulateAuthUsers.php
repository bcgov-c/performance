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
    protected $signature = 'command:PopulateAuthUsers';

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

        $now = date('Y-m-d H:i:s', strtotime($start_time));

        $count_del = \DB::table('auth_users AS au')->whereRaw("type = 'HR'")->count();
        \DB::statement("
            DELETE 
            FROM auth_users 
            WHERE type = 'HR'
        ");
        $this->info(Carbon::now()->format('c')." - Deleted {$count_del} HR entries.");

        \DB::statement("
            INSERT INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    ao.user_id AS auth_id,
                    u.id AS user_id,
                    '{$now}',
                    '{$now}'
                FROM 
                    users 
                        AS u 
                    INNER JOIN employee_demo 
                        AS ed 
                        USE INDEX(idx_employee_demo_employee_id_date_deleted)
                        ON ed.employee_id = u.employee_id 
                            AND ed.date_deleted IS NULL
                    INNER JOIN admin_orgs 
                        AS ao
                        ON ao.version = 2
                            AND inherited = 0
                            AND ao.orgid = ed.orgid
            )
        ");
        $count_direct = \DB::table('auth_users AS au')->whereRaw("type = 'HR'")->count();
        $this->info(Carbon::now()->format('c')." - Inserted {$count_direct} HR Admin explicitly assigned users.");

        \DB::statement("
            INSERT INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    aotv.user_id AS auth_id,
                    u.id AS user_id,
                    '{$now}',
                    '{$now}'
                FROM 
                    users 
                        AS u 
                    INNER JOIN employee_demo 
                        AS ed 
                        USE INDEX(idx_employee_demo_employee_id_date_deleted)
                        ON ed.employee_id = u.employee_id 
                            AND ed.date_deleted IS NULL
                    INNER JOIN employee_demo_tree
                        AS edt
                        ON edt.id = ed.orgid
                    INNER JOIN admin_org_tree_view 
                        AS aotv 
                        ON aotv.version = 2 
                            AND aotv.inherited = 1
                            AND (
                                (aotv.level = 0 AND aotv.organization_key = edt.organization_key)  OR
                                (aotv.level = 1 AND aotv.level1_key = edt.level1_key) OR
                                (aotv.level = 2 AND aotv.level2_key = edt.level2_key) OR
                                (aotv.level = 3 AND aotv.level3_key = edt.level3_key) OR
                                (aotv.level = 4 AND aotv.level4_key = edt.level4_key)
                              )
                WHERE 
                    NOT EXISTS (SELECT DISTINCT 1 FROM auth_users WHERE type = 'HR' AND auth_id = aotv.user_id AND user_id = u.id)
            )
        ");
        $count_total = \DB::table('auth_users AS au')->whereRaw("type = 'HR'")->count();
        $count_inherited = $count_total - $count_direct;
        $this->info(Carbon::now()->format('c')." - Inserted {$count_inherited} HR Admin inherited users.");

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
            'details' => "Deleted {$count_del} rows.  Inserted {$count_total} ({$count_direct} + {$count_inherited}) rows."
          ]
        );
  
        $this->info( 'Populate Authorized HR Users, Completed: ' . $end_time);
  
    }
}
