<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserDemoJrHistoryView7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            ALTER VIEW user_demo_jr_history_view
            AS
            SELECT DISTINCT
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
                ua.reporting_to_position_number,
                d.date_updated,
                d.date_deleted,
				ua.jr_id,
				ua.jr_due_date_paused AS due_date_paused,
				ua.jr_next_conversation_date AS next_conversation_date,
				ua.jr_excused_type AS j_excused_type,
				ua.jr_current_manual_excuse AS current_manual_excuse,
				ua.jr_created_by_id AS created_by_id,
				ua.jr_created_at AS j_created_at,
				ua.jr_updated_by_id AS j_updated_by_id,
				ua.jr_updated_at AS j_updated_at,
				ua.jr_excused_reason_id AS j_excused_reason_id,
				ua.jr_excused_reason_desc AS j_excused_reason_desc,
				ua.jr_updated_by_name AS j_updated_by_name,
				ua.excused_updated_by_name as j_excused_updated_by_name,
                k.created_at AS k_created_at,
                k.excused_type AS k_excused_type,
				ua.r_name,
				ua.reason_id,
				ua.reason_name,
				ua.excusedtype as j_excusedtype,
				ua.excusedlink as j_excusedlink,
				ua.excused_by_name,
				ua.created_at_string,
                u.employee_id as employee_id_search,
                d.employee_name as employee_name_search
            FROM
                users AS u 
                    USE INDEX (idx_users_employeeid_emplrecord)
                JOIN employee_demo AS d 
                    USE INDEX (idx_employee_demo_employeeid_orgid) 
                    ON d.employee_id = u.employee_id 
                INNER JOIN employee_demo_tree AS edt
					ON edt.id = d.orgid
				INNER JOIN users_annex AS ua 
					USE INDEX (idx_users_annex_userid_orgid)
					ON ua.user_id = u.id
                        AND ua.orgid = d.orgid
                JOIN employee_demo_jr AS j 
                    USE INDEX (idx_employee_demo_jr_employeeid_id) 
                    ON j.employee_id = u.employee_id
                JOIN employee_demo_jr AS k 
                    USE INDEX (idx_employee_demo_jr_employeeid_id) 
                    ON k.employee_id = u.employee_id
            WHERE 
                NOT j.excused_type IS NULL
                AND k.excused_type IS NULL
                AND j.id < k.id
                AND k.id = (SELECT MIN(m.id) FROM employee_demo_jr AS m USE INDEX (idx_employee_demo_jr_employeeid_id) WHERE m.employee_id = k.employee_id AND m.id > j.id AND m.excused_type IS NULL)
                AND j.id IN (SELECT x.id FROM employee_demo_jr AS x USE INDEX (idx_employee_demo_jr_employeeid_id) WHERE x.employee_id = u.employee_id AND NOT x.excused_type IS NULL)
                AND NOT EXISTS (
                    SELECT 1 
                    FROM employee_demo_jr 
                        AS x               
                        USE INDEX (idx_employee_demo_jr_employeeid_id)
                    WHERE x.employee_id = j.employee_id 
                        AND x.id > j.id AND x.id < k.id 
                        AND x.excused_type IS NULL
                )
                AND NOT EXISTS (
                    SELECT 1 
                    FROM employee_demo_jr 
                        AS y 
                        USE INDEX (idx_employee_demo_jr_employeeid_id)
                    WHERE y.employee_id = u.employee_id 
                        AND NOT y.excused_type IS NULL 
                        AND y.id = (SELECT MAX(y1.id) FROM employee_demo_jr AS y1 USE INDEX (idx_employee_demo_jr_employeeid_id) WHERE y1.employee_id = u.employee_id AND y1.id < j.id))
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
        //     DROP VIEW user_demo_jr_history_view
        // ");
    }


}
