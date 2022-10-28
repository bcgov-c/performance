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
                jn.name as updated_by_name
            FROM
                users as u
                LEFT JOIN employee_demo as d ON d.guid = u.guid 
                LEFT JOIN employee_demo_jr as j ON j.guid = u.guid
                LEFT JOIN users as jn ON jn.id = j.updated_by_id
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
