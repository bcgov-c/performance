<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserDemoJrView2 extends Migration
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
                main.employee_id,
                main.deptid,
                main.employee_name,
                main.empl_record,
                main.employee_email,
                main.jobcode,
                main.jobcode_desc,
                main.job_indicator,
                main.employee_status,
                main.position_number,
                main.manager_id,
                main.supervisor_position_number,
                main.supervisor_emplid,
                main.supervisor_name,
                main.supervisor_email,
                main.date_updated,
                main.date_deleted,
                main.employee_name_search,
                main.orgid,
                main.level,
                main.organization_key,
                main.level1_key,
                main.level2_key,
                main.level3_key,
                main.level4_key,
                main.level5_key,
                main.organization,
                main.level1_program,
                main.level2_division,
                main.level3_branch,
                main.level4,
                main.level5,
                main.organization_deptid,
                main.level1_deptid,
                main.level2_deptid,
                main.level3_deptid,
                main.level4_deptid,
                main.level5_deptid,
                det.due_date_paused,
                det.next_conversation_date,
                det.excused_type,
                det.current_manual_excuse,
                det.current_employee_status,
                det.created_by_id,
                det.created_at,
                det.updated_by_id,
                det.updated_at,
                det.j_excused_reason_id,
                det.excused_reason_desc,
                det.updated_by_name,
                rel.excused_updated_by,
                rel.excused_reason_id,
                rel.reporting_to,
                rel.guid,
                rel.excused_flag,
                rel.excused_updated_at,
                rel.user_id,
                rel.user_name,
                rel.joining_date,
                rel.acctlock,
                rel.employee_id_search,
                rel.excused_updated_by_name,
                rel.r_name,
                rel.reporting_to_employee_id,
                rel.edo_employee_id,
                rel.edo_employee_status,
                rel.edo_empl_record,
                rel.reporting_to_name,
                rel.reporting_to_email,
                CASE WHEN det.excused_type = 'A' THEN CASE WHEN det.current_employee_status = 'A' THEN 2 ELSE 1 END ELSE rel.excused_reason_id END AS reason_id,
                CASE WHEN det.excused_type = 'A' THEN CASE WHEN det.current_employee_status = 'A' THEN 'Classification' ELSE 'PeopleSoft Status' END WHEN det.excused_type = 'M' THEN det.excused_reason_desc ELSE CASE WHEN rel.excused_flag = 1 THEN rel.r_name ELSE '' END END AS reason_name,
                CASE WHEN det.excused_type = 'A' THEN 'Auto' WHEN det.excused_type = 'M' THEN 'Manual' ELSE CASE WHEN rel.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                CASE WHEN det.excused_type = 'A' THEN 'Auto' WHEN det.excused_type = 'M' THEN 'Manual' ELSE CASE WHEN rel.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                CASE WHEN det.excused_type = 'A' THEN 'System' WHEN det.excused_type = 'M' THEN CASE WHEN det.updated_by_name <> '' THEN det.updated_by_name ELSE det.updated_by_id END ELSE CASE WHEN rel.excused_flag = 1 THEN CASE WHEN rel.excused_updated_by_name <> '' THEN rel.excused_updated_by_name ELSE rel.excused_updated_by END ELSE '' END END AS excused_by_name,
                CASE WHEN det.excused_type = 'A' THEN DATE(det.created_at) WHEN det.excused_type = 'M' THEN DATE(det.updated_at) ELSE CASE WHEN rel.excused_flag = 1 THEN rel.excused_updated_at ELSE '' END END AS created_at_string
            FROM 
                (SELECT
                    d.employee_id,
                    d.deptid,
                    d.employee_name,
                    d.empl_record,
                    d.employee_email,
                    d.jobcode,
                    d.jobcode_desc,
                    d.job_indicator,
                    d.employee_status,
                    d.position_number,
                    d.manager_id,
                    d.supervisor_position_number,
                    d.supervisor_emplid,
                    d.supervisor_name,
                    d.supervisor_email,
                    d.date_updated,
                    d.date_deleted,
                    d.employee_name AS employee_name_search,
                    edt.id AS orgid,
                    edt.level,
                    edt.organization_key,
                    edt.level1_key,
                    edt.level2_key,
                    edt.level3_key,
                    edt.level4_key,
                    edt.level5_key,
                    edt.organization,
                    edt.level1_program,
                    edt.level2_division,
                    edt.level3_branch,
                    edt.level4,
                    edt.level5,
                    edt.organization_deptid,
                    edt.level1_deptid,
                    edt.level2_deptid,
                    edt.level3_deptid,
                    edt.level4_deptid,
                    edt.level5_deptid
                FROM
                    employee_demo_tree AS edt USE INDEX (idx_edt_deptid) ,
                    employee_demo AS d USE INDEX (idx_employee_demo_deptid, PRIMARY) 
                WHERE
                    edt.deptid = d.deptid
                ) AS main,
                (select 
                    j.employee_id,
                    j.id AS jr_id,
                    j.due_date_paused,
                    j.next_conversation_date,
                    j.excused_type,
                    j.current_manual_excuse,
                    j.current_employee_status,
                    j.created_by_id,
                    j.created_at,
                    j.updated_by_id,
                    j.updated_at,
                    j.excused_reason_id AS j_excused_reason_id,
                    j.excused_reason_desc,
                    j.updated_by_name
                FROM
                    employee_demo_jr AS j
                WHERE 
                    j.id = (SELECT j1.id FROM employee_demo_jr AS j1 WHERE j1.employee_id = j.employee_id ORDER BY j1.id DESC LIMIT 1)
                ) AS det,
                (SELECT
                    u.employee_id AS employee_id,
                    u.excused_updated_by,
                    u.excused_reason_id,
                    u.reporting_to,
                    u.guid AS guid,
                    u.excused_flag,
                    u.excused_updated_at,
                    u.id AS user_id,
                    u.name AS user_name,
                    u.joining_date,
                    u.acctlock,
                    u.employee_id AS employee_id_search,
                    en.name AS excused_updated_by_name,
                    r.name AS r_name,
                    urt.employee_id AS reporting_to_employee_id,
                    edo.employee_id AS edo_employee_id,
                    edo.employee_status AS edo_employee_status,
                    edo.empl_record AS edo_empl_record,
                    edo.employee_name AS reporting_to_name,
                    edo.employee_email AS reporting_to_email
                FROM 
                    users AS u USE INDEX (PRIMARY)
                    LEFT JOIN excused_reasons AS r ON r.id = u.excused_reason_id
                    LEFT JOIN users AS en ON en.id = u.excused_updated_by
                    LEFT JOIN users AS urt ON urt.id = u.reporting_to
                    LEFT JOIN employee_demo AS edo ON edo.employee_id = urt.employee_id
                ) AS rel
            WHERE
                main.employee_id = det.employee_id
                AND 
                main.employee_id = rel.employee_id
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
