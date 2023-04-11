<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserDemoJrView4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            ALTER VIEW user_demo_jr_view
            AS
            SELECT 
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
                edt.id AS orgid,
                edt.level,
                edt.organization,
                edt.level1_program,
                edt.level2_division,
                edt.level3_branch,
                edt.level4,
                edt.level5,
                edt.organization_key,
                edt.level1_key,
                edt.level2_key,
                edt.level3_key,
                edt.level4_key,
                edt.level5_key,
                edt.organization_deptid,
                edt.level1_deptid,
                edt.level2_deptid,
                edt.level3_deptid,
                edt.level4_deptid,
                edt.level5_deptid,
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
                users AS u 
                JOIN employee_demo AS d USE INDEX (idx_employee_demo_employeeid_record) ON u.employee_id = d.employee_id
                LEFT JOIN employee_demo_tree AS edt ON edt.id = d.orgid
                LEFT JOIN employee_demo_jr AS j ON j.employee_id = d.employee_id AND j.id = (SELECT j1.id FROM employee_demo_jr AS j1 WHERE j1.employee_id = j.employee_id ORDER BY j1.id DESC LIMIT 1)
                LEFT JOIN excused_reasons AS r ON r.id = u.excused_reason_id
                LEFT JOIN users AS en ON en.id = u.excused_updated_by
                LEFT JOIN users AS urt ON urt.id = u.reporting_to
                LEFT JOIN employee_demo AS edo USE INDEX (idx_employee_demo_employeeid_name_email) ON edo.employee_id = urt.employee_id
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
        //     DROP VIEW user_demo_jr_view
        // ");
    }


}
