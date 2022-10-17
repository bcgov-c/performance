<?php

namespace App\Http\Controllers\SysAdmin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\JobSchedAudit;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;


class JobSchedAuditController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function index(Request $request) {
    
        if($request->ajax()) {

            $audits = JobSchedAudit::select('job_sched_audit.*')
                        ->when($request->job_name, function($query) use($request) {
                            return $query->where('job_name', 'like', '%'.$request->job_name.'%');
                        })
                        ->when($request->status, function($query) use($request) {
                            return $query->where('status', $request->status);
                        })
                        ->when($request->start_time, function($query) use($request) {
                            return $query->where(function($q) use($request) {
                                 return $q->where(function($q1) use($request) {
                                         return  $q1->where('end_time', '>=' , $request->start_time)
                                                    ->orWhereNull('end_time');
                                    })
                                    ->where('start_time', '>' , $request->start_time);
                            });
                        })
                        ->when($request->end_time, function($query) use($request) {
                            // return $query->where('start_time', '<=' , $request->end_time);
                            return $query->where(function($q) use($request) {
                                return $q->where(function($q1) use($request) {
                                        return  $q1->where('end_time', '<=' , $request->end_time)
                                                   ->orWhereNull('end_time');
                                   })
                                   ->where('start_time', '<' , $request->end_time);
                           });
                        })
                        ->when($request->include_trashed, function($query) {
                            return $query->withTrashed();
                        });
                            
            return Datatables::of($audits)
                        ->addColumn('message_text', function ($audit) {
                            $more_link = ' ... <br><a class="more-link text-danger" data-id="'. $audit->id .'" >click here for more detail</a>';
                            $maxline = 3;
                            $lines = preg_split('#\r?\n#', $audit->details);
                            if ( count($lines) > $maxline) {
                                // return nl2br( substr($audit->message, 0, $maxline)) . $more_link;
                                return nl2br( implode( PHP_EOL , array_slice( $lines, 0, 3) ) . $more_link );
                            } else {   
                                return nl2br( $audit->details);
                            }
                        })
                        ->addColumn('action', function ($audit) {
                            return '<a class="btn btn-info btn-sm  show-audit" data-id="'. $audit->id .'" >Show</a>';
                        })
                        // ->addColumn('deleted_by', function ($audit) {
                        //     return $audit->deleted_at ? $audit->updated_by->name : '';       
                        // })
                        ->rawColumns(['message_text', 'action'])
                        ->make(true);
        }

        $status_list = JobSchedAudit::job_status_options();

        return view('sysadmin.schedule-job-audits.index', compact('request', 'status_list') );

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {

        if ($request->ajax()) {

            $audit = JobSchedAudit::where('id', $id)->first();

            return response()->json($audit);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
    */
    public function destroy($id)
    {

     
    }

}
