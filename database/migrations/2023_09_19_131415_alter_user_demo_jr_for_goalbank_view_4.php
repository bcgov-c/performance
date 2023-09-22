<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserDemoJrForGoalbankView4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            ALTER VIEW user_demo_jr_for_goalbank_view
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
                ua.orgid,
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
                emv.supervisor_emplid AS manager_id,
                emv.supervisor_position_number,
                emv.supervisor_emplid,
                emv.supervisor_name,
                emv.supervisor_email,
                emv.supervisor_emplid AS reporting_to_employee_id,
                emv.supervisor_name AS reporting_to_name,
                emv.supervisor_email AS reporting_to_email,
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
                ua.excusedtype,
                ua.excusedlink,
                ua.excused_by_name,
                ua.created_at_string,
                u.employee_id AS employee_id_search,
                d.employee_name AS employee_name_search,
                ua.isSupervisor,
                ua.isDelegate
            FROM
                users AS u
                    USE INDEX (idx_users_employeeid_emplrecord)
                INNER JOIN employee_demo AS d
                    USE INDEX (idx_employee_demo_employeeid_record)
                    ON d.employee_id = u.employee_id
                INNER JOIN employee_demo_tree AS edt
					ON edt.id = d.orgid
                INNER JOIN users_annex AS ua
                    ON ua.user_id = u.id
                        AND ua.orgid = d.orgid
                LEFT JOIN employee_managers_view emv
                    ON emv.employee_id = d.employee_id AND emv.position_number = d.position_number 
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
