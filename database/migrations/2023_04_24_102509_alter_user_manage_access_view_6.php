<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterUserManageAccessView6 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
        ALTER VIEW user_manage_access_view
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
                , odoh.name AS orgname
                , odoh.organization_key
                , odoh.level1_key
                , odoh.level2_key
                , odoh.level3_key
                , odoh.level4_key
                , odoh.level5_key
                , odoh.organization
                , odoh.level1_program
                , odoh.level2_division
                , odoh.level3_branch
                , odoh.level4
                , odoh.level5
                , odoh.organization_deptid
                , odoh.level1_deptid
                , odoh.level2_deptid
                , odoh.level3_deptid
                , odoh.level4_deptid
                , odoh.level5_deptid
                , ed.deptid
                , ed.guid
                , mhr.model_id
                , mhr.role_id
                , mhr.reason
                , mhr.model_type
                , r.longname AS role_longname
                , (SELECT DISTINCT 1 FROM model_has_roles AS mhr2 WHERE mhr2.model_id = u.id AND mhr2.role_id = 3) AS hradmin
                , (SELECT DISTINCT 1 FROM model_has_roles AS mhr2 WHERE mhr2.model_id = u.id AND mhr2.role_id = 4) AS sysadmin
                , (CASE WHEN mhr.role_id = 3 THEN (SELECT COUNT(DISTINCT ao.orgid) FROM admin_orgs AS ao WHERE ao.user_id = u.id AND ao.version = 2) ELSE NULL END) AS org_count
            FROM 
                (
                    (
                        users 
                            AS u 
                            USE INDEX (idx_users_employeeid_emplrecord), 
                        model_has_roles 
                            AS mhr, 
                        roles 
                            AS r
                    ) 
                    LEFT JOIN employee_demo 
                        AS ed 
                        USE INDEX (idx_employee_demo_employeeid_orgid) 
                        ON ed.employee_id = u.employee_id 
                            AND ed.date_deleted IS NULL
                )
                LEFT JOIN employee_demo_tree 
                    AS odoh 
                    ON odoh.id = ed.orgid
            WHERE 
                u.id = mhr.model_id
                AND mhr.role_id = r.id
                AND TRIM(mhr.model_type) = 'App\\\Models\\\User'
                AND mhr.role_id IN (3, 4) 
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
        //     DROP VIEW user_manage_access_view
        // ");
    }


}
