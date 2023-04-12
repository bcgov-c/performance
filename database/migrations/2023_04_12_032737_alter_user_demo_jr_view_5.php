<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserDemoJrView5 extends Migration
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
                edj.id AS jr_id,
                edj.due_date_paused,
                edj.next_conversation_date,
                edj.excused_type,
                edj.current_manual_excuse,
                edj.created_by_id,
                edj.created_at,
                edj.updated_by_id,
                edj.updated_at,
                edj.excused_reason_id AS edj_excused_reason_id,
                edj.excused_reason_desc AS edj_excused_reason_desc,
                edj.updated_by_name,
                en.name AS excused_updated_by_name,
                r.name AS r_name,
                CASE when edj.excused_type = 'A' THEN CASE when edj.current_employee_status = 'A' THEN 2 ELSE 1 END ELSE u.excused_reason_id END AS reason_id,
                CASE when edj.excused_type = 'A' THEN CASE when edj.current_employee_status = 'A' THEN 'Classification' ELSE 'PeopleSoft Status' END when edj.excused_type = 'M' THEN edj.excused_reason_desc ELSE CASE when u.excused_flag = 1 THEN r.name ELSE '' END END AS reason_name,
                CASE when edj.excused_type = 'A' THEN 'Auto' when edj.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                CASE when edj.excused_type = 'A' THEN 'Auto' when edj.excused_type = 'M' THEN 'Manual' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                CASE when edj.excused_type = 'A' THEN 'System' when edj.excused_type = 'M' THEN CASE when edj.updated_by_name <> '' THEN edj.updated_by_name ELSE edj.updated_by_id END ELSE CASE when u.excused_flag = 1 THEN CASE when en.name <> '' THEN en.name ELSE u.excused_updated_by END ELSE '' END END AS excused_by_name,
                CASE when edj.excused_type = 'A' THEN date(edj.created_at) when edj.excused_type = 'M' THEN date(edj.updated_at) ELSE CASE when u.excused_flag = 1 THEN u.excused_updated_at ELSE '' END END AS created_at_string,
                u.employee_id AS employee_id_search,
                d.employee_name AS employee_name_search
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
                LEFT JOIN excused_reasons AS r 
                    ON r.id = u.excused_reason_id
                LEFT JOIN users AS en 
                    USE INDEX (idx_users_id)
                    ON en.id = u.excused_updated_by) ON u.employee_id = d.employee_id
                LEFT JOIN (users AS urt 
                    USE INDEX (idx_users_id)
                LEFT JOIN employee_demo AS edo 
                    USE INDEX (idx_employee_demo_employeeid_name_email) 
                    ON edo.employee_id = urt.employee_id) ON urt.id = u.reporting_to
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
