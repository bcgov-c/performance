<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserTableSeeder_GoLive extends Seeder
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
        'id' => 1001,
        'email' => 'employee1@example.com',
        'name' => 'Employee 1',
        'password' => 'employee1@2022',
        'reporting_to' => 2001,
        'role' => 'Employee',
        'joining_date' => '01-OCT-2021'
      ],
      [
        'id' => 1002,
        'email' => 'employee2@example.com',
        'name' => 'Employee 2',
        'password' => 'employee2@2022',
        'reporting_to' => 2001,
        'role' => 'Employee',
        'joining_date' => '01-JAN-2020'
      ],
      [
        'id' => 1003,
        'email' => 'employee3@example.com',
        'name' => 'Employee 3',
        'password' => 'employee3@2022',
        'reporting_to' => 2002,
        'role' => 'Employee',
        'joining_date' => '14-MAR-2021'
      ],
      [
        'id' => 2001,
        'email' => 'supervisor1@example.com',
        'name' => 'Supervisor 1',
        'password' => 'supervisor1@2022',
        'reporting_to' => 9999,
        'role' => 'Supervisor',
        'joining_date' => '01-AUG-2019'
      ],
      [
        'id' => 2002,
        'email' => 'supervisor2@example.com',
        'name' => 'Supervisor 2',
        'password' => 'supervisor2@2022',
        'reporting_to' => 9999,
        'role' => 'Supervisor',
        'joining_date' => '08-AUG-2016'
      ],
      [
        'id' => 3001,
        'email' => 'hradmin1@example.com',
        'name' => 'HR Admin 1',
        'password' => 'hradmin1@2022',
        'reporting_to' => 9999,
        'role' => 'HR Admin',
        'joining_date' => '04-MAR-2014'
      ],
      [
        'id' => 3002,
        'email' => 'hradmin2@example.com',
        'name' => 'HR Admin 2',
        'password' => 'hradmin2@2022',
        'reporting_to' => 9999,
        'role' => 'HR Admin',
        'joining_date' => '05-APR-2014'
      ],
      [
        'id' => 4001,
        'email' => 'sysadmin1@example.com',
        'name' => 'Sys Admin 1',
        'password' => 'sysadmin1@2022',
        'reporting_to' => 9999,
        'role' => 'Sys Admin',
        'joining_date' => '14-FEB-2014'
      ],
      [
        'id' => 4002,
        'email' => 'sysadmin2@example.com',
        'name' => 'Sys Admin 2',
        'password' => 'sysadmin2@2022',
        'reporting_to' => 9999,
        'role' => 'Sys Admin',
        'joining_date' => '11-FEB-2014'
      ],
      [
        'id' => 9999,
        'email' => 'executive1@example.com',
        'name' => 'Executive 1',
        'password' => 'executive1@2022',
        'role' => 'Supervisor',
        'joining_date' => '04-DEC-2014'
      ],


    ];


    foreach ($users as $user) {
      $entry = User::updateOrCreate([
        'email' => $user['email'],
      ], [
        'id' => $user['id'],
        'name' => $user['name'],
        'password' => Hash::make($user['password']),
        'reporting_to' => $user['reporting_to'] ?? null,
        'joining_date' => Carbon::createFromFormat("d-M-Y", $user['joining_date']),
      ]);

      $entry->assignRole($user['role']);
    }



  }
}
