<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSupervisorViews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            CREATE OR REPLACE VIEW employee_managers_view
            AS
            SELECT emv_u0.employee_id, 
                emv_ed0.position_number, 
                emv_ed0.orgid, 
                emv_su0.employee_id supervisor_emplid, 
                emv_sed0.employee_name supervisor_name, 
                emv_sed0.position_number supervisor_position_number, 
                emv_sed0.employee_email supervisor_email, 
                0 priority, 
                'Override' source 
            FROM employee_supervisor emv_ev0, 
                users emv_u0, 
                employee_demo emv_ed0 USE INDEX (idx_employee_demo_employeeid_record), 
                users emv_su0, 
                employee_demo emv_sed0 USE INDEX (idx_employee_demo_employeeid_record)
            WHERE emv_ev0.user_id = emv_u0.id 
                AND emv_u0.employee_id = emv_ed0.employee_id 
                AND emv_ev0.supervisor_id = emv_su0.id 
                AND emv_su0.employee_id = emv_sed0.employee_id
                AND emv_ed0.date_deleted IS NULL
                AND emv_sed0.date_deleted IS NULL
                AND emv_ev0.deleted_at IS NULL
            UNION
            SELECT emv_eam.employee_id, 
                emv_eam.position_number, 
                emv_eam.orgid, 
                emv_eam.supervisor_emplid, 
                emv_eam.supervisor_name, 
                emv_eam.supervisor_position_number, 
                emv_eam.supervisor_email, 
                emv_eam.priority, 
                emv_eam.source
            FROM employee_managers emv_eam 
            WHERE NOT EXISTS (SELECT 1 FROM employee_supervisor emv_xes, users emv_xu WHERE emv_xes.user_id = emv_xu.id AND emv_xes.deleted_at IS NULL AND emv_xu.employee_id = emv_eam.employee_id)
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }


}
