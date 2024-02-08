<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserDemoJrView19 extends Migration
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
                d.orgid,
                ua.level,
                ua.organization,
                ua.level1_program,
                ua.level2_division,
                ua.level3_branch,
                ua.level4,
                ua.level5,
                ua.level6,
                ua.organization_key,
                ua.level1_key,
                ua.level2_key,
                ua.level3_key,
                ua.level4_key,
                ua.level5_key,
                ua.level6_key,
                ua.organization_deptid,
                ua.level1_deptid,
                ua.level2_deptid,
                ua.level3_deptid,
                ua.level4_deptid,
                ua.level5_deptid,
                ua.level6_deptid,
                d.deptid,
                d.employee_status,
                d.position_number,
                d.manager_id,
                d.supervisor_position_number,
                d.supervisor_emplid,
                d.supervisor_name,
                d.supervisor_email,
                ua.reporting_to_employee_id,
                ua.reporting_to_name,
                ua.reporting_to_email,
                ua.reporting_to_position_number,
                d.date_updated,
                d.date_deleted,
                ua.jr_id,
                ua.jr_due_date_paused AS due_date_paused,
                ua.jr_next_conversation_date AS next_conversation_date,
                ua.jr_excused_type AS excused_type,
                ua.jr_current_manual_excuse AS current_manual_excuse,
                ua.jr_created_by_id AS created_by_id,
                ua.jr_created_at AS created_at,
                ua.jr_updated_by_id AS updated_by_id,
                ua.jr_updated_at AS updated_at,
                ua.jr_excused_reason_id AS edj_excused_reason_id,
                ua.jr_excused_reason_desc AS edj_excused_reason_desc,
                ua.jr_updated_by_name AS updated_by_name,
                ua.excused_updated_by_name,
                ua.r_name,
                ua.reason_id,
                ua.reason_name,
                CASE when ua.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedtype,
                CASE when ua.jr_excused_type = 'A' THEN 'Auto' ELSE CASE when u.excused_flag = 1 THEN 'Manual' ELSE 'No' END END AS excusedlink,
                ua.excused_by_name,
                ua.created_at_string,
                u.employee_id AS employee_id_search,
                d.employee_name AS employee_name_search,
                ua.reportees
            FROM
                users AS u
                    USE INDEX (idx_users_id)
                JOIN employee_demo AS d 
                    USE INDEX (idx_employee_demo_employeeid_orgid)
                    ON d.employee_id = u.employee_id
                LEFT JOIN users_annex AS ua
                    USE INDEX (users_annex_employee_id_record_index)
                    ON (ua.employee_id = d.employee_id
                        AND ua.empl_record = d.empl_record)
            WHERE 
                d.pdp_excluded = 0
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }


}
