<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterHrUserDemoJrView7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement('DROP VIEW IF EXISTS hr_user_demo_jr_view');
        
        \DB::statement("
            CREATE VIEW hr_user_demo_jr_view
            AS
            SELECT 
                o.user_id AS ao_user_id,
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
                ua.level,
                ua.organization,
                ua.level1_program,
                ua.level2_division,
                ua.level3_branch,
                ua.level4,
                ua.level5,
                ua.organization_key,
                ua.level1_key,
                ua.level2_key,
                ua.level3_key,
                ua.level4_key,
                ua.level5_key,
                ua.organization_deptid,
                ua.level1_deptid,
                ua.level2_deptid,
                ua.level3_deptid,
                ua.level4_deptid,
                ua.level5_deptid,
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
				ua.excusedtype,
				ua.excusedlink,
				ua.excused_by_name,
				ua.created_at_string,
				u.employee_id AS employee_id_search,
				d.employee_name AS employee_name_search
            FROM 
				employee_demo 
					AS d 
					USE INDEX (idx_employee_demo_employeeid_orgid)
				INNER JOIN users 
					AS u 
					USE INDEX (idx_users_employeeid_emplrecord)
					ON u.employee_id = d.employee_id
				INNER JOIN users_annex 
					AS ua 
					USE INDEX (idx_users_annex_userid_orgid)
                    ON ua.user_id = u.id AND ua.orgid = d.orgid
				INNER JOIN admin_orgs 
					AS o
					USE INDEX (idx_admin_orgs_orgid_version_inherited_user_id)
					ON o.orgid = d.orgid
						AND o.version = 2
						AND o.inherited = 0
            WHERE d.date_deleted IS NULL
            UNION
            SELECT 
                o.user_id AS ao_user_id,
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
                ua.level,
                ua.organization,
                ua.level1_program,
                ua.level2_division,
                ua.level3_branch,
                ua.level4,
                ua.level5,
                ua.organization_key,
                ua.level1_key,
                ua.level2_key,
                ua.level3_key,
                ua.level4_key,
                ua.level5_key,
                ua.organization_deptid,
                ua.level1_deptid,
                ua.level2_deptid,
                ua.level3_deptid,
                ua.level4_deptid,
                ua.level5_deptid,
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
				ua.excusedtype,
				ua.excusedlink,
				ua.excused_by_name,
				ua.created_at_string,
				u.employee_id AS employee_id_search,
				d.employee_name AS employee_name_search
            FROM 
				employee_demo 
					AS d 
					USE INDEX (idx_employee_demo_employeeid_orgid)
				INNER JOIN users 
					AS u 
					USE INDEX (idx_users_employeeid_emplrecord)
                    ON u.employee_id = d.employee_id
				INNER JOIN users_annex 
					AS ua 
					USE INDEX (idx_users_annex_userid_orgid)
                    ON ua.user_id = u.id AND ua.orgid = d.orgid
                INNER JOIN admin_orgs 
					AS o
					USE INDEX (idx_admin_orgs_orgid_version_inherited_user_id)
					ON o.version = 2
						AND o.inherited = 1
				INNER JOIN employee_demo_tree 
					AS edt      
                    ON edt.id = o.orgid
						AND (
							   (edt.level = 0 AND edt.organization_key = ua.organization_key)
							OR (edt.level = 1 AND edt.organization_key = ua.organization_key AND edt.level1_key = ua.level1_key)
							OR (edt.level = 2 AND edt.organization_key = ua.organization_key AND edt.level1_key = ua.level1_key AND edt.level2_key = ua.level2_key)
							OR (edt.level = 3 AND edt.organization_key = ua.organization_key AND edt.level1_key = ua.level1_key AND edt.level2_key = ua.level2_key AND edt.level3_key = ua.level3_key)
							OR (edt.level = 4 AND edt.organization_key = ua.organization_key AND edt.level1_key = ua.level1_key AND edt.level2_key = ua.level2_key AND edt.level3_key = ua.level3_key AND edt.level4_key = ua.level4_key)
							OR (edt.level = 5 AND edt.organization_key = ua.organization_key AND edt.level1_key = ua.level1_key AND edt.level2_key = ua.level2_key AND edt.level3_key = ua.level3_key AND edt.level4_key = ua.level4_key AND edt.level5_key = ua.level5_key)
						)
            WHERE 
                d.date_deleted IS NULL
            ORDER BY 
                ao_user_id, 
                user_id, 
                employee_id
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
        //     DROP VIEW hr_user_demo_jr_view
        // ");
    }


}
