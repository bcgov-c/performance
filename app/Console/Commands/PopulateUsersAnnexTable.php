<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\JobSchedAudit;

class PopulateUsersAnnexTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:PopulateUsersAnnexTable';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build employee related details in users_annex table';

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
        $this->info( 'Populate Users Annex, Started:   '. $start_time);
  
        $job_name = 'command:PopulateUsersAnnexTable';
        $status = 'Initiated';
        $audit_id = JobSchedAudit::insertGetId(
          [
            'job_name' => $job_name,
            'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
            'status' => $status
          ]
        );

        \DB::beginTransaction();
        try {
            \DB::statement("
                DELETE 
                FROM users_annex 
            ");

            $this->info(Carbon::now()->format('c')." - Process static base...");
            \DB::statement("
                INSERT INTO users_annex (
                    user_id,
                    orgid,
                    employee_id,
                    empl_record,
                    level,
                    headcount,
                    groupcount,
                    organization,
                    level1_program,
                    level2_division,
                    level3_branch,
                    level4,
                    level5,
                    level6,
                    organization_key,
                    level1_key,
                    level2_key,
                    level3_key,
                    level4_key,
                    level5_key,
                    level6_key,
                    organization_deptid,
                    level1_deptid,
                    level2_deptid,
                    level3_deptid,
                    level4_deptid,
                    level5_deptid,
                    level6_deptid,
                    organization_orgid,
                    level1_orgid,
                    level2_orgid,
                    level3_orgid,
                    level4_orgid,
                    level5_orgid,
                    level6_orgid,
                    jr_id,
                    jr_due_date_paused,
                    jr_next_conversation_date,
                    jr_excused_type,
                    jr_current_manual_excuse,
                    jr_created_by_id,
                    jr_created_at,
                    jr_updated_by_id,
                    jr_updated_at,
                    jr_excused_reason_id,
                    jr_excused_reason_desc,
                    jr_updated_by_name,
                    excused_updated_by_name,
                    r_name,
                    reason_id,
                    reason_name,
                    excusedtype,
                    excusedlink,
                    excused_by_name,
                    created_at_string,
                    created_at,
                    updated_at,
                    isSupervisor,
                    isDelegate,
                    reportees
                )
                SELECT DISTINCT
                    u.id,
                    d.orgid,
                    d.employee_id,
                    d.empl_record,
                    edt.level,
                    edt.headcount,
                    edt.groupcount,
                    edt.organization,
                    edt.level1_program,
                    edt.level2_division,
                    edt.level3_branch,
                    edt.level4,
                    edt.level5,
                    edt.level6,
                    edt.organization_key,
                    edt.level1_key,
                    edt.level2_key,
                    edt.level3_key,
                    edt.level4_key,
                    edt.level5_key,
                    edt.level6_key,
                    edt.organization_deptid,
                    edt.level1_deptid,
                    edt.level2_deptid,
                    edt.level3_deptid,
                    edt.level4_deptid,
                    edt.level5_deptid,
                    edt.level6_deptid,
                    edt.organization_orgid,
                    edt.level1_orgid,
                    edt.level2_orgid,
                    edt.level3_orgid,
                    edt.level4_orgid,
                    edt.level5_orgid,
                    edt.level6_orgid,
                    edj.id AS jr_id,
                    edj.due_date_paused,
                    edj.next_conversation_date,
                    edj.excused_type,
                    edj.current_manual_excuse,
                    edj.created_by_id,
                    edj.created_at,
                    edj.updated_by_id,
                    edj.updated_at,
                    edj.excused_reason_id AS excused_reason_id,
                    edj.excused_reason_desc AS excused_reason_desc,
                    edj.updated_by_name,
                    en.name AS excused_updated_by_name,
                    r.name AS reason_name,
                    CASE when edj.excused_type = 'A' THEN CASE when edj.current_employee_status = 'A' THEN edj.excused_reason_id ELSE 1 END ELSE u.excused_reason_id END AS reason_id,
                    CASE when edj.excused_type = 'A' THEN CASE when edj.current_employee_status = 'A' THEN edj.excused_reason_desc ELSE 'PeopleSoft Status' END when edj.excused_type = 'M' THEN edj.excused_reason_desc ELSE CASE when u.excused_flag = 1 THEN r.name ELSE '' END END AS reason_name,
                    CASE when edj.excused_type = 'A' THEN 'Auto' when edj.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                    CASE when edj.excused_type = 'A' THEN 'Auto' when edj.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                    CASE when edj.excused_type = 'A' THEN 'System' when edj.excused_type = 'M' THEN CASE when edj.updated_by_name <> '' THEN edj.updated_by_name ELSE edj.updated_by_id END ELSE CASE when u.excused_flag = 1 THEN CASE when en.name <> '' THEN en.name ELSE u.excused_updated_by END ELSE '' END END AS excused_by_name,
                    CASE when edj.excused_type = 'A' THEN date(edj.created_at) when edj.excused_type = 'M' THEN date(edj.updated_at) ELSE CASE when u.excused_flag = 1 THEN u.excused_updated_at ELSE '' END END AS created_at_string,
                    NOW(),
                    NOW(),
                    CASE WHEN (SELECT 1 FROM users AS su WHERE su.reporting_to = u.id LIMIT 1) THEN 1 ELSE 0 END AS isSupervisor,
                    CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_with = u.id LIMIT 1) THEN 1 ELSE 0 END AS isDelegate,
                    0 AS reportees
                FROM
                    (employee_demo AS d 
                        USE INDEX (idx_employee_demo_employeeid_orgid)
                    LEFT JOIN employee_demo_tree AS edt
                        ON edt.id = d.orgid
                    LEFT JOIN employee_demo_jr AS edj
                        USE INDEX (idx_employee_demo_jr_employeeid_id)
                        ON edj.employee_id = d.employee_id AND edj.id = (SELECT edj1.id FROM employee_demo_jr AS edj1 USE INDEX (idx_employee_demo_jr_employeeid_id) WHERE edj1.employee_id = edj.employee_id ORDER BY edj1.id DESC LIMIT 1)) 
                    INNER JOIN (users AS u
                        USE INDEX (idx_users_employeeid_emplrecord)
                    LEFT JOIN users AS en 
                        USE INDEX (idx_users_id)
                        ON en.id = u.excused_updated_by) ON u.employee_id = d.employee_id
                    LEFT JOIN excused_reasons AS r 
                        ON r.id = u.excused_reason_id
            ");

            $this->info(Carbon::now()->format('c')." - Process Supervisor Overrides...");
            \DB::statement("
                UPDATE users_annex AS target,
                    (SELECT ua.id, u.employee_id, ed.employee_name, u.name, u.email, ed.position_number, es.supervisor_id, 
                        (SELECT 1 FROM users_annex uax WHERE uax.user_id = ua.user_id AND uax.reporting_to_employee_id IS NOT NULL LIMIT 1) AS manager_updated
                    FROM users_annex AS ua, employee_supervisor AS es, users AS u, employee_demo AS ed
                    WHERE ua.user_id = es.user_id
                        AND es.supervisor_id = u.id
                        AND es.deleted_at IS NULL
                        AND u.employee_id = ed.employee_id
                        AND ed.date_deleted IS NULL) AS source
                SET 
                    target.reporting_to_employee_id = source.employee_id,
                    target.reporting_to_name = source.employee_name,
                    target.reporting_to_name2 = source.name,
                    target.reporting_to_email = source.email,
                    target.reporting_to_position_number = source.position_number,
                    target.reporting_to_userid = source.supervisor_id
                WHERE target.id = source.id
                    AND source.manager_updated IS NULL
            ");

            $this->info(Carbon::now()->format('c')." - Process Preferred Supervisors...");
            \DB::statement("
                UPDATE users_annex AS target,
                    (SELECT ua.id, em.supervisor_emplid, em.supervisor_name, em.supervisor_name2, em.supervisor_email, em.supervisor_position_number, em.supervisor_userid,
                        (SELECT 1 FROM users_annex uax WHERE uax.user_id = ua.user_id AND uax.reporting_to_employee_id IS NOT NULL LIMIT 1) AS manager_updated
                    FROM users_annex AS ua, employee_demo AS ed, employee_managers AS em, preferred_supervisor AS ps
                    WHERE ua.employee_id = em.employee_id
                        AND ua.employee_id = ed.employee_id
                        AND ua.empl_record = ed.empl_record
                        AND em.employee_id = ps.employee_id
                        AND ed.position_number = ps.position_nbr
                        AND ed.date_deleted IS NULL
                        AND em.supervisor_emplid = ps.supv_empl_id) AS source
                SET 
                    target.reporting_to_employee_id = source.supervisor_emplid,
                    target.reporting_to_name = source.supervisor_name,
                    target.reporting_to_name2 = source.supervisor_name2,
                    target.reporting_to_email = source.supervisor_email,
                    target.reporting_to_position_number = source.supervisor_position_number,
                    target.reporting_to_userid = source.supervisor_userid
                WHERE target.id = source.id
                    AND source.manager_updated IS NULL
            ");
            
            $this->info(Carbon::now()->format('c')." - Process Supervisors...");
            \DB::statement("
                UPDATE users_annex AS target,
                    (SELECT ua.id, em.supervisor_emplid, em.supervisor_name, em.supervisor_name2, em.supervisor_email, em.supervisor_position_number, em.supervisor_userid,
                        (SELECT 1 FROM users_annex uax WHERE uax.user_id = ua.user_id AND uax.reporting_to_employee_id IS NOT NULL LIMIT 1) AS manager_updated
                    FROM users_annex AS ua, employee_demo AS ed, employee_managers AS em
                    WHERE ua.employee_id = ed.employee_id
                        AND ua.empl_record = ed.empl_record
                        AND ua.employee_id = em.employee_id
                        AND ed.position_number = em.position_number) AS source
                SET 
                    target.reporting_to_employee_id = source.supervisor_emplid,
                    target.reporting_to_name = source.supervisor_name,
                    target.reporting_to_name2 = source.supervisor_name2,
                    target.reporting_to_email = source.supervisor_email,
                    target.reporting_to_position_number = source.supervisor_position_number,
                    target.reporting_to_userid = source.supervisor_userid
                WHERE target.id = source.id
                    AND source.manager_updated IS NULL
            ");

            $this->info(Carbon::now()->format('c')." - Process Reportee Count...");
            \DB::statement("
                UPDATE 
                    users_annex AS target,
                    employee_demo AS upd_ed,
                    users_annex_reportees_view  AS upd_uarv
                SET 
                    target.reportees = upd_uarv.reportees
                WHERE 
                    target.employee_id = upd_ed.employee_id
                    AND target.empl_record = upd_ed.empl_record
                    AND upd_uarv.employee_id = upd_ed.employee_id
                    AND upd_uarv.position_number = upd_ed.position_number
            ");
            
            \DB::commit();


             //#1158 Employees with no supervisor in ODS data are not displayed properly in PDP       
             //add a step 7 that displays supervisor name = "Vacant" if all of the above return no data?
             $this->info(Carbon::now()->format('c')." - Process vacant supervisors...");
             \DB::statement("
             UPDATE users_annex AS target,
                    (SELECT ua.id, em.supervisor_emplid, em.supervisor_name, em.supervisor_name2, em.supervisor_email, em.supervisor_position_number, em.supervisor_userid,
                        (SELECT 1 FROM users_annex uax WHERE uax.user_id = ua.user_id AND uax.reporting_to_employee_id IS NOT NULL LIMIT 1) AS manager_updated
                    FROM users_annex AS ua, employee_demo AS ed, employee_managers AS em
                    WHERE ua.employee_id = ed.employee_id
                        AND ua.empl_record = ed.empl_record
                        AND ua.employee_id = em.employee_id
                        AND ed.position_number = em.position_number) AS source
                SET 
                    target.reporting_to_name = 'Vacant'
                WHERE target.id = source.id
                    AND (target.reporting_to_name IS NULL OR target.reporting_to_name = '')
             ");
            
             \DB::commit();       


            $this->info(Carbon::now()->format('c')." - Commit all...");
        } catch (Exception $e) {
            echo 'Unable to populate users_annex table.'; echo "\r\n";
            \DB::rollback();
            $end_time = Carbon::now()->format('c');
            DB::table('job_sched_audit')->updateOrInsert(
              [
                'id' => $audit_id
              ],
              [
                'job_name' => $job_name,
                'start_time' => date('Y-m-d H:i:s', strtotime($start_time)),
                'end_time' => date('Y-m-d H:i:s', strtotime($end_time)),
                'status' => 'Failed',
                'details' => 'Unable to populate users_annex table.',
              ]
            );
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
          ]
        );
  
        $this->info( 'Populate Users Annex, Completed: ' . $end_time);
  
    }
}
