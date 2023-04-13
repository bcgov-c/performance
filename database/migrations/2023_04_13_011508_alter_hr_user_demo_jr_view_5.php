<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHrUserDemoJrView5 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement('DROP VIEW IF EXISTS hr_user_demo_jr_view');
        
        \DB::statement("
            CREATE VIEW hr_user_demo_jr_view
            AS
            SELECT 
                o.user_id AS ao_user_id,
                u.id AS user_id,
                u.name AS user_name,
                u.employee_id,
                u.guid,
                u.excused_flag,
                u.excused_reason_id,
                u.excused_updated_by,
                u.excused_updated_at,
                u.joining_date,
                u.acctlock,
                u.reporting_to,
                d.empl_record,
                d.employee_name,
                d.employee_email,
                d.jobcode,
                d.jobcode_desc,
                d.job_indicator,
                t.id AS orgid,
                t.level,
                t.organization,
                t.level1_program,
                t.level2_division,
                t.level3_branch,
                t.level4,
                t.level5,
                t.organization_key,
                t.level1_key,
                t.level2_key,
                t.level3_key,
                t.level4_key,
                t.level5_key,
                t.organization_deptid,
                t.level1_deptid,
                t.level2_deptid,
                t.level3_deptid,
                t.level4_deptid,
                t.level5_deptid,
                d.deptid,
                d.employee_status,
                d.position_number,
                d.manager_id,
                d.supervisor_position_number,
                d.supervisor_emplid,
                d.supervisor_name,
                d.supervisor_email,
                urt.employee_id AS reporting_to_employee_id,
                edo.employee_name AS reporting_to_name,
                edo.employee_email AS reporting_to_email,
                d.date_updated,
                d.date_deleted,
                j.id AS jr_id,
                j.due_date_paused,
                j.next_conversation_date,
                j.excused_type,
                j.current_manual_excuse,
                j.created_by_id,
                j.created_at,
                j.updated_by_id,
                j.updated_at,
                j.excused_reason_id AS j_excused_reason_id,
                j.excused_reason_desc AS j_excused_reason_desc,
				j.updated_by_name,
                en.name AS excused_updated_by_name,
                r.name AS r_name,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 2 ELSE 1 END ELSE u.excused_reason_id END AS reason_id,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 'Classification' ELSE 'PeopleSoft Status' END when j.excused_type = 'M' THEN j.excused_reason_desc ELSE CASE when u.excused_flag = 1 THEN r.name ELSE '' END END AS reason_name,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                CASE when j.excused_type = 'A' THEN 'System' when j.excused_type = 'M' THEN CASE when j.updated_by_name <> '' THEN j.updated_by_name ELSE j.updated_by_id END ELSE CASE when u.excused_flag = 1 THEN CASE when en.name <> '' THEN en.name ELSE u.excused_updated_by END ELSE '' END END AS excused_by_name,
                CASE when j.excused_type = 'A' THEN date(j.created_at) when j.excused_type = 'M' THEN date(j.updated_at) ELSE CASE when u.excused_flag = 1 THEN u.excused_updated_at ELSE '' END END AS created_at_string,
                u.employee_id AS employee_id_search,
                d.employee_name AS employee_name_search
            FROM 
				(employee_demo 
					AS d 
					USE INDEX (idx_employee_demo_employeeid_orgid)
                LEFT JOIN employee_demo_jr j 
					USE INDEX (idx_employee_demo_jr_employeeid_id)
					ON j.employee_id = d.employee_id AND j.id = (SELECT MAX(j1.id) FROM employee_demo_jr AS j1 WHERE j1.employee_id = j.employee_id)
				INNER JOIN employee_demo_tree 
					AS t
					ON t.id = d.orgid
				INNER JOIN admin_orgs 
					AS o
					USE INDEX (idx_admin_orgs_orgid_version_inherited_user_id)
					ON o.orgid = d.orgid
						AND o.version = 2
						AND o.inherited = 0
				) 
				INNER JOIN (
					users 
						AS u 
						USE INDEX (idx_users_employeeid_emplrecord)
					LEFT JOIN users 
						AS en 
						USE INDEX (idx_users_id)
						ON en.id = u.excused_updated_by
					LEFT JOIN excused_reasons 
						AS r 
						ON r.id = u.excused_reason_id
				) ON u.employee_id = d.employee_id
				LEFT JOIN (
                    users 
						AS urt 
						USE INDEX (idx_users_id)
					LEFT JOIN employee_demo 
						AS edo 
						USE INDEX (idx_employee_demo_employeeid_name_email) 
						ON edo.employee_id = urt.employee_id 
				) ON urt.id = u.reporting_to
            WHERE d.date_deleted IS NULL
            UNION
            SELECT 
                o.user_id AS ao_user_id,
                u.id AS user_id,
                u.name AS user_name,
                u.employee_id,
                u.guid,
                u.excused_flag,
                u.excused_reason_id,
                u.excused_updated_by,
                u.excused_updated_at,
                u.joining_date,
                u.acctlock,
                u.reporting_to,
                d.empl_record,
                d.employee_name,
                d.employee_email,
                d.jobcode,
                d.jobcode_desc,
                d.job_indicator,
                t.id AS orgid,
                t.level,
                t.organization,
                t.level1_program,
                t.level2_division,
                t.level3_branch,
                t.level4,
                t.level5,
                t.organization_key,
                t.level1_key,
                t.level2_key,
                t.level3_key,
                t.level4_key,
                t.level5_key,
                t.organization_deptid,
                t.level1_deptid,
                t.level2_deptid,
                t.level3_deptid,
                t.level4_deptid,
                t.level5_deptid,
                d.deptid,
                d.employee_status,
                d.position_number,
                d.manager_id,
                d.supervisor_position_number,
                d.supervisor_emplid,
                d.supervisor_name,
                d.supervisor_email,
                urt.employee_id AS reporting_to_employee_id,
                edo.employee_name AS reporting_to_name,
                edo.employee_email AS reporting_to_email,
                d.date_updated,
                d.date_deleted,
                j.id AS jr_id,
                j.due_date_paused,
                j.next_conversation_date,
                j.excused_type,
                j.current_manual_excuse,
                j.created_by_id,
                j.created_at,
                j.updated_by_id,
                j.updated_at,
                j.excused_reason_id AS j_excused_reason_id,
                j.excused_reason_desc AS j_excused_reason_desc,
                j.updated_by_name,
                en.name AS excused_updated_by_name,
                r.name AS r_name,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 2 ELSE 1 END ELSE u.excused_reason_id END AS reason_id,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 'Classification' ELSE 'PeopleSoft Status' END when j.excused_type = 'M' THEN j.excused_reason_desc ELSE CASE when u.excused_flag = 1 THEN r.name ELSE '' END END AS reason_name,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                CASE when j.excused_type = 'A' THEN 'System' when j.excused_type = 'M' THEN CASE when j.updated_by_name <> '' THEN j.updated_by_name ELSE j.updated_by_id END ELSE CASE when u.excused_flag = 1 THEN CASE when en.name <> '' THEN en.name ELSE u.excused_updated_by END ELSE '' END END AS excused_by_name,
                CASE when j.excused_type = 'A' THEN date(j.created_at) when j.excused_type = 'M' THEN date(j.updated_at) ELSE CASE when u.excused_flag = 1 THEN u.excused_updated_at ELSE '' END END AS created_at_string,
                CASE when 1 = 1 THEN u.employee_id ELSE u.employee_id END AS employee_id_search,
                CASE when 1 = 1 THEN d.employee_name ELSE d.employee_name END AS employee_name_search
            FROM 
				(employee_demo 
					AS d 
					USE INDEX (idx_employee_demo_employeeid_orgid)
                LEFT JOIN employee_demo_jr j 
					USE INDEX (idx_employee_demo_jr_employeeid_id)
					ON j.employee_id = d.employee_id AND j.id = (SELECT MAX(j1.id) FROM employee_demo_jr AS j1 WHERE j1.employee_id = j.employee_id)
				INNER JOIN employee_demo_tree 
					AS t
					ON t.id = d.orgid
				INNER JOIN admin_orgs 
					AS o
					USE INDEX (idx_admin_orgs_orgid_version_inherited_user_id)
					ON o.orgid = d.orgid
						AND o.version = 2
						AND o.inherited = 1
						AND (
							   EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l0, admin_orgs o2 WHERE o2.user_id = o.user_id AND o2.inherited = 1 AND o2.version = 2 AND l0.id = o2.orgid AND l0.level = 0 AND l0.organization_key = t.organization_key)
							OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l1, admin_orgs o2 WHERE o2.user_id = o.user_id AND o2.inherited = 1 AND o2.version = 2 AND l1.id = o2.orgid AND l1.level = 1 AND l1.organization_key = t.organization_key AND l1.level1_key = t.level1_key)
							OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l2, admin_orgs o2 WHERE o2.user_id = o.user_id AND o2.inherited = 1 AND o2.version = 2 AND l2.id = o2.orgid AND l2.level = 2 AND l2.organization_key = t.organization_key AND l2.level1_key = t.level1_key AND l2.level2_key = t.level2_key)
							OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l3, admin_orgs o2 WHERE o2.user_id = o.user_id AND o2.inherited = 1 AND o2.version = 2 AND l3.id = o2.orgid AND l3.level = 3 AND l3.organization_key = t.organization_key AND l3.level1_key = t.level1_key AND l3.level2_key = t.level2_key AND l3.level3_key = t.level3_key)
							OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l4, admin_orgs o2 WHERE o2.user_id = o.user_id AND o2.inherited = 1 AND o2.version = 2 AND l4.id = o2.orgid AND l4.level = 4 AND l4.organization_key = t.organization_key AND l4.level1_key = t.level1_key AND l4.level2_key = t.level2_key AND l4.level3_key = t.level3_key AND l4.level4_key = t.level4_key)
						)
				) 
				INNER JOIN (
					users 
						AS u 
						USE INDEX (idx_users_employeeid_emplrecord)
					LEFT JOIN users 
						AS en 
						USE INDEX (idx_users_id)
						ON en.id = u.excused_updated_by
					LEFT JOIN excused_reasons 
						AS r 
						ON r.id = u.excused_reason_id
				) ON u.employee_id = d.employee_id
				LEFT JOIN (
                    users 
						AS urt 
						USE INDEX (idx_users_id)
					LEFT JOIN employee_demo 
						AS edo 
						USE INDEX (idx_employee_demo_employeeid_name_email) 
						ON edo.employee_id = urt.employee_id 
				) ON urt.id = u.reporting_to
            
            WHERE d.date_deleted IS NULL
            ORDER BY ao_user_id, user_id, employee_id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // \DB::statement("
        //     DROP VIEW hr_user_demo_jr_view
        // ");
    }


}
