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

            \DB::statement("
                INSERT INTO users_annex (
                  user_id,
                  orgid,
                  employee_id,
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
                  reporting_to_employee_id,
                  reporting_to_name,
                  reporting_to_email,
                  reporting_to_position_number,
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
                  u.employee_id,
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
                  CASE WHEN ps.supv_empl_id IS NOT NULL THEN ps.supv_empl_id 
                  ELSE 
                      CASE WHEN uesd.employee_id IS NOT NULL THEN uesd.employee_id
                      ELSE
                          CASE WHEN em.supervisor_emplid IS NOT NULL THEN em.supervisor_emplid
                          ELSE
                              NULL
                          END
                      END
                  END reporting_to_employee_id,
                  CASE WHEN ps.supv_empl_id IS NOT NULL THEN psd.employee_name 
                  ELSE 
                      CASE WHEN uesd.employee_id IS NOT NULL THEN uesd.employee_name
                      ELSE
                          CASE WHEN em.supervisor_emplid IS NOT NULL THEN em.supervisor_name
                          ELSE
                              NULL
                          END
                      END
                  END reporting_to_name,
                  CASE WHEN ps.supv_empl_id IS NOT NULL THEN psd.employee_email 
                  ELSE 
                      CASE WHEN uesd.employee_id IS NOT NULL THEN uesd.employee_email
                      ELSE
                          CASE WHEN em.supervisor_emplid IS NOT NULL THEN em.supervisor_email
                          ELSE
                              NULL
                          END
                      END
                  END reporting_to_email,
                  CASE WHEN ps.supv_empl_id IS NOT NULL THEN psd.position_number 
                  ELSE 
                      CASE WHEN uesd.employee_id IS NOT NULL THEN uesd.position_number
                      ELSE
                          CASE WHEN em.supervisor_emplid IS NOT NULL THEN em.supervisor_position_number
                          ELSE
                              NULL
                          END
                      END
                  END reporting_to_position_number,
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
                  CASE when edj.excused_type = 'A' THEN CASE when edj.current_employee_status = 'A' THEN 2 ELSE 1 END ELSE u.excused_reason_id END AS reason_id,
                  CASE when edj.excused_type = 'A' THEN CASE when edj.current_employee_status = 'A' THEN 'Classification' ELSE 'PeopleSoft Status' END when edj.excused_type = 'M' THEN edj.excused_reason_desc ELSE CASE when u.excused_flag = 1 THEN r.name ELSE '' END END AS reason_name,
                  CASE when edj.excused_type = 'A' THEN 'Auto' when edj.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                  CASE when edj.excused_type = 'A' THEN 'Auto' when edj.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                  CASE when edj.excused_type = 'A' THEN 'System' when edj.excused_type = 'M' THEN CASE when edj.updated_by_name <> '' THEN edj.updated_by_name ELSE edj.updated_by_id END ELSE CASE when u.excused_flag = 1 THEN CASE when en.name <> '' THEN en.name ELSE u.excused_updated_by END ELSE '' END END AS excused_by_name,
                  CASE when edj.excused_type = 'A' THEN date(edj.created_at) when edj.excused_type = 'M' THEN date(edj.updated_at) ELSE CASE when u.excused_flag = 1 THEN u.excused_updated_at ELSE '' END END AS created_at_string,
                  NOW(),
                  NOW(),
                  CASE WHEN (SELECT 1 FROM users AS su WHERE su.reporting_to = u.id LIMIT 1) THEN 1 ELSE 0 END AS isSupervisor,
                  CASE WHEN (SELECT 1 FROM shared_profiles AS sp WHERE sp.shared_with = u.id LIMIT 1) THEN 1 ELSE 0 END AS isDelegate,
                  ( (SELECT COUNT(dmo.employee_id) FROM positions AS posn, employee_demo AS dmo WHERE posn.reports_to = d.position_number AND posn.position_nbr = dmo.position_number AND dmo.date_deleted IS NULL) +
				              (SELECT COUNT(dmo.employee_id) FROM positions AS sspn, positions AS spn, employee_demo AS dmo WHERE d.position_number = sspn.reports_to and sspn.position_nbr = spn.reports_to AND spn.position_nbr = dmo.position_number 
                      AND dmo.date_deleted IS NULL AND NOT EXISTS (SELECT 1 FROM employee_demo AS non WHERE non.position_number = sspn.position_nbr LIMIT 1)) ) AS reportees
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
                  LEFT JOIN employee_managers AS em
                    USE INDEX (employee_managers_employee_id_position_number_index)
                    ON em.employee_id = d.employee_id
                      AND em.position_number = d.position_number
                  LEFT JOIN employee_supervisor AS es
                    USE INDEX (employee_supervisor_user_id_supervisor_id_index)
                    ON es.user_id = u.id
                  LEFT JOIN users AS ues
                    USE INDEX (idx_users_id)
                    ON ues.id = es.supervisor_id
                  LEFT JOIN employee_demo AS uesd
                    USE INDEX (employee_demo_employee_id_empl_record_unique)
                    ON uesd.employee_id = ues.employee_id
                  LEFT JOIN preferred_supervisor AS ps
                    USE INDEX (preferred_supervisor_employee_id_position_nbr_index)
                    ON ps.employee_id = d.employee_id
                      AND ps.position_nbr = d.position_number
                  LEFT JOIN employee_demo AS psd
                    USE INDEX (employee_demo_employee_id_empl_record_unique)
                    ON psd.employee_id = ps.supv_empl_id
            ");
            \DB::commit();
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
