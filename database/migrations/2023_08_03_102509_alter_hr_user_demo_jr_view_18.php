<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHrUserDemoJrView18 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \DB::statement("
            ALTER VIEW hr_user_demo_jr_view
            AS
            SELECT 
                au.auth_id,
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
                edt.level,
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
                auth_users AS au
                INNER JOIN users AS u 
                    USE INDEX (idx_users_id)
                    ON u.id = au.user_id
                INNER JOIN employee_demo AS d 
                    USE INDEX (idx_employee_demo_employeeid_orgid)
                    ON d.employee_id = u.employee_id
                INNER JOIN employee_demo_tree AS edt
					ON edt.id = d.orgid
                INNER JOIN auth_orgs AS ao
                    USE INDEX (idx_auth_orgs_type_auth_id_orgid)
                    ON ao.type = 'HR' AND ao.auth_id = au.auth_id AND ao.orgid = d.orgid
                INNER JOIN users_annex AS ua 
                    ON ua.user_id = u.id
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
