<?php

namespace App\Http\Controllers\HRAdmin;



use App\Models\User;
use App\Models\EmployeeDemo;
use Illuminate\Http\Request;
use App\Models\NotificationLog;
use App\Models\OrganizationTree;
use Yajra\Datatables\Datatables;
use Illuminate\Support\Facades\DB;
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
                ->whereHas('recipients.recipient', function ($query) use($recipients) { 
                        $query->whereRaw("lower(name) like  '%". strtolower($recipients) . "%'"); 
                })
                ->when($alert_format, function ($query) use($alert_format) {
                        $query->where('alert_format', $alert_format);
                })
                ->select(['id', 'subject', 'recipients', 'alert_type', 'alert_format', 'description','date_sent','created_at'])
                ->with(['recipients']);


            return Datatables::of($notifications)
                ->addColumn('alert_type_name', function ($notification) {
                    return $notification->alert_type_name(); 
                })
                ->addColumn('alert_format_name', function ($notification) {
                     return $notification->alert_format_name(); 
                })
                ->addColumn('recipients', function ($notification) {
                    $userIds = $notification->recipients()->pluck('recipient_id')->toArray();
                    $users = User::whereIn('id', $userIds)->pluck('name');
                     return implode('; ', $users->toArray() );
                })
                ->addColumn('action', function ($notification) {

                    return '<a href="#" class="notification-modal btn btn-xs btn-primary" value="'. $notification->id .'"><i class="glyphicon glyphicon-envelope"></i>View</a>';
                })
                //->removeColumn('password')
                ->make(true);
        }

        $alert_format_list = NotificationLog::ALERT_FORMAT;

        return view('hradmin.notifications.index', compact('alert_format_list') );

    }

    public function show(Request $request) 
    {
        $notificationLog = NotificationLog::where('id', $request->notification_id)->first();

        if($request->ajax()){
            return view('hradmin.notifications.partials.show', compact('notificationLog') ); 
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

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                    ->groupBy('job_title')->pluck('job_title') : null;

        $request->session()->flash('level0', $level0);
        $request->session()->flash('level1', $level1);
        $request->session()->flash('level2', $level2);
        $request->session()->flash('level3', $level3);
        $request->session()->flash('level4', $level4);
        $request->session()->flash('job_titles', $job_titles);
        $request->session()->flash('userCheck', $request->userCheck);  // Dynamic load 
        

        // Matched Employees 
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4, $job_titles);
        $sql = clone $demoWhere; 
        $matched_emp_ids = $sql->select([ 'employee_id', 'employee_name', 'job_title', 'employee_email', 
                'employee_demo.organization', 'employee_demo.level1_program', 'employee_demo.level2_division',
                'employee_demo.level3_branch','employee_demo.level4', 'employee_demo.deptid'])
                ->orderBy('employee_id')
                ->pluck('employee_demo.employee_id');        
        
        $alert_format_list = NotificationLog::ALERT_FORMAT;
        $criteriaList = $this->search_criteria_list();
        

        return view('hradmin.notifications.notify', compact('alert_format_list', 'criteriaList','matched_emp_ids', 'old_selected_emp_ids') );
    
    }


    public function loadOrganizationTree(Request $request) {

        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                    ->groupBy('job_title')->pluck('job_title') : null;

        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4, $job_titles);

        $rows = $sql_level4->groupBy('organization_trees.id')->select('organization_trees.id')
            ->union( $sql_level3->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $sql_level2->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $sql_level1->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->union( $sql_level0->groupBy('organization_trees.id')->select('organization_trees.id') )
            ->pluck('organization_trees.id'); 

        $orgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();

        // Employee Count by Organization
        $countByOrg = $sql_level4->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row"))
        ->union( $sql_level3->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level2->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level1->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row")) )
        ->union( $sql_level0->groupBy('organization_trees.id')->select('organization_trees.id', DB::raw("COUNT(*) as count_row") ) )
        ->pluck('count_row', 'organization_trees.id');  
        
        // // Employee ID by Tree ID
        $empIdsByOrgId = [];
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4, $job_titles);
        $sql = clone $demoWhere; 
        $rows = $sql->join('organization_trees', function($join) use($request) {
                $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                    ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                    ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                    ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                    ->on('employee_demo.level4', '=', 'organization_trees.level4');
                })
                ->select('organization_trees.id','employee_demo.employee_id')
                ->groupBy('organization_trees.id', 'employee_demo.employee_id')
                ->orderBy('organization_trees.id')->orderBy('employee_demo.employee_id')
                ->get();

        $empIdsByOrgId = $rows->groupBy('id')->all();

        if($request->ajax()){
            return view('hradmin.notifications.partials.recipient-tree', compact('orgs','countByOrg','empIdsByOrgId') );
        } 

    }


    public function getDatatableEmployees(Request $request) {


        if($request->ajax()){

            $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
            $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
            $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
            $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
            $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
            $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                        ->groupBy('job_title')->pluck('job_title') : null;
    
            $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4, $job_titles);

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
            'sender_id'          => 'required',
            //'recipients'         => 'required',
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
            return redirect()->action([NotificationController::class, 'notify'] )
               ->withErrors($validator)->withInput();
            //return redirect()->to( route('hradmin.notifications.notify') )->withErrors($validator)->withInput();
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
        $sendMail->sender_id = $request->sender_id;
        $sendMail->useQueue = true;
        $sendMail->subject = $request->subject;
        $sendMail->body = $request->body;
        $sendMail->alertFormat = $request->alert_format;
        $success = $sendMail->sendMailWithoutGenericTemplate();
        if ($success) {
            return redirect()->route('hradmin.notifications.notify')
                ->with('success','Email with subject "' . $request->subject  . '" was successfully sent.');
        } else {
            return redirect()->back()
                ->with('failure','Error! Email with subject "' . $request->subject  . '" was failed to sent out.');
        }


    

    }

    public function getUsers(Request $request)
    {

        $search = $request->search;
        $users =  User::whereRaw("lower(name) like '%". strtolower($search)."%'")
                    ->whereNotNull('email')->paginate();

        return ['data'=> $users];
                  
    }


    public function getOrganizations(Request $request) {

        $orgs = OrganizationTree::orderby('name','asc')->select('id','name')
            ->where('level',0)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
            })
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getPrograms(Request $request) {

        $level0 = $request->level0 ? OrganizationTree::where('id',$request->level0)->first() : null;

        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',1)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name );
            })
            ->groupBy('name')
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getDivisions(Request $request) {

        $level0 = $request->level0 ? OrganizationTree::where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::where('id', $request->level1)->first() : null;

        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',2)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('level1_program', $level1->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();


        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getBranches(Request $request) {

        $level0 = $request->level0 ? OrganizationTree::where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::where('id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::where('id', $request->level2)->first() : null;

        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',3)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('level2_division', $level2->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
    } 

    public function getLevel4(Request $request) {

        $level0 = $request->level0 ? OrganizationTree::where('id', $request->level0)->first() : null;
        $level1 = $request->level1 ? OrganizationTree::where('id', $request->level1)->first() : null;
        $level2 = $request->level2 ? OrganizationTree::where('id', $request->level2)->first() : null;
        $level3 = $request->level3 ? OrganizationTree::where('id', $request->level3)->first() : null;

        $orgs = OrganizationTree::orderby('name','asc')->select(DB::raw('min(id) as id'),'name')
            ->where('level',4)
            ->when( $request->q , function ($q) use($request) {
                return $q->whereRaw("LOWER(name) LIKE '%" . strtolower($request->q) . "%'");
                })
            ->when( $level0 , function ($q) use($level0) {
                return $q->where('organization', $level0->name) ;
            })
            ->when( $level1 , function ($q) use($level1) {
                return $q->where('level1_program', $level1->name );
            })
            ->when( $level2 , function ($q) use($level2) {
                return $q->where('level2_division', $level2->name );
            })
            ->when( $level3 , function ($q) use($level3) {
                return $q->where('level3_branch', $level3->name );
            })
            ->groupBy('name')
            ->limit(300)
            ->get();

        $formatted_orgs = [];
        foreach ($orgs as $org) {
            $formatted_orgs[] = ['id' => $org->id, 'text' => $org->name ];
        }

        return response()->json($formatted_orgs);
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

    
        $level0 = $request->dd_level0 ? OrganizationTree::where('id', $request->dd_level0)->first() : null;
        $level1 = $request->dd_level1 ? OrganizationTree::where('id', $request->dd_level1)->first() : null;
        $level2 = $request->dd_level2 ? OrganizationTree::where('id', $request->dd_level2)->first() : null;
        $level3 = $request->dd_level3 ? OrganizationTree::where('id', $request->dd_level3)->first() : null;
        $level4 = $request->dd_level4 ? OrganizationTree::where('id', $request->dd_level4)->first() : null;
        $job_titles = $request->job_titles ? EmployeeDemo::whereIn('job_title', $request->job_titles)->select('job_title')
                    ->groupBy('job_title')->pluck('job_title') : null;

        list($sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4) = 
            $this->baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4, $job_titles);
       
        $rows = $sql_level4->where('organization_trees.id', $id)
            ->union( $sql_level3->where('organization_trees.id', $id) )
            ->union( $sql_level2->where('organization_trees.id', $id) )
            ->union( $sql_level1->where('organization_trees.id', $id) )
            ->union( $sql_level0->where('organization_trees.id', $id) );

        $employees = $rows->get();
        //$orgs = OrganizationTree::whereIn('id', $rows->toArray() )->get()->toTree();    

        // $org = OrganizationTree::where('id', $id)->first();
        // $employees = $org ? $org->employees() : [];
        $parent_id = $id;
        
        // if($request->ajax()){
            return view('hradmin.notifications.partials.employee', compact('parent_id', 'employees') ); 
        // } 
    }

    protected function search_criteria_list() {
        return [
            'all' => 'All',
            'emp' => 'Employee ID', 
            'name'=> 'Employee Name',
            'cls' => 'Classification', 
            'dpt' => 'Department ID'
        ];
    }

    protected function baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4, $job_titles) {
        // Base Where Clause
        $demoWhere = EmployeeDemo::whereNull('employee_demo.date_deleted')
        ->when( $level0, function ($q) use($level0) {
            return $q->where('employee_demo.organization', $level0->name);
        })
        ->when( $level1, function ($q) use($level1) {
            return $q->where('employee_demo.level1_program', $level1->name);
        })
        ->when( $level2, function ($q) use($level2) {
            return $q->where('employee_demo.level2_division', $level2->name);
        })
        ->when( $level3, function ($q) use($level3) {
            return $q->where('employee_demo.level3_branch', $level3->name);
        })
        ->when( $level4, function ($q) use($level4) {
            return $q->where('employee_demo.level4', $level4->name);
        })
        ->when( $job_titles , function ($q) use($job_titles) {
            return $q->whereIn('employee_demo.job_title', $job_titles->toArray() );
        })
        ->when( $request->active_since , function ($q) use($request) {
            return $q->where('employee_demo.hire_dt', '>=', $request->active_since);
        })
        ->when( $request->search_text && $request->criteria == 'all', function ($q) use($request) {
            $q->where(function($query) use ($request) {
                
                return $query->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->search_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.classification_group) LIKE '%" . strtolower($request->search_text) . "%'")
                    ->orWhereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->search_text) . "%'");
            });
        })
        ->when( $request->search_text && $request->criteria == 'emp', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_id) LIKE '%" . strtolower($request->search_text) . "%'");
        })
        ->when( $request->search_text && $request->criteria == 'name', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.employee_name) LIKE '%" . strtolower($request->search_text) . "%'");
        })
        ->when( $request->search_text && $request->criteria == 'cls', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.classification_group) LIKE '%" . strtolower($request->search_text) . "%'");
        })
        ->when( $request->search_text && $request->criteria == 'dpt', function ($q) use($request) {
            return $q->whereRaw("LOWER(employee_demo.deptid) LIKE '%" . strtolower($request->search_text) . "%'");
        });
        return $demoWhere;
    }


    protected function baseFilteredSQLs($request, $level0, $level1, $level2, $level3, $level4, $job_titles) {

        // Base Where Clause
        $demoWhere = $this->baseFilteredWhere($request, $level0, $level1, $level2, $level3, $level4, $job_titles);

        $sql_level0 = clone $demoWhere; 
        $sql_level0->join('organization_trees', function($join) use($level0) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->where('organization_trees.level', '=', 0);
            });
            
        $sql_level1 = clone $demoWhere; 
        $sql_level1->join('organization_trees', function($join) use($level0, $level1) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->where('organization_trees.level', '=', 1);
            });
            
        $sql_level2 = clone $demoWhere; 
        $sql_level2->join('organization_trees', function($join) use($level0, $level1, $level2) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->where('organization_trees.level', '=', 2);    
            });    
            
        $sql_level3 = clone $demoWhere; 
        $sql_level3->join('organization_trees', function($join) use($level0, $level1, $level2, $level3) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->where('organization_trees.level', '=', 3);    
            });
            
        $sql_level4 = clone $demoWhere; 
        $sql_level4->join('organization_trees', function($join) use($level0, $level1, $level2, $level3, $level4) {
            $join->on('employee_demo.organization', '=', 'organization_trees.organization')
                ->on('employee_demo.level1_program', '=', 'organization_trees.level1_program')
                ->on('employee_demo.level2_division', '=', 'organization_trees.level2_division')
                ->on('employee_demo.level3_branch', '=', 'organization_trees.level3_branch')
                ->on('employee_demo.level4', '=', 'organization_trees.level4')
                ->where('organization_trees.level', '=', 4);
            });

        return  [$sql_level0, $sql_level1, $sql_level2, $sql_level3, $sql_level4];

    }

}
