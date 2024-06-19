<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GoalBankOrg;


class DataFix_796_GoalBankOrg_DeptID extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::statement("
            UPDATE goal_bank_orgs AS b
            SET deptid = (
                SELECT DISTINCT e.deptid 
                FROM employee_demo AS e 
                WHERE e.organization = b.organization
                AND (e.level1_program = b.level1_program OR ((e.level1_program IS NULL OR TRIM(e.level1_program) = '') AND (b.level1_program IS NULL OR TRIM(b.level1_program) = '')))
                AND (e.level2_division = b.level2_division OR ((e.level2_division IS NULL OR TRIM(e.level2_division) = '') AND (b.level2_division IS NULL OR TRIM(b.level2_division) = '')))
                AND (e.level3_branch = b.level3_branch OR ((e.level3_branch IS NULL OR TRIM(e.level3_branch) = '') AND (b.level3_branch IS NULL OR TRIM(b.level3_branch) = '')))
                AND (e.level4 = b.level4 OR ((e.level4 IS NULL OR TRIM(e.level4) = '') AND (b.level4 IS NULL OR TRIM(b.level4) = '')))
                AND TRIM(e.organization) <> ''
                AND not e.organization IS NULL
            )
            WHERE b.deptid IS NULL 
            OR TRIM(b.deptid) = ''
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
