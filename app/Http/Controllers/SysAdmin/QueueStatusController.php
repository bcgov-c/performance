<?php

namespace App\Http\Controllers\SysAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Support\Facades\DB;


class QueueStatusController extends Controller
{
    public function show() {
        // Run the custom command
        Artisan::call('queue:status');

        // Get the output of the command
        $output = Artisan::output();

        // Pass the output to the view
        return View::make('sysadmin.queue.status', compact('output'));
    }

    public function processes() {
        // Execute the ps -eF command and get the output
        $processes = shell_exec('ps -eF');

        return View::make('sysadmin.queue.processes', compact('processes'));
    }

    public function fixModle(){
        //#1206 Information from ODS (supervisor info) doesn't sync with PDP user annex table
        $employeeId = '188978';
        $reportingToEmployeeId = '132126';
        $reportingToPositionNumber = '00133674';
        $reportingToName = 'Warren,Bryna Elita Mae';
        $reportingToEmail = 'Bryna.Warren@gov.bc.ca';
        $reportingToUserId = '20985';

        // Run the update query
        DB::table('users_annex')
            ->where('employee_id', $employeeId)
            ->update([
                'reporting_to_employee_id' => $reportingToEmployeeId,
                'reporting_to_position_number' => $reportingToPositionNumber,
                'reporting_to_name' => $reportingToName,
                'reporting_to_email' => $reportingToEmail,
            ]);

        DB::table('employee_managers')
            ->where('employee_id', $employeeId)
            ->update([
                'supervisor_emplid' => $reportingToEmployeeId,
                'supervisor_position_number' => $reportingToPositionNumber,
                'supervisor_name' => $reportingToName,
                'supervisor_email' => $reportingToEmail,
                'supervisor_userid' => $reportingToUserId,
            ]);    

        DB::table('users')
            ->where('employee_id', $employeeId)
            ->update([
                'reporting_to' => $reportingToUserId,
            ]); 

        DB::table('users')
            ->where('employee_id', '169412')
            ->update([
                'empl_record' => 0,
            ]); 


        echo "Employees updated.";

        
    }
}
