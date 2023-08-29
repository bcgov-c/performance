<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Goal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class DataFix_1095_ProfileCorrections extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Disable duplicate profile
        User::whereRaw("users.id IN (17448,51793,28472,51873,34985,35571,38335,42846,43052,44256,45501,51756,51800,48023,52143,48402)")
        ->whereRaw("NOT employee_id LIKE '%locked-by-1095-%'")
        ->update([
            'guid' => DB::raw("CONCAT('locked-by-1095-', guid)"),
            'employee_id' => DB::raw("CONCAT('locked-by-1095-', employee_id)"),
            'email' => DB::raw("CONCAT('locked-by-1095-', email)"),
            'acctlock' => DB::raw('1'),
        ]);

        //Update email address in user profile
        \DB::statement("UPDATE users SET email = (SELECT ed.employee_email FROM employee_demo AS ed WHERE ed.employee_id = users.employee_id) WHERE users.id IN (48250,49533);");
    }
}
