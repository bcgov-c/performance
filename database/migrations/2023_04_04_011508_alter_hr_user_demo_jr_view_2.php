<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHrUserDemoJrView2 extends Migration
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
                jn.name AS updated_by_name,
                en.name AS excused_updated_by_name,
                r.name AS r_name,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 2 ELSE 1 END ELSE u.excused_reason_id END AS reason_id,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 'Classification' ELSE 'PeopleSoft Status' END when j.excused_type = 'M' THEN j.excused_reason_desc ELSE CASE when u.excused_flag = 1 THEN r.name ELSE '' END END AS reason_name,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                CASE when j.excused_type = 'A' THEN 'System' when j.excused_type = 'M' THEN CASE when jn.name <> '' THEN jn.name ELSE j.updated_by_id END ELSE CASE when u.excused_flag = 1 THEN CASE when en.name <> '' THEN en.name ELSE u.excused_updated_by END ELSE '' END END AS excused_by_name,
                CASE when j.excused_type = 'A' THEN date(j.created_at) when j.excused_type = 'M' THEN date(j.updated_at) ELSE CASE when u.excused_flag = 1 THEN u.excused_updated_at ELSE '' END END AS created_at_string,
                CASE when 1 = 1 THEN u.employee_id ELSE u.employee_id END AS employee_id_search,
                CASE when 1 = 1 THEN d.employee_name ELSE d.employee_name END AS employee_name_search
            FROM employee_demo d, employee_demo_tree t, admin_orgs o, users u
                LEFT JOIN employee_demo_jr j ON j.employee_id = u.employee_id AND j.id = (SELECT MAX(j1.id) FROM employee_demo_jr AS j1 WHERE j1.employee_id = j.employee_id)
                LEFT JOIN users jn ON jn.id = j.updated_by_id
                LEFT JOIN users AS en ON en.id = u.excused_updated_by
                LEFT JOIN excused_reasons AS r ON r.id = u.excused_reason_id
                LEFT JOIN users AS urt ON urt.id = u.reporting_to
                LEFT JOIN employee_demo AS edo ON (
                    urt.employee_id = edo.employee_id 
                    AND edo.empl_record = (SELECT MIN(edo2.empl_record) FROM employee_demo AS edo2 WHERE edo2.employee_id = edo.employee_id AND edo2.employee_status = edo.employee_status)
                    AND edo.employee_status = (SELECT MIN(edo1.employee_status) FROM employee_demo AS edo1 WHERE edo1.employee_id = edo.employee_id)
                )
            WHERE d.deptid = t.deptid
                AND d.date_deleted IS NULL
                AND d.employee_id = u.employee_id
                AND o.version = 2
                AND o.inherited = 0
                AND t.id = o.orgid
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
                jn.name AS updated_by_name,
                en.name AS excused_updated_by_name,
                r.name AS r_name,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 2 ELSE 1 END ELSE u.excused_reason_id END AS reason_id,
                CASE when j.excused_type = 'A' THEN CASE when j.current_employee_status = 'A' THEN 'Classification' ELSE 'PeopleSoft Status' END when j.excused_type = 'M' THEN j.excused_reason_desc ELSE CASE when u.excused_flag = 1 THEN r.name ELSE '' END END AS reason_name,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                CASE when j.excused_type = 'A' THEN 'Auto' when j.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                CASE when j.excused_type = 'A' THEN 'System' when j.excused_type = 'M' THEN CASE when jn.name <> '' THEN jn.name ELSE j.updated_by_id END ELSE CASE when u.excused_flag = 1 THEN CASE when en.name <> '' THEN en.name ELSE u.excused_updated_by END ELSE '' END END AS excused_by_name,
                CASE when j.excused_type = 'A' THEN date(j.created_at) when j.excused_type = 'M' THEN date(j.updated_at) ELSE CASE when u.excused_flag = 1 THEN u.excused_updated_at ELSE '' END END AS created_at_string,
                CASE when 1 = 1 THEN u.employee_id ELSE u.employee_id END AS employee_id_search,
                CASE when 1 = 1 THEN d.employee_name ELSE d.employee_name END AS employee_name_search
            FROM employee_demo d, employee_demo_tree t, admin_orgs o, users u
                LEFT JOIN employee_demo_jr j ON j.employee_id = u.employee_id AND j.id = (SELECT MAX(j1.id) FROM employee_demo_jr AS j1 WHERE j1.employee_id = j.employee_id)
                LEFT JOIN users jn ON jn.id = j.updated_by_id
                LEFT JOIN users AS en ON en.id = u.excused_updated_by
                LEFT JOIN excused_reasons AS r ON r.id = u.excused_reason_id
                LEFT JOIN users AS urt ON urt.id = u.reporting_to
                LEFT JOIN employee_demo AS edo ON (
                    urt.employee_id = edo.employee_id 
                    AND edo.empl_record = (SELECT MIN(edo2.empl_record) FROM employee_demo AS edo2 WHERE edo2.employee_id = edo.employee_id AND edo2.employee_status = edo.employee_status)
                    AND edo.employee_status = (SELECT MIN(edo1.employee_status) FROM employee_demo AS edo1 WHERE edo1.employee_id = edo.employee_id)
                )
            WHERE d.deptid = t.deptid
                AND d.date_deleted IS NULL
                AND d.employee_id = u.employee_id
                AND o.version = 2
                AND o.inherited = 1
                AND (
                    EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l0 WHERE l0.id = o.orgid AND l0.level = 0 AND l0.organization_key = t.organization_key)
                    OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l1 WHERE l1.id = o.orgid AND l1.level = 1 AND l1.organization_key = t.organization_key AND l1.level1_key = t.level1_key)
                    OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l2 WHERE l2.id = o.orgid AND l2.level = 2 AND l2.organization_key = t.organization_key AND l2.level1_key = t.level1_key AND l2.level2_key = t.level2_key)
                    OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l3 WHERE l3.id = o.orgid AND l3.level = 3 AND l3.organization_key = t.organization_key AND l3.level1_key = t.level1_key AND l3.level2_key = t.level2_key AND l3.level3_key = t.level3_key)
                    OR EXISTS (SELECT DISTINCT 1 FROM employee_demo_tree l4 WHERE l4.id = o.orgid AND l4.level = 4 AND l4.organization_key = t.organization_key AND l4.level1_key = t.level1_key AND l4.level2_key = t.level2_key AND l4.level3_key = t.level3_key AND l4.level4_key = t.level4_key)
                )
            ORDER BY user_id, employee_id
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
