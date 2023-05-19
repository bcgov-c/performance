<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;

class PopulateAuthOrgs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PopulateAuthOrgs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build assigned orgs based on admin_orgs table';

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
        $this->info( 'Populate Authorized HR Organizations, Started:   '. $start_time);
  
        $job_name = 'command:PopulateAuthOrgs';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );

        $now = date('Y-m-d H:i:s', strtotime($start_time));

        $count_del = \DB::table('auth_orgs')->whereRaw("type = 'HR'")->count();
        \DB::statement("
            DELETE 
            FROM auth_orgs 
            WHERE type = 'HR'
        ");
        $this->info(Carbon::now()->format('c')." - Deleted {$count_del} HR entries.");

        \DB::statement("
            INSERT INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    ao.user_id AS auth_id,
                    ao.orgid AS orgid,
                    '{$now}',
                    '{$now}'
                FROM 
                    admin_orgs 
                        AS ao
                WHERE ao.version = 2
                    AND inherited = 0
            )
        ");
        $count_direct = \DB::table('auth_orgs AS au')->whereRaw("type = 'HR'")->count();
        $this->info(Carbon::now()->format('c')." - Inserted {$count_direct} HR Admin explicitly assigned organizations.");

        \DB::statement("
            INSERT INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    aotv.user_id AS auth_id,
                    edt.id AS orgid,
                    '{$now}',
                    '{$now}'
                FROM 
                    employee_demo_tree
                        AS edt
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
                    NOT EXISTS (SELECT DISTINCT 1 FROM auth_orgs WHERE type = 'HR' AND auth_id = aotv.user_id AND orgid = edt.id)
            )
        ");
        $count_total = \DB::table('auth_orgs')->whereRaw("type = 'HR'")->count();
        $count_inherited = $count_total - $count_direct;
        $this->info(Carbon::now()->format('c')." - Inserted {$count_inherited} HR Admin inherited organizations.");

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
  
        $this->info( 'Populate Authorized HR Organizations, Completed: ' . $end_time);
  
    }
}
