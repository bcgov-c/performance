<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHrUserDemoJrHistoryView9 extends Migration
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
                    USE INDEX (idx_employee_demo_employeeid_orgid)
                    ON d.employee_id = u.employee_id
                INNER JOIN users_annex AS ua
                    USE INDEX (users_annex_employee_id_record_index)
                    ON ua.employee_id = d.employee_id
                        AND ua.empl_record = d.empl_record
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
