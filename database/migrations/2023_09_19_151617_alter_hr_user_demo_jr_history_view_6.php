<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHrUserDemoJrHistoryView6
 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            ALTER VIEW hr_user_demo_jr_history_view
            AS
            SELECT DISTINCT
                au.auth_id,
                u.id AS user_id,
                u.name as user_name,
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
                j.id as jr_id,
                j.due_date_paused,
                j.next_conversation_date,
                j.excused_type AS j_excused_type,
                j.current_manual_excuse,
                j.created_by_id,
                j.created_at AS j_created_at,
                j.updated_by_id AS j_updated_by_id,
                j.updated_at AS j_updated_at,
                j.excused_reason_id as j_excused_reason_id,
                j.excused_reason_desc as j_excused_reason_desc,
                jn.name as j_updated_by_name,
                ua.excused_updated_by_name,
                k.created_at AS k_created_at,
                k.excused_type AS k_excused_type,
				ua.r_name,
				ua.reason_id,
				ua.reason_name,
				ua.excusedtype,
				ua.excusedlink,
				ua.excused_by_name,
				ua.created_at_string,
				u.employee_id AS employee_id_search,
				d.employee_name AS employee_name_search
            FROM
                auth_users AS au
                INNER JOIN users AS u 
                    ON u.id = au.user_id
                INNER JOIN employee_demo AS d 
                    USE INDEX (idx_employee_demo_employeeid_record)
                    ON d.employee_id = u.employee_id
                INNER JOIN employee_demo_tree AS edt
					ON edt.id = d.orgid
                INNER JOIN users_annex AS ua 
                    USE INDEX (idx_users_annex_userid_orgid)
                    ON ua.user_id = u.id
                        AND ua.orgid = d.orgid
                INNER JOIN employee_demo_jr AS j 
                    ON j.employee_id = u.employee_id
                        AND NOT j.excused_type IS NULL
                        AND j.id IN (SELECT x.id FROM employee_demo_jr x WHERE x.employee_id = u.employee_id AND NOT x.excused_type IS NULL)
                        AND NOT EXISTS (SELECT 1 FROM employee_demo_jr y WHERE y.employee_id = u.employee_id AND NOT y.excused_type IS NULL AND y.id = (SELECT MAX(y1.id) FROM employee_demo_jr y1 WHERE y1.employee_id = u.employee_id AND y1.id < j.id))
                LEFT JOIN users AS jn 
                    ON jn.id = j.updated_by_id
                INNER JOIN employee_demo_jr AS k 
                    ON k.employee_id = u.employee_id
                        AND k.excused_type IS NULL
                        AND k.id = (SELECT MIN(m.id) FROM employee_demo_jr m WHERE m.employee_id = k.employee_id AND m.id > j.id AND m.excused_type IS NULL)
                        AND k.id > j.id
                        AND NOT EXISTS (SELECT 1 FROM employee_demo_jr x WHERE x.employee_id = j.employee_id AND x.id > j.id AND x.id < k.id AND x.excused_type IS NULL)
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
