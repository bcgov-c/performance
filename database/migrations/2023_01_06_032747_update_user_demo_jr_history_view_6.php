<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserDemoJrHistoryView6 extends Migration
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
                d.organization,
                d.level1_program,
                d.level2_division,
                d.level3_branch,
                d.level4,
                d.deptid,
                d.employee_status,
                d.position_number,
                d.manager_id,
                d.supervisor_position_number,
                d.supervisor_emplid,
                d.supervisor_name,
                d.supervisor_email,
                urt.employee_id AS reporting_to_employee_id,
                urt.name AS reporting_to_name,
                urt.email AS reporting_to_email,
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
                en.name as j_excused_updated_by_name,
                k.created_at AS k_created_at,
                k.excused_type AS k_excused_type,
                r.name as r_name,
                case when j.excused_type = 'A' then case when j.current_employee_status = 'A' then 2 else 1 end else u.excused_reason_id end as reason_id,
                case when j.excused_type = 'A' then case when j.current_employee_status = 'A' then 'Classification' else 'PeopleSoft Status' end when j.excused_type = 'M' then j.excused_reason_desc else case when u.excused_flag = 1 then r.name else '' end end as reason_name,
                case when j.excused_type = 'A' then 'Auto' when j.excused_type = 'M' then 'Manual' else case when u.excused_flag = 1 then 'Manual' else 'No' end end as j_excusedtype,
                case when j.excused_type = 'A' then 'Auto' when j.excused_type = 'M' then 'Manual' else case when u.excused_flag = 1 then 'Manual' else 'No' end end as j_excusedlink,
                case when j.excused_type = 'A' then 'System' when j.excused_type = 'M' then case when en.name <> '' then en.name else u.excused_updated_by end else '' end as excused_by_name,
                case when j.excused_type = 'A' then date(j.created_at) when j.excused_type = 'M' then date(j.updated_at) else case when u.excused_flag = 1 then u.excused_updated_at else '' end end as created_at_string,
                case when 1 = 1 then u.employee_id else u.employee_id end as employee_id_search,
                case when 1 = 1 then d.employee_name else d.employee_name end as employee_name_search
            FROM
                users AS u
                JOIN employee_demo AS d ON d.guid = u.guid 
                JOIN employee_demo_jr AS j ON j.guid = u.guid
                JOIN employee_demo_jr AS k ON k.guid = u.guid
                LEFT JOIN users AS jn ON jn.id = j.updated_by_id
                LEFT JOIN users AS en ON en.id = u.excused_updated_by
                LEFT JOIN excused_reasons AS r ON r.id = u.excused_reason_id
                LEFT JOIN users AS urt ON urt.id = u.reporting_to
            WHERE 
                NOT u.guid IS NULL
                AND TRIM(u.guid) <> ''
                AND NOT d.guid IS NULL
                AND TRIM(d.guid) <> ''
                AND NOT j.guid IS NULL
                AND TRIM(j.guid) <> ''
                AND NOT k.guid IS NULL
                AND TRIM(k.guid) <> ''
                AND NOT j.excused_type IS NULL
                AND k.excused_type IS NULL
                AND j.id < k.id
                AND k.id = (SELECT MIN(m.id) FROM employee_demo_jr m WHERE m.guid = k.guid AND m.id > j.id AND m.excused_type IS NULL)
                AND j.id IN (SELECT x.id FROM employee_demo_jr x WHERE x.guid = u.guid AND NOT x.excused_type IS NULL)
                AND NOT EXISTS (SELECT 1 FROM employee_demo_jr x WHERE x.guid = j.guid AND x.id > j.id AND x.id < k.id AND x.excused_type IS NULL)
                AND NOT EXISTS (SELECT 1 FROM employee_demo_jr y WHERE y.guid = u.guid AND NOT y.excused_type IS NULL AND y.id = (SELECT MAX(y1.id) FROM employee_demo_jr y1 WHERE y1.guid = u.guid AND y1.id < j.id))
            ORDER BY u.employee_id, j.id
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
