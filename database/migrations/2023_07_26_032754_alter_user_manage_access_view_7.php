<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterUserManageAccessView7 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        DB::statement("
        ALTER VIEW `user_manage_access_view` AS
            SELECT 
                `u`.`id` AS `user_id`,
                `u`.`employee_id` AS `employee_id`,
                (CASE
                    WHEN (TRIM(`u`.`name`) <> '') THEN `u`.`name`
                    ELSE `u`.`id`
                END) AS `user_name`,
                (CASE
                    WHEN (TRIM(`ed`.`employee_name`) <> '') THEN `ed`.`employee_name`
                    ELSE `ed`.`employee_id`
                END) AS `demo_name`,
                (CASE
                    WHEN (TRIM(`ed`.`employee_name`) <> '') THEN `ed`.`employee_name`
                    ELSE (CASE
                        WHEN (TRIM(`u`.`name`) <> '') THEN `u`.`name`
                        ELSE (CASE
                            WHEN
                                ((`ed`.`employee_id` IS NULL)
                                    OR (TRIM(`ed`.`employee_id`) = ''))
                            THEN
                                `u`.`id`
                            ELSE `ed`.`employee_id`
                        END)
                    END)
                END) AS `display_name`,
                `u`.`email` AS `user_email`,
                `ed`.`jobcode` AS `jobcode`,
                `ed`.`jobcode_desc` AS `jobcode_desc`,
                `odoh`.`id` AS `orgid`,
                `odoh`.`name` AS `orgname`,
                `odoh`.`organization_key` AS `organization_key`,
                `odoh`.`level1_key` AS `level1_key`,
                `odoh`.`level2_key` AS `level2_key`,
                `odoh`.`level3_key` AS `level3_key`,
                `odoh`.`level4_key` AS `level4_key`,
                `odoh`.`level5_key` AS `level5_key`,
                `odoh`.`level6_key` AS `level6_key`,
                `odoh`.`organization` AS `organization`,
                `odoh`.`level1_program` AS `level1_program`,
                `odoh`.`level2_division` AS `level2_division`,
                `odoh`.`level3_branch` AS `level3_branch`,
                `odoh`.`level4` AS `level4`,
                `odoh`.`level5` AS `level5`,
                `odoh`.`level6` AS `level6`,
                `odoh`.`organization_deptid` AS `organization_deptid`,
                `odoh`.`level1_deptid` AS `level1_deptid`,
                `odoh`.`level2_deptid` AS `level2_deptid`,
                `odoh`.`level3_deptid` AS `level3_deptid`,
                `odoh`.`level4_deptid` AS `level4_deptid`,
                `odoh`.`level5_deptid` AS `level5_deptid`,
                `odoh`.`level6_deptid` AS `level6_deptid`,
                `ed`.`deptid` AS `deptid`,
                `ed`.`guid` AS `guid`,
                `mhr`.`model_id` AS `model_id`,
                `mhr`.`role_id` AS `role_id`,
                `mhr`.`reason` AS `reason`,
                `mhr`.`model_type` AS `model_type`,
                `r`.`longname` AS `role_longname`,
                (SELECT DISTINCT
                        1
                    FROM
                        `model_has_roles` `mhr2`
                    WHERE
                        ((`mhr2`.`model_id` = `u`.`id`)
                            AND (`mhr2`.`role_id` = 3))) AS `hradmin`,
                (SELECT DISTINCT
                        1
                    FROM
                        `model_has_roles` `mhr2`
                    WHERE
                        ((`mhr2`.`model_id` = `u`.`id`)
                            AND (`mhr2`.`role_id` = 4))) AS `sysadmin`,
                (CASE
                    WHEN
                        (`mhr`.`role_id` = 3)
                    THEN
                        (SELECT 
                                COUNT(DISTINCT `ao`.`orgid`)
                            FROM
                                `admin_orgs` `ao`
                            WHERE
                                ((`ao`.`user_id` = `u`.`id`)
                                    AND (`ao`.`version` = 2)))
                    ELSE NULL
                END) AS `org_count`
            FROM
                ((((`users` `u` USE INDEX (IDX_USERS_EMPLOYEEID_EMPLRECORD)
                JOIN `model_has_roles` `mhr`)
                JOIN `roles` `r`)
                LEFT JOIN `employee_demo` `ed` USE INDEX (IDX_EMPLOYEE_DEMO_EMPLOYEEID_ORGID) ON (((`ed`.`employee_id` = `u`.`employee_id`)
                    AND (`ed`.`date_deleted` IS NULL))))
                LEFT JOIN `employee_demo_tree` `odoh` ON ((`odoh`.`id` = `ed`.`orgid`)))
            WHERE
                ((`u`.`id` = `mhr`.`model_id`)
                    AND (`mhr`.`role_id` = `r`.`id`)
                    AND (`mhr`.`role_id` IN (3 , 4, 5)));   

        ");

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
