<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ModelHasRoleAudit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Console\Command;



class DataFix_1163_Initialize_Model_Has_Role_Audits extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info(Carbon::now()." - Begin setup initial deptid and position number in model_has_role_audits table.");
        \DB::statement("
            INSERT INTO model_has_role_audits (model_id, role_id, deptid, position_number, created_at, updated_at, updated_by) 
            SELECT 
                mhr.model_id, 
                mhr.role_id,
                (SELECT deptid FROM employee_demo AS ed, users AS u WHERE ed.employee_id = u.employee_id AND u.id = mhr.model_id AND ed.date_deleted IS NULL
                    AND ed.empl_record = (SELECT MIN(ed1.empl_record) FROM employee_demo AS ed1 WHERE ed1.employee_id = ed.employee_id AND ed1.date_deleted IS NULL)
                ) AS deptid,
                (SELECT position_number FROM employee_demo AS ed, users AS u WHERE ed.employee_id = u.employee_id AND u.id = mhr.model_id AND ed.date_deleted IS NULL
                    AND ed.empl_record = (SELECT MIN(ed1.empl_record) FROM employee_demo AS ed1 WHERE ed1.employee_id = ed.employee_id AND ed1.date_deleted IS NULL)
                ) AS position_number,
                NOW() AS created_at,
                NOW() AS updated_at,
                'ZH1163' AS updated_by
            FROM model_has_roles AS mhr
            WHERE mhr.role_id = 5
            AND NOT EXISTS (SELECT 1 FROM model_has_role_audits AS mhra WHERE mhra.model_id = mhr.model_id AND mhra.role_id = mhr.role_id AND mhra.deleted_at IS NULL)
        ");
        $this->command->info(Carbon::now()." - End setup initial deptid and position number in model_has_role_audits table.");
    }
}
