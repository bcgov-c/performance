<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserListView extends Migration
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
                , ed.organization
                , ed.level1_program
                , ed.level2_division
                , ed.level3_branch
                , ed.level4
                , ed.deptid
                , ed.guid
            FROM users AS u LEFT OUTER JOIN employee_demo AS ed ON ed.employee_id = u.employee_id AND ed.date_deleted IS NULL
            ORDER BY 
                u.id
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
        // \DB::statement("
        //     DROP VIEW user_list_view
        // ");
    }


}
