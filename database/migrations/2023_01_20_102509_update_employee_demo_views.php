<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEmployeeDemoViews extends Migration
{
    public static $employeedemo_sql = "
        SELECT 
            ed.guid
            , ed.employee_id
            , ed.empl_record
            , ed.effdt
            , ed.effseq
            , ed.employee_name
            , ed.employee_first_name
            , ed.employee_middle_name
            , ed.employee_last_name
            , ed.employee_status
            , ed.employee_status_long
            , ed.employee_email
            , ed.classification
            , ed.classification_group
            , ed.empl_class
            , ed.empl_ctg
            , ed.job_indicator
            , ed.job_title
            , ed.jobcode
            , ed.jobcode_desc
            , ed.jobcodedescgroup
            , ed.hire_dt
            , ed.position_number
            , ed.position_title
            , ed.position_start_date
            , ed.appointment_status
            , ed.business_unit
            , ed.deptid
            , ed.organization
            , ed.level1_program
            , ed.level2_division
            , ed.level3_branch
            , ed.level4
            , ed.supervisor_position_number
            , ed.supervisor_position_title
            , ed.supervisor_position_start_date
            , ed.supervisor_emplid
            , ed.supervisor_name
            , ed.supervisor_email
            , ed.manager_id
            , ed.manager_first_name
            , ed.manager_last_name
            , ed.date_created
            , ed.date_updated
            , ed.date_deleted
        FROM 
            employee_demo AS ed   
        WHERE
            1 = 1
    ";

    public static $users_employeedemo_sql = "
        SELECT 
            ed.guid
            , ed.employee_id
            , ed.empl_record
            , ed.effdt
            , ed.effseq
            , ed.employee_name
            , ed.employee_first_name
            , ed.employee_middle_name
            , ed.employee_last_name
            , ed.employee_status
            , ed.employee_status_long
            , ed.employee_email
            , ed.classification
            , ed.classification_group
            , ed.empl_class
            , ed.empl_ctg
            , ed.job_indicator
            , ed.job_title
            , ed.jobcode
            , ed.jobcode_desc
            , ed.jobcodedescgroup
            , ed.hire_dt
            , ed.position_number
            , ed.position_title
            , ed.position_start_date
            , ed.appointment_status
            , ed.business_unit
            , ed.deptid
            , ed.organization
            , ed.level1_program
            , ed.level2_division
            , ed.level3_branch
            , ed.level4
            , ed.supervisor_position_number
            , ed.supervisor_position_title
            , ed.supervisor_position_start_date
            , ed.supervisor_emplid
            , ed.supervisor_name
            , ed.supervisor_email
            , ed.manager_id
            , ed.manager_first_name
            , ed.manager_last_name
            , ed.date_created
            , ed.date_updated
            , ed.date_deleted
            , u.id AS user_id
            , u.guid AS user_guid
            , ed.guid AS demo_guid
        FROM 
            users AS u
            , employee_demo AS ed   
        WHERE
            u.employee_id = ed.employee_id
    ";

    public static $where_active = "
            AND ed.employee_status = 'A'
            AND ed.date_deleted IS NULL
    ";

    public static $where_inactive = "
            AND ed.employee_status <> 'A'
            AND ed.date_deleted IS NULL
    ";

    public static $where_deleted = "
            AND NOT ed.date_deleted IS NULL
    ";

    public static $where_not_deleted = "
            AND ed.date_deleted IS NULL
    ";

    public static $order_by_id = "
        ORDER BY 
            ed.employee_id
            , ed.empl_record
            , ed.effdt
            , ed.effseq
    ";

    public static $order_by_name = "
        ORDER BY 
            ed.employee_name
            , ed.guid
            , ed.employee_id
            , ed.empl_record
            , ed.effdt
            , ed.effseq
    ";

    public static $order_by_guid = "
        ORDER BY 
            ed.guid
            , ed.employee_id
            , ed.empl_record
            , ed.effdt
            , ed.effseq
    ";


    public static $order_by_dept = "
        ORDER BY 
            ed.deptid
            , ed.employee_id
            , ed.empl_record
            , ed.effdt
            , ed.effseq
    ";

    public static $order_by_org = "
        ORDER BY 
            ed.organization
            , ed.level1_program
            , ed.level2_division
            , ed.level3_branch
            , ed.level4
            , ed.employee_id
            , ed.empl_record
            , ed.effdt
            , ed.effseq
    ";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        self::alterView("employees_vw", self::$employeedemo_sql, "", self::$order_by_guid);
        self::alterView("employees_by_guid_vw", self::$employeedemo_sql, "", self::$order_by_guid);
        self::alterView("users_vw", self::$users_employeedemo_sql, "", self::$order_by_id);
        self::alterView("users_active_by_org_vw", self::$users_employeedemo_sql, self::$where_active, self::$order_by_org);
        self::alterView("users_active_by_dept_vw", self::$users_employeedemo_sql, self::$where_active, self::$order_by_dept);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // self::dropView("employees_vw");
        // self::dropView("employees_by_guid_vw");
        // self::dropView("users_vw");
        // self::dropView("users_active_by_org_vw");
        // self::dropView("users_active_by_dept_vw");
    }

    public function alterView($viewName, $viewSQL, $viewWhere, $viewOrderBy) {
        \DB::statement('
            ALTER VIEW '.$viewName.'
            AS
            '.$viewSQL.'
            '.$viewWhere.'
            '.$viewOrderBy.'
        ');
    }

    public function dropView($viewName) {
        \DB::statement(" DROP VIEW ".$viewName);
    }
}
