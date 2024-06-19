<?php

namespace App\Http\Controllers\SysAdmin;



use App\Models\User;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use App\Models\OrganizationTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;


class NotificationController extends Controller
{
    //
    public function index(Request $request) {

        if($request->ajax()){

            $date_sent_from = $request->get('date_sent_from') ? $request->get('date_sent_from') : '1900-01-01';
            $date_sent_to = $request->get('date_sent_to') ?  $request->get('date_sent_to') : '2099-12-31';
            $recipients = $request->get('recipients') ? $request->get('recipients') : '';
            $alert_format = $request->get('alert_format');

            $notifications = NotificationLog::when($date_sent_from, function ($query) use($date_sent_from, $date_sent_to) {
                    $query->whereBetween('date_sent', [$date_sent_from, $date_sent_to] );
                })
                ->when($recipients, function ($query) use($recipients) { 
                    if (App::environment(['production'])) {
                        $query->whereHas('recipients.recipient', function ($query) use($recipients) { 
                            return $query->where('name', 'LIKE', "%{$recipients}%"); 
                        });        
                    } else {
                        $query->where(function ($q) use($recipients) {
                            $q->where('description', 'LIKE', "%{$recipients}%%") 
                              ->orWhereExists(function ($q2) use($recipients) {
                                    $q2->select(DB::raw(1))
                                        ->from('users')
                                        ->whereColumn('notification_logs.notify_user_id', 'users.id')
                                        ->where('users.name', 'LIKE', "%{$recipients}%"); 
                            });
                        });
                    }
                })
                ->when($alert_format, function ($query) use($alert_format) {
                        $query->where('alert_format', $alert_format);
                })
                ->when($request->notify_user, function ($query) use($request) {
                    $query->whereHas('notify_user', function ($query) use($request) { 
                        $query->where('name', 'LIKE', "%{$request->notify_user}%") 
                              ->orWhere('users.employee_id', 'LIKE', "%{$request->notify_user}%"); 
                    });   
                })
                ->when($request->overdue_user, function ($query) use($request) {
                    $query->whereHas('overdue_user', function ($query) use($request) { 
                        $query->where('name', 'LIKE', "%{$request->overdue_user}%") 
                              ->orWhere('users.employee_id', 'LIKE', "%{$request->overdue_user}%"); 
                    });   
                })
                ->when($request->notify_due_date, function ($query) use($request) {
                    $query->where('notify_due_date', $request->notify_due_date );
                })
                ->when($request->notify_for_days || $request->notify_for_days == '0', function ($query) use($request) {
                    $query->where('notify_for_days', $request->notify_for_days);
                })
                ->select(['id', 'subject', 'recipients', 'alert_type', 'alert_format', 'notify_user_id', 'overdue_user_id', 
                            'notify_due_date', 'notify_for_days', 'description','date_sent','created_at'])
                ->with(['recipients'])
                ->with(['notify_user', 'overdue_user']);

            return Datatables::of($notifications)
                ->addColumn('alert_type_name', function ($notification) {
                    return $notification->alert_type_name(); 
                })
                ->addColumn('alert_format_name', function ($notification) {
                     return $notification->alert_format_name(); 
                })
                ->addColumn('recipients', function ($notification) {
                    if ($notification->notify_user_id) {
                        return $notification->notify_user->name;
                    }

                    $userIds = $notification->recipients()->pluck('recipient_id')->toArray();
                    $users = User::whereIn('id', $userIds)->pluck('name');

                    if ($users->count() > 1) 
                       return $users->count() . ' recipients';
                    else if ($users->count() == 1) 
                       return $users[0];
                    else 
                        return '';
                })
                ->addColumn('action', function ($notification) {
                    if ($notification->alert_format == 'E') {
                        return '<a href="#" class="notification-modal btn btn-xs btn-primary" value="'. $notification->id .'"><i class="glyphicon glyphicon-envelope"></i>View</a>';
                    } else {
                        return '';
                    }
                })
                ->make(true);
        }

        $alert_format_list = NotificationLog::ALERT_FORMAT;

        return view('sysadmin.notifications.index', compact('alert_format_list') );

    }

    public function show(Request $request) 
    {
        $notificationLog = NotificationLog::where('id', $request->notification_id)->first();

        if($request->ajax()){
            return view('sysadmin.notifications.partials.show', compact('notificationLog') ); 
        } 
    }

    
    public function notify(Request $request) 
    {

        $errors = session('errors');

        $old_selected_emp_ids = []; // $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        if ($errors) {
            $old = session()->getOldInput();

            $request->dd_level0 = isset($old['dd_level0']) ? $old['dd_level0'] : null;
            $request->dd_level1 = isset($old['dd_level1']) ? $old['dd_level1'] : null;
            $request->dd_level2 = isset($old['dd_level2']) ? $old['dd_level2'] : null;
            $request->dd_level3 = isset($old['dd_level3']) ? $old['dd_level3'] : null;
            $request->dd_level4 = isset($old['dd_level4']) ? $old['dd_level4'] : null;

            $request->job_titles = isset($old['job_titles']) ? $old['job_titles'] : null;
            $request->active_since = isset($old['active_since']) ? $old['active_since'] : null;
            $request->search_text = isset($old['search_text']) ? $old['search_text'] : null;
            
            $request->orgCheck = isset($old['orgCheck']) ? $old['orgCheck'] : null;
            $request->userCheck = isset($old['userCheck']) ? $old['userCheck'] : null;

            $old_selected_emp_ids = isset($old['selected_emp_ids']) ? json_decode($old['selected_emp_ids']) : [];

        } 

        // no validation and move filter variable to old 
        if ($request->btn_search) {
            session()->put('_old_input', [
                'dd_level0' => $request->dd_level0,
                'dd_level1' => $request->dd_level1,
                'dd_level2' => $request->dd_level2,
                'dd_level3' => $request->dd_level3,
                'dd_level4' => $request->dd_level4,
                'job_titles' => $request->job_titles,
                'active_since' => $request->active_since,
                'criteria' => $request->criteria,
                'search_text' => $request->search_text,
                'orgCheck' => $request->orgCheck,
                'userCheck' => $request->userCheck,
            ]);
        }
        $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                    ->groupBy('job_title')->pluck('job_title') : null;

        $request->session()->flash('dd_level0', $request->dd_level0);
        $request->session()->flash('dd_level1', $request->dd_level1);
        $request->session()->flash('dd_level2', $request->dd_level2);
        $request->session()->flash('dd_level3', $request->dd_level3);
        $request->session()->flash('dd_level4', $request->dd_level4);
        $request->session()->flash('job_titles', $job_titles);
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $job_titles);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 'employee_id', 'employee_name', 'job_title', 'employee_email', 
                'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division',
                'employee_demo.level3_branch','employee_demo.level4', 'employee_demo.deptid'])
                ->orderBy('employee_id')
                ->pluck('employee_demo.employee_id');        
        
        $alert_format_list = NotificationLog::ALERT_FORMAT;
        $criteriaList = $this->search_criteria_list();
        

        return view('sysadmin.notifications.notify', compact('alert_format_list', 'criteriaList','matched_emp_ids', 'old_selected_emp_ids') );
    
    }


    public function loadOrganizationTree(Request $request) {
        $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                    ->groupBy('job_title')->pluck('job_title') : null;

        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $job_titles);
        
        $rows = $sql_level4->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id')
            ->union( $sql_level3->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id') )
            ->union( $sql_level2->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id') )
            ->union( $sql_level1->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id') )
            ->union( $sql_level0->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id') )
            ->pluck('employee_demo_tree.id'); 
        $orgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();

        // Employee Count by Organization
        $countByOrg = $sql_level4->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id', DB::raw("COUNT(*) as count_row"))
        ->union( $sql_level3->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level2->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level1->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level0->groupBy('employee_demo_tree.id')->select('employee_demo_tree.id', DB::raw("COUNT(*) as count_row") ) )
        ->pluck('count_row', 'employee_demo_tree.id');  
        
        // // Employee ID by Tree ID
        $empIdsByOrgId = [];
        $demoWhere = $this->baseFilteredWhere($request, $job_titles);
        $sql = clone $demoWhere; 
        $rows = $sql->join('employee_demo_tree', function($join) use($request) {
                $join->on('employee_demo.organization', '=', 'employee_demo_tree.organization')
                    ->on('employee_demo.level1_program', '=', 'employee_demo_tree.level1_program')
                    ->on('employee_demo.level2_division', '=', 'employee_demo_tree.level2_division')
                    ->on('employee_demo.level3_branch', '=', 'employee_demo_tree.level3_branch')
                    ->on('employee_demo.level4', '=', 'employee_demo_tree.level4');
                })
                ->select('employee_demo_tree.id','employee_demo.employee_id')
                ->groupBy('employee_demo_tree.id', 'employee_demo.employee_id')
                ->orderBy('employee_demo_tree.id')->orderBy('employee_demo.employee_id')
                ->get();

        $empIdsByOrgId = $rows->groupBy('id')->all();

        if($request->ajax()){
            return view('sysadmin.notifications.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
        } 

    }


    public function getDatatableEmployees(Request $request) {

        if($request->ajax()){
            $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                        ->groupBy('job_title')->pluck('job_title') : null;
    
            $demoWhere = $this->baseFilteredWhere($request, $job_titles);

            $sql = clone $demoWhere; 

            $employees = $sql->select([ 'employee_id', 'employee_name', 'job_title', 'employee_email', 
                'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division',
                'employee_demo.level3_branch','employee_demo.level4', 'employee_demo.deptid']);

            return Datatables::of($employees)
                ->addColumn('action', function ($employee) {
                    return '<a href="#" class="notification-modal btn btn-xs btn-primary" value="'. 
                        $employee->employee_id .'"><i class="glyphicon glyphicon-envelope"></i>View</a>';
                })
                ->addColumn('select_users', static function ($employee) {
                        return '<input pid="1335" type="checkbox" id="userCheck'. 
                            $employee->employee_id .'" name="userCheck[]" value="'. $employee->employee_id .'" class="dt-body-center">';
                })->rawColumns(['select_users','action'])
                ->make(true);
        }
    }


    public function send(Request $request) 
    {

        $selected_emp_ids = $request->selected_emp_ids ? json_decode($request->selected_emp_ids) : [];
        $request->userCheck = $selected_emp_ids;

        // array for build the select option on the page
        if ($request->recipients) {
            $recipients = User::whereIn('id', $request->recipients)->pluck('name','id');
            $request->session()->flash('old_recipients', 
                 $recipients
            );
        }

        if ($request->sender_id) {
            $sender_ids = User::whereIn('id', array($request->sender_id) )->pluck('name','id');
            $request->session()->flash('old_sender_ids', 
                 $sender_ids
            );
        }

        //setup Validator and passing request data and rules
        $validator = Validator::make(request()->all(), [
            'orgCheck'         => 'required_if:userCheck,null',
            'userCheck'         => 'required_if:orgCheck,null',
            'subject'            => 'required',
            'body'               => 'required',
        ]);

        //hook to add additional rules by calling the ->after method
        $validator->after(function ($validator) {
            if (request('sender_id')) {
                $user = User::find(request('sender_id'));
                if ( !($user->azure_id) ) {
                    $validator->errors()->add('sender_id', 'The selected sender is not an Azure AD user.'); 
                }
            }

        });
    
        //run validation which will redirect on failure
        if ($validator->fails()) {
            if (count($validator->errors())>0) {
                $request->session()->flash('message', 'There are one or more errors on the page. Please review and try again.');            
            }
            
            return redirect()->action([NotificationController::class, 'notify'] )
               ->withErrors($validator)->withInput();
          }

        // Send a notification to all participants that you would like to schedule a conversation 
        $current_user = User::find(Auth::id());

        $employee_ids = ($request->userCheck) ? $request->userCheck : [];

        $toRecipients = EmployeeDemo::select('users.id')
                ->join('users', 'employee_demo.employee_id', 'users.employee_id')
                ->whereIn('employee_demo.employee_id', $selected_emp_ids )
                ->distinct()
                ->orderBy('employee_demo.employee_name')
                ->pluck('users.id') ;


        // Method 1: Real-Time
        $sendMail = new \App\MicrosoftGraph\SendMail();
        $sendMail->toRecipients = $toRecipients->toArray();
        $sendMail->sender_id = '';
        $sendMail->useQueue = true;
        $sendMail->subject = $request->subject;
        $sendMail->body = $request->body;
        $sendMail->alertFormat = $request->alert_format;
        $success = $sendMail->sendMailWithoutGenericTemplate();
        if ($success) {
            return redirect()->route('sysadmin.notifications.notify')
                ->with('success','Email with subject "' . $request->subject  . '" was successfully sent.');
        }

    }

    public function getUsers(Request $request)
    {

        $search = $request->search;
        $users =  User::whereRaw("lower(name) like '%". strtolower($search)."%'")
                    ->whereNotNull('email')->paginate();

        return ['data'=> $users];
                  
    }


    public function getJobTitles() {

        $rows = EmployeeDemo::select('job_title')
           ->whereNotIn('job_title', ['', ' '])
           ->orderBy('job_title')
           ->distinct()->get();

        $formatted_data = [];
           foreach ($rows as $item) {
               $formatted_data[] = ['id' => $item->job_title, 'text' => $item->job_title ];
        }
   
        return response()->json($formatted_data);

    }

    public function getEmployees(Request $request,  $id) {
        $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                    ->groupBy('job_title')->pluck('job_title') : null;

        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request);
       
        $rows = $sql_level4->where('employee_demo_tree.id', $id)
            ->union( $sql_level3->where('employee_demo_tree.id', $id) )
            ->union( $sql_level2->where('employee_demo_tree.id', $id) )
            ->union( $sql_level1->where('employee_demo_tree.id', $id) )
            ->union( $sql_level0->where('employee_demo_tree.id', $id) );

        $employees = $rows->get();
        $parent_id = $id;
        
            return view('sysadmin.notifications.partials.employee', compact('parent_id', 'employees') ); 
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'employee_demo.employee_id' => 'Employee ID', 
            'employee_demo.employee_name'=> 'Employee Name',
            'employee_demo.classification_group' => 'Classification', 
            'employee_demo.deptid' => 'Department ID'
        ];
    }

    protected function baseFilteredWhere($request, $job_titles) {
        return EmployeeDemo::join('employee_demo_tree as u', 'u.deptid', 'employee_demo.deptid')
            ->whereNull('employee_demo.date_deleted')
            ->when($request->dd_level0, function($q) use($request) { return $q->whereRaw("u.organization_key = {$request->dd_level0}"); })
            ->when($request->dd_level1, function($q) use($request) { return $q->whereRaw("u.level1_key = {$request->dd_level1}"); })
            ->when($request->dd_level2, function($q) use($request) { return $q->whereRaw("u.level2_key = {$request->dd_level2}"); })
            ->when($request->dd_level3, function($q) use($request) { return $q->whereRaw("u.level3_key = {$request->dd_level3}"); })
            ->when($request->dd_level4, function($q) use($request) { return $q->whereRaw("u.level4_key = {$request->dd_level4}"); })
            ->when($job_titles, function ($q) use($job_titles) { return $q->whereIn('employee_demo.job_title', $job_titles->toArray() ); })
            ->when($request->active_since, function ($q) use($request) { return $q->where('employee_demo.hire_dt', '>=', $request->active_since); })
            ->when($request->search_text && $request->criteria != 'all', function($q) use($request) { 
                return $q->where("{$request->criteria}", 'LIKE', "%{$request->search_text}%"); 
            })
            ->when($request->search_text && $request->criteria == 'all', function($q) use($request) { 
                return $q->where(function($q1) use($request) {
                    return $q1->where('employee_demo.employee_id', 'LIKE', "%{$request->search_text}%")
                    ->orWhere('employee_demo.employee_name', 'LIKE', "%{$request->search_text}%")
                    ->orWhere('employee_demo.classification_group', 'LIKE', "%{$request->search_text}%")
                    ->orWhere('employee_demo.deptid', 'LIKE', "%{$request->search_text}%"); 
                });
            });
    }


    protected function baseFilteredSQLs($request, $job_titles) {

        // Base Where Clause
        $demoWhere = $this->baseFilteredWhere($request, $job_titles);

        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('employee_demo_tree', function($join) use($level0) {
            $join->on('employee_demo.organization_key', '=', 'employee_demo_tree.organization_key')
                ->where('employee_demo_tree.level', '=', 0);
            });
            
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('employee_demo_tree', function($join) use($level0, $level1) {
            $join->on('employee_demo.organization_key', '=', 'employee_demo_tree.organization_key')
                ->on('employee_demo.level1_key', '=', 'employee_demo_tree.level1_key')
                ->where('employee_demo_tree.level', '=', 1);
            });
            
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('employee_demo_tree', function($join) use($level0, $level1, $level2) {
            $join->on('employee_demo.organization_key', '=', 'employee_demo_tree.organization_key')
                ->on('employee_demo.level1_key', '=', 'employee_demo_tree.level1_key')
                ->on('employee_demo.level2_key', '=', 'employee_demo_tree.level2_key')
                ->where('employee_demo_tree.level', '=', 2);    
            });    
            
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('employee_demo_tree', function($join) use($level0, $level1, $level2, $level3) {
            $join->on('employee_demo.organization_key', '=', 'employee_demo_tree.organization_key')
                ->on('employee_demo.level1_key', '=', 'employee_demo_tree.level1_key')
                ->on('employee_demo.level2_key', '=', 'employee_demo_tree.level2_key')
                ->on('employee_demo.level3_key', '=', 'employee_demo_tree.level3_key')
                ->where('employee_demo_tree.level', '=', 3);    
            });
            
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('employee_demo_tree', function($join) use($level0, $level1, $level2, $level3, $level4) {
            $join->on('employee_demo.organization_key', '=', 'employee_demo_tree.organization_key')
                ->on('employee_demo.level1_key', '=', 'employee_demo_tree.level1_key')
                ->on('employee_demo.level2_key', '=', 'employee_demo_tree.level2_key')
                ->on('employee_demo.level3_key', '=', 'employee_demo_tree.level3_key')
                ->on('employee_demo.level4_key', '=', 'employee_demo_tree.level4_key')
                ->where('employee_demo_tree.level', '=', 4);
            });

        return  [$sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4];

    }

}
