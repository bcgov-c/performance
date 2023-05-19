<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserListView4 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            ALTER VIEW user_list_view
            AS
            SELECT 
                u.id AS user_id
                , u.employee_id 
                , CASE WHEN TRIM(u.name) <> '' THEN u.name ELSE u.id END AS user_name
                , CASE WHEN TRIM(ed.employee_name) <> '' THEN ed.employee_name ELSE ed.employee_id END AS demo_name
                , CASE WHEN TRIM(ed.employee_name) <> '' THEN ed.employee_name ELSE CASE WHEN TRIM(u.name) <> '' THEN u.name ELSE CASE WHEN (ed.employee_id IS NULL || TRIM(ed.employee_id) = '') THEN u.id ELSE ed.employee_id END END END AS display_name
                , u.email AS user_email
                , ed.jobcode
                , ed.jobcode_desc
                , odoh.id AS orgid
                , odoh.organization_key
                , odoh.level1_key
                , odoh.level2_key
                , odoh.level3_key
                , odoh.level4_key
                , odoh.organization
                , odoh.level1_program
                , odoh.level2_division
                , odoh.level3_branch
                , odoh.level4
                , odoh.organization_deptid
                , odoh.level1_deptid
                , odoh.level2_deptid
                , odoh.level3_deptid
                , odoh.level4_deptid
                , ed.deptid
                , ed.guid
            FROM 
                users 
                    AS u 
                    USE INDEX (idx_users_employeeid_emplrecord)
                LEFT OUTER JOIN employee_demo 
                    AS ed 
                    USE INDEX (idx_employee_demo_employeeid_orgid) 
                    ON ed.employee_id = u.employee_id AND ed.date_deleted IS NULL
                LEFT OUTER JOIN employee_demo_tree 
                    AS odoh ON odoh.id = ed.orgid
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
        //     DROP VIEW user_list_view
        // ");
    }


}
