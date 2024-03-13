<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;
use App\Models\ModelHasRoleAudit;
use App\Models\AdminOrg;
use App\Models\AdminOrgUser;
use App\Models\UserDemoJrView;

class AutoRemoveAccess extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:AutoRemoveAccess';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove access for Service Representatives & HR Admins when change in Dept ID or Position Nbr is detected.';

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
        $this->info( 'Auto Remove Access, Started:   '. $start_time);

        $switch = strtolower(env('PRCS_AUTO_REMOVE_ACCESS'));
  
        $job_name = 'command:AutoRemoveAccess';
        $status = (($switch == 'on') ? 'Initiated' : 'Disabled');
        $audit_id = JobSchedAudit::insertGetId(
            [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
            ]
        );

        $count_success = 0;
        $count_failed = 0;

        if ($switch == 'on') {

            $candidates = \DB::table('model_has_roles AS mhr')
                ->selectRaw('mhr.model_id, mhr.role_id')
                ->whereRaw('
                    mhr.role_id IN (3, 5)
                    AND NOT (EXISTS (SELECT 1 FROM model_has_role_audits AS mhra, users AS u, employee_demo AS ed WHERE mhra.model_id = mhr.model_id AND mhra.role_id = mhr.role_id AND mhra.deleted_at IS NULL AND mhra.model_id = u.id AND u.employee_id = ed.employee_id AND ed.date_deleted IS NULL AND ed.deptid = mhra.deptid)
                    AND EXISTS (SELECT 1 FROM model_has_role_audits AS mhra, users AS u, employee_demo AS ed WHERE mhra.model_id = mhr.model_id AND mhra.role_id = mhr.role_id AND mhra.deleted_at IS NULL AND mhra.model_id = u.id AND u.employee_id = ed.employee_id AND ed.date_deleted IS NULL AND ed.position_number = mhra.position_number))
                ')
                ->get();
    
            foreach($candidates as $oneid){
                \DB::beginTransaction();
                try {
                    ModelHasRoleAudit::updateOrCreate([
                        'model_id' => $oneid->model_id,
                        'role_id' => $oneid->role_id,
                    ], [
                        'deleted_at' => Carbon::now(),
                        'deleted_by' => 'AutoRemoveAccess',
                    ]);
                    if ($oneid->role_id == 3) {
                        $orgs = AdminOrg::whereRaw("user_id = {$oneid->model_id}")->delete();
                        $this->refreshAdminOrgUsersById( $oneid->model_id );
                    }
                    \DB::table('model_has_roles')->whereRaw("model_id = {$oneid->model_id} AND role_id = {$oneid->role_id}")->delete();
                    \DB::commit();
                    echo 'Removed access for '.$oneid->model_id.' - '.$oneid->role_id.'.'; echo "\r\n";
                    $count_success += 1;
                } catch (Exception $e) {
                    echo 'Unable to remove access for user '.$oneid->model_id.'.'; echo "\r\n";
                    \DB::rollback();
                    $count_failed += 1;
                }
            }

        } else {
            $this->info( 'Process is currently disabled; or "PRCS_AUTO_REMOVE_ACCESS=on" is currently missing in the config.');
        }

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
            'details' => "Deleted {$count_success} rows.  Failed {$count_failed} rows."
          ]
        );
  
        $this->info( 'Auto Remove Access, Completed: ' . $end_time);
  
    }

    protected function refreshAdminOrgUsersById($user_id) {
        // #809 Update the model 'AdminOrgUsers' on the latest updated user, and instantly available on Statistics and Reporting 
        // Step 1 -- Clean up for the updated user id
        AdminOrgUser::where('granted_to_id', $user_id)->where('access_type', 0)->where('shared_profile_id', 0)->delete();
        // Step 2 -- insert record
        AdminOrgUser::insertUsing([
            'granted_to_id', 'allowed_user_id',
            'admin_org_id'
        ], 
            UserDemoJrView::join('admin_orgs', 'admin_orgs.orgid', 'user_demo_jr_view.orgid')
                ->where('admin_orgs.version', 2)
                ->whereNull('user_demo_jr_view.date_deleted')
                ->where('admin_orgs.user_id',  $user_id )
                ->select('admin_orgs.user_id', 'user_demo_jr_view.user_id', 'admin_orgs.orgid')
                ->distinct()
        );
        // Populate auth_users tables
        \DB::statement("
            DELETE 
            FROM auth_users 
            WHERE type = 'HR'
                AND auth_id = {$user_id}
        ");
        $now = date('Y-m-d H:i:s', strtotime(Carbon::now()->format('c')));
        // Insert non-inherited orgs
        \DB::statement("
            INSERT IGNORE INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
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
                        ON ao.user_id = {$user_id}
                            AND ao.version = 2
                            AND inherited = 0
                            AND ao.orgid = ed.orgid
            )
        ");
        // Insert inherited orgs
        \DB::statement("
            INSERT IGNORE INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
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
                        ON aotv.user_id = {$user_id}
                            AND aotv.version = 2 
                            AND aotv.inherited = 1
                            AND aotv.level = 0 AND aotv.organization_key = edt.organization_key
                WHERE 
                    NOT EXISTS (SELECT DISTINCT 1 FROM auth_users WHERE auth_users.type = 'HR' AND auth_users.auth_id = aotv.user_id AND auth_users.user_id = u.id)
            )
        ");
        $level = 0;
        do {
            $level += 1;
            \DB::statement("
                INSERT IGNORE INTO auth_users (type, auth_id, user_id, created_at, updated_at) (
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
                            ON aotv.user_id = {$user_id}
                                AND aotv.version = 2 
                                AND aotv.inherited = 1
                                AND aotv.level = {$level} AND aotv.level{$level}_key = edt.level{$level}_key
                    WHERE 
                        NOT EXISTS (SELECT DISTINCT 1 FROM auth_users WHERE auth_users.type = 'HR' AND auth_users.auth_id = aotv.user_id AND auth_users.user_id = u.id)
                )
            ");
        } while ($level < 4);
        // Populate auth_org table
        \DB::statement("
            DELETE 
            FROM auth_orgs 
            WHERE type = 'HR'
                AND auth_id = {$user_id}
        ");
        \DB::statement("
            INSERT IGNORE INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
                SELECT DISTINCT
                    'HR',
                    ao.user_id AS auth_id,
                    ao.orgid AS orgid,
                    '{$now}',
                    '{$now}'
                FROM 
                    admin_orgs 
                        AS ao
                WHERE ao.user_id = {$user_id}
                    AND ao.version = 2
                    AND inherited = 0
            )
        ");
        \DB::statement("
            INSERT IGNORE INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
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
                        ON aotv.user_id = {$user_id}
                            AND aotv.version = 2 
                            AND aotv.inherited = 1
                            AND aotv.level = 0 AND aotv.organization_key = edt.organization_key
                WHERE 
                    NOT EXISTS (SELECT DISTINCT 1 FROM auth_orgs WHERE auth_orgs.type = 'HR' AND auth_orgs.auth_id = aotv.user_id AND auth_orgs.orgid = edt.id)
            )
        ");        
        $level = 0;
        do {
            $level += 1;
            \DB::statement("
                INSERT IGNORE INTO auth_orgs (type, auth_id, orgid, created_at, updated_at) (
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
                            ON aotv.user_id = {$user_id}
                                AND aotv.version = 2 
                                AND aotv.inherited = 1
                                AND aotv.level = {$level} AND aotv.level{$level}_key = edt.level{$level}_key
                    WHERE 
                        NOT EXISTS (SELECT DISTINCT 1 FROM auth_orgs WHERE auth_orgs.type = 'HR' AND auth_orgs.auth_id = aotv.user_id AND auth_orgs.orgid = edt.id)
                )
            ");
        } while ($level < 4);
    }

}
