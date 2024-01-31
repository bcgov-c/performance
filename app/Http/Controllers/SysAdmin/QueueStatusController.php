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
        //disabled

        
    }
}
