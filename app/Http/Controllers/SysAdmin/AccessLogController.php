<?php

namespace App\Http\Controllers\SysAdmin;

use Carbon\Carbon;
use App\Models\AccessLog;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AccessLogController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
    
        if($request->ajax()) {

            // $columns = ["code","name","status","created_at"];
            $access_logs = AccessLog::join('users', 'users.id', 'access_logs.user_id')
                            ->where( function($query) use($request) {
                                return $query->when($request->term, function($q) use($request) {
                                         return $q->where('users.name','LIKE','%'.$request->term.'%')
                                            ->orWhere('users.idir','LIKE','%'.$request->term.'%')
                                            ->orWhere('users.employee_id','LIKE','%'.$request->term.'%');
                                });
                            })
                            ->when($request->login_at_from, function($query) use($request) {
                                return $query->where('login_at', '>=', $request->login_at_from); 
                            })
                            ->when($request->login_at_to, function($query) use($request) {
                                return $query->where('login_at', '<=', $request->login_at_to); 
                            })
                            ->select('access_logs.*', 'users.name', 'users.employee_id',
                                        DB::raw("(Select idir FROM employee_demo
                                            where users.employee_id = employee_demo.employee_id
                                                and employee_demo.date_deleted is null
                                                and employee_demo.pdp_excluded = 0
                                            limit 1 ) as idir"),
                                        DB::raw("(Select organization FROM employee_demo
                                                   where users.employee_id = employee_demo.employee_id
                                                     and employee_demo.date_deleted is null
                                                     and employee_demo.pdp_excluded = 0
                                                limit 1 ) as organization")
                            );
                            
//    return( [$access_logs->toSql(), $access_logs->getBindings() ]);                                

            return Datatables::of($access_logs)
                    ->addIndexColumn()
                    ->make(true);
        }

        return view('sysadmin.system-security.access-logs',compact('request') );

    }


    public function export(Request $request) {

        $sql = AccessLog::join('users', 'users.id', 'access_logs.user_id')
                            ->where( function($query) use($request) {
                                return $query->when($request->term, function($q) use($request) {
                                        return $q->where('users.name','LIKE','%'.$request->term.'%')
                                            ->orWhere('users.idir','LIKE','%'.$request->term.'%')
                                            ->orWhere('users.employee_id','LIKE','%'.$request->term.'%');
                                });
                            })
                            ->when($request->login_at_from, function($query) use($request) {
                                return $query->where('login_at', '>=', $request->login_at_from); 
                            })
                            ->when($request->login_at_to, function($query) use($request) {
                                return $query->where('login_at', '<=', $request->login_at_to); 
                            })
                            ->select('access_logs.*', 'users.name', 'users.employee_id',
                                        DB::raw("(Select idir FROM employee_demo
                                        where users.employee_id = employee_demo.employee_id
                                            and employee_demo.date_deleted is null
                                            and employee_demo.pdp_excluded = 0
                                        limit 1 ) as idir"),
                                        DB::raw("(Select organization FROM employee_demo
                                                where users.employee_id = employee_demo.employee_id
                                                    and employee_demo.date_deleted is null
                                                    and employee_demo.pdp_excluded = 0
                                                limit 1 ) as organization")
                            );


        $access_logs = $sql->get();

      // Generating Output file
        $filename = 'Acccess_Log_'. Carbon::now()->format('YmdHis') . '.csv';
      
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = ['Tran ID',  'User Name', 'IDIR', 'Employee ID',  'Organization', 
                    'Login Method',  'Identity Provider', 'User ID', 'Login at', 'Logout at', 'Login IP'
                   ];

        $callback = function() use($access_logs, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($access_logs as $log) {
                $row['Tran ID'] = $log->id;
                $row['User Name'] = $log->name;
                $row['IDIR'] = $log->idir;
                $row['Employee ID'] = $log->employee_id;
                $row['User ID'] = $log->user_id;
                $row['Organization'] = $log->organization;
                $row['Login Method'] = $log->login_method;
                $row['Identity Provider'] = $log->identity_provider;
                $row['Login at'] = $log->login_at;
                $row['Logout at'] = $log->logout_at;
                $row['Login IP'] = $log->login_ip;

                fputcsv($file, array($row['Tran ID'], $row['User Name'], $row['IDIR'], 
                        $row['Employee ID'],  $row['Organization'], $row['Login Method'],
                        $row['Identity Provider'], $row['User ID'], $row['Login at'], $row['Logout at'], $row['Login IP'] ));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);

    }

}
