<?php

// NOT INTENDED FOR PRODUCTION
// ONLY FOR TEST AND TRAINING

namespace Database\Seeders;

use App\Models\AdminOrg;
use App\Models\EmployeeDemo;
use App\Models\ModelHasRoleAudit;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DataFix_1277_Create_Evaluator_Functional_Accounts extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {

    // User Creation

    $users = [
        [
            'id' => 1003,
            'email' => 'employee3@example.com',
            'name' => 'Employee 3',
            'reporting_to' => 2003,
            'role' => 'Employee',
            'joining_date' => '03-JAN-2024',
            'employee_id' => '004667'
        ],
        [
            'id' => 2003,
            'email' => 'supervisor3@example.com',
            'name' => 'Supervisor 3',
            'reporting_to' => 9999,
            'role' => 'Supervisor',
            'joining_date' => '03-FEB-2024',
            'employee_id' => '131075'
        ],
        [
            'id' => 3003,
            'email' => 'hradmin3@example.com',
            'name' => 'HR Admin 3',
            'reporting_to' => 9999,
            'role' => 'HR Admin',
            'joining_date' => '03-MAR-2024',
            'employee_id' => '193485'
        ],
        [
            'id' => 4003,
            'email' => 'sysadmin3@example.com',
            'name' => 'Sys Admin 3',
            'reporting_to' => 9999,
            'role' => 'Sys Admin',
            'joining_date' => '03-APR-2024',
            'employee_id' => NULL
        ],
        [
            'id' => 5003,
            'email' => 'service3@example.com',
            'name' => 'Service Representative 3',
            'reporting_to' => 9999,
            'role' => 'Service Representative',
            'joining_date' => '03-MAY-2024',
            'employee_id' => '080069'
        ],

    ];

    foreach ($users as $user) {

        $tempPassword = $this->generateRandomString(32);

        $entry = User::updateOrCreate([
            'email' => $user['email'],
        ], [
            'id' => $user['id'],
            'name' => $user['name'],
            'password' => Hash::make($tempPassword),
            'reporting_to' => $user['reporting_to'] ?? null,
            'joining_date' => Carbon::createFromFormat("d-M-Y", $user['joining_date']),
            'employee_id' => $user['employee_id'],
        ]);

        $entry->assignRole($user['role']);

        echo 'Created/Updated '.$user['name'];  echo "\r\n";

    }


    $edhradm = EmployeeDemo::where('employee_id', '193485')->first();

    $adminorg = AdminOrg::where('user_id', \DB::raw(3003))->where('version', \DB::raw(2))->where('orgid', \DB::raw(164))->firstOrNew();

    $adminorg->user_id = 3003;
    $adminorg->version = 2;
    $adminorg->orgid = 164;
    $adminorg->inherited = 1;
    $adminorg->save();

    echo 'Created/Updated 3003 Admin Org';  echo "\r\n";

    $audit = ModelHasRoleAudit::where('model_id', \DB::raw(3003))->where('role_id', \DB::raw(3))->whereNull('deleted_at')->firstOrNew();
    $audit->model_id = 3003;
    $audit->role_id = 3;
    $audit->position_number = $edhradm->position_number;
    $audit->deptid = $edhradm->deptid;
    $audit->updated_by = 'ZH1277';
    $audit->save();

    echo 'Created/Updated 3003 Audit';  echo "\r\n";


    $edhradm = EmployeeDemo::where('employee_id', '080069')->first();

    $audit = ModelHasRoleAudit::where('model_id', \DB::raw(5003))->where('role_id', \DB::raw(5))->whereNull('deleted_at')->firstOrNew();
    $audit->model_id = 5003;
    $audit->role_id = 5;
    $audit->position_number = $edhradm->position_number;
    $audit->deptid = $edhradm->deptid;
    $audit->updated_by = 'ZH1277';
    $audit->save();

    echo 'Created/Updated 5003 Audit';  echo "\r\n";

  }

  function generateRandomString($length = 16) {
    return substr(str_shuffle(str_repeat($x='0123456789qwertyuiopasdfghjklzxcvbnmZXCVBNMASDFGHJKLQWERTYUIOP', ceil($length/strlen($x)))), 1, $length);
  }

}
