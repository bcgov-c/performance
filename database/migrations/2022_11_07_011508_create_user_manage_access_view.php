<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserManageAccessView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::statement('DROP VIEW IF EXISTS user_manage_access_view');
        
        \DB::statement("
            CREATE VIEW user_manage_access_view
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
                , ed.organization
                , ed.level1_program
                , ed.level2_division
                , ed.level3_branch
                , ed.level4
                , ed.deptid
                , ed.guid
                , mhr.model_id
                , mhr.role_id
                , mhr.reason
                , mhr.model_type
                , r.longname AS role_longname
                , (SELECT DISTINCT 1 FROM model_has_roles AS mhr2 WHERE mhr2.model_id = u.id AND mhr2.role_id = 3) AS hradmin
                , (SELECT DISTINCT 1 FROM model_has_roles AS mhr2 WHERE mhr2.model_id = u.id AND mhr2.role_id = 4) AS sysadmin
            FROM ((users AS u, model_has_roles AS mhr, roles AS r) LEFT OUTER JOIN employee_demo AS ed ON ed.guid = u.guid AND ed.date_deleted IS NULL)
            WHERE u.id = mhr.model_id
            AND mhr.role_id = r.id
            AND TRIM(mhr.model_type) = 'App\\\Models\\\User'
            AND mhr.role_id IN (3, 4) 
            ORDER BY 
                u.id
                , mhr.role_id
                , ed.organization
                , ed.level1_program
                , ed.level2_division
                , ed.level3_branch
                , ed.level4
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
            DROP VIEW user_manage_access_view
        ");
    }


}
