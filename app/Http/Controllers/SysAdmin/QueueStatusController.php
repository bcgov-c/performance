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
        $result = DB::table('model_has_roles as mhr')
                    ->join('users_annex as ua', 'mhr.model_id', '=', 'ua.user_id')
                    ->where('ua.reportees', '>', 0)
                    ->where('mhr.role_id', 1)
                    ->whereNotExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('model_has_roles as mhr2')
                            ->whereRaw('mhr2.model_id = mhr.model_id')
                            ->where('mhr2.role_id', 2);
                    })
                    ->select('ua.user_id')
                    ->get();

        foreach($result as $r) {
            $user_id = $r->user_id;
            $existingRecord = DB::table('model_has_roles')
                        ->where([
                            'model_id' => $user_id,
                            'role_id' => 2,
                            'model_type' => 'App\\Models\\User',
                        ])->exists();
            if (!$existingRecord) {
                        $result = DB::table('model_has_roles')->insert([
                                'model_id' => $user_id,
                                'role_id' => 2,
                                'model_type' => 'App\\Models\\User',
                            ]);
            }
        }            


    }
}
