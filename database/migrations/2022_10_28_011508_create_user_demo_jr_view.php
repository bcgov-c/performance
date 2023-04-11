<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserDemoJrView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('DROP VIEW IF EXISTS user_demo_jr_view;');

        \DB::statement("
            CREATE VIEW user_demo_jr_view
            AS
            SELECT
                u.id as user_id,
                u.name as user_name,
                u.employee_id,
                u.guid,
                u.excused_flag,
                u.excused_reason_id,
                u.excused_updated_by,
                u.excused_updated_at,
                u.joining_date,
                u.reporting_to,
                u.acctlock,
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
                d.date_updated,
                d.date_deleted,
                j.id as jr_id,
                j.due_date_paused,
                j.next_conversation_date,
                j.excused_type,
                j.current_manual_excuse,
                j.created_by_id,
                j.created_at,
                j.updated_by_id,
                j.updated_at,
                jn.name as updated_by_name,
                en.name as excused_updated_by_name,
                case when j.excused_type = 'A' then case when j.current_employee_status = 'A' then 2 else 1 end else u.excused_reason_id end as reason_id,
                case when j.excused_type = 'A' then case when j.current_employee_status = 'A' then 'Classification' else 'PeopleSoft Status' end when j.excused_type = 'M' then r.name else case when u.excused_flag = 1 then r.name else '' end end as reason_name,
                case when j.excused_type = 'A' then 'Auto' when j.excused_type = 'M' then 'Manual' else case when u.excused_flag = 1 then 'Manual' else 'No' end end as excusedtype,
                case when j.excused_type = 'A' then 'Auto' when j.excused_type = 'M' then 'Manual' else case when u.excused_flag = 1 then 'Manual' else 'No' end end as excusedlink,
                case when j.excused_type = 'A' then 'System' when j.excused_type = 'M' then case when jn.name <> '' then jn.name else j.updated_by_id end else case when u.excused_flag = 1 then case when en.name <> '' then en.name else u.excused_updated_by end else '' end end as excused_by_name,
                case when (j.excused_type = 'A' or j.current_manual_excuse = 'Y') then date(j.created_at) else '' end as created_at_string,
                case when 1 = 1 then u.employee_id else u.employee_id end as employee_id_search,
                case when 1 = 1 then d.employee_name else d.employee_name end as employee_name_search
            FROM
                users as u
                LEFT JOIN employee_demo as d ON d.guid = u.guid 
                LEFT JOIN employee_demo_jr as j ON j.guid = u.guid
                LEFT JOIN users as jn ON jn.id = j.updated_by_id
                LEFT JOIN users as en ON en.id = u.excused_updated_by
                LEFT JOIN excused_reasons as r ON r.id = u.excused_reason_id
            WHERE 
                NOT u.guid is null
                AND TRIM(u.guid) <> ''
                AND j.id = (select max(j1.id) from employee_demo_jr as j1 where j1.guid = j.guid)
            ORDER BY u.employee_id
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("
            DROP VIEW user_demo_jr_view
        ");
    }


}
