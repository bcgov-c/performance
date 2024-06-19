<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAnnexReporteesView extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        \DB::statement("
            CREATE VIEW `users_annex_reportees_view` AS
            SELECT 
                `d_ua`.`reporting_to_employee_id` AS `employee_id`,
                `d_ua`.`reporting_to_position_number` AS `position_number`,
                COUNT(`d_ed`.`employee_id`) AS `reportees`
            FROM
                (`employee_demo` `d_ed` USE INDEX (EMPLOYEE_DEMO_EMPLOYEE_ID_EMPL_RECORD_UNIQUE)
                JOIN `users_annex` `d_ua` USE INDEX (IDX_USERS_ANNEX_REPORTING_TO_EMPLOYEE_ID_POSITION_NUMBER))
            WHERE
                ((`d_ed`.`employee_id` = `d_ua`.`employee_id`)
                    AND (`d_ed`.`empl_record` = `d_ua`.`empl_record`)
                    AND (`d_ed`.`date_deleted` IS NULL))
            GROUP BY `d_ua`.`reporting_to_employee_id` , `d_ua`.`reporting_to_position_number`
            HAVING (`d_ua`.`reporting_to_employee_id` IS NOT NULL);
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
            DROP VIEW users_annex_reportees_view
        ");
    }


}
